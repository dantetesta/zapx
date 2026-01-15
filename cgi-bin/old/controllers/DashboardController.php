<?php
/**
 * Controller do Dashboard
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 */

class DashboardController extends Controller {
    private $contactModel;
    private $tagModel;
    private $dispatchModel;
    private $userModel;

    public function __construct() {
        $this->contactModel = $this->model('Contact');
        $this->tagModel = $this->model('Tag');
        $this->dispatchModel = $this->model('DispatchHistory');
        $this->userModel = $this->model('User');
    }

    /**
     * Página inicial do dashboard
     */
    public function index() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        // Obter estatísticas
        $stats = [
            'contacts' => $this->contactModel->countByUser($userId),
            'tags' => $this->tagModel->countByUser($userId),
            'dispatches' => $this->dispatchModel->getStats($userId)
        ];

        // Obter saldo de mensagens
        $messageBalance = $this->userModel->getMessageBalance($userId);

        // Obter últimos disparos
        $recentDispatches = $this->dispatchModel->getByUser($userId, 10);

        $this->view('dashboard/index', [
            'user' => $user,
            'stats' => $stats,
            'messageBalance' => $messageBalance,
            'recentDispatches' => $recentDispatches
        ]);
    }
}
