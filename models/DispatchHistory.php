<?php
/**
 * Model de Histórico de Disparos
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 */

class DispatchHistory {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Criar registro de disparo
     */
    public function create($userId, $contactId, $message, $mediaType = 'text', $thumbnailPath = null) {
        $sql = "INSERT INTO dispatch_history (user_id, contact_id, message, media_type, thumbnail_path, status) 
                VALUES (:user_id, :contact_id, :message, :media_type, :thumbnail_path, 'pending')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':contact_id' => $contactId,
            ':message' => $message,
            ':media_type' => $mediaType,
            ':thumbnail_path' => $thumbnailPath
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Atualizar status do disparo
     */
    public function updateStatus($id, $status, $errorMessage = null) {
        $sql = "UPDATE dispatch_history 
                SET status = :status, error_message = :error_message, sent_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':error_message' => $errorMessage,
            ':id' => $id
        ]);
    }

    /**
     * Buscar histórico por usuário
     */
    public function getByUser($userId, $limit = 100) {
        $sql = "SELECT dh.*, c.name as contact_name, c.phone as contact_phone
                FROM dispatch_history dh
                INNER JOIN contacts c ON dh.contact_id = c.id
                WHERE dh.user_id = :user_id
                ORDER BY dh.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Contar disparos por status
     */
    public function countByStatus($userId, $status) {
        $sql = "SELECT COUNT(*) as total 
                FROM dispatch_history 
                WHERE user_id = :user_id AND status = :status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':status' => $status]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Obter estatísticas de disparos
     */
    public function getStats($userId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM dispatch_history 
                WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Buscar relatório com filtros e paginação
     */
    public function getReport($userId, $filters = [], $page = 1, $perPage = 50) {
        $where = ["dh.user_id = :user_id"];
        $params = [':user_id' => $userId];
        
        // Filtro por status
        if (!empty($filters['status'])) {
            $where[] = "dh.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        // Filtro por data inicial
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(dh.sent_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        // Filtro por data final
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(dh.sent_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        // Filtro por contato (nome ou telefone)
        if (!empty($filters['search'])) {
            $where[] = "(c.name LIKE :search OR c.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT dh.*, c.name as contact_name, c.phone as contact_phone
                FROM dispatch_history dh
                INNER JOIN contacts c ON dh.contact_id = c.id
                WHERE $whereClause
                ORDER BY dh.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Contar total de registros para relatório
     */
    public function countReport($userId, $filters = []) {
        $where = ["dh.user_id = :user_id"];
        $params = [':user_id' => $userId];
        
        if (!empty($filters['status'])) {
            $where[] = "dh.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(dh.sent_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(dh.sent_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(c.name LIKE :search OR c.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) as total
                FROM dispatch_history dh
                INNER JOIN contacts c ON dh.contact_id = c.id
                WHERE $whereClause";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Exportar relatório completo (sem paginação)
     */
    public function exportReport($userId, $filters = []) {
        $where = ["dh.user_id = :user_id"];
        $params = [':user_id' => $userId];
        
        if (!empty($filters['status'])) {
            $where[] = "dh.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(dh.sent_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(dh.sent_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(c.name LIKE :search OR c.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT dh.*, c.name as contact_name, c.phone as contact_phone
                FROM dispatch_history dh
                INNER JOIN contacts c ON dh.contact_id = c.id
                WHERE $whereClause
                ORDER BY dh.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obter estatísticas dos resultados filtrados
     */
    public function getFilteredStats($userId, $filters = []) {
        $where = ["dh.user_id = :user_id"];
        $params = [':user_id' => $userId];
        
        // Aplicar mesmos filtros do getReport
        if (!empty($filters['status'])) {
            $where[] = "dh.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(dh.sent_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(dh.sent_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(c.name LIKE :search OR c.phone LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN dh.status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN dh.status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN dh.status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM dispatch_history dh
                INNER JOIN contacts c ON dh.contact_id = c.id
                WHERE $whereClause";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Deletar múltiplos registros de disparo
     */
    public function deleteMultiple($ids, $userId, $isAdmin = false) {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'Nenhum registro selecionado'];
        }
        
        // Buscar thumbnails antes de deletar
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        if ($isAdmin) {
            $sql = "SELECT id, thumbnail_path FROM dispatch_history WHERE id IN ($placeholders)";
            $params = $ids;
        } else {
            $sql = "SELECT id, thumbnail_path FROM dispatch_history WHERE id IN ($placeholders) AND user_id = ?";
            $params = array_merge($ids, [$userId]);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll();
        
        // Deletar thumbnails
        $deletedThumbnails = 0;
        foreach ($records as $record) {
            if (!empty($record['thumbnail_path'])) {
                $thumbnailPath = __DIR__ . '/../' . $record['thumbnail_path'];
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                    $deletedThumbnails++;
                }
            }
        }
        
        // Deletar registros do banco
        if ($isAdmin) {
            $sql = "DELETE FROM dispatch_history WHERE id IN ($placeholders)";
            $params = $ids;
        } else {
            $sql = "DELETE FROM dispatch_history WHERE id IN ($placeholders) AND user_id = ?";
            $params = array_merge($ids, [$userId]);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $deletedCount = $stmt->rowCount();
        
        return [
            'success' => true,
            'deleted' => $deletedCount,
            'thumbnails_deleted' => $deletedThumbnails
        ];
    }
    
    /**
     * Limpar todos os registros (apenas admin)
     */
    public function deleteAll($userId, $isAdmin = false) {
        if (!$isAdmin) {
            return ['success' => false, 'message' => 'Apenas administradores podem limpar todos os registros'];
        }
        
        // Buscar todos os thumbnails
        $sql = "SELECT thumbnail_path FROM dispatch_history WHERE thumbnail_path IS NOT NULL";
        $stmt = $this->db->query($sql);
        $records = $stmt->fetchAll();
        
        // Deletar thumbnails
        $deletedThumbnails = 0;
        foreach ($records as $record) {
            $thumbnailPath = __DIR__ . '/../' . $record['thumbnail_path'];
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
                $deletedThumbnails++;
            }
        }
        
        // Deletar todos os registros
        $sql = "DELETE FROM dispatch_history";
        $stmt = $this->db->query($sql);
        $deletedCount = $stmt->rowCount();
        
        return [
            'success' => true,
            'deleted' => $deletedCount,
            'thumbnails_deleted' => $deletedThumbnails
        ];
    }
    
    /**
     * Obter disparos por dia (para gráfico)
     * 
     * @param int $userId ID do usuário
     * @param int $days Número de dias para buscar
     * @return array Array com datas e quantidades
     */
    public function getDispatchesByDay($userId, $days = 7) {
        $sql = "SELECT 
                    DATE(sent_at) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM dispatch_history
                WHERE user_id = :user_id
                AND sent_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY DATE(sent_at)
                ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':days' => $days
        ]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obter taxa de sucesso/falha (para gráfico)
     * 
     * @param int $userId ID do usuário
     * @return array Array com contadores por status
     */
    public function getSuccessRate($userId) {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM dispatch_history
                WHERE user_id = :user_id
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        $results = $stmt->fetchAll();
        
        // Formatar resultado
        $data = [
            'sent' => 0,
            'failed' => 0,
            'pending' => 0
        ];
        
        foreach ($results as $row) {
            $data[$row['status']] = (int)$row['count'];
        }
        
        return $data;
    }
}
