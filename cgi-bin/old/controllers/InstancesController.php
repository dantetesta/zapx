<?php
/**
 * Controller de Gerenciamento de Instâncias Evolution API
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 15:00:00
 */

class InstancesController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    /**
     * Listar todas as instâncias (apenas admin)
     */
    public function index() {
        $this->requireAdmin();

        $user = $this->getCurrentUser();
        
        // Buscar todos os usuários com instâncias
        $users = $this->userModel->getAll();
        
        // Filtrar apenas usuários com instância criada
        $instances = array_filter($users, function($u) {
            return !empty($u['evolution_instance']);
        });

        $this->view('instances/index', [
            'user' => $user,
            'instances' => $instances
        ]);
    }

    /**
     * Obter status da instância via API
     */
    public function getStatus() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $userId = $_POST['user_id'] ?? '';

        if (empty($userId)) {
            $this->json(['success' => false, 'message' => 'ID do usuário é obrigatório']);
            return;
        }

        $userData = $this->userModel->findById($userId);

        if (!$userData || empty($userData['evolution_instance'])) {
            $this->json(['success' => false, 'message' => 'Instância não encontrada']);
            return;
        }

        try {
            $result = $this->checkInstanceStatus(
                EVOLUTION_API_URL,
                EVOLUTION_API_KEY,
                $userData['evolution_instance']
            );
            $this->json($result);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    /**
     * Gerar QR Code da instância
     */
    public function generateQRCode() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $userId = $_POST['user_id'] ?? '';

        if (empty($userId)) {
            $this->json(['success' => false, 'message' => 'ID do usuário é obrigatório']);
            return;
        }

        $userData = $this->userModel->findById($userId);

        if (!$userData || empty($userData['evolution_instance'])) {
            $this->json(['success' => false, 'message' => 'Instância não encontrada']);
            return;
        }

        try {
            $result = $this->getInstanceQRCode(
                EVOLUTION_API_URL,
                EVOLUTION_API_KEY,
                $userData['evolution_instance']
            );
            $this->json($result);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    /**
     * Desconectar instância
     */
    public function disconnect() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $userId = $_POST['user_id'] ?? '';

        if (empty($userId)) {
            $this->json(['success' => false, 'message' => 'ID do usuário é obrigatório']);
            return;
        }

        $userData = $this->userModel->findById($userId);

        if (!$userData || empty($userData['evolution_instance'])) {
            $this->json(['success' => false, 'message' => 'Instância não encontrada']);
            return;
        }

        try {
            $result = $this->disconnectInstance(
                EVOLUTION_API_URL,
                EVOLUTION_API_KEY,
                $userData['evolution_instance']
            );

            if ($result['success']) {
                // Atualizar status no banco
                $updateResult = $this->userModel->updateEvolutionConfig($userId, [
                    'evolution_status' => 'close',
                    'evolution_qrcode' => null,
                    'evolution_phone_number' => null
                ]);

                if (!$updateResult) {
                    error_log("ZAPX: Falha ao atualizar status de desconexão. UserID: $userId");
                }
            }

            $this->json($result);
        } catch (Exception $e) {
            error_log("ZAPX: Erro ao desconectar instância. UserID: $userId, Erro: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    /**
     * Deletar instância
     */
    public function delete() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $userId = $_POST['user_id'] ?? '';

        if (empty($userId)) {
            $this->json(['success' => false, 'message' => 'ID do usuário é obrigatório']);
            return;
        }

        $userData = $this->userModel->findById($userId);

        if (!$userData || empty($userData['evolution_instance'])) {
            $this->json(['success' => false, 'message' => 'Instância não encontrada']);
            return;
        }

        try {
            // Deletar na API Evolution
            $result = $this->deleteInstance(
                EVOLUTION_API_URL,
                EVOLUTION_API_KEY,
                $userData['evolution_instance']
            );

            // Sempre limpar no banco, mesmo se falhar na API
            // (a instância pode já ter sido deletada manualmente)
            $updateResult = $this->userModel->updateEvolutionConfig($userId, [
                'evolution_instance' => null,
                'evolution_instance_token' => null,
                'evolution_status' => null,
                'evolution_qrcode' => null,
                'evolution_phone_number' => null,
                'evolution_created_at' => null
            ]);

            if (!$updateResult) {
                error_log("ZAPX: Falha ao limpar dados da instância no banco. UserID: $userId");
                $this->json(['success' => false, 'message' => 'Erro ao atualizar banco de dados']);
                return;
            }

            // Se chegou aqui, o banco foi atualizado com sucesso
            $this->json([
                'success' => true, 
                'message' => 'Instância removida com sucesso',
                'api_result' => $result
            ]);
        } catch (Exception $e) {
            error_log("ZAPX: Erro ao deletar instância. UserID: $userId, Erro: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    /**
     * Reiniciar instância
     */
    public function restart() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $userId = $_POST['user_id'] ?? '';

        if (empty($userId)) {
            $this->json(['success' => false, 'message' => 'ID do usuário é obrigatório']);
            return;
        }

        $userData = $this->userModel->findById($userId);

        if (!$userData || empty($userData['evolution_instance'])) {
            $this->json(['success' => false, 'message' => 'Instância não encontrada']);
            return;
        }

        try {
            $result = $this->restartInstance(
                EVOLUTION_API_URL,
                EVOLUTION_API_KEY,
                $userData['evolution_instance']
            );
            $this->json($result);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    /**
     * Verificar status da instância
     */
    private function checkInstanceStatus($apiUrl, $apiKey, $instance) {
        $endpoint = rtrim($apiUrl, '/') . '/instance/connectionState/' . $instance;

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $apiKey
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
            return ['success' => false, 'message' => "Erro HTTP $httpCode"];
        }

        $data = json_decode($response, true);
        
        return [
            'success' => true,
            'status' => $data['instance']['state'] ?? 'unknown',
            'data' => $data
        ];
    }

    /**
     * Obter QR Code
     */
    private function getInstanceQRCode($apiUrl, $apiKey, $instance) {
        $endpoint = rtrim($apiUrl, '/') . '/instance/connect/' . $instance;

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'message' => "Erro HTTP $httpCode"];
        }

        $data = json_decode($response, true);

        if (isset($data['qrcode'])) {
            return [
                'success' => true,
                'qrcode' => $data['qrcode'],
                'message' => 'QR Code gerado'
            ];
        }

        if (isset($data['instance']['state']) && $data['instance']['state'] === 'open') {
            return ['success' => false, 'message' => 'WhatsApp já conectado'];
        }

        return ['success' => false, 'message' => 'QR Code não disponível'];
    }

    /**
     * Desconectar instância
     */
    private function disconnectInstance($apiUrl, $apiKey, $instance) {
        $endpoint = rtrim($apiUrl, '/') . '/instance/logout/' . $instance;

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            return ['success' => true, 'message' => 'Instância desconectada com sucesso'];
        }

        return ['success' => false, 'message' => "Erro ao desconectar: HTTP $httpCode"];
    }

    /**
     * Deletar instância
     */
    private function deleteInstance($apiUrl, $apiKey, $instance) {
        $endpoint = rtrim($apiUrl, '/') . '/instance/delete/' . $instance;

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            return ['success' => true, 'message' => 'Instância deletada com sucesso'];
        }

        return ['success' => false, 'message' => "Erro ao deletar: HTTP $httpCode"];
    }

    /**
     * Reiniciar instância
     */
    private function restartInstance($apiUrl, $apiKey, $instance) {
        $endpoint = rtrim($apiUrl, '/') . '/instance/restart/' . $instance;

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            return ['success' => true, 'message' => 'Instância reiniciada com sucesso'];
        }

        return ['success' => false, 'message' => "Erro ao reiniciar: HTTP $httpCode"];
    }
}
