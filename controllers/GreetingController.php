<?php
/**
 * Controller de Saudações Personalizadas
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-15 18:32:00
 */

class GreetingController extends Controller {
    private $greetingModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Greeting.php';
        $this->greetingModel = new Greeting();
    }

    // Listar saudações do usuário
    public function index() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $greetings = $this->greetingModel->getAllByUser($user['id']);
        
        // Se não tiver saudações, criar as padrão
        if (empty($greetings)) {
            $this->greetingModel->createDefaultsForUser($user['id']);
            $greetings = $this->greetingModel->getAllByUser($user['id']);
        }
        
        $this->view('greeting/index', [
            'user' => $user,
            'greetings' => $greetings
        ]);
    }

    // Criar nova saudação (POST)
    public function store() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        $user = $this->getCurrentUser();
        $template = trim($_POST['template'] ?? '');
        
        if (empty($template)) {
            $this->json(['success' => false, 'message' => 'Template não pode ser vazio'], 400);
            return;
        }
        
        if (strlen($template) > 500) {
            $this->json(['success' => false, 'message' => 'Template muito longo (máx 500 caracteres)'], 400);
            return;
        }
        
        $this->greetingModel->create($user['id'], $template);
        
        $this->json([
            'success' => true,
            'message' => 'Saudação criada com sucesso!'
        ]);
    }

    // Atualizar saudação (POST)
    public function update($id = null) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $template = trim($_POST['template'] ?? '');
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        
        if (empty($template)) {
            $this->json(['success' => false, 'message' => 'Template não pode ser vazio'], 400);
            return;
        }
        
        $greeting = $this->greetingModel->findById($id, $user['id']);
        if (!$greeting) {
            $this->json(['success' => false, 'message' => 'Saudação não encontrada'], 404);
            return;
        }
        
        $this->greetingModel->update($id, $user['id'], $template, $isActive);
        
        $this->json([
            'success' => true,
            'message' => 'Saudação atualizada!'
        ]);
    }

    // Deletar saudação (POST)
    public function delete($id = null) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        
        $greeting = $this->greetingModel->findById($id, $user['id']);
        if (!$greeting) {
            $this->json(['success' => false, 'message' => 'Saudação não encontrada'], 404);
            return;
        }
        
        $this->greetingModel->delete($id, $user['id']);
        
        $this->json([
            'success' => true,
            'message' => 'Saudação removida!'
        ]);
    }

    // Ativar/Desativar saudação (POST)
    public function toggle($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        
        $greeting = $this->greetingModel->findById($id, $user['id']);
        if (!$greeting) {
            $this->json(['success' => false, 'message' => 'Saudação não encontrada'], 404);
            return;
        }
        
        $this->greetingModel->toggleActive($id, $user['id']);
        
        $this->json([
            'success' => true,
            'message' => 'Status alterado!'
        ]);
    }

    // Reordenar saudações (POST)
    public function reorder() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        $user = $this->getCurrentUser();
        $orderedIds = $_POST['order'] ?? [];
        
        if (empty($orderedIds) || !is_array($orderedIds)) {
            $this->json(['success' => false, 'message' => 'Ordem inválida'], 400);
            return;
        }
        
        $this->greetingModel->reorder($user['id'], $orderedIds);
        
        $this->json([
            'success' => true,
            'message' => 'Ordem atualizada!'
        ]);
    }

    // Resetar para saudações padrão (POST)
    public function reset() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        $user = $this->getCurrentUser();
        
        // Deletar todas as saudações atuais
        $sql = "DELETE FROM user_greetings WHERE user_id = :user_id";
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([':user_id' => $user['id']]);
        
        // Criar saudações padrão
        $this->greetingModel->createDefaultsForUser($user['id']);
        
        $this->json([
            'success' => true,
            'message' => 'Saudações restauradas para o padrão!'
        ]);
    }

    // Preview de saudação (GET)
    public function preview() {
        $this->requireAuth();
        
        $template = $_GET['template'] ?? '{periodo}, {nome}, tudo bem?';
        $name = $_GET['name'] ?? 'João';
        $phone = $_GET['phone'] ?? '5511999999999';
        
        $result = $this->greetingModel->processGreeting($template, $name, $phone);
        
        $this->json([
            'success' => true,
            'preview' => $result
        ]);
    }
}
