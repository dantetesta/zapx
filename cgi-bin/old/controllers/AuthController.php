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
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validação básica
            if (empty($email) || empty($password)) {
                $this->view('auth/login', [
                    'error' => 'Por favor, preencha todos os campos.'
                ]);
                return;
            }

            // Buscar usuário
            $user = $this->userModel->findByEmail($email);

            if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                // Login bem-sucedido
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];

                $this->redirect('dashboard/index');
            } else {
                $this->view('auth/login', [
                    'error' => 'Email ou senha inválidos.',
                    'email' => $email
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
                'errors' => $errors,
                'name' => $name,
                'email' => $email,
                'is_admin' => $isAdmin,
                'message_limit' => $messageLimit
            ]);
        } else {
            $this->view('auth/register');
        }
    }
}
