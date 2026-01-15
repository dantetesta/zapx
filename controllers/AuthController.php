<?php
/**
 * Controller de Autenticação
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 */

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    /**
     * Página de login
     */
    public function login() {
        // Se já estiver logado, redirecionar para dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard/index');
        }

        // Processar formulário de login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 🔒 SEGURANÇA: Validar token CSRF
            if (!CSRF::validateRequest()) {
                $this->view('auth/login', [
                    'error' => 'Token de segurança inválido. Por favor, tente novamente.'
                ]);
                return;
            }
            
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validação básica
            if (empty($email) || empty($password)) {
                $this->view('auth/login', [
                    'error' => 'Por favor, preencha todos os campos.'
                ]);
                return;
            }
            
            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->view('auth/login', [
                    'error' => 'Email inválido.'
                ]);
                return;
            }
            
            // 🔒 SEGURANÇA: Verificar Rate Limiting
            require_once __DIR__ . '/../core/RateLimit.php';
            $rateLimitStatus = RateLimit::check($email);
            
            if ($rateLimitStatus['blocked']) {
                $errorMessage = RateLimit::getErrorMessage($rateLimitStatus);
                error_log("🚨 Rate Limit: Bloqueio de login para {$email} - Nível {$rateLimitStatus['level']}");
                
                $this->view('auth/login', [
                    'error' => $errorMessage,
                    'email' => $email,
                    'rate_limit' => $rateLimitStatus
                ]);
                return;
            }

            // Validar reCAPTCHA se estiver ativado
            if (defined('RECAPTCHA_ENABLED') && RECAPTCHA_ENABLED) {
                $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
                
                if (empty($recaptchaResponse)) {
                    $this->view('auth/login', [
                        'error' => 'Por favor, complete o reCAPTCHA.',
                        'email' => $email
                    ]);
                    return;
                }
                
                // Verificar reCAPTCHA com Google
                $recaptchaValid = $this->verifyRecaptcha($recaptchaResponse);
                
                if (!$recaptchaValid) {
                    $this->view('auth/login', [
                        'error' => 'Verificação reCAPTCHA falhou. Tente novamente.',
                        'email' => $email
                    ]);
                    return;
                }
            }

            // Buscar usuário
            $user = $this->userModel->findByEmail($email);

            if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                // Login bem-sucedido
                
                // 🔒 SEGURANÇA: Resetar contador de tentativas
                RateLimit::record($email, true);
                
                // 🔒 SEGURANÇA: Regenerar ID da sessão para prevenir Session Fixation
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];

                $this->redirect('dashboard/index');
            } else {
                // Login falhou - Registrar tentativa
                RateLimit::record($email, false);
                
                // Verificar novamente o status após registrar
                $rateLimitStatus = RateLimit::check($email);
                $errorMessage = 'Email ou senha inválidos.';
                
                // Se atingiu o limite, mostrar aviso
                if ($rateLimitStatus['attempts'] >= 3 && !$rateLimitStatus['blocked']) {
                    $remaining = $rateLimitStatus['max_attempts'] - $rateLimitStatus['attempts'];
                    $errorMessage .= " Você tem mais {$remaining} tentativa(s) antes do bloqueio temporário.";
                }
                
                $this->view('auth/login', [
                    'error' => $errorMessage,
                    'email' => $email,
                    'rate_limit' => $rateLimitStatus
                ]);
            }
        } else {
            $this->view('auth/login');
        }
    }

    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        $this->redirect('auth/login');
    }

    /**
     * Página de registro (apenas para admin criar usuários)
     */
    public function register() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 🔒 SEGURANÇA: Validar token CSRF
            if (!CSRF::validateRequest()) {
                $this->view('auth/register', [
                    'user' => $this->getCurrentUser(),
                    'errors' => ['Token de segurança inválido. Por favor, tente novamente.']
                ]);
                return;
            }
            
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
            $messageLimit = intval($_POST['message_limit'] ?? 1000);
            $sendEmail = isset($_POST['send_email']) ? true : false;

            // Validações
            $errors = [];

            if (empty($name)) {
                $errors[] = 'Nome é obrigatório.';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email inválido.';
            }

            if (empty($password) || strlen($password) < 6) {
                $errors[] = 'Senha deve ter no mínimo 6 caracteres.';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'As senhas não coincidem.';
            }

            // Validar limite de mensagens
            if ($messageLimit < 100) {
                $errors[] = 'Limite de mensagens deve ser no mínimo 100.';
            }

            // Verificar se email já existe
            if ($this->userModel->findByEmail($email)) {
                $errors[] = 'Este email já está cadastrado.';
            }

            if (empty($errors)) {
                $userId = $this->userModel->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'is_admin' => $isAdmin,
                    'message_limit' => $messageLimit
                ]);

                if ($userId) {
                    // 📧 ENVIAR EMAIL COM CREDENCIAIS (se marcado)
                    if ($sendEmail) {
                        try {
                            require_once __DIR__ . '/../core/EmailHelper.php';
                            
                            // Timeout de 15 segundos para envio de email
                            set_time_limit(15);
                            
                            $loginUrl = APP_URL . '/auth/login';
                            error_log("📧 Iniciando envio de credenciais por email para: $email");
                            
                            $emailSent = EmailHelper::sendUserCredentials($name, $email, $password, $loginUrl);
                            
                            if ($emailSent) {
                                error_log("✅ Email de credenciais enviado com sucesso");
                                $this->redirect('users/index?success=1&email_sent=1');
                            } else {
                                error_log("⚠️ Falha ao enviar email (retornou false)");
                                $this->redirect('users/index?success=1&email_failed=1');
                            }
                        } catch (Exception $e) {
                            error_log("❌ Exceção ao tentar enviar email: " . $e->getMessage());
                            error_log("   Stack: " . $e->getTraceAsString());
                            $this->redirect('users/index?success=1&email_failed=1');
                        } catch (Error $e) {
                            error_log("❌ Erro fatal ao tentar enviar email: " . $e->getMessage());
                            error_log("   Stack: " . $e->getTraceAsString());
                            $this->redirect('users/index?success=1&email_failed=1');
                        }
                    } else {
                        $this->redirect('users/index?success=1');
                    }
                } else {
                    $errors[] = 'Erro ao criar usuário.';
                }
            }

            $this->view('auth/register', [
                'user' => $this->getCurrentUser(),
                'errors' => $errors,
                'name' => $name,
                'email' => $email,
                'is_admin' => $isAdmin,
                'message_limit' => $messageLimit
            ]);
        } else {
            $this->view('auth/register', [
                'user' => $this->getCurrentUser()
            ]);
        }
    }

    /**
     * Verificar reCAPTCHA com Google
     */
    private function verifyRecaptcha($response) {
        if (!defined('RECAPTCHA_SECRET_KEY') || empty(RECAPTCHA_SECRET_KEY)) {
            error_log('⚠️ reCAPTCHA: Secret key não configurada');
            return false;
        }

        $secretKey = RECAPTCHA_SECRET_KEY;
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        
        // Preparar dados para envio
        $data = [
            'secret' => $secretKey,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        // Fazer requisição para API do Google
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($verifyUrl, false, $context);
        
        if ($result === false) {
            error_log('❌ reCAPTCHA: Falha ao conectar com API do Google');
            return false;
        }
        
        $resultJson = json_decode($result, true);
        
        // Log para debug
        if (isset($resultJson['success']) && $resultJson['success']) {
            error_log('✅ reCAPTCHA: Validação bem-sucedida');
            return true;
        } else {
            $errors = isset($resultJson['error-codes']) ? implode(', ', $resultJson['error-codes']) : 'unknown';
            error_log('❌ reCAPTCHA: Validação falhou - ' . $errors);
            return false;
        }
    }
}
