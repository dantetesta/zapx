<?php
/**
 * Model de Fila de Disparo
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-14 23:10:00
 */

class Queue {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Adicionar item na fila
     */
    public function addItem($campaignId, $contactId) {
        $sql = "INSERT INTO dispatch_queue (campaign_id, contact_id) VALUES (:campaign_id, :contact_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':campaign_id' => $campaignId,
            ':contact_id' => $contactId
        ]);
    }

    /**
     * Adicionar múltiplos itens na fila (batch insert)
     */
    public function addBatch($campaignId, $contactIds) {
        if (empty($contactIds)) {
            return 0;
        }

        $sql = "INSERT INTO dispatch_queue (campaign_id, contact_id) VALUES ";
        $values = [];
        $params = [];
        
        foreach ($contactIds as $index => $contactId) {
            $values[] = "(:campaign_id_{$index}, :contact_id_{$index})";
            $params[":campaign_id_{$index}"] = $campaignId;
            $params[":contact_id_{$index}"] = $contactId;
        }
        
        $sql .= implode(', ', $values);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return count($contactIds);
    }

    /**
     * Obter próximo item para processar
     */
    public function getNextItem($campaignId) {
        $sql = "SELECT q.*, c.name as contact_name, c.phone as contact_phone
                FROM dispatch_queue q
                INNER JOIN contacts c ON q.contact_id = c.id
                WHERE q.campaign_id = :campaign_id 
                AND q.status = 'pending'
                ORDER BY q.id ASC
                LIMIT 1
                FOR UPDATE";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':campaign_id' => $campaignId]);
        return $stmt->fetch();
    }

    /**
     * Marcar item como processando
     */
    public function markProcessing($id) {
        $sql = "UPDATE dispatch_queue SET status = 'processing', scheduled_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Marcar item como enviado
     */
    public function markSent($id) {
        $sql = "UPDATE dispatch_queue SET status = 'sent', sent_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Marcar item como falhou
     */
    public function markFailed($id, $errorMessage = null) {
        $sql = "UPDATE dispatch_queue SET status = 'failed', error_message = :error, attempts = attempts + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':error' => $errorMessage]);
    }

    /**
     * Cancelar itens pendentes de uma campanha
     */
    public function cancelPending($campaignId) {
        $sql = "UPDATE dispatch_queue SET status = 'cancelled' WHERE campaign_id = :campaign_id AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':campaign_id' => $campaignId]);
    }

    /**
     * Resetar itens em processamento (stuck) para uma campanha
     */
    public function resetStuck($campaignId, $timeoutMinutes = 5) {
        $sql = "UPDATE dispatch_queue 
                SET status = 'pending', scheduled_at = NULL 
                WHERE campaign_id = :campaign_id 
                AND status = 'processing' 
                AND scheduled_at < DATE_SUB(NOW(), INTERVAL :timeout MINUTE)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':campaign_id' => $campaignId,
            ':timeout' => $timeoutMinutes
        ]);
    }

    /**
     * Obter itens da fila para exibição
     */
    public function getItems($campaignId, $limit = 100, $status = null) {
        $sql = "SELECT q.*, c.name as contact_name, c.phone as contact_phone
                FROM dispatch_queue q
                INNER JOIN contacts c ON q.contact_id = c.id
                WHERE q.campaign_id = :campaign_id";
        
        if ($status) {
            $sql .= " AND q.status = :status";
        }
        
        $sql .= " ORDER BY q.id ASC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        if ($status) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Contar itens por status
     */
    public function countByStatus($campaignId) {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM dispatch_queue
                WHERE campaign_id = :campaign_id
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':campaign_id' => $campaignId]);
        
        $results = $stmt->fetchAll();
        $counts = [
            'pending' => 0,
            'processing' => 0,
            'sent' => 0,
            'failed' => 0,
            'cancelled' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int)$row['count'];
        }
        
        return $counts;
    }

    /**
     * Verificar se há itens pendentes
     */
    public function hasPending($campaignId) {
        $sql = "SELECT COUNT(*) as count FROM dispatch_queue WHERE campaign_id = :campaign_id AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':campaign_id' => $campaignId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Obter itens com informações de contato para relatórios
     */
    public function getWithContactInfo($campaignId, $limit = 500) {
        $sql = "SELECT q.*, c.name as contact_name, c.phone as contact_phone
                FROM dispatch_queue q
                INNER JOIN contacts c ON q.contact_id = c.id
                WHERE q.campaign_id = :campaign_id
                ORDER BY 
                    CASE q.status 
                        WHEN 'failed' THEN 1 
                        WHEN 'sent' THEN 2 
                        WHEN 'processing' THEN 3 
                        WHEN 'pending' THEN 4 
                        ELSE 5 
                    END,
                    q.sent_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
