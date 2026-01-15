<?php
/**
 * Controller de Contatos
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 * Última atualização: 2025-10-29 14:55:00 - Correção CSRF na deleção
 */

class ContactsController extends Controller {
    private $contactModel;
    private $tagModel;

    public function __construct() {
        $this->contactModel = $this->model('Contact');
        $this->tagModel = $this->model('Tag');
    }

    /**
     * Listar contatos com paginação
     */
    public function index() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        $search = $_GET['search'] ?? null;
        $tagId = $_GET['tag'] ?? null;
        
        // Limpar valores vazios
        if (empty($search)) $search = null;
        if (empty($tagId)) $tagId = null;
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;

        $contacts = $this->contactModel->getByUser($userId, $search, $tagId, $page, $perPage);
        $totalContacts = $this->contactModel->countByUser($userId, $search, $tagId);
        $totalPages = ceil($totalContacts / $perPage);
        
        $tags = $this->tagModel->getByUser($userId);

        $this->view('contacts/index', [
            'user' => $user,
            'contacts' => $contacts,
            'tags' => $tags,
            'search' => $search,
            'selectedTag' => $tagId,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalContacts' => $totalContacts,
            'perPage' => $perPage
        ]);
    }

    /**
     * Criar contato
     */
    public function create() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 🔒 SEGURANÇA: Validar token CSRF
            CSRF::validateOrDie();
            
            $user = $this->getCurrentUser();
            $userId = $user['id'];

            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';

            // 🔒 SEGURANÇA: Validar tamanho de strings
            if (strlen($name) > 255) {
                $this->json(['success' => false, 'message' => 'Nome muito longo (máximo 255 caracteres).'], 400);
                return;
            }
            
            if (strlen($phone) > 20) {
                $this->json(['success' => false, 'message' => 'Telefone inválido (máximo 20 caracteres).'], 400);
                return;
            }

            // Limpar telefone
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            // Validações
            if (empty($phone)) {
                $this->json(['success' => false, 'message' => 'Telefone é obrigatório.'], 400);
                return;
            }
            
            // Validar formato do telefone (mínimo 10 dígitos)
            if (strlen($phone) < 10) {
                $this->json(['success' => false, 'message' => 'Telefone inválido. Mínimo 10 dígitos.'], 400);
                return;
            }

            // Verificar se telefone já existe
            if ($this->contactModel->phoneExists($userId, $phone)) {
                $this->json(['success' => false, 'message' => 'Este telefone já está cadastrado! Verifique sua lista de contatos.'], 409);
                return;
            }

            $contactId = $this->contactModel->create($userId, $name, $phone);

            if ($contactId) {
                // Processar tags se foram enviadas
                $tags = $_POST['tags'] ?? [];
                if (!empty($tags) && is_array($tags)) {
                    $this->processTags($contactId, $tags, $userId);
                }
                
                $this->json(['success' => true, 'message' => 'Contato criado com sucesso!', 'id' => $contactId]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao criar contato.'], 500);
            }
        }
    }

    /**
     * Atualizar contato
     */
    public function update($id) {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 🔒 SEGURANÇA: Validar token CSRF
            CSRF::validateOrDie();
            
            $user = $this->getCurrentUser();
            $userId = $user['id'];

            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';

            // 🔒 SEGURANÇA: Validar tamanho de strings
            if (strlen($name) > 255) {
                $this->json(['success' => false, 'message' => 'Nome muito longo (máximo 255 caracteres).'], 400);
                return;
            }
            
            if (strlen($phone) > 20) {
                $this->json(['success' => false, 'message' => 'Telefone inválido (máximo 20 caracteres).'], 400);
                return;
            }

            // Validações
            if (empty($phone)) {
                $this->json(['success' => false, 'message' => 'Telefone é obrigatório.'], 400);
                return;
            }

            // Verificar se telefone já existe (excluindo o próprio contato)
            if ($this->contactModel->phoneExists($userId, $phone, $id)) {
                $this->json(['success' => false, 'message' => 'Este telefone já está cadastrado em outro contato!'], 409);
                return;
            }

            $success = $this->contactModel->update($id, $userId, $name, $phone);

            if ($success) {
                // Processar tags
                $tags = $_POST['tags'] ?? [];
                $this->processTags($id, $tags, $userId);
                
                $this->json(['success' => true, 'message' => 'Contato atualizado com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao atualizar contato.'], 500);
            }
        }
    }

    /**
     * Deletar contato
     */
    public function delete($id) {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 🔒 SEGURANÇA: Validar token CSRF
            CSRF::validateOrDie();
            
            $user = $this->getCurrentUser();
            $userId = $user['id'];

            $success = $this->contactModel->delete($id, $userId);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Contato deletado com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao deletar contato.'], 500);
            }
        }
    }

    /**
     * Adicionar tag ao contato
     */
    public function addTag() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contactId = $_POST['contact_id'] ?? '';
            $tagId = $_POST['tag_id'] ?? '';

            if (empty($contactId) || empty($tagId)) {
                $this->json(['success' => false, 'message' => 'Dados inválidos.'], 400);
                return;
            }

            $success = $this->contactModel->addTag($contactId, $tagId);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Tag adicionada com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao adicionar tag.'], 500);
            }
        }
    }

    /**
     * Remover tag do contato
     */
    public function removeTag() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contactId = $_POST['contact_id'] ?? '';
            $tagId = $_POST['tag_id'] ?? '';

            if (empty($contactId) || empty($tagId)) {
                $this->json(['success' => false, 'message' => 'Dados inválidos.'], 400);
                return;
            }

            $success = $this->contactModel->removeTag($contactId, $tagId);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Tag removida com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao remover tag.'], 500);
            }
        }
    }
    
    /**
     * Deletar múltiplos contatos
     */
    public function deleteMultiple() {
        $this->requireAuth();
        
        // Validar CSRF Token
        require_once __DIR__ . '/../core/CSRF.php';
        CSRF::validateOrDie();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->getCurrentUser();
            $userId = $user['id'];
            
            // Receber IDs via POST (FormData)
            $idsJson = $_POST['ids'] ?? '';
            $ids = json_decode($idsJson, true);

            if (empty($ids) || !is_array($ids)) {
                $this->json(['success' => false, 'message' => 'Nenhum contato selecionado.'], 400);
                return;
            }

            $success = $this->contactModel->deleteMultiple($ids, $userId);

            if ($success) {
                $count = count($ids);
                $this->json([
                    'success' => true, 
                    'message' => "$count contato(s) deletado(s) com sucesso!",
                    'deleted' => $count
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao deletar contatos.'], 500);
            }
        }
    }

    /**
     * Importar contatos de CSV
     */
    public function import() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->getCurrentUser();
            $userId = $user['id'];

            // Verificar se arquivo foi enviado
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $this->json(['success' => false, 'message' => 'Erro ao fazer upload do arquivo.'], 400);
                return;
            }

            $file = $_FILES['csv_file'];

            // Verificar tamanho (5MB para CSV)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                $this->json(['success' => false, 'message' => 'Arquivo muito grande. Máximo: 5MB.'], 400);
                return;
            }

            // Verificar extensão
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_CSV_EXTENSIONS)) {
                $this->json(['success' => false, 'message' => 'Formato de arquivo inválido. Use CSV.'], 400);
                return;
            }

            // Ler arquivo CSV
            $csvData = [];
            if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $csvData[] = $data;
                }
                fclose($handle);
            }

            // Importar contatos
            $result = $this->contactModel->importFromCSV($userId, $csvData);

            // Montar mensagem detalhada
            $message = "✅ {$result['imported']} contatos importados com sucesso!";
            
            if ($result['duplicates'] > 0) {
                $message .= "\n⚠️ {$result['duplicates']} duplicatas ignoradas";
            }
            
            if (count($result['errors']) > 0) {
                $message .= "\n❌ " . count($result['errors']) . " erros encontrados";
            }

            $this->json([
                'success' => true,
                'message' => $message,
                'imported' => $result['imported'],
                'duplicates' => $result['duplicates'],
                'errors' => $result['errors'],
                'total_processed' => $result['total_processed']
            ]);
        }
    }

    /**
     * Exportar contatos para CSV
     */
    public function export() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        // Obter filtros
        $search = $_GET['search'] ?? null;
        $tagId = $_GET['tag'] ?? null;

        // Buscar contatos com filtros aplicados
        $contacts = $this->contactModel->getByUser($userId, $search, $tagId);

        // Configurar headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="contatos_zapx_' . date('Y-m-d_His') . '.csv"');

        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalho
        fputcsv($output, ['nome', 'telefone', 'tag']);
        
        // Dados dos contatos
        foreach ($contacts as $contact) {
            // Buscar tags do contato
            $contactTags = $this->contactModel->getContactTags($contact['id'], $userId);
            $tagNames = array_map(function($tag) {
                return $tag['name'];
            }, $contactTags);
            
            $tagString = implode('|', $tagNames); // Separar múltiplas tags com |
            
            fputcsv($output, [
                $contact['name'] ?: '',
                $contact['phone'],
                $tagString
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Download template CSV
     */
    public function downloadTemplate() {
        $this->requireAuth();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="template_contatos_zapx.csv"');

        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalho ATUALIZADO com tag
        fputcsv($output, ['nome', 'telefone', 'tag']);
        
        // Exemplos com diferentes formatos
        fputcsv($output, ['João Silva', '11999999999', 'Clientes']);
        fputcsv($output, ['Maria Santos', '5511988888888', 'VIP']);
        fputcsv($output, ['Pedro Costa', '(11) 97777-7777', 'Leads']);
        fputcsv($output, ['Ana Oliveira', '21987654321', 'VIP|Clientes']); // Múltiplas tags
        fputcsv($output, ['Carlos Lima', '11966666666', '']); // Sem tag
        
        fclose($output);
        exit;
    }

    /**
     * Obter tags de um contato específico
     */
    public function getContactTags() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $contactId = $_POST['contact_id'] ?? '';

        if (empty($contactId)) {
            $this->json(['success' => false, 'message' => 'ID do contato é obrigatório']);
            return;
        }

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        try {
            // Buscar tags do contato
            $contactTags = $this->contactModel->getContactTags($contactId, $userId);
            
            $this->json([
                'success' => true, 
                'tags' => $contactTags
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erro ao buscar tags: ' . $e->getMessage()]);
        }
    }

    /**
     * Processar tags do contato (adicionar/remover)
     */
    private function processTags($contactId, $selectedTags, $userId) {
        try {
            // Buscar tags atuais do contato
            $currentTags = $this->contactModel->getContactTags($contactId, $userId);
            $currentTagIds = array_column($currentTags, 'id');
            
            // Converter para array se necessário
            if (!is_array($selectedTags)) {
                $selectedTags = [];
            }
            
            // Tags para adicionar (estão selecionadas mas não estão no contato)
            $tagsToAdd = array_diff($selectedTags, $currentTagIds);
            
            // Tags para remover (estão no contato mas não estão selecionadas)
            $tagsToRemove = array_diff($currentTagIds, $selectedTags);
            
            // Adicionar novas tags
            foreach ($tagsToAdd as $tagId) {
                $this->contactModel->addTag($contactId, $tagId, $userId);
            }
            
            // Remover tags desmarcadas
            foreach ($tagsToRemove as $tagId) {
                $this->contactModel->removeTag($contactId, $tagId, $userId);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao processar tags: " . $e->getMessage());
        }
    }
}
