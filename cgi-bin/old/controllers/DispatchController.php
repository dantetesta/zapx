<?php
/**
 * Controller de Disparo em Massa
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 17:46:00
 * 
 * Última atualização: Solução híbrida Base64/URL
 * - Vídeos > 3MB: Envia via URL pública (evita "Maximum call stack size exceeded")
 * - Vídeos < 3MB, imagens, documentos, áudio: base64 PURO (sem prefixo data URI)
 * - Evolution API V2 rejeita prefixo "data:video/mp4;base64," com erro 400
 * - Evolution API tem limite de payload JSON (~5-6MB base64)
 * - Timeout: 180s (3 minutos) | Connect timeout: 30s
 * - Logs detalhados: método (URL/base64), tempo, tamanho
 */

class DispatchController extends Controller {
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
     * Página de disparo
     */
    public function index() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        $tags = $this->tagModel->getByUser($userId);
        $contacts = $this->contactModel->getByUser($userId);

        $this->view('dispatch/index', [
            'user' => $user,
            'tags' => $tags,
            'contacts' => $contacts
        ]);
    }

    /**
     * Obter contatos por tag ou individual
     */
    public function getContacts() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        $type = $_GET['type'] ?? 'all';
        $tagId = $_GET['tag_id'] ?? null;
        $contactId = $_GET['contact_id'] ?? null;

        $contacts = [];

        if ($type === 'tag' && $tagId) {
            $contacts = $this->contactModel->getByUser($userId, null, $tagId);
        } elseif ($type === 'individual' && $contactId) {
            $contact = $this->contactModel->findById($contactId, $userId);
            if ($contact) {
                $contacts = [$contact];
            }
        } else {
            $contacts = $this->contactModel->getByUser($userId);
        }

        $this->json(['success' => true, 'contacts' => $contacts]);
    }

    /**
     * Enviar mensagem com suporte a mídia (integração com Evolution API)
     */
    public function send() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido.'], 405);
            return;
        }

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        // Verificar limite de mensagens
        if (!$this->userModel->canSendMessage($userId)) {
            $balance = $this->userModel->getMessageBalance($userId);
            $this->json([
                'success' => false, 
                'message' => 'Limite de mensagens atingido! Você enviou ' . $balance['sent'] . ' de ' . $balance['limit'] . ' mensagens este mês. O limite será resetado em ' . date('d/m/Y', strtotime($balance['reset_date'] . ' +1 month')) . '.'
            ], 403);
            return;
        }

        // Obter dados do usuário (instância específica dele)
        $userData = $this->userModel->findById($userId);

        // Verificar se usuário tem instância criada
        if (empty($userData['evolution_instance'])) {
            $this->json([
                'success' => false, 
                'message' => 'Você precisa criar sua instância WhatsApp primeiro. Acesse: Menu → Conectar WhatsApp'
            ], 400);
            return;
        }

        // Verificar se tem token da instância
        if (empty($userData['evolution_instance_token'])) {
            $this->json([
                'success' => false, 
                'message' => 'Token da instância não encontrado. Reconecte sua instância WhatsApp.'
            ], 400);
            return;
        }

        // Usar configurações da instância específica do usuário
        $apiUrl = EVOLUTION_API_URL; // URL base é centralizada
        $apiKey = $userData['evolution_instance_token']; // Token específico do usuário
        $instance = $userData['evolution_instance']; // Nome da instância do usuário

        // Log para debug
        error_log("=== DISPARO INICIADO ===");
        error_log("User ID: $userId");
        error_log("Instance: $instance");
        error_log("API URL: $apiUrl");
        error_log("Token: " . substr($apiKey, 0, 10) . "...");
        error_log("Phone Number: " . ($userData['evolution_phone_number'] ?? 'não definido'));
        error_log("Status: " . ($userData['evolution_status'] ?? 'não definido'));

        $contactId = $_POST['contact_id'] ?? '';
        $message = $_POST['message'] ?? '';
        $contactName = $_POST['contact_name'] ?? '';
        $contactPhone = $_POST['contact_phone'] ?? '';
        $mediaType = $_POST['media_type'] ?? 'text'; // text, image, video, audio, document

        // Validações básicas
        if (empty($contactId) || empty($contactPhone)) {
            $this->json(['success' => false, 'message' => 'Dados de contato inválidos.'], 400);
            return;
        }

        // Validar se tem mensagem ou mídia
        if (empty($message) && $mediaType === 'text') {
            $this->json(['success' => false, 'message' => 'Digite uma mensagem.'], 400);
            return;
        }

        // Processar upload de mídia se houver
        $mediaData = null;
        if ($mediaType !== 'text' && isset($_FILES['media_file'])) {
            $uploadResult = $this->processMediaUpload($_FILES['media_file'], $mediaType);
            if (!$uploadResult['success']) {
                $this->json(['success' => false, 'message' => $uploadResult['message']], 400);
                return;
            }
            $mediaData = $uploadResult['data'];
        }

        // Substituir macros na mensagem
        $finalMessage = str_replace('{nome}', $contactName ?: 'Cliente', $message);

        // Registrar no histórico
        $historyData = [
            'message' => $finalMessage,
            'media_type' => $mediaType,
            'media_filename' => $mediaData['filename'] ?? null
        ];
        $historyId = $this->dispatchModel->create($userId, $contactId, json_encode($historyData));

        // Enviar via Evolution API
        try {
            if ($mediaType === 'text') {
                // Enviar apenas texto
                $result = $this->sendTextMessage($apiUrl, $apiKey, $instance, $contactPhone, $finalMessage);
            } else {
                // Enviar mídia
                $result = $this->sendMediaMessage($apiUrl, $apiKey, $instance, $contactPhone, $finalMessage, $mediaData, $mediaType);
            }

            if ($result['success']) {
                $this->dispatchModel->updateStatus($historyId, 'sent');
                
                // Incrementar contador de mensagens enviadas
                $this->userModel->incrementMessageCount($userId, 1);
                
                // Obter saldo atualizado
                $balance = $this->userModel->getMessageBalance($userId);
                
                // 🧹 LIMPAR ARQUIVO DE MÍDIA APÓS ENVIO BEM-SUCEDIDO
                if ($mediaData && isset($mediaData['filepath']) && file_exists($mediaData['filepath'])) {
                    if (unlink($mediaData['filepath'])) {
                        error_log("🗑️ Arquivo de mídia removido: " . $mediaData['filepath']);
                    } else {
                        error_log("⚠️ Não foi possível remover arquivo: " . $mediaData['filepath']);
                    }
                }
                
                error_log("✅ MENSAGEM ENVIADA COM SUCESSO!");
                
                $this->json([
                    'success' => true, 
                    'message' => 'Mensagem enviada com sucesso!',
                    'balance' => $balance
                ]);
            } else {
                $errorMsg = $result['error'] ?? 'Erro desconhecido';
                error_log("❌ FALHA NO ENVIO: $errorMsg");
                
                // Log detalhado do erro
                error_log("Detalhes do erro: " . json_encode($result));
                
                $this->dispatchModel->updateStatus($historyId, 'failed', $errorMsg);
                
                // 🧹 LIMPAR ARQUIVO DE MÍDIA EM CASO DE FALHA
                if ($mediaData && isset($mediaData['filepath']) && file_exists($mediaData['filepath'])) {
                    if (unlink($mediaData['filepath'])) {
                        error_log("🗑️ Arquivo de mídia removido (falha): " . $mediaData['filepath']);
                    }
                }
                
                // Mensagem mais amigável para o usuário
                $userMessage = "Falha ao enviar: $errorMsg\n\n";
                $userMessage .= "Verifique:\n";
                $userMessage .= "• WhatsApp está conectado?\n";
                $userMessage .= "• Instância está ativa?\n";
                $userMessage .= "• Número do destinatário está correto?";
                
                $this->json(['success' => false, 'message' => $userMessage], 500);
            }
        } catch (Exception $e) {
            error_log("❌ EXCEÇÃO NO ENVIO: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // 🧹 LIMPAR ARQUIVO DE MÍDIA EM CASO DE EXCEÇÃO
            if ($mediaData && isset($mediaData['filepath']) && file_exists($mediaData['filepath'])) {
                if (unlink($mediaData['filepath'])) {
                    error_log("🗑️ Arquivo de mídia removido (exceção): " . $mediaData['filepath']);
                }
            }
            
            $this->dispatchModel->updateStatus($historyId, 'failed', $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro crítico ao enviar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Processar upload de mídia
     */
    private function processMediaUpload($file, $mediaType) {
        // Verificar se houve erro no upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'Arquivo excede upload_max_filesize do php.ini (' . ini_get('upload_max_filesize') . ')',
                UPLOAD_ERR_FORM_SIZE => 'Arquivo excede MAX_FILE_SIZE do formulário',
                UPLOAD_ERR_PARTIAL => 'Upload parcial - arquivo não foi completamente enviado',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada no servidor',
                UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever arquivo no disco',
                UPLOAD_ERR_EXTENSION => 'Uma extensão PHP bloqueou o upload'
            ];
            
            $errorMsg = $uploadErrors[$file['error']] ?? 'Erro desconhecido no upload (código: ' . $file['error'] . ')';
            
            error_log("❌ ERRO DE UPLOAD:");
            error_log("   Código: " . $file['error']);
            error_log("   Mensagem: " . $errorMsg);
            error_log("   Arquivo: " . ($file['name'] ?? 'não definido'));
            error_log("   Tamanho: " . ($file['size'] ?? 0) . " bytes");
            error_log("   upload_max_filesize: " . ini_get('upload_max_filesize'));
            error_log("   post_max_size: " . ini_get('post_max_size'));
            
            return ['success' => false, 'message' => $errorMsg];
        }

        // Definir tipos MIME permitidos por categoria
        $allowedTypes = [
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'],
            'video' => [
                'video/mp4', 
                'video/avi', 
                'video/mov', 
                'video/wmv', 
                'video/webm',
                'video/quicktime',  // .mov no Mac
                'video/x-msvideo',  // .avi
                'video/x-ms-wmv',   // .wmv
                'video/mpeg',       // .mpeg
                'video/3gpp',       // .3gp
                'video/x-flv'       // .flv
            ],
            'audio' => [
                'audio/mp3', 
                'audio/mpeg',       // .mp3
                'audio/wav', 
                'audio/ogg', 
                'audio/m4a', 
                'audio/aac',
                'audio/x-m4a'       // .m4a alternativo
            ],
            'document' => [
                'application/pdf', 
                'application/msword',  // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',  // .docx
                'application/vnd.ms-excel',  // .xls
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',  // .xlsx
                'text/plain',
                'text/csv'
            ]
        ];

        // Verificar tipo MIME
        $fileMimeType = $file['type'];
        
        // Log para debug
        error_log("=== UPLOAD DE MÍDIA ===");
        error_log("Media Type: $mediaType");
        error_log("File MIME Type: $fileMimeType");
        error_log("File Name: " . $file['name']);
        error_log("File Size: " . round($file['size'] / 1024 / 1024, 2) . " MB");
        error_log("File Extension: " . pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!isset($allowedTypes[$mediaType]) || !in_array($fileMimeType, $allowedTypes[$mediaType])) {
            error_log("❌ MIME Type não permitido!");
            error_log("Tipos permitidos: " . implode(', ', $allowedTypes[$mediaType]));
            return ['success' => false, 'message' => 'Tipo de arquivo não permitido para ' . $mediaType . '. MIME: ' . $fileMimeType];
        }

        // Verificar tamanho baseado no tipo (limites do WhatsApp API)
        $maxSizes = [
            'image' => 5 * 1024 * 1024,   // 5MB para imagens
            'video' => 16 * 1024 * 1024,  // 16MB para vídeos
            'audio' => 16 * 1024 * 1024,  // 16MB para áudio
            'document' => 16 * 1024 * 1024 // 16MB para documentos
        ];
        
        $maxSize = $maxSizes[$mediaType] ?? (5 * 1024 * 1024);
        $maxSizeMB = round($maxSize / 1024 / 1024, 1);
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => "Arquivo muito grande. Máximo {$maxSizeMB}MB para {$mediaType}."];
        }
        
        error_log("✅ Tamanho OK: " . round($file['size'] / 1024 / 1024, 2) . "MB (limite: {$maxSizeMB}MB)");

        // Validação específica para vídeos
        if ($mediaType === 'video') {
            error_log("📹 Validando vídeo...");
            error_log("ℹ️ WhatsApp aceita: MP4 com H.264 (vídeo) + AAC (áudio)");
            
            // Verificar se é realmente MP4
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ['mp4', 'mov'])) {
                error_log("⚠️ Extensão não recomendada: $extension");
                return ['success' => false, 'message' => 'Para vídeos, use preferencialmente MP4. Extensão atual: ' . $extension];
            }
            
            error_log("✅ Extensão válida: $extension");
        }

        // Criar diretório de upload se não existir
        $uploadDir = 'uploads/media/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Gerar nome único para o arquivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_') . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Erro ao salvar arquivo.'];
        }

        // Converter arquivo para base64
        $fileContent = file_get_contents($filepath);
        $base64Pure = base64_encode($fileContent);
        
        // Para mídia (image, video, document): adicionar prefixo data URI
        // Para áudio: base64 puro (sem prefixo)
        // Formato: data:video/mp4;base64,XXXXX
        $base64WithPrefix = "data:{$fileMimeType};base64,{$base64Pure}";
        
        error_log("📦 Base64 gerado:");
        error_log("   Tamanho puro: " . strlen($base64Pure));
        error_log("   Tamanho com prefixo: " . strlen($base64WithPrefix));
        error_log("   Prefixo: " . substr($base64WithPrefix, 0, 50) . "...");

        return [
            'success' => true,
            'data' => [
                'filename' => $filename,
                'filepath' => $filepath,
                'base64' => $base64Pure,  // Base64 PURO (para áudio)
                'base64_with_prefix' => $base64WithPrefix,  // Base64 COM prefixo (para mídia)
                'mimetype' => $fileMimeType,
                'size' => $file['size']
            ]
        ];
    }

    /**
     * Enviar mensagem de texto via Evolution API V2
     */
    private function sendTextMessage($apiUrl, $apiKey, $instance, $phone, $message) {
        // Limpar telefone (remover apenas caracteres especiais, manter números)
        // O número JÁ VEM COMPLETO DO BANCO COM DDI
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        error_log("📞 Número para envio: $phone");

        // Endpoint da Evolution API V2
        $endpoint = rtrim($apiUrl, '/') . '/message/sendText/' . $instance;

        // Dados para envio (Evolution API V2 - formato correto)
        $data = [
            'number' => $phone,
            'text' => $message  // Direto no body, não dentro de textMessage
        ];

        return $this->makeAPIRequest($endpoint, $apiKey, $data);
    }

    /**
     * Enviar mensagem com mídia via Evolution API V2
     */
    private function sendMediaMessage($apiUrl, $apiKey, $instance, $phone, $caption, $mediaData, $mediaType) {
        // Limpar telefone (remover apenas caracteres especiais)
        // O número JÁ VEM COMPLETO DO BANCO COM DDI
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Escolher endpoint baseado no tipo de mídia
        if ($mediaType === 'audio') {
            // Endpoint para áudio
            $endpoint = rtrim($apiUrl, '/') . '/message/sendWhatsAppAudio/' . $instance;
            
            // Payload Evolution API V2 - áudio (usa base64 PURO, sem prefixo)
            $data = [
                'number' => $phone,
                'audio' => $mediaData['base64'],  // Base64 puro
                'encoding' => true
            ];
            
            error_log("=== ENVIANDO ÁUDIO ===");
            error_log("Endpoint: $endpoint");
            error_log("Base64 Length: " . strlen($mediaData['base64']));
        } else {
            // Para image, video, document
            $endpoint = rtrim($apiUrl, '/') . '/message/sendMedia/' . $instance;
            
            // Verificar tamanho do arquivo
            $fileSizeMB = $mediaData['size'] / 1024 / 1024;
            
            // Para vídeos > 3MB, usar URL ao invés de base64
            // Evita erro "Maximum call stack size exceeded" na Evolution API
            if ($mediaType === 'video' && $fileSizeMB > 3) {
                error_log("⚠️ Vídeo grande ({$fileSizeMB}MB) - Usando URL ao invés de base64");
                
                // Gerar URL pública do arquivo
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $fileUrl = $protocol . '://' . $host . '/' . $mediaData['filepath'];
                
                $data = [
                    'number' => $phone,
                    'mediatype' => $mediaType,
                    'media' => $fileUrl,  // URL pública
                    'fileName' => $mediaData['filename']
                ];
                
                error_log("=== ENVIANDO VÍDEO VIA URL ===");
                error_log("URL: $fileUrl");
            } else {
                // Para imagens, documentos e vídeos pequenos: usar base64
                $data = [
                    'number' => $phone,
                    'mediatype' => $mediaType,
                    'mimetype' => $mediaData['mimetype'],
                    'media' => $mediaData['base64'],  // Base64 PURO
                    'fileName' => $mediaData['filename']
                ];
                
                error_log("=== ENVIANDO MÍDIA VIA BASE64 ===");
                error_log("Base64 Length: " . strlen($mediaData['base64']));
            }
            
            // Adicionar caption se houver (OPCIONAL)
            if (!empty($caption)) {
                $data['caption'] = $caption;
            }
            
            // Log detalhado
            error_log("Endpoint: $endpoint");
            error_log("MediaType: $mediaType");
            error_log("FileName: " . $mediaData['filename']);
            error_log("File Size: {$fileSizeMB}MB");
        }

        return $this->makeAPIRequest($endpoint, $apiKey, $data);
    }

    /**
     * Fazer requisição para a Evolution API
     */
    private function makeAPIRequest($endpoint, $apiKey, $data) {
        // Marcar tempo de início
        $startTime = microtime(true);
        
        // Log para debug
        $jsonData = json_encode($data);
        $payloadSize = strlen($jsonData);
        
        error_log("=== REQUISIÇÃO EVOLUTION API ===");
        error_log("Endpoint: $endpoint");
        error_log("Payload Size: " . round($payloadSize / 1024 / 1024, 2) . " MB");
        error_log("API Key: " . substr($apiKey, 0, 10) . "...");
        
        // Configurar cURL
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 30s para conectar
        curl_setopt($ch, CURLOPT_TIMEOUT, 180); // 3 minutos para vídeos grandes
        curl_setopt($ch, CURLOPT_VERBOSE, false); // Desabilitar verbose para não poluir logs

        error_log("⏳ Enviando requisição...");
        $response = curl_exec($ch);
        
        // Informações detalhadas do cURL
        $curlInfo = curl_getinfo($ch);
        $httpCode = $curlInfo['http_code'];
        $totalTime = $curlInfo['total_time'];
        $uploadSize = $curlInfo['size_upload'];
        $downloadSize = $curlInfo['size_download'];
        $error = curl_error($ch);
        curl_close($ch);

        // Calcular tempo total
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime), 2);

        // Log da resposta
        error_log("=== RESPOSTA EVOLUTION API ===");
        error_log("HTTP Code: $httpCode");
        error_log("Tempo Total: {$executionTime}s");
        error_log("Upload: " . round($uploadSize / 1024 / 1024, 2) . " MB");
        error_log("Download: " . round($downloadSize / 1024, 2) . " KB");
        error_log("Response: " . substr($response, 0, 500) . (strlen($response) > 500 ? '...' : ''));

        if ($error) {
            error_log("Evolution API Error: $error");
            return ['success' => false, 'error' => 'Erro de conexão: ' . $error];
        }

        if ($httpCode !== 200 && $httpCode !== 201) {
            $responseData = json_decode($response, true);
            $errorMsg = $responseData['message'] ?? $responseData['error'] ?? 'Erro desconhecido';
            
            // Log detalhado do erro
            error_log("❌ Evolution API HTTP Error: $httpCode");
            error_log("❌ Error Message: $errorMsg");
            error_log("❌ Full Response: " . print_r($responseData, true));
            error_log("❌ Request Endpoint: $endpoint");
            error_log("❌ Request Data: " . json_encode($data, JSON_PRETTY_PRINT));
            
            // Mensagem detalhada para o usuário
            $userMessage = "Erro HTTP $httpCode: $errorMsg";
            
            // Adicionar detalhes específicos se disponível
            if (isset($responseData['details'])) {
                $userMessage .= "\nDetalhes: " . json_encode($responseData['details']);
            }
            
            return ['success' => false, 'error' => $userMessage, 'debug' => $responseData];
        }

        error_log("✅ Evolution API Success - HTTP $httpCode");
        return ['success' => true, 'response' => json_decode($response, true)];
    }

    /**
     * NOTA: Função formatPhoneNumber foi REMOVIDA
     * 
     * A partir de agora, os números são armazenados COMPLETOS no banco de dados
     * com o DDI do país (ex: 5511999999999, 14155551234, 442071234567)
     * 
     * O sistema usa a biblioteca intl-tel-input no frontend para garantir
     * que os números sejam salvos no formato internacional correto.
     */

    /**
     * Histórico de disparos
     */
    public function history() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        $history = $this->dispatchModel->getByUser($userId, 100);
        $stats = $this->dispatchModel->getStats($userId);

        $this->view('dispatch/history', [
            'user' => $user,
            'history' => $history,
            'stats' => $stats
        ]);
    }
}
