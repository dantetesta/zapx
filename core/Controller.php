<?php
/**
 * Classe Base para Controllers
 * 
 * @author Dante Testa - https://dantetesta.com.br
 * @date 2025-10-26 07:56:00
 * @version 2.0.0
 * @updated Sistema anti-cache integrado
 */

class Controller {
    
    public function __construct() {
        // Aplicar headers anti-cache em TODAS as páginas
        AntiCache::setHeaders();
        
        // Carregar helper CSRF
        require_once __DIR__ . '/CSRF.php';
    }
    /**
     * Carregar view
     */
    protected function view($view, $data = []) {
        extract($data);
        
        $viewFile = 'views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View não encontrada: " . $view);
        }
    }

    /**
     * Carregar model
     */
    protected function model($model) {
        $modelFile = 'models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            die("Model não encontrado: " . $model);
        }
    }

    /**
     * Redirecionar
     */
    protected function redirect($url) {
        header('Location: ' . APP_URL . '/' . $url);
        exit;
    }

    /**
     * Retornar JSON com anti-cache
     */
    protected function json($data, $statusCode = 200) {
        // Limpar qualquer output anterior
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code($statusCode);
        
        // Aplicar headers anti-cache JSON
        AntiCache::setJsonHeaders();
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Verificar se está logado
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Verificar se é admin
     */
    protected function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }

    /**
     * Requerer autenticação
     */
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }

    /**
     * Requerer admin
     */
    protected function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            $this->redirect('dashboard/index');
        }
    }

    /**
     * Obter usuário logado
     */
    protected function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'is_admin' => $_SESSION['is_admin']
            ];
        }
        return null;
    }
}
