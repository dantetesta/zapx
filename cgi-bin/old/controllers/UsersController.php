<?php
/**
 * Controller de Usuários
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 */

class UsersController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    /**
     * Listar usuários (apenas admin)
     */
    public function index() {
        $this->requireAdmin();

        $users = $this->userModel->getAll();
        $user = $this->getCurrentUser();
        
        // Buscar contagem de disparos para cada usuário
        $db = Database::getInstance()->getConnection();
        foreach ($users as &$userItem) {
            // Contar mensagens enviadas no mês atual
            $stmt = $db->prepare("
                SELECT COUNT(*) as total 
                FROM dispatch_history 
                WHERE user_id = ? 
                AND YEAR(sent_at) = YEAR(CURDATE()) 
                AND MONTH(sent_at) = MONTH(CURDATE())
            ");
            $stmt->execute([$userItem['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $userItem['messages_sent_this_month'] = $result['total'] ?? 0;
        }

        $this->view('users/index', [
            'user' => $user,
            'users' => $users
        ]);
    }

    /**
     * Perfil do usuário
     */
    public function profile() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userData = $this->userModel->findById($user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $defaultCountryCode = $_POST['default_country_code'] ?? '55';
            $timezone = $_POST['timezone'] ?? 'America/Sao_Paulo';

            $errors = [];

            // Validações
            if (empty($name)) {
                $errors[] = 'Nome é obrigatório.';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email inválido.';
            }

            // Se está tentando mudar a senha
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $errors[] = 'Senha atual é obrigatória para alterar a senha.';
                } elseif (!$this->userModel->verifyPassword($currentPassword, $userData['password'])) {
                    $errors[] = 'Senha atual incorreta.';
                } elseif (strlen($newPassword) < 6) {
                    $errors[] = 'Nova senha deve ter no mínimo 6 caracteres.';
                } elseif ($newPassword !== $confirmPassword) {
                    $errors[] = 'As senhas não coincidem.';
                }
            }

            if (empty($errors)) {
                $updateData = [
                    'name' => $name,
                    'email' => $email,
                    'default_country_code' => $defaultCountryCode,
                    'timezone' => $timezone
                ];

                if (!empty($newPassword)) {
                    $updateData['password'] = $newPassword;
                }

                $success = $this->userModel->update($user['id'], $updateData);

                if ($success) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    $userData = $this->userModel->findById($user['id']);
                    $this->view('users/profile', [
                        'user' => $this->getCurrentUser(),
                        'userData' => $userData,
                        'success' => 'Perfil atualizado com sucesso!'
                    ]);
                    return;
                } else {
                    $errors[] = 'Erro ao atualizar perfil.';
                }
            }

            $this->view('users/profile', [
                'user' => $user,
                'userData' => $userData,
                'errors' => $errors
            ]);
        } else {
            $this->view('users/profile', [
                'user' => $user,
                'userData' => $userData
            ]);
        }
    }

    /**
     * Editar usuário (apenas admin)
     */
    public function edit($id) {
        $this->requireAdmin();

        $userData = $this->userModel->findById($id);
        
        if (!$userData) {
            $this->redirect('users/index');
            return;
        }

        $this->view('users/edit', [
            'user' => $this->getCurrentUser(),
            'userData' => $userData
        ]);
    }

    /**
     * Atualizar usuário (apenas admin)
     */
    public function update($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users/edit/' . $id);
            return;
        }

        $userData = $this->userModel->findById($id);
        
        if (!$userData) {
            $this->redirect('users/index');
            return;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        $messageLimit = intval($_POST['message_limit'] ?? 1000);

        $errors = [];

        // Validações
        if (empty($name)) {
            $errors[] = 'Nome é obrigatório.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido.';
        }

        // Verificar se email já existe (exceto para o próprio usuário)
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser && $existingUser['id'] != $id) {
            $errors[] = 'Este email já está cadastrado.';
        }

        // Validar senha se foi informada
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors[] = 'Senha deve ter no mínimo 6 caracteres.';
            }
            if ($password !== $confirmPassword) {
                $errors[] = 'As senhas não coincidem.';
            }
        }

        // Validar limite de mensagens
        if ($messageLimit < 100) {
            $errors[] = 'Limite de mensagens deve ser no mínimo 100.';
        }

        if (empty($errors)) {
            $updateData = [
                'name' => $name,
                'email' => $email,
                'is_admin' => $isAdmin,
                'message_limit' => $messageLimit
            ];

            // Adicionar senha apenas se foi informada
            if (!empty($password)) {
                $updateData['password'] = $password;
            }

            $success = $this->userModel->update($id, $updateData);

            if ($success) {
                $userData = $this->userModel->findById($id);
                $this->view('users/edit', [
                    'user' => $this->getCurrentUser(),
                    'userData' => $userData,
                    'success' => true
                ]);
                return;
            } else {
                $errors[] = 'Erro ao atualizar usuário.';
            }
        }

        $userData = $this->userModel->findById($id);
        $this->view('users/edit', [
            'user' => $this->getCurrentUser(),
            'userData' => $userData,
            'errors' => $errors
        ]);
    }

    /**
     * Resetar contador de mensagens (apenas admin)
     */
    public function resetMessageCount($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $success = $this->userModel->resetMessageCount($id);

        if ($success) {
            $this->json(['success' => true, 'message' => 'Contador resetado com sucesso!']);
        } else {
            $this->json(['success' => false, 'message' => 'Erro ao resetar contador.'], 500);
        }
    }

    /**
     * Deletar usuário (apenas admin)
     */
    public function delete($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentUser = $this->getCurrentUser();

            // Não pode deletar a si mesmo
            if ($id == $currentUser['id']) {
                $this->json(['success' => false, 'message' => 'Você não pode deletar sua própria conta.'], 400);
                return;
            }

            $success = $this->userModel->delete($id);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Usuário deletado com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao deletar usuário.'], 500);
            }
        }
    }

    /**
     * Testar conexão com Evolution API
     */
    public function testEvolutionAPI() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $url = $_POST['url'] ?? '';
        $token = $_POST['token'] ?? '';
        $instance = $_POST['instance'] ?? '';

        if (empty($url) || empty($token) || empty($instance)) {
            $this->json(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
            return;
        }

        try {
            $result = $this->testEvolutionConnection($url, $token, $instance);
            $this->json($result);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    /**
     * Obter QR Code da instância
     */
    public function getQRCode() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $url = $_POST['url'] ?? '';
        $token = $_POST['token'] ?? '';
        $instance = $_POST['instance'] ?? '';

        if (empty($url) || empty($token) || empty($instance)) {
            $this->json(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
            return;
        }

        try {
            $result = $this->getInstanceQRCode($url, $token, $instance);
            $this->json($result);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    /**
     * Testar conexão com a Evolution API
     */
    private function testEvolutionConnection($apiUrl, $token, $instance) {
        // Limpar URL
        $apiUrl = rtrim($apiUrl, '/');
        
        // Endpoint para verificar status da instância
        $endpoint = $apiUrl . '/instance/connectionState/' . $instance;

        // Configurar cURL
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'Erro de conexão: ' . $error];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'message' => "Erro HTTP $httpCode. Verifique URL e token."];
        }

        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Resposta inválida da API'];
        }

        // Verificar se a instância existe e está funcionando
        if (isset($data['instance'])) {
            return [
                'success' => true, 
                'message' => 'Conexão estabelecida com sucesso!',
                'status' => $data['instance']['state'] ?? 'unknown'
            ];
        }

        return ['success' => false, 'message' => 'Instância não encontrada ou inativa'];
    }

    /**
     * Obter QR Code da instância
     */
    private function getInstanceQRCode($apiUrl, $token, $instance) {
        // Limpar URL
        $apiUrl = rtrim($apiUrl, '/');
        
        // Endpoint para obter QR Code
        $endpoint = $apiUrl . '/instance/connect/' . $instance;

        // Configurar cURL
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'Erro de conexão: ' . $error];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'message' => "Erro HTTP $httpCode. Verifique credenciais."];
        }

        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Resposta inválida da API'];
        }

        // Verificar se tem QR Code
        if (isset($data['qrcode']) && !empty($data['qrcode'])) {
            return [
                'success' => true,
                'qrcode' => $data['qrcode'],
                'message' => 'QR Code gerado com sucesso'
            ];
        }

        // Se não tem QR Code, pode estar já conectado
        if (isset($data['instance']['state']) && $data['instance']['state'] === 'open') {
            return [
                'success' => false,
                'message' => 'WhatsApp já está conectado! Não é necessário escanear QR Code.'
            ];
        }

        return ['success' => false, 'message' => 'QR Code não disponível. Tente reconectar a instância.'];
    }
}
