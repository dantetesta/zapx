<?php
/**
 * Controller de Campanhas de Disparo
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-14 23:10:00
 * 
 * Gerencia campanhas de disparo em massa com processamento back-end
 */

class CampaignController extends Controller {
    private $campaignModel;
    private $queueModel;
    private $contactModel;
    private $tagModel;
    private $userModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Campaign.php';
        require_once __DIR__ . '/../models/Queue.php';
        
        $this->campaignModel = new Campaign();
        $this->queueModel = new Queue();
        $this->contactModel = $this->model('Contact');
        $this->tagModel = $this->model('Tag');
        $this->userModel = $this->model('User');
    }

    /**
     * Listar campanhas do usuário
     */
    public function index() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $campaigns = $this->campaignModel->getByUser($user['id']);
        
        $this->view('campaign/index', [
            'user' => $user,
            'campaigns' => $campaigns
        ]);
    }

    /**
     * Página de criar nova campanha
     */
    public function create() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $tags = $this->tagModel->getByUser($user['id']);
        $contacts = $this->contactModel->getByUser($user['id']);
        
        // Contar contatos por tag
        $tagCounts = [];
        foreach ($tags as $tag) {
            $tagContacts = $this->contactModel->getByUser($user['id'], null, $tag['id']);
            $tagCounts[$tag['id']] = count($tagContacts);
        }
        
        $this->view('campaign/create', [
            'user' => $user,
            'tags' => $tags,
            'contacts' => $contacts,
            'tagCounts' => $tagCounts,
            'totalContacts' => count($contacts)
        ]);
    }

    /**
     * Salvar e iniciar campanha (POST)
     */
    public function store() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        $user = $this->getCurrentUser();
        $userId = $user['id'];
        
        // Validar dados
        $message = trim($_POST['message'] ?? '');
        $dispatchType = $_POST['dispatch_type'] ?? 'all';
        $tagId = $_POST['tag_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $mediaType = $_POST['media_type'] ?? 'text';
        
        if (empty($message) && $mediaType === 'text') {
            $this->json(['success' => false, 'message' => 'Digite uma mensagem'], 400);
            return;
        }
        
        // Obter contatos
        if ($dispatchType === 'tag' && $tagId) {
            $contacts = $this->contactModel->getByUser($userId, null, $tagId);
        } else {
            $contacts = $this->contactModel->getByUser($userId);
            $tagId = null;
        }
        
        if (empty($contacts)) {
            $this->json(['success' => false, 'message' => 'Nenhum contato encontrado'], 400);
            return;
        }
        
        // Processar upload de mídia se houver
        $mediaPath = null;
        $mediaFilename = null;
        
        if ($mediaType !== 'text' && isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->processMediaUpload($_FILES['media_file'], $mediaType);
            if (!$uploadResult['success']) {
                $this->json(['success' => false, 'message' => $uploadResult['message']], 400);
                return;
            }
            $mediaPath = $uploadResult['filepath'];
            $mediaFilename = $uploadResult['filename'];
        }
        
        // Criar campanha
        $campaignId = $this->campaignModel->create([
            'user_id' => $userId,
            'name' => $name ?: 'Campanha ' . date('d/m/Y H:i'),
            'message' => $message,
            'media_type' => $mediaType,
            'media_path' => $mediaPath,
            'media_filename' => $mediaFilename,
            'tag_id' => $tagId,
            'total_contacts' => count($contacts),
            'min_interval' => DISPATCH_MIN_INTERVAL,
            'max_interval' => DISPATCH_MAX_INTERVAL
        ]);
        
        if (!$campaignId) {
            $this->json(['success' => false, 'message' => 'Erro ao criar campanha'], 500);
            return;
        }
        
        // Popular fila com contatos
        $contactIds = array_column($contacts, 'id');
        $this->queueModel->addBatch($campaignId, $contactIds);
        
        // Iniciar campanha automaticamente
        $this->campaignModel->updateStatus($campaignId, 'running');
        
        $this->json([
            'success' => true,
            'message' => 'Campanha criada e iniciada!',
            'campaign_id' => $campaignId,
            'total_contacts' => count($contacts),
            'redirect' => APP_URL . '/campaign/monitor/' . $campaignId
        ]);
    }

    /**
     * Página de monitoramento da campanha
     */
    public function monitor($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->redirect('/campaign');
            return;
        }
        
        $user = $this->getCurrentUser();
        $campaign = $this->campaignModel->findById($id, $user['id']);
        
        if (!$campaign) {
            $this->redirect('/campaign');
            return;
        }
        
        $queueItems = $this->queueModel->getItems($id, 500);
        $stats = $this->queueModel->countByStatus($id);
        
        $this->view('campaign/monitor', [
            'user' => $user,
            'campaign' => $campaign,
            'queueItems' => $queueItems,
            'stats' => $stats
        ]);
    }

    /**
     * API: Obter status da campanha (polling)
     */
    public function status($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $stats = $this->campaignModel->getStats($id);
        
        if (!$stats || $stats['user_id'] != $user['id']) {
            $this->json(['success' => false, 'message' => 'Campanha não encontrada'], 404);
            return;
        }
        
        // Calcular progresso
        $total = $stats['total_contacts'];
        $processed = $stats['sent_count'] + $stats['failed_count'];
        $progress = $total > 0 ? round(($processed / $total) * 100, 1) : 0;
        
        $this->json([
            'success' => true,
            'campaign' => [
                'id' => $stats['id'],
                'name' => $stats['name'],
                'status' => $stats['status'],
                'total' => $total,
                'sent' => $stats['sent_count'],
                'failed' => $stats['failed_count'],
                'pending' => $stats['pending_queue'],
                'processing' => $stats['processing_queue'],
                'progress' => $progress,
                'started_at' => $stats['started_at'],
                'last_processed_at' => $stats['last_processed_at']
            ]
        ]);
    }

    /**
     * API: Obter itens da fila
     */
    public function queueItems($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $campaign = $this->campaignModel->findById($id, $user['id']);
        
        if (!$campaign) {
            $this->json(['success' => false, 'message' => 'Campanha não encontrada'], 404);
            return;
        }
        
        $items = $this->queueModel->getItems($id, 100);
        
        $this->json([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * API: Pausar campanha
     */
    public function pause($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $campaign = $this->campaignModel->findById($id, $user['id']);
        
        if (!$campaign) {
            $this->json(['success' => false, 'message' => 'Campanha não encontrada'], 404);
            return;
        }
        
        if ($campaign['status'] !== 'running') {
            $this->json(['success' => false, 'message' => 'Campanha não está em execução'], 400);
            return;
        }
        
        $this->campaignModel->updateStatus($id, 'paused');
        
        $this->json([
            'success' => true,
            'message' => 'Campanha pausada'
        ]);
    }

    /**
     * API: Retomar campanha
     */
    public function resume($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $campaign = $this->campaignModel->findById($id, $user['id']);
        
        if (!$campaign) {
            $this->json(['success' => false, 'message' => 'Campanha não encontrada'], 404);
            return;
        }
        
        if ($campaign['status'] !== 'paused') {
            $this->json(['success' => false, 'message' => 'Campanha não está pausada'], 400);
            return;
        }
        
        $this->campaignModel->updateStatus($id, 'running');
        
        $this->json([
            'success' => true,
            'message' => 'Campanha retomada'
        ]);
    }

    /**
     * API: Cancelar campanha
     */
    public function cancel($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $campaign = $this->campaignModel->findById($id, $user['id']);
        
        if (!$campaign) {
            $this->json(['success' => false, 'message' => 'Campanha não encontrada'], 404);
            return;
        }
        
        if (!in_array($campaign['status'], ['running', 'paused', 'pending'])) {
            $this->json(['success' => false, 'message' => 'Campanha não pode ser cancelada'], 400);
            return;
        }
        
        // Cancelar itens pendentes na fila
        $this->queueModel->cancelPending($id);
        
        // Atualizar status da campanha
        $this->campaignModel->updateStatus($id, 'cancelled');
        
        $this->json([
            'success' => true,
            'message' => 'Campanha cancelada'
        ]);
    }

    /**
     * API: Processar próximo item (fallback AJAX se cron não funcionar)
     */
    public function process($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $campaign = $this->campaignModel->findById($id, $user['id']);
        
        if (!$campaign) {
            $this->json(['success' => false, 'message' => 'Campanha não encontrada'], 404);
            return;
        }
        
        if ($campaign['status'] !== 'running') {
            $this->json(['success' => false, 'message' => 'Campanha não está em execução'], 400);
            return;
        }
        
        // Carregar processor e processar
        require_once __DIR__ . '/../core/QueueProcessor.php';
        $processor = new QueueProcessor();
        $result = $processor->processCampaign($id);
        
        $this->json($result);
    }

    /**
     * Deletar campanha
     */
    public function delete($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $campaign = $this->campaignModel->findById($id, $user['id']);
        
        if (!$campaign) {
            $this->json(['success' => false, 'message' => 'Campanha não encontrada'], 404);
            return;
        }
        
        // Não permitir deletar campanha em execução
        if ($campaign['status'] === 'running') {
            $this->json(['success' => false, 'message' => 'Pause ou cancele a campanha antes de deletar'], 400);
            return;
        }
        
        // Deletar mídia se existir
        if (!empty($campaign['media_path']) && file_exists($campaign['media_path'])) {
            unlink($campaign['media_path']);
        }
        
        $this->campaignModel->delete($id, $user['id']);
        
        $this->json([
            'success' => true,
            'message' => 'Campanha deletada'
        ]);
    }

    /**
     * Página de relatórios de campanhas
     */
    public function reports() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $userId = $user['id'];
        
        // Buscar todas as campanhas do usuário
        $campaigns = $this->campaignModel->getByUser($userId);
        
        // Estatísticas gerais
        $totalCampaigns = count($campaigns);
        $totalSent = 0;
        $totalFailed = 0;
        $totalContacts = 0;
        $completedCampaigns = 0;
        $runningCampaigns = 0;
        
        foreach ($campaigns as $campaign) {
            $totalSent += $campaign['sent_count'];
            $totalFailed += $campaign['failed_count'];
            $totalContacts += $campaign['total_contacts'];
            
            if ($campaign['status'] === 'completed') {
                $completedCampaigns++;
            }
            if ($campaign['status'] === 'running') {
                $runningCampaigns++;
            }
        }
        
        // Taxa de sucesso
        $successRate = ($totalSent + $totalFailed) > 0 
            ? round(($totalSent / ($totalSent + $totalFailed)) * 100, 1) 
            : 0;
        
        // Buscar detalhes da fila para campanhas recentes
        $recentCampaigns = array_slice($campaigns, 0, 10);
        foreach ($recentCampaigns as &$campaign) {
            $campaign['queue_stats'] = $this->queueModel->countByStatus($campaign['id']);
        }
        
        $this->view('campaign/reports', [
            'user' => $user,
            'campaigns' => $campaigns,
            'recentCampaigns' => $recentCampaigns,
            'stats' => [
                'totalCampaigns' => $totalCampaigns,
                'totalSent' => $totalSent,
                'totalFailed' => $totalFailed,
                'totalContacts' => $totalContacts,
                'completedCampaigns' => $completedCampaigns,
                'runningCampaigns' => $runningCampaigns,
                'successRate' => $successRate
            ]
        ]);
    }

    /**
     * API: Detalhes de uma campanha para relatório
     */
    public function reportDetail($id = null) {
        $this->requireAuth();
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }
        
        $user = $this->getCurrentUser();
        $campaign = $this->campaignModel->findById($id, $user['id']);
        
        if (!$campaign) {
            $this->json(['success' => false, 'message' => 'Campanha não encontrada'], 404);
            return;
        }
        
        // Buscar itens da fila com detalhes
        $queueItems = $this->queueModel->getWithContactInfo($id);
        $stats = $this->queueModel->countByStatus($id);
        
        $this->json([
            'success' => true,
            'campaign' => $campaign,
            'queueItems' => $queueItems,
            'stats' => $stats
        ]);
    }

    /**
     * Processar upload de mídia
     */
    private function processMediaUpload($file, $mediaType) {
        // Verificar erro
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erro no upload'];
        }
        
        // Tipos permitidos
        $allowedTypes = [
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'video' => ['video/mp4', 'video/quicktime', 'video/webm'],
            'audio' => ['audio/mp3', 'audio/mpeg', 'audio/wav', 'audio/ogg'],
            'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        ];
        
        if (!isset($allowedTypes[$mediaType]) || !in_array($file['type'], $allowedTypes[$mediaType])) {
            return ['success' => false, 'message' => 'Tipo de arquivo não permitido'];
        }
        
        // Tamanho máximo
        $maxSizes = [
            'image' => UPLOAD_MAX_SIZE_IMAGE,
            'video' => UPLOAD_MAX_SIZE_VIDEO,
            'audio' => UPLOAD_MAX_SIZE_AUDIO,
            'document' => UPLOAD_MAX_SIZE_DOCUMENT
        ];
        
        if ($file['size'] > ($maxSizes[$mediaType] ?? 5 * 1024 * 1024)) {
            return ['success' => false, 'message' => 'Arquivo muito grande'];
        }
        
        // Criar diretório
        $uploadDir = 'uploads/campaigns/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Salvar arquivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('campaign_') . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Erro ao salvar arquivo'];
        }
        
        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $file['name']
        ];
    }
}
