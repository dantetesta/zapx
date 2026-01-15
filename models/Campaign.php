<?php
/**
 * Model de Campanhas de Disparo
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-14 23:10:00
 */

class Campaign {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Criar nova campanha
     */
    public function create($data) {
        $sql = "INSERT INTO dispatch_campaigns 
                (user_id, name, message, media_type, media_path, media_filename, tag_id, total_contacts, min_interval, max_interval) 
                VALUES (:user_id, :name, :message, :media_type, :media_path, :media_filename, :tag_id, :total_contacts, :min_interval, :max_interval)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':name' => $data['name'] ?? null,
            ':message' => $data['message'],
            ':media_type' => $data['media_type'] ?? 'text',
            ':media_path' => $data['media_path'] ?? null,
            ':media_filename' => $data['media_filename'] ?? null,
            ':tag_id' => $data['tag_id'] ?? null,
            ':total_contacts' => $data['total_contacts'] ?? 0,
            ':min_interval' => $data['min_interval'] ?? DISPATCH_MIN_INTERVAL,
            ':max_interval' => $data['max_interval'] ?? DISPATCH_MAX_INTERVAL
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Buscar campanha por ID
     */
    public function findById($id, $userId = null) {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM dispatch_queue WHERE campaign_id = c.id AND status = 'pending') as pending_count,
                       (SELECT COUNT(*) FROM dispatch_queue WHERE campaign_id = c.id AND status = 'processing') as processing_count
                FROM dispatch_campaigns c 
                WHERE c.id = :id";
        
        if ($userId) {
            $sql .= " AND c.user_id = :user_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = [':id' => $id];
        if ($userId) {
            $params[':user_id'] = $userId;
        }
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Listar campanhas do usuário
     */
    public function getByUser($userId, $limit = 50) {
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM dispatch_queue WHERE campaign_id = c.id AND status = 'pending') as pending_count
                FROM dispatch_campaigns c 
                WHERE c.user_id = :user_id 
                ORDER BY c.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Atualizar status da campanha
     */
    public function updateStatus($id, $status) {
        $updates = ['status' => $status];
        
        switch ($status) {
            case 'running':
                $updates['started_at'] = date('Y-m-d H:i:s');
                $updates['paused_at'] = null;
                break;
            case 'paused':
                $updates['paused_at'] = date('Y-m-d H:i:s');
                break;
            case 'completed':
            case 'cancelled':
                $updates['completed_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        $setParts = [];
        $params = [':id' => $id];
        
        foreach ($updates as $key => $value) {
            $setParts[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        $sql = "UPDATE dispatch_campaigns SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Incrementar contadores
     */
    public function incrementCounter($id, $field) {
        $allowedFields = ['sent_count', 'failed_count'];
        if (!in_array($field, $allowedFields)) {
            return false;
        }
        
        $sql = "UPDATE dispatch_campaigns 
                SET $field = $field + 1, last_processed_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verificar se campanha está completa
     */
    public function checkCompletion($id) {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM dispatch_queue WHERE campaign_id = :id AND status IN ('pending', 'processing')) as remaining
                FROM dispatch_campaigns WHERE id = :id2";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':id2' => $id]);
        $result = $stmt->fetch();
        
        if ($result && $result['remaining'] == 0) {
            $this->updateStatus($id, 'completed');
            return true;
        }
        return false;
    }

    /**
     * Obter campanhas em execução (para o processor)
     */
    public function getRunningCampaigns() {
        $sql = "SELECT * FROM dispatch_campaigns WHERE status = 'running' ORDER BY last_processed_at ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Deletar campanha
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM dispatch_campaigns WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * Obter estatísticas da campanha
     */
    public function getStats($id) {
        $sql = "SELECT 
                    c.*,
                    (SELECT COUNT(*) FROM dispatch_queue WHERE campaign_id = c.id) as total_queue,
                    (SELECT COUNT(*) FROM dispatch_queue WHERE campaign_id = c.id AND status = 'sent') as sent_queue,
                    (SELECT COUNT(*) FROM dispatch_queue WHERE campaign_id = c.id AND status = 'failed') as failed_queue,
                    (SELECT COUNT(*) FROM dispatch_queue WHERE campaign_id = c.id AND status = 'pending') as pending_queue,
                    (SELECT COUNT(*) FROM dispatch_queue WHERE campaign_id = c.id AND status = 'processing') as processing_queue
                FROM dispatch_campaigns c
                WHERE c.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
