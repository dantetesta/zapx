<?php
/**
 * Model de Saudações Personalizadas
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2026-01-15 18:32:00
 */

class Greeting {
    private $db;
    
    // Saudações padrão para novos usuários
    private $defaultGreetings = [
        '{periodo}, {nome}, tudo bem?',
        '{periodo}, {nome}, como vai?',
        '{periodo}, {nome}, espero que esteja bem!',
        '{periodo}, {nome}, tudo certo por aí?',
        'Olá {nome}, {periodo}!',
        'Oi {nome}, {periodo}, tudo bem?',
        '{nome}, {periodo}, como está?',
        '{periodo}! Tudo bem, {nome}?',
        '{periodo}, {nome}, passando para falar com você',
        'Tudo bem {nome}? {periodo}!',
        '{periodo}, tudo tranquilo {nome}?',
        'Olá {nome}, {periodo}, espero que esteja tudo certo!',
        '{nome}, {periodo}! Como estão as coisas?',
        '{periodo} {nome}, tudo em ordem?',
        'Oi {nome}! {periodo}, tudo certo?',
        '{periodo}, {nome}, como está por aí?',
        '{nome}, olá! {periodo}!',
        '{periodo}! {nome}, espero que esteja bem!',
        'Como vai {nome}? {periodo}!',
        '{periodo}, passando para falar com você, {nome}',
        '{nome}, {periodo}, tudo tranquilo?',
        'Olá! {periodo}, {nome}!',
        '{periodo} {nome}! Tudo bem por aí?',
        '{nome}, {periodo}, espero que esteja tudo bem!',
        'Tudo certo {nome}? {periodo}!',
        '{periodo}, {nome}! Como você está?',
        'Oi! {periodo}, {nome}, tudo bem?',
        '{nome}, como vai? {periodo}!',
        '{periodo}! Olá {nome}!',
        '{periodo}, {nome}, desejo que esteja bem!'
    ];
    
    // Adjetivos para substituir {nome} quando não houver nome
    private $adjectives = [
        'tudo bem',
        'tudo certo',
        'tudo tranquilo',
        'como vai',
        'como está'
    ];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Buscar saudações ativas do usuário
    public function getByUser($userId) {
        $sql = "SELECT * FROM user_greetings WHERE user_id = :user_id AND is_active = 1 ORDER BY sort_order ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Buscar todas as saudações do usuário (incluindo inativas)
    public function getAllByUser($userId) {
        $sql = "SELECT * FROM user_greetings WHERE user_id = :user_id ORDER BY sort_order ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Buscar saudação por ID
    public function findById($id, $userId) {
        $sql = "SELECT * FROM user_greetings WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Criar saudação
    public function create($userId, $template) {
        $sql = "INSERT INTO user_greetings (user_id, template, sort_order) 
                SELECT :user_id, :template, COALESCE(MAX(sort_order), 0) + 1 
                FROM user_greetings WHERE user_id = :user_id2";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':template' => $template,
            ':user_id2' => $userId
        ]);
    }
    
    // Atualizar saudação
    public function update($id, $userId, $template, $isActive = 1) {
        $sql = "UPDATE user_greetings SET template = :template, is_active = :is_active WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':template' => $template,
            ':is_active' => $isActive
        ]);
    }
    
    // Deletar saudação
    public function delete($id, $userId) {
        $sql = "DELETE FROM user_greetings WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }
    
    // Ativar/Desativar saudação
    public function toggleActive($id, $userId) {
        $sql = "UPDATE user_greetings SET is_active = NOT is_active WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }
    
    // Reordenar saudações
    public function reorder($userId, $orderedIds) {
        $sql = "UPDATE user_greetings SET sort_order = :sort WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        
        foreach ($orderedIds as $index => $id) {
            $stmt->execute([':id' => $id, ':user_id' => $userId, ':sort' => $index]);
        }
        return true;
    }
    
    // Criar saudações padrão para novo usuário
    public function createDefaultsForUser($userId) {
        foreach ($this->defaultGreetings as $index => $template) {
            $sql = "INSERT INTO user_greetings (user_id, template, sort_order) VALUES (:user_id, :template, :sort)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId, ':template' => $template, ':sort' => $index]);
        }
        return true;
    }
    
    // Obter índice atual de saudação para uma campanha
    public function getCampaignIndex($campaignId) {
        $sql = "SELECT current_index FROM campaign_greeting_index WHERE campaign_id = :campaign_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':campaign_id' => $campaignId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['current_index'] : 0;
    }
    
    // Incrementar índice e obter próxima saudação
    public function getNextGreeting($campaignId, $userId) {
        $greetings = $this->getByUser($userId);
        
        if (empty($greetings)) {
            $this->createDefaultsForUser($userId);
            $greetings = $this->getByUser($userId);
        }
        
        if (empty($greetings)) {
            return $this->defaultGreetings[0];
        }
        
        $currentIndex = $this->getCampaignIndex($campaignId);
        $totalGreetings = count($greetings);
        
        // Calcular índice atual (circular)
        $index = $currentIndex % $totalGreetings;
        $template = $greetings[$index]['template'];
        
        // Incrementar índice para próximo disparo
        $sql = "INSERT INTO campaign_greeting_index (campaign_id, current_index) 
                VALUES (:campaign_id, 1) 
                ON DUPLICATE KEY UPDATE current_index = current_index + 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':campaign_id' => $campaignId]);
        
        return $template;
    }
    
    // Processar macro {saudacao} substituindo {periodo}, {nome}, {numero}
    public function processGreeting($template, $contactName, $contactPhone) {
        // Detectar período do dia
        $hora = (int) date('H');
        if ($hora >= 5 && $hora < 12) {
            $periodo = 'Bom dia';
        } elseif ($hora >= 12 && $hora < 18) {
            $periodo = 'Boa tarde';
        } else {
            $periodo = 'Boa noite';
        }
        
        // Se não tiver nome, usar adjetivo aleatório
        if (empty(trim($contactName))) {
            $adjective = $this->adjectives[array_rand($this->adjectives)];
            $contactName = $adjective;
        } else {
            // Pegar apenas o primeiro nome
            $nameParts = explode(' ', trim($contactName));
            $contactName = ucfirst(strtolower($nameParts[0]));
        }
        
        // Substituir macros
        $result = str_replace(
            ['{periodo}', '{nome}', '{numero}'],
            [$periodo, $contactName, $contactPhone],
            $template
        );
        
        return $result;
    }
    
    // Contar saudações do usuário
    public function countByUser($userId) {
        $sql = "SELECT COUNT(*) as total FROM user_greetings WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
