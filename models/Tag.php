<?php
/**
 * Model de Tag/Categoria
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 */

class Tag {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Criar nova tag
     */
    public function create($userId, $name, $color = '#3B82F6') {
        $sql = "INSERT INTO tags (user_id, name, color) VALUES (:user_id, :name, :color)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => $name,
            ':color' => $color
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Buscar tag por ID
     */
    public function findById($id, $userId) {
        $sql = "SELECT * FROM tags WHERE id = :id AND user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Listar tags do usuário
     */
    public function getByUser($userId) {
        $sql = "SELECT t.*, COUNT(ct.contact_id) as contact_count
                FROM tags t
                LEFT JOIN contact_tags ct ON t.id = ct.tag_id
                WHERE t.user_id = :user_id
                GROUP BY t.id
                ORDER BY t.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Atualizar tag
     */
    public function update($id, $userId, $name, $color) {
        $sql = "UPDATE tags SET name = :name, color = :color WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':color' => $color,
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    /**
     * Deletar tag
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM tags WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * Contar tags do usuário
     */
    public function countByUser($userId) {
        $sql = "SELECT COUNT(*) as total FROM tags WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['total'];
    }
}
