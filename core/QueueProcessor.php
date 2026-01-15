<?php
/**
 * Processador de Fila de Disparos
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-14 23:10:00
 * 
 * Processa campanhas de disparo em background
 * Executado via Cron ou chamada AJAX
 */

class QueueProcessor {
    private $db;
    private $campaignModel;
    private $queueModel;
    private $userModel;
    private $processId;
    private $maxExecutionTime = 55;
    private $maxConsecutiveFailures = 5; // Pausa após X falhas seguidas
    private $maxRetries = 3; // Tentativas por mensagem
    private $consecutiveFailures = 0;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->processId = uniqid('proc_');
        
        // Carregar models
        require_once __DIR__ . '/../models/Campaign.php';
        require_once __DIR__ . '/../models/Queue.php';
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Greeting.php';
        
        $this->campaignModel = new Campaign();
        $this->queueModel = new Queue();
        $this->userModel = new User();
        $this->greetingModel = new Greeting();
    }

    /**
     * Processar todas as campanhas ativas
     */
    public function processAll() {
        $startTime = time();
        $processed = 0;
        
        error_log("🚀 [{$this->processId}] Iniciando processamento de filas...");
        
        // Buscar campanhas em execução
        $campaigns = $this->campaignModel->getRunningCampaigns();
        
        if (empty($campaigns)) {
            error_log("ℹ️ [{$this->processId}] Nenhuma campanha em execução");
            return ['processed' => 0, 'campaigns' => 0];
        }
        
        error_log("📋 [{$this->processId}] " . count($campaigns) . " campanha(s) em execução");
        
        foreach ($campaigns as $campaign) {
            // Verificar tempo de execução
            if ((time() - $startTime) >= $this->maxExecutionTime) {
                error_log("⏱️ [{$this->processId}] Tempo limite atingido, parando...");
                break;
            }
            
            // Tentar obter lock da campanha
            if (!$this->acquireLock($campaign['id'])) {
                error_log("🔒 [{$this->processId}] Campanha #{$campaign['id']} já está sendo processada");
                continue;
            }
            
            try {
                // Resetar itens stuck
                $this->queueModel->resetStuck($campaign['id']);
                
                // Processar próximo item
                $result = $this->processNextItem($campaign);
                
                if ($result) {
                    $processed++;
                }
                
                // Verificar se campanha terminou
                $this->campaignModel->checkCompletion($campaign['id']);
                
            } catch (Exception $e) {
                error_log("❌ [{$this->processId}] Erro na campanha #{$campaign['id']}: " . $e->getMessage());
            } finally {
                // Liberar lock
                $this->releaseLock($campaign['id']);
            }
        }
        
        error_log("✅ [{$this->processId}] Processamento concluído. Items: $processed");
        
        return [
            'processed' => $processed,
            'campaigns' => count($campaigns),
            'process_id' => $this->processId
        ];
    }

    /**
     * Processar próximo item de uma campanha
     */
    private function processNextItem($campaign) {
        // Obter próximo item
        $item = $this->queueModel->getNextItem($campaign['id']);
        
        if (!$item) {
            error_log("📭 [{$this->processId}] Campanha #{$campaign['id']}: Sem itens pendentes");
            return false;
        }
        
        error_log("📤 [{$this->processId}] Processando item #{$item['id']} - {$item['contact_phone']}");
        
        // Marcar como processando
        $this->queueModel->markProcessing($item['id']);
        
        // Obter dados do usuário (para API)
        $user = $this->userModel->findById($campaign['user_id']);
        
        if (!$user || empty($user['evolution_instance']) || empty($user['evolution_instance_token'])) {
            $this->queueModel->markFailed($item['id'], 'Instância WhatsApp não configurada');
            $this->campaignModel->incrementCounter($campaign['id'], 'failed_count');
            return false;
        }
        
        // Verificar status da instância (a cada 10 itens ou primeira vez)
        static $instanceCheckCount = 0;
        if ($instanceCheckCount % 10 === 0) {
            $instanceStatus = $this->checkInstanceStatus($user);
            if (!$instanceStatus['connected']) {
                error_log("⚠️ [{$this->processId}] Instância desconectada: {$instanceStatus['state']}");
                $this->queueModel->markForRetry($item['id'], 'Instância WhatsApp desconectada');
                $this->consecutiveFailures++;
                
                if ($this->consecutiveFailures >= 3) {
                    $this->campaignModel->updateStatus($campaign['id'], 'paused');
                    error_log("⛔ [{$this->processId}] Campanha pausada: WhatsApp desconectado");
                }
                return false;
            }
        }
        $instanceCheckCount++;
        
        // Verificar limite de mensagens
        if (!$this->userModel->canSendMessage($campaign['user_id'])) {
            $this->queueModel->markFailed($item['id'], 'Limite de mensagens atingido');
            $this->campaignModel->incrementCounter($campaign['id'], 'failed_count');
            // Pausar campanha se limite atingido
            $this->campaignModel->updateStatus($campaign['id'], 'paused');
            error_log("⚠️ [{$this->processId}] Campanha pausada: limite de mensagens");
            return false;
        }
        
        // Preparar mensagem (substituir macros)
        $contactName = $item['contact_name'] ?: '';
        $contactPhone = $item['contact_phone'] ?: '';
        $message = $campaign['message'];
        
        // Processar macro {saudacao} se existir
        if (strpos($message, '{saudacao}') !== false) {
            $greetingTemplate = $this->greetingModel->getNextGreeting($campaign['id'], $campaign['user_id']);
            $greetingText = $this->greetingModel->processGreeting($greetingTemplate, $contactName, $contactPhone);
            $message = str_replace('{saudacao}', $greetingText, $message);
        }
        
        // Substituir outros macros
        $displayName = !empty(trim($contactName)) ? ucfirst(strtolower(explode(' ', trim($contactName))[0])) : 'Cliente';
        $message = str_replace('{nome}', $displayName, $message);
        $message = str_replace('{numero}', $contactPhone, $message);
        
        // Enviar mensagem
        $result = $this->sendMessage($user, $item['contact_phone'], $message, $campaign);
        
        if ($result['success']) {
            $this->queueModel->markSent($item['id']);
            $this->campaignModel->incrementCounter($campaign['id'], 'sent_count');
            $this->userModel->incrementMessageCount($campaign['user_id'], 1);
            $this->consecutiveFailures = 0; // Reset contador de falhas
            error_log("✅ [{$this->processId}] Enviado para {$item['contact_phone']}");
            
            // Sortear intervalo aleatório para o próximo envio
            $interval = rand($campaign['min_interval'], $campaign['max_interval']);
            $nextSendAt = date('Y-m-d H:i:s', time() + $interval);
            
            // Salvar delay sorteado na campanha para exibição no monitor
            $this->saveNextDelay($campaign['id'], $interval, $nextSendAt);
            
            error_log("⏳ [{$this->processId}] Aguardando {$interval}s antes do próximo...");
            sleep($interval);
            
            // Limpar delay após aguardar
            $this->clearNextDelay($campaign['id']);
            
            return true;
        } else {
            $this->consecutiveFailures++;
            
            // Verificar se é erro recuperável e se pode tentar novamente
            $attempts = $item['attempts'] ?? 0;
            $isRecoverable = $this->isRecoverableError($result['error']);
            
            if ($isRecoverable && $attempts < $this->maxRetries) {
                // Voltar para pending para retry
                $this->queueModel->markForRetry($item['id'], $result['error']);
                error_log("🔄 [{$this->processId}] Retry agendado para {$item['contact_phone']} (tentativa " . ($attempts + 1) . ")");
            } else {
                $this->queueModel->markFailed($item['id'], $result['error']);
                $this->campaignModel->incrementCounter($campaign['id'], 'failed_count');
                error_log("❌ [{$this->processId}] Falha permanente: {$result['error']}");
            }
            
            // Pausar campanha se muitas falhas consecutivas
            if ($this->consecutiveFailures >= $this->maxConsecutiveFailures) {
                $this->campaignModel->updateStatus($campaign['id'], 'paused');
                error_log("⛔ [{$this->processId}] Campanha pausada: {$this->maxConsecutiveFailures} falhas consecutivas");
                return false;
            }
            
            // Aguardar mesmo após falha (evita rate limiting)
            $interval = rand(5, 10);
            error_log("⏳ [{$this->processId}] Aguardando {$interval}s após falha...");
            sleep($interval);
            
            return false;
        }
    }

    /**
     * Enviar mensagem via Evolution API
     */
    private function sendMessage($user, $phone, $message, $campaign) {
        $apiUrl = EVOLUTION_API_URL;
        $apiKey = $user['evolution_instance_token'];
        $instance = $user['evolution_instance'];
        
        // Limpar telefone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Verificar tipo de mídia
        if ($campaign['media_type'] !== 'text' && !empty($campaign['media_path'])) {
            return $this->sendMediaMessage($apiUrl, $apiKey, $instance, $phone, $message, $campaign);
        }
        
        // Enviar texto
        $endpoint = rtrim($apiUrl, '/') . '/message/sendText/' . $instance;
        $data = [
            'number' => $phone,
            'text' => $message
        ];
        
        return $this->makeAPIRequest($endpoint, $apiKey, $data);
    }

    /**
     * Enviar mensagem com mídia
     */
    private function sendMediaMessage($apiUrl, $apiKey, $instance, $phone, $caption, $campaign) {
        $mediaType = $campaign['media_type'];
        $mediaPath = $campaign['media_path'];
        
        if (!file_exists($mediaPath)) {
            return ['success' => false, 'error' => 'Arquivo de mídia não encontrado'];
        }
        
        // Ler arquivo e converter para base64
        $fileContent = file_get_contents($mediaPath);
        $base64 = base64_encode($fileContent);
        $mimeType = mime_content_type($mediaPath);
        
        if ($mediaType === 'audio') {
            $endpoint = rtrim($apiUrl, '/') . '/message/sendWhatsAppAudio/' . $instance;
            $data = [
                'number' => $phone,
                'audio' => $base64,
                'encoding' => true
            ];
        } else {
            $endpoint = rtrim($apiUrl, '/') . '/message/sendMedia/' . $instance;
            
            // Para vídeos grandes, usar URL
            $fileSize = filesize($mediaPath);
            if ($mediaType === 'video' && $fileSize > 3 * 1024 * 1024) {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? parse_url(APP_URL, PHP_URL_HOST);
                $fileUrl = APP_URL . '/' . $mediaPath;
                
                $data = [
                    'number' => $phone,
                    'mediatype' => $mediaType,
                    'media' => $fileUrl,
                    'fileName' => $campaign['media_filename']
                ];
            } else {
                $data = [
                    'number' => $phone,
                    'mediatype' => $mediaType,
                    'mimetype' => $mimeType,
                    'media' => $base64,
                    'fileName' => $campaign['media_filename']
                ];
            }
            
            if (!empty($caption)) {
                $data['caption'] = $caption;
            }
        }
        
        return $this->makeAPIRequest($endpoint, $apiKey, $data);
    }

    /**
     * Verificar se é erro recuperável (pode tentar novamente)
     */
    private function isRecoverableError($error) {
        $recoverablePatterns = [
            'timeout',
            'connection',
            'temporarily',
            'rate limit',
            'too many',
            'busy',
            'unavailable',
            'try again',
            '429',
            '500',
            '502',
            '503',
            '504'
        ];
        
        $errorLower = strtolower($error);
        foreach ($recoverablePatterns as $pattern) {
            if (strpos($errorLower, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar status da instância antes de enviar
     */
    private function checkInstanceStatus($user) {
        $apiUrl = EVOLUTION_API_URL;
        $apiKey = $user['evolution_instance_token'];
        $instance = $user['evolution_instance'];
        
        $endpoint = rtrim($apiUrl, '/') . '/instance/connectionState/' . $instance;
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['connected' => false, 'error' => 'Não foi possível verificar status'];
        }
        
        $data = json_decode($response, true);
        $state = $data['instance']['state'] ?? $data['state'] ?? 'unknown';
        
        return [
            'connected' => ($state === 'open' || $state === 'connected'),
            'state' => $state
        ];
    }

    /**
     * Fazer requisição para Evolution API
     */
    private function makeAPIRequest($endpoint, $apiKey, $data) {
        $startTime = microtime(true);
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); // Reduzido para 15s
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Reduzido para 60s

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        
        $executionTime = round(microtime(true) - $startTime, 2);
        error_log("📡 [{$this->processId}] API Response: HTTP {$httpCode} em {$executionTime}s");

        if ($error) {
            // Erros específicos de timeout
            if ($errno === CURLE_OPERATION_TIMEDOUT) {
                return ['success' => false, 'error' => 'Timeout na conexão (API lenta)'];
            }
            if ($errno === CURLE_COULDNT_CONNECT) {
                return ['success' => false, 'error' => 'Não foi possível conectar à API'];
            }
            return ['success' => false, 'error' => 'Erro de conexão: ' . $error];
        }

        if ($httpCode === 0) {
            return ['success' => false, 'error' => 'Sem resposta da API'];
        }

        if ($httpCode === 429) {
            return ['success' => false, 'error' => 'Rate limit - muitas requisições'];
        }

        if ($httpCode >= 500) {
            return ['success' => false, 'error' => "Erro no servidor da API (HTTP {$httpCode})"];
        }

        if ($httpCode !== 200 && $httpCode !== 201) {
            $responseData = json_decode($response, true);
            $errorMsg = $responseData['message'] ?? $responseData['error'] ?? 'Erro HTTP ' . $httpCode;
            
            // Log detalhado para debug
            error_log("❌ [{$this->processId}] API Error: {$errorMsg}");
            if (!empty($responseData)) {
                error_log("❌ [{$this->processId}] Response: " . json_encode($responseData));
            }
            
            return ['success' => false, 'error' => $errorMsg];
        }

        return ['success' => true, 'response' => json_decode($response, true)];
    }

    /**
     * Adquirir lock de campanha
     */
    private function acquireLock($campaignId) {
        try {
            // Limpar locks antigos (mais de 5 minutos)
            $sql = "DELETE FROM dispatch_locks WHERE locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
            $this->db->exec($sql);
            
            // Tentar inserir lock
            $sql = "INSERT INTO dispatch_locks (campaign_id, locked_by) VALUES (:campaign_id, :locked_by)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':campaign_id' => $campaignId,
                ':locked_by' => $this->processId
            ]);
            
            return true;
        } catch (PDOException $e) {
            // Lock já existe (UNIQUE constraint)
            return false;
        }
    }

    /**
     * Liberar lock de campanha
     */
    private function releaseLock($campaignId) {
        $sql = "DELETE FROM dispatch_locks WHERE campaign_id = :campaign_id AND locked_by = :locked_by";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':campaign_id' => $campaignId,
            ':locked_by' => $this->processId
        ]);
    }

    /**
     * Processar uma campanha específica (chamada AJAX)
     */
    public function processCampaign($campaignId) {
        $campaign = $this->campaignModel->findById($campaignId);
        
        if (!$campaign) {
            return ['success' => false, 'error' => 'Campanha não encontrada'];
        }
        
        if ($campaign['status'] !== 'running') {
            return ['success' => false, 'error' => 'Campanha não está em execução'];
        }
        
        // Tentar obter lock
        if (!$this->acquireLock($campaignId)) {
            return ['success' => false, 'error' => 'Campanha já está sendo processada'];
        }
        
        try {
            $this->queueModel->resetStuck($campaignId);
            $result = $this->processNextItem($campaign);
            $this->campaignModel->checkCompletion($campaignId);
            
            return [
                'success' => true,
                'processed' => $result ? 1 : 0,
                'stats' => $this->campaignModel->getStats($campaignId)
            ];
        } finally {
            $this->releaseLock($campaignId);
        }
    }

    /**
     * Salvar próximo delay sorteado para exibição no monitor
     */
    private function saveNextDelay($campaignId, $delay, $nextSendAt) {
        $sql = "UPDATE dispatch_campaigns SET next_delay = :delay, next_send_at = :next_send_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':delay' => $delay,
            ':next_send_at' => $nextSendAt,
            ':id' => $campaignId
        ]);
    }

    /**
     * Limpar delay após aguardar
     */
    private function clearNextDelay($campaignId) {
        $sql = "UPDATE dispatch_campaigns SET next_delay = NULL, next_send_at = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $campaignId]);
    }
}
