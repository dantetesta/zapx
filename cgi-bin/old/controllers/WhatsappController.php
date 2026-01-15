<?php
/**
 * Controller para Gerenciamento de Instâncias WhatsApp
 * 
 * @author Dante Testa - https://dantetesta.com.br
 * @date 2025-10-26 07:57:00
 * @version 1.0.4
 * @updated Refatorado para usar $this->json() com sistema anti-cache centralizado
 */

class WhatsappController extends Controller {
    private $userModel;
    private $db;

    public function __construct() {
        parent::__construct(); // Aplicar anti-cache
        $this->userModel = $this->model('User');
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * Página principal de gerenciamento WhatsApp
     */
    public function conectar() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $userData = $this->getUserData($user['id']);
        
        // DEBUG
        error_log("=== CONECTAR WHATSAPP ===");
        error_log("User ID: " . $user['id']);
        error_log("User Name: " . $user['name']);
        error_log("Evolution Instance: " . ($userData['evolution_instance'] ?? 'NULL'));
        error_log("Evolution Token: " . ($userData['evolution_instance_token'] ?? 'NULL'));
        error_log("Evolution Status: " . ($userData['evolution_status'] ?? 'NULL'));

        // Verificar se usuário já tem instância
        $hasInstance = !empty($userData['evolution_instance']);
        error_log("Has Instance: " . ($hasInstance ? 'SIM' : 'NÃO'));
        
        // Se tem instância, buscar status atual
        $instanceData = null;
        $autoOpenQRCode = false;
        
        if ($hasInstance) {
            $status = $userData['evolution_status'] ?? 'unknown';
            
            $instanceData = [
                'name' => $userData['evolution_instance'],
                'phone' => $userData['evolution_phone_number'],
                'status' => $status,
                'created_at' => $userData['evolution_created_at'] ?? null
            ];
            
            // Auto-abrir QR Code se status = created (instância criada mas não conectada)
            if ($status === 'created') {
                $autoOpenQRCode = true;
            }
        }

        $this->view('users/conectar-whatsapp', [
            'user' => $user,  // Para o navbar
            'userData' => $userData,  // Para os dados completos
            'hasInstance' => $hasInstance,
            'instanceData' => $instanceData,
            'autoOpenQRCode' => $autoOpenQRCode
        ]);
    }

    /**
     * Obter dados completos do usuário do banco
     */
    private function getUserData($userId) {
        return $this->userModel->findById($userId);
    }

    /**
     * Criar nova instância (AJAX)
     */
    public function createInstance() {
        // Usar método json() do Controller base (já tem anti-cache e buffer)
        
        // Verificar autenticação
        if (!$this->isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Você precisa estar logado']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido']);
        }
        
        try {

        $user = $this->getCurrentUser();
        
        // VERIFICAÇÃO CRÍTICA: User ID
        if (!$user || !isset($user['id'])) {
            error_log("❌ ERRO CRÍTICO: Usuário não está logado ou ID não encontrado!");
            $this->json(['success' => false, 'message' => 'Erro: Usuário não identificado. Faça login novamente.']);
        }
        
        error_log("✅ User ID identificado: " . $user['id']);
        error_log("User completo: " . json_encode($user));
        
        $userData = $this->getUserData($user['id']);

        // Verificar se já tem instância
        if (!empty($userData['evolution_instance'])) {
            $this->json(['success' => false, 'message' => 'Você já possui uma instância. Delete a atual para criar uma nova.']);
        }

        $ddi = trim($_POST['ddi'] ?? '55');
        $phoneNumber = trim($_POST['phone_number'] ?? '');

        error_log("=== DADOS DO FORMULÁRIO ===");
        error_log("DDI recebido: '$ddi'");
        error_log("Phone Number recebido: '$phoneNumber'");
        error_log("POST completo: " . json_encode($_POST));

        if (empty($phoneNumber)) {
            error_log("❌ ERRO: Número do telefone está vazio!");
            $this->json(['success' => false, 'message' => 'Número do telefone é obrigatório']);
        }

        // Gerar nome único da instância automaticamente
        // Formato: zapx_{user_id}_{timestamp_hash}
        $uniqueName = 'zapx_' . $user['id'] . '_' . substr(md5(uniqid() . time()), 0, 8);
        
        error_log("=== CRIANDO INSTÂNCIA ===");
        error_log("User ID: {$user['id']}");
        error_log("Nome gerado: $uniqueName");
        error_log("DDI: $ddi");
        error_log("Telefone: $phoneNumber");

        // Limpar e formatar número com DDI
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        $ddi = preg_replace('/[^0-9]/', '', $ddi);
        
        // Adicionar DDI se o número não começar com ele
        if (!str_starts_with($phoneNumber, $ddi)) {
            $phoneNumber = $ddi . $phoneNumber;
        }

        // Criar instância na Evolution API
        $dados = [
            'instanceName' => $uniqueName,
            'integration' => 'WHATSAPP-BAILEYS',
            'qrcode' => true,
            'number' => $phoneNumber
        ];

        // SALVAR NO BANCO PRIMEIRO (antes de chamar API)
        error_log("=== SALVANDO NO BANCO ANTES DA API ===");
        $configDataPreAPI = [
            'evolution_instance' => $uniqueName,
            'evolution_phone_number' => $phoneNumber,
            'evolution_status' => 'creating',
            'evolution_created_at' => date('Y-m-d H:i:s')
        ];
        $this->userModel->updateEvolutionConfig($user['id'], $configDataPreAPI);
        error_log("✅ Dados salvos no banco ANTES de chamar API");

        error_log("=== CHAMANDO API EVOLUTION ===");
        error_log("Endpoint: /instance/create");
        error_log("Dados: " . json_encode($dados));

        $resultado = $this->fazerRequisicaoAPI('/instance/create', $dados, 'POST');
        
        error_log("=== RESULTADO DA API ===");
        error_log("Sucesso: " . ($resultado['sucesso'] ? 'SIM' : 'NÃO'));
        error_log("Resultado completo: " . json_encode($resultado));

        // TEMPORÁRIO: Salvar no banco MESMO se API falhar (para debug)
        $apiSuccess = $resultado['sucesso'] ?? false;

        if ($apiSuccess) {
            // Extrair token da resposta da API
            $apiResponse = $resultado['dados'] ?? [];
            $instanceToken = null;
            
            // A Evolution API V2 retorna o token em diferentes formatos
            // Tentar TODOS os formatos possíveis
            if (isset($apiResponse['hash']) && is_string($apiResponse['hash'])) {
                $instanceToken = $apiResponse['hash'];
                error_log("Token extraído de: hash (string)");
            } elseif (isset($apiResponse['hash']['apikey'])) {
                $instanceToken = $apiResponse['hash']['apikey'];
                error_log("Token extraído de: hash.apikey");
            } elseif (isset($apiResponse['apikey'])) {
                $instanceToken = $apiResponse['apikey'];
                error_log("Token extraído de: apikey");
            } elseif (isset($apiResponse['instance']['token'])) {
                $instanceToken = $apiResponse['instance']['token'];
                error_log("Token extraído de: instance.token");
            } elseif (isset($apiResponse['instance']['apikey'])) {
                $instanceToken = $apiResponse['instance']['apikey'];
                error_log("Token extraído de: instance.apikey");
            } elseif (isset($apiResponse['token'])) {
                $instanceToken = $apiResponse['token'];
                error_log("Token extraído de: token");
            } elseif (isset($apiResponse['instance']['hash'])) {
                $instanceToken = $apiResponse['instance']['hash'];
                error_log("Token extraído de: instance.hash");
            } else {
                error_log("❌ Token NÃO encontrado em nenhum formato conhecido!");
            }
            
            // Log para debug
            error_log("=== INSTÂNCIA CRIADA ===");
            error_log("Instance Name: $uniqueName");
            error_log("Token extraído: " . ($instanceToken ?? 'NULL'));
            error_log("Resposta completa: " . json_encode($apiResponse));
            error_log("Hash da resposta: " . json_encode($apiResponse['hash'] ?? 'NOT FOUND'));
            
            // ATUALIZAR no banco com TOKEN e status=created
            $configDataWithToken = [
                'evolution_status' => 'created'
            ];
            
            // Adicionar token se foi encontrado
            if ($instanceToken) {
                $configDataWithToken['evolution_instance_token'] = $instanceToken;
                error_log("✅ Token será salvo: " . $instanceToken);
            } else {
                error_log("⚠️ ATENÇÃO: Token não foi extraído da resposta da API!");
            }
            
            error_log("=== ATUALIZANDO NO BANCO COM TOKEN ===");
            error_log("User ID para update: " . $user['id']);
            error_log("User ID tipo: " . gettype($user['id']));
            error_log("Config Data: " . json_encode($configDataWithToken));
            
            $updateResult = $this->userModel->updateEvolutionConfig($user['id'], $configDataWithToken);
            
            error_log("Resultado update: " . ($updateResult ? 'SUCCESS' : 'FAILED'));
            
            // Verificar se realmente salvou
            $userAfter = $this->userModel->findById($user['id']);
            error_log("=== VERIFICAÇÃO FINAL ===");
            error_log("Dados após update - evolution_instance: " . ($userAfter['evolution_instance'] ?? 'NULL'));
            error_log("Dados após update - evolution_phone_number: " . ($userAfter['evolution_phone_number'] ?? 'NULL'));
            error_log("Dados após update - evolution_instance_token: " . ($userAfter['evolution_instance_token'] ?? 'NULL'));
            error_log("Dados após update - evolution_status: " . ($userAfter['evolution_status'] ?? 'NULL'));
            
            // Se o token não foi salvo, tentar novamente com UPDATE direto
            if (empty($userAfter['evolution_instance_token']) && $instanceToken) {
                error_log("⚠️ Token não foi salvo! Tentando UPDATE direto...");
                $sql = "UPDATE users SET evolution_instance_token = :token WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $directUpdate = $stmt->execute([':token' => $instanceToken, ':id' => $user['id']]);
                error_log("UPDATE direto: " . ($directUpdate ? 'SUCCESS' : 'FAILED'));
            }

            $this->json([
                'success' => true,
                'message' => 'Instância criada com sucesso!' . ($instanceToken ? '' : ' (Token será gerado ao conectar)'),
                'instance' => [
                    'name' => $uniqueName,
                    'phone' => $phoneNumber,
                    'token' => $instanceToken
                ],
                'debug' => [
                    'saved_in_db' => !empty($userAfter['evolution_instance_token']),
                    'token_from_api' => $instanceToken
                ]
            ]);
        } else {
            error_log("=== ERRO AO CRIAR INSTÂNCIA NA API ===");
            error_log("Dados enviados: " . json_encode($dados));
            error_log("Resultado: " . json_encode($resultado));
            
            // MODO DEBUG: Salvar no banco MESMO com erro na API
            error_log("⚠️ MODO DEBUG: Salvando no banco mesmo com erro na API");
            
            $configData = [
                'evolution_instance' => $uniqueName,
                'evolution_phone_number' => $phoneNumber,
                'evolution_status' => 'api_failed',
                'evolution_created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->userModel->updateEvolutionConfig($user['id'], $configData);
            
            $erro_msg = 'Erro desconhecido';
            if (isset($resultado['dados']['response']['message'])) {
                if (is_array($resultado['dados']['response']['message'])) {
                    $erro_msg = implode(', ', $resultado['dados']['response']['message']);
                } else {
                    $erro_msg = $resultado['dados']['response']['message'];
                }
            } elseif (isset($resultado['dados']['message'])) {
                $erro_msg = $resultado['dados']['message'];
            } elseif (isset($resultado['erro'])) {
                $erro_msg = $resultado['erro'];
            }

            $this->json([
                'success' => false, 
                'message' => 'Erro ao criar instância na API: ' . $erro_msg . ' (Dados salvos no banco para debug)'
            ]);
        }
        
        } catch (Exception $e) {
            error_log("❌ EXCEÇÃO FATAL em createInstance: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->json([
                'success' => false,
                'message' => 'Erro fatal ao criar instância: ' . $e->getMessage()
            ]);
        } catch (Error $e) {
            error_log("❌ ERRO FATAL em createInstance: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->json([
                'success' => false,
                'message' => 'Erro crítico: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obter QR Code para conexão (AJAX)
     */
    public function getQRCode() {
        header('Content-Type: application/json');
        
        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Você precisa estar logado']);
            exit;
        }

        $user = $this->getCurrentUser();
        $userData = $this->getUserData($user['id']);
        $instance = $userData['evolution_instance'] ?? '';

        if (empty($instance)) {
            echo json_encode(['success' => false, 'message' => 'Nenhuma instância encontrada']);
            exit;
        }

        // Buscar QR Code via endpoint connect
        $resultado = $this->fazerRequisicaoAPI("/instance/connect/$instance", null, 'GET');

        if ($resultado['sucesso'] && isset($resultado['dados'])) {
            $qrCodeData = $resultado['dados']['base64'] ?? $resultado['dados']['qrcode'] ?? null;

            if ($qrCodeData) {
                // Atualizar status no banco
                $this->userModel->updateEvolutionConfig($user['id'], [
                    'evolution_status' => 'qrcode_generated'
                ]);

                // Verificar se já tem prefixo data:image
                if (strpos($qrCodeData, 'data:image') === 0) {
                    // Já tem prefixo, usar direto
                    $finalQrCode = $qrCodeData;
                } else {
                    // Não tem prefixo, adicionar
                    $finalQrCode = 'data:image/png;base64,' . $qrCodeData;
                }

                echo json_encode([
                    'success' => true,
                    'qrcode' => $finalQrCode
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'QR Code não disponível na resposta']);
            }
        } else {
            $erro_msg = $resultado['dados']['message'] ?? 'Erro ao gerar QR Code';
            echo json_encode(['success' => false, 'message' => $erro_msg]);
        }
    }

    /**
     * Verificar status da conexão (AJAX)
     */
    public function checkStatus() {
        header('Content-Type: application/json');
        
        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Você precisa estar logado']);
            exit;
        }

        $user = $this->getCurrentUser();
        $userData = $this->getUserData($user['id']);
        $instance = $userData['evolution_instance'] ?? '';

        if (empty($instance)) {
            echo json_encode(['success' => false, 'message' => 'Nenhuma instância encontrada']);
            exit;
        }

        $resultado = $this->fazerRequisicaoAPI("/instance/connectionState/$instance");

        if ($resultado['sucesso'] && isset($resultado['dados'])) {
            $status = $resultado['dados']['instance']['state'] ?? 
                     $resultado['dados']['state'] ?? 
                     $resultado['dados']['connectionStatus'] ?? 
                     'close';
            
            // Tentar extrair token da resposta (caso ainda não tenha)
            $apiResponse = $resultado['dados'] ?? [];
            $instanceToken = null;
            
            // FORMATO CORRETO: hash É O TOKEN (string UUID)
            if (isset($apiResponse['hash']) && is_string($apiResponse['hash'])) {
                $instanceToken = $apiResponse['hash'];  // ✅ CORRETO!
            } elseif (isset($apiResponse['hash']['apikey'])) {
                $instanceToken = $apiResponse['hash']['apikey'];
            } elseif (isset($apiResponse['apikey'])) {
                $instanceToken = $apiResponse['apikey'];
            } elseif (isset($apiResponse['instance']['token'])) {
                $instanceToken = $apiResponse['instance']['token'];
            } elseif (isset($apiResponse['token'])) {
                $instanceToken = $apiResponse['token'];
            }

            // Atualizar status no banco (e token se encontrado)
            $updateData = ['evolution_status' => $status];
            
            // Se encontrou token E o usuário ainda não tem token, salvar
            if ($instanceToken && empty($userData['evolution_instance_token'])) {
                $updateData['evolution_instance_token'] = $instanceToken;
                error_log("Token capturado no checkStatus: " . substr($instanceToken, 0, 20) . "...");
            }
            
            $this->userModel->updateEvolutionConfig($user['id'], $updateData);

            $connected = ($status === 'open');

            echo json_encode([
                'success' => true,
                'status' => $status,
                'connected' => $connected,
                'message' => $connected ? 'Conectado' : 'Desconectado'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao verificar status']);
        }
    }

    /**
     * Desconectar instância (AJAX)
     */
    public function disconnect() {
        header('Content-Type: application/json');
        
        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Você precisa estar logado']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }

        $user = $this->getCurrentUser();
        $userData = $this->getUserData($user['id']);
        $instance = $userData['evolution_instance'] ?? '';

        if (empty($instance)) {
            echo json_encode(['success' => false, 'message' => 'Nenhuma instância encontrada']);
            exit;
        }

        $resultado = $this->fazerRequisicaoAPI("/instance/logout/$instance", null, 'POST');

        // Atualizar banco independente do resultado
        $this->userModel->updateEvolutionConfig($user['id'], [
            'evolution_status' => 'disconnected'
        ]);

        if ($resultado['sucesso']) {
            echo json_encode(['success' => true, 'message' => 'WhatsApp desconectado com sucesso!']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Instância desconectada localmente']);
        }
    }

    /**
     * Deletar instância (AJAX)
     */
    public function deleteInstance() {
        header('Content-Type: application/json');
        
        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Você precisa estar logado']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }

        $user = $this->getCurrentUser();
        $userData = $this->getUserData($user['id']);
        $instance = $userData['evolution_instance'] ?? '';

        if (empty($instance)) {
            echo json_encode(['success' => false, 'message' => 'Nenhuma instância encontrada']);
            exit;
        }

        // Deletar na Evolution API
        $resultado = $this->fazerRequisicaoAPI("/instance/delete/$instance", null, 'DELETE');

        // Limpar dados do banco independente do resultado da API
        $this->userModel->updateEvolutionConfig($user['id'], [
            'evolution_instance' => null,
            'evolution_phone_number' => null,
            'evolution_status' => null,
            'evolution_created_at' => null
        ]);

        echo json_encode(['success' => true, 'message' => 'Instância deletada com sucesso!']);
    }

    /**
     * Fazer requisição à Evolution API
     */
    private function fazerRequisicaoAPI($endpoint, $dados = null, $metodo = 'GET') {
        $url = EVOLUTION_API_URL . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . EVOLUTION_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        if ($metodo === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($dados) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
            }
        } elseif ($metodo === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $resposta = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erro = curl_error($ch);
        curl_close($ch);
        
        return [
            'sucesso' => $http_code >= 200 && $http_code < 300,
            'http_code' => $http_code,
            'dados' => json_decode($resposta, true),
            'erro' => $erro
        ];
    }
}
?>
