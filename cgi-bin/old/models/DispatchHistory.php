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
    public function create($userId, $contactId, $message) {
        $sql = "INSERT INTO dispatch_history (user_id, contact_id, message, status) 
                VALUES (:user_id, :contact_id, :message, 'pending')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':contact_id' => $contactId,
            ':message' => $message
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
}
