<?php
/**
 * Rate Limiting para Login
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-28 11:47:00
 * Versão: 1.0.0
 * 
 * Protege contra ataques de força bruta limitando tentativas de login.
 */

class RateLimit {
    /**
     * Chave base para armazenar tentativas
     */
    private static $keyPrefix = 'login_attempts_';
    
    /**
     * Níveis de bloqueio
     */
    private static $levels = [
        1 => ['attempts' => 6, 'lockout' => 300],      // 6 tentativas = 5 minutos
        2 => ['attempts' => 12, 'lockout' => 900],     // 12 tentativas = 15 minutos
        3 => ['attempts' => 24, 'lockout' => 86400],   // 24 tentativas = 24 horas
    ];
    
    /**
     * Verificar se IP/Email está bloqueado
     * 
     * @param string $identifier Email ou IP
     * @return array ['blocked' => bool, 'wait_time' => int, 'attempts' => int, 'level' => int]
     */
    public static function check($identifier) {
        $key = self::$keyPrefix . md5($identifier);
        
        // Obter dados da sessão
        $attempts = $_SESSION[$key]['attempts'] ?? 0;
        $firstAttempt = $_SESSION[$key]['first_attempt'] ?? time();
        $lastAttempt = $_SESSION[$key]['last_attempt'] ?? 0;
        $level = $_SESSION[$key]['level'] ?? 0;
        
        // Se não tem tentativas, não está bloqueado
        if ($attempts === 0) {
            return [
                'blocked' => false,
                'wait_time' => 0,
                'attempts' => 0,
                'level' => 0
            ];
        }
        
        // Calcular tempo desde última tentativa
        $timeSinceLastAttempt = time() - $lastAttempt;
        
        // Determinar nível de bloqueio atual
        $currentLevel = self::getLevel($attempts);
        
        // Se mudou de nível, atualizar
        if ($currentLevel > $level) {
            $_SESSION[$key]['level'] = $currentLevel;
            $level = $currentLevel;
        }
        
        // Obter tempo de bloqueio do nível atual
        $lockoutTime = self::$levels[$level]['lockout'] ?? 0;
        
        // Se passou o tempo de bloqueio, resetar
        if ($timeSinceLastAttempt >= $lockoutTime) {
            self::reset($identifier);
            return [
                'blocked' => false,
                'wait_time' => 0,
                'attempts' => 0,
                'level' => 0
            ];
        }
        
        // Calcular tempo restante
        $waitTime = $lockoutTime - $timeSinceLastAttempt;
        
        // Verificar se está bloqueado
        $isBlocked = $attempts >= self::$levels[$level]['attempts'];
        
        return [
            'blocked' => $isBlocked,
            'wait_time' => $waitTime,
            'attempts' => $attempts,
            'level' => $level,
            'max_attempts' => self::$levels[$level]['attempts']
        ];
    }
    
    /**
     * Registrar tentativa de login
     * 
     * @param string $identifier Email ou IP
     * @param bool $success Se o login foi bem-sucedido
     */
    public static function record($identifier, $success = false) {
        $key = self::$keyPrefix . md5($identifier);
        
        // Se login bem-sucedido, resetar contador
        if ($success) {
            self::reset($identifier);
            return;
        }
        
        // Incrementar tentativas
        $attempts = ($_SESSION[$key]['attempts'] ?? 0) + 1;
        $firstAttempt = $_SESSION[$key]['first_attempt'] ?? time();
        
        // Determinar nível
        $level = self::getLevel($attempts);
        
        // Armazenar na sessão
        $_SESSION[$key] = [
            'attempts' => $attempts,
            'first_attempt' => $firstAttempt,
            'last_attempt' => time(),
            'level' => $level
        ];
        
        // Log de segurança
        error_log(sprintf(
            '🔒 Rate Limit: %s - Tentativa %d (Nível %d)',
            $identifier,
            $attempts,
            $level
        ));
    }
    
    /**
     * Resetar contador de tentativas
     * 
     * @param string $identifier Email ou IP
     */
    public static function reset($identifier) {
        $key = self::$keyPrefix . md5($identifier);
        
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Determinar nível de bloqueio baseado em tentativas
     * 
     * @param int $attempts Número de tentativas
     * @return int Nível (1, 2 ou 3)
     */
    private static function getLevel($attempts) {
        if ($attempts >= self::$levels[3]['attempts']) {
            return 3; // 24+ tentativas = Nível 3 (24h)
        } elseif ($attempts >= self::$levels[2]['attempts']) {
            return 2; // 12+ tentativas = Nível 2 (15min)
        } elseif ($attempts >= self::$levels[1]['attempts']) {
            return 1; // 6+ tentativas = Nível 1 (5min)
        }
        return 0; // Menos de 6 = Sem bloqueio
    }
    
    /**
     * Formatar tempo de espera em texto legível
     * 
     * @param int $seconds Segundos
     * @return string Texto formatado
     */
    public static function formatWaitTime($seconds) {
        if ($seconds >= 86400) {
            $hours = floor($seconds / 3600);
            return $hours . ' hora(s)';
        } elseif ($seconds >= 3600) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'min';
        } elseif ($seconds >= 60) {
            $minutes = ceil($seconds / 60);
            return $minutes . ' minuto(s)';
        } else {
            return $seconds . ' segundo(s)';
        }
    }
    
    /**
     * Obter mensagem de erro formatada
     * 
     * @param array $status Status do rate limit
     * @return string Mensagem de erro
     */
    public static function getErrorMessage($status) {
        $waitTime = self::formatWaitTime($status['wait_time']);
        $attempts = $status['attempts'];
        $maxAttempts = $status['max_attempts'];
        
        $messages = [
            1 => "Muitas tentativas de login. Aguarde {$waitTime} antes de tentar novamente.",
            2 => "⚠️ Conta temporariamente bloqueada. Aguarde {$waitTime} antes de tentar novamente.",
            3 => "🚨 CONTA BLOQUEADA! Muitas tentativas de login. Aguarde {$waitTime} ou entre em contato com o suporte."
        ];
        
        return $messages[$status['level']] ?? "Aguarde {$waitTime} antes de tentar novamente.";
    }
    
    /**
     * Obter informações de todas as tentativas (para admin)
     * 
     * @return array Lista de IPs/emails bloqueados
     */
    public static function getAllAttempts() {
        $attempts = [];
        
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, self::$keyPrefix) === 0 && is_array($value)) {
                $attempts[] = [
                    'key' => $key,
                    'attempts' => $value['attempts'] ?? 0,
                    'level' => $value['level'] ?? 0,
                    'first_attempt' => $value['first_attempt'] ?? 0,
                    'last_attempt' => $value['last_attempt'] ?? 0
                ];
            }
        }
        
        return $attempts;
    }
}
