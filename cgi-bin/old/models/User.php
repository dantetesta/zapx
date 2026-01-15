<?php
/**
 * Model de Usuário
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 */

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Criar novo usuário
     */
    public function create($data) {
        $sql = "INSERT INTO users (name, email, password, is_admin, message_limit, messages_sent, limit_reset_date) 
                VALUES (:name, :email, :password, :is_admin, :message_limit, :messages_sent, :limit_reset_date)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':is_admin' => $data['is_admin'] ?? 0,
            ':message_limit' => $data['message_limit'] ?? 1000,
            ':messages_sent' => 0,
            ':limit_reset_date' => date('Y-m-d')
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Buscar usuário por email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Buscar usuário por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Listar todos os usuários
     */
    public function getAll() {
        $sql = "SELECT 
                    id, 
                    name, 
                    email, 
                    is_admin, 
                    evolution_instance,
                    evolution_instance_token,
                    evolution_phone_number,
                    evolution_status,
                    evolution_qrcode,
                    evolution_created_at,
                    message_limit,
                    messages_sent,
                    limit_reset_date,
                    created_at 
                FROM users 
                ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Atualizar usuário
     */
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['is_admin'])) {
            $fields[] = "is_admin = :is_admin";
            $params[':is_admin'] = $data['is_admin'];
        }
        if (isset($data['evolution_instance'])) {
            $fields[] = "evolution_instance = :evolution_instance";
            $params[':evolution_instance'] = $data['evolution_instance'];
        }
        if (isset($data['evolution_instance_token'])) {
            $fields[] = "evolution_instance_token = :evolution_instance_token";
            $params[':evolution_instance_token'] = $data['evolution_instance_token'];
        }
        if (isset($data['evolution_phone_number'])) {
            $fields[] = "evolution_phone_number = :evolution_phone_number";
            $params[':evolution_phone_number'] = $data['evolution_phone_number'];
        }
        if (isset($data['evolution_status'])) {
            $fields[] = "evolution_status = :evolution_status";
            $params[':evolution_status'] = $data['evolution_status'];
        }
        if (isset($data['evolution_qrcode'])) {
            $fields[] = "evolution_qrcode = :evolution_qrcode";
            $params[':evolution_qrcode'] = $data['evolution_qrcode'];
        }
        if (isset($data['message_limit'])) {
            $fields[] = "message_limit = :message_limit";
            $params[':message_limit'] = $data['message_limit'];
        }
        if (isset($data['default_country_code'])) {
            $fields[] = "default_country_code = :default_country_code";
            $params[':default_country_code'] = $data['default_country_code'];
        }
        if (isset($data['timezone'])) {
            $fields[] = "timezone = :timezone";
            $params[':timezone'] = $data['timezone'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Atualizar configuração da Evolution API
     */
    public function updateEvolutionConfig($userId, $data) {
        error_log("=== updateEvolutionConfig CHAMADO ===");
        error_log("User ID recebido: " . $userId);
        error_log("Data recebida: " . json_encode($data));
        
        $fields = [];
        $params = [':id' => $userId];

        // Permitir valores NULL
        $allowedFields = [
            'evolution_instance',
            'evolution_instance_token',
            'evolution_phone_number',
            'evolution_status',
            'evolution_qrcode',
            'evolution_created_at'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
                error_log("Campo adicionado: $field = " . $data[$field]);
            }
        }

        if (empty($fields)) {
            error_log("❌ Nenhum campo para atualizar!");
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        error_log("SQL gerado: " . $sql);
        error_log("Params: " . json_encode($params));
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                $rowCount = $stmt->rowCount();
                error_log("✅ UPDATE executado! Linhas afetadas: " . $rowCount);
                
                if ($rowCount === 0) {
                    error_log("⚠️ ATENÇÃO: UPDATE não afetou nenhuma linha! User ID pode não existir.");
                }
            } else {
                error_log("❌ UPDATE falhou!");
                error_log("Error Info: " . json_encode($stmt->errorInfo()));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("❌ EXCEÇÃO no UPDATE: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletar usuário
     */
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verificar senha
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Contar total de usuários
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM users";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Verificar e resetar limite mensal se necessário
     */
    public function checkAndResetMonthlyLimit($userId) {
        $user = $this->findById($userId);
        if (!$user) return false;

        $lastReset = new DateTime($user['limit_reset_date']);
        $now = new DateTime();
        
        // Calcular diferença em meses
        $interval = $lastReset->diff($now);
        $monthsDiff = ($interval->y * 12) + $interval->m;

        // Se passou 1 mês ou mais, resetar contador
        if ($monthsDiff >= 1) {
            $sql = "UPDATE users 
                    SET messages_sent = 0, 
                        limit_reset_date = :reset_date 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':reset_date' => $now->format('Y-m-d'),
                ':id' => $userId
            ]);
        }

        return true;
    }

    /**
     * Verificar se usuário pode enviar mensagens
     */
    public function canSendMessage($userId) {
        // Verificar e resetar limite se necessário
        $this->checkAndResetMonthlyLimit($userId);

        $user = $this->findById($userId);
        if (!$user) return false;

        // Admin tem limite ilimitado
        if ($user['is_admin'] == 1) return true;

        // Verificar se ainda tem saldo
        return $user['messages_sent'] < $user['message_limit'];
    }

    /**
     * Obter saldo de mensagens disponíveis
     */
    public function getMessageBalance($userId) {
        $this->checkAndResetMonthlyLimit($userId);
        
        $user = $this->findById($userId);
        if (!$user) {
            return [
                'limit' => 0,
                'sent' => 0,
                'remaining' => 0,
                'reset_date' => date('Y-m-d')
            ];
        }

        // Admin tem limite ilimitado
        if ($user['is_admin'] == 1) {
            return [
                'limit' => 'ilimitado',
                'sent' => $user['messages_sent'],
                'remaining' => 'ilimitado',
                'reset_date' => $user['limit_reset_date']
            ];
        }

        $remaining = $user['message_limit'] - $user['messages_sent'];
        
        return [
            'limit' => $user['message_limit'],
            'sent' => $user['messages_sent'],
            'remaining' => max(0, $remaining),
            'reset_date' => $user['limit_reset_date']
        ];
    }

    /**
     * Incrementar contador de mensagens enviadas
     */
    public function incrementMessageCount($userId, $count = 1) {
        $sql = "UPDATE users 
                SET messages_sent = messages_sent + :count 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':count' => $count,
            ':id' => $userId
        ]);
    }

    /**
     * Atualizar limite de mensagens do usuário
     */
    public function updateMessageLimit($userId, $newLimit) {
        $sql = "UPDATE users 
                SET message_limit = :limit 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':limit' => $newLimit,
            ':id' => $userId
        ]);
    }

    /**
     * Resetar contador de mensagens manualmente
     */
    public function resetMessageCount($userId) {
        $sql = "UPDATE users 
                SET messages_sent = 0, 
                    limit_reset_date = :reset_date 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':reset_date' => date('Y-m-d'),
            ':id' => $userId
        ]);
    }
}
