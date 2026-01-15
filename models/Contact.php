<?php
/**
 * Model de Contato
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 20:23:00
 */

class Contact {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Criar novo contato
     */
    public function create($userId, $name, $phone) {
        // Limpar telefone (remover caracteres especiais)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        $sql = "INSERT INTO contacts (user_id, name, phone) VALUES (:user_id, :name, :phone)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => $name,
            ':phone' => $phone
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Buscar contato por ID
     */
    public function findById($id, $userId) {
        $sql = "SELECT c.*, GROUP_CONCAT(t.id) as tag_ids, GROUP_CONCAT(t.name) as tag_names, GROUP_CONCAT(t.color) as tag_colors
                FROM contacts c
                LEFT JOIN contact_tags ct ON c.id = ct.contact_id
                LEFT JOIN tags t ON ct.tag_id = t.id
                WHERE c.id = :id AND c.user_id = :user_id
                GROUP BY c.id
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Listar contatos do usuário com paginação opcional
     * 
     * @param int $userId ID do usuário
     * @param string|null $search Termo de busca
     * @param int|null $tagId ID da tag para filtrar
     * @param int|null $page Número da página (null = sem paginação)
     * @param int $perPage Itens por página
     * @return array Lista de contatos
     */
    public function getByUser($userId, $search = null, $tagId = null, $page = null, $perPage = 20) {
        try {
            // Base SQL
            $sql = "SELECT c.*, GROUP_CONCAT(DISTINCT t.id) as tag_ids, 
                    GROUP_CONCAT(DISTINCT t.name) as tag_names, 
                    GROUP_CONCAT(DISTINCT t.color) as tag_colors
                    FROM contacts c
                    LEFT JOIN contact_tags ct ON c.id = ct.contact_id
                    LEFT JOIN tags t ON ct.tag_id = t.id
                    WHERE c.user_id = ?";
            
            $params = [$userId];
            
            // Adicionar busca se existir
            if (!empty($search)) {
                $sql .= " AND (LOWER(c.name) LIKE LOWER(?) OR c.phone LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            // Adicionar filtro de tag se existir
            if (!empty($tagId)) {
                $sql .= " AND c.id IN (SELECT contact_id FROM contact_tags WHERE tag_id = ?)";
                $params[] = $tagId;
            }
            
            $sql .= " GROUP BY c.id ORDER BY c.created_at DESC";
            
            // Adicionar paginação apenas se $page for especificado
            if ($page !== null) {
                $offset = ($page - 1) * $perPage;
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = (int)$perPage;
                $params[] = (int)$offset;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            
            // LOG DE DEBUG
            error_log("=== BUSCA DE CONTATOS ===");
            error_log("User ID: " . $userId);
            error_log("Search: " . ($search ?? 'null'));
            error_log("Tag ID: " . ($tagId ?? 'null'));
            error_log("Page: " . ($page ?? 'SEM PAGINAÇÃO'));
            error_log("Per Page: " . $perPage);
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            error_log("Resultados encontrados: " . count($results));
            error_log("========================");
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("=== ERRO AO BUSCAR CONTATOS ===");
            error_log("Mensagem: " . $e->getMessage());
            error_log("===============================");
            return [];
        }
    }
    
    /**
     * Contar total de contatos (para paginação)
     */
    public function countByUser($userId, $search = null, $tagId = null) {
        try {
            $sql = "SELECT COUNT(DISTINCT c.id) as total
                    FROM contacts c
                    LEFT JOIN contact_tags ct ON c.id = ct.contact_id
                    WHERE c.user_id = ?";
            
            $params = [$userId];
            
            // Adicionar busca se existir
            if (!empty($search)) {
                $sql .= " AND (LOWER(c.name) LIKE LOWER(?) OR c.phone LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            // Adicionar filtro de tag se existir
            if (!empty($tagId)) {
                $sql .= " AND c.id IN (SELECT contact_id FROM contact_tags WHERE tag_id = ?)";
                $params[] = $tagId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("=== ERRO AO CONTAR CONTATOS ===");
            error_log("Mensagem: " . $e->getMessage());
            error_log("===============================");
            return 0;
        }
    }

    /**
     * Atualizar contato
     */
    public function update($id, $userId, $name, $phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        $sql = "UPDATE contacts SET name = :name, phone = :phone WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    /**
     * Deletar contato
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM contacts WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }
    
    /**
     * Deletar múltiplos contatos
     */
    public function deleteMultiple($ids, $userId) {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        
        // Criar placeholders para IN clause
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $sql = "DELETE FROM contacts WHERE id IN ($placeholders) AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        
        // Bind dos IDs + userId
        $params = array_merge($ids, [$userId]);
        
        return $stmt->execute($params);
    }

    /**
     * Adicionar tag ao contato
     */
    public function addTag($contactId, $tagId) {
        $sql = "INSERT IGNORE INTO contact_tags (contact_id, tag_id) VALUES (:contact_id, :tag_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':contact_id' => $contactId, ':tag_id' => $tagId]);
    }

    /**
     * Remover tag do contato
     */
    public function removeTag($contactId, $tagId) {
        $sql = "DELETE FROM contact_tags WHERE contact_id = :contact_id AND tag_id = :tag_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':contact_id' => $contactId, ':tag_id' => $tagId]);
    }

    /**
     * Remover todas as tags do contato
     */
    public function removeAllTags($contactId) {
        $sql = "DELETE FROM contact_tags WHERE contact_id = :contact_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':contact_id' => $contactId]);
    }

    /**
     * Importar contatos de CSV com suporte a tags e DDI padrão
     */
    public function importFromCSV($userId, $csvData) {
        $imported = 0;
        $duplicates = 0;
        $errors = [];
        $processedPhones = []; // Rastrear telefones já processados nesta importação
        
        // Buscar DDI padrão do usuário
        $sql = "SELECT default_country_code FROM users WHERE id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch();
        $defaultDDI = $user['default_country_code'] ?? '55';

        foreach ($csvData as $index => $row) {
            // Pular linha de cabeçalho
            if ($index === 0) continue;

            // Verificar se tem pelo menos o telefone
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            // Determinar qual coluna é o telefone
            $phone = '';
            $name = '';
            $tagString = '';

            // Se primeira coluna parece ser telefone
            if (preg_match('/[0-9]/', $row[0])) {
                $phone = $row[0];
                $name = $row[1] ?? '';
                $tagString = $row[2] ?? '';
            } else {
                $name = $row[0];
                $phone = $row[1] ?? '';
                $tagString = $row[2] ?? '';
            }

            // Limpar telefone (remover apenas espaços, parênteses, hífens, mas manter +)
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            // Remover + do início se existir
            $phone = ltrim($phone, '+');

            // Verificar se número tem DDI
            // Se tiver menos de 12 dígitos, provavelmente não tem DDI
            if (strlen($phone) < 12) {
                // Adicionar DDI padrão do usuário
                $phone = $defaultDDI . $phone;
            }

            // Validar telefone (mínimo 10 dígitos)
            if (empty($phone) || strlen($phone) < 10) {
                $errors[] = "Linha " . ($index + 1) . ": Telefone inválido";
                continue;
            }

            // Verificar duplicata na planilha (mesmo telefone aparece 2x)
            if (in_array($phone, $processedPhones)) {
                $duplicates++;
                continue;
            }

            // Verificar se telefone já existe no banco
            if ($this->phoneExists($userId, $phone)) {
                $duplicates++;
                continue;
            }

            try {
                // Criar contato
                $contactId = $this->create($userId, $name ?: null, $phone);
                
                // Adicionar à lista de processados
                $processedPhones[] = $phone;
                
                // Processar tags se houver
                if (!empty($tagString)) {
                    $this->processContactTags($contactId, $userId, $tagString);
                }
                
                $imported++;
            } catch (Exception $e) {
                $errors[] = "Linha " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'duplicates' => $duplicates,
            'errors' => $errors,
            'total_processed' => count($csvData) - 1 // -1 para excluir cabeçalho
        ];
    }

    /**
     * Processar tags do contato durante importação
     */
    private function processContactTags($contactId, $userId, $tagString) {
        // Separar múltiplas tags (separadas por | ou ;)
        $tagNames = preg_split('/[|;]/', $tagString);
        
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;

            // Buscar ou criar tag
            $tag = $this->findOrCreateTag($userId, $tagName);
            
            if ($tag) {
                // Vincular tag ao contato
                $this->addTagToContact($contactId, $tag['id']);
            }
        }
    }

    /**
     * Buscar ou criar tag
     */
    private function findOrCreateTag($userId, $tagName) {
        // Buscar tag existente
        $sql = "SELECT * FROM tags WHERE user_id = :user_id AND name = :name LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => $tagName
        ]);
        $tag = $stmt->fetch();

        // Se não existir, criar
        if (!$tag) {
            // Cores vibrantes e escuras (evitar amarelo claro, cinza claro, branco)
            $colors = [
                'blue',      // Azul
                'indigo',    // Índigo
                'purple',    // Roxo
                'pink',      // Rosa
                'red',       // Vermelho
                'orange',    // Laranja
                'green',     // Verde
                'teal',      // Verde-azulado
                'cyan'       // Ciano
            ];
            $randomColor = $colors[array_rand($colors)];
            
            $sql = "INSERT INTO tags (user_id, name, color) VALUES (:user_id, :name, :color)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':name' => $tagName,
                ':color' => $randomColor
            ]);
            
            $tagId = $this->db->lastInsertId();
            
            $tag = [
                'id' => $tagId,
                'name' => $tagName,
                'color' => $randomColor
            ];
        }

        return $tag;
    }

    /**
     * Adicionar tag ao contato
     */
    private function addTagToContact($contactId, $tagId) {
        // Verificar se já existe o vínculo
        $sql = "SELECT id FROM contact_tags WHERE contact_id = :contact_id AND tag_id = :tag_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':contact_id' => $contactId,
            ':tag_id' => $tagId
        ]);
        
        if ($stmt->fetch()) {
            return true; // Já vinculado
        }

        // Criar vínculo
        $sql = "INSERT INTO contact_tags (contact_id, tag_id) VALUES (:contact_id, :tag_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':contact_id' => $contactId,
            ':tag_id' => $tagId
        ]);
    }

    /**
     * Verificar se telefone já existe para o usuário
     */
    public function phoneExists($userId, $phone, $excludeId = null) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        $sql = "SELECT id FROM contacts WHERE user_id = :user_id AND phone = :phone";
        $params = [':user_id' => $userId, ':phone' => $phone];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Obter tags de um contato específico
     */
    public function getContactTags($contactId, $userId) {
        $sql = "SELECT t.id, t.name, t.color 
                FROM tags t 
                INNER JOIN contact_tags ct ON t.id = ct.tag_id 
                WHERE ct.contact_id = :contact_id AND t.user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':contact_id' => $contactId,
            ':user_id' => $userId
        ]);
        
        return $stmt->fetchAll();
    }
}
