<?php
/**
 * Helper de Proteção CSRF (Cross-Site Request Forgery)
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-01-28 08:00:00
 * Versão: 1.0.0
 * 
 * Protege contra ataques CSRF gerando e validando tokens únicos por sessão.
 */

class CSRF {
    /**
     * Nome da chave do token na sessão
     */
    private static $tokenName = 'csrf_token';
    
    /**
     * Tempo de expiração do token (em segundos)
     * Padrão: 2 horas (7200 segundos)
     */
    private static $tokenLifetime = 7200;
    
    /**
     * Gerar um novo token CSRF
     * 
     * @return string Token gerado
     */
    public static function generateToken() {
        // Gerar token aleatório seguro
        $token = bin2hex(random_bytes(32));
        
        // Armazenar na sessão com timestamp
        $_SESSION[self::$tokenName] = [
            'token' => $token,
            'time' => time()
        ];
        
        return $token;
    }
    
    /**
     * Obter o token atual (ou gerar um novo se não existir)
     * 
     * @return string Token atual
     */
    public static function getToken() {
        // Se não existe token ou está expirado, gerar novo
        if (!self::tokenExists() || self::isTokenExpired()) {
            return self::generateToken();
        }
        
        return $_SESSION[self::$tokenName]['token'];
    }
    
    /**
     * Verificar se token existe na sessão
     * 
     * @return bool
     */
    private static function tokenExists() {
        return isset($_SESSION[self::$tokenName]) && 
               isset($_SESSION[self::$tokenName]['token']) &&
               isset($_SESSION[self::$tokenName]['time']);
    }
    
    /**
     * Verificar se token está expirado
     * 
     * @return bool
     */
    private static function isTokenExpired() {
        if (!self::tokenExists()) {
            return true;
        }
        
        $tokenTime = $_SESSION[self::$tokenName]['time'];
        $currentTime = time();
        
        return ($currentTime - $tokenTime) > self::$tokenLifetime;
    }
    
    /**
     * Validar token CSRF
     * 
     * @param string $token Token a ser validado
     * @return bool True se válido, False se inválido
     */
    public static function validateToken($token) {
        // Verificar se token existe na sessão
        if (!self::tokenExists()) {
            error_log('🔒 CSRF: Token não encontrado na sessão');
            return false;
        }
        
        // Verificar se token está expirado
        if (self::isTokenExpired()) {
            error_log('🔒 CSRF: Token expirado');
            return false;
        }
        
        // Verificar se token recebido é válido
        $sessionToken = $_SESSION[self::$tokenName]['token'];
        
        // Comparação segura contra timing attacks
        if (!hash_equals($sessionToken, $token)) {
            error_log('🔒 CSRF: Token inválido');
            return false;
        }
        
        return true;
    }
    
    /**
     * Gerar campo hidden HTML com token CSRF
     * 
     * @return string HTML do campo hidden
     */
    public static function getTokenField() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Validar token do POST atual
     * 
     * @return bool True se válido, False se inválido
     */
    public static function validateRequest() {
        // Verificar se é requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // GET não precisa de validação CSRF
        }
        
        // Obter token do POST
        $token = $_POST['csrf_token'] ?? '';
        
        if (empty($token)) {
            error_log('🔒 CSRF: Token não enviado no POST');
            return false;
        }
        
        return self::validateToken($token);
    }
    
    /**
     * Validar token e redirecionar se inválido
     * 
     * @param string $redirectUrl URL para redirecionar em caso de falha
     * @param string $errorMessage Mensagem de erro
     */
    public static function validateOrDie($redirectUrl = null, $errorMessage = null) {
        if (!self::validateRequest()) {
            // Definir mensagem de erro padrão
            if ($errorMessage === null) {
                $errorMessage = 'Token de segurança inválido ou expirado. Por favor, tente novamente.';
            }
            
            $_SESSION['csrf_error'] = $errorMessage;
            
            // Redirecionar ou retornar erro
            if ($redirectUrl !== null) {
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                // Se não tem URL de redirecionamento, retornar JSON (para AJAX)
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => 'csrf_token_invalid'
                ]);
                exit;
            }
        }
    }
    
    /**
     * Obter e limpar mensagem de erro CSRF
     * 
     * @return string|null Mensagem de erro ou null
     */
    public static function getError() {
        if (isset($_SESSION['csrf_error'])) {
            $error = $_SESSION['csrf_error'];
            unset($_SESSION['csrf_error']);
            return $error;
        }
        return null;
    }
    
    /**
     * Regenerar token (útil após ações sensíveis)
     */
    public static function regenerateToken() {
        return self::generateToken();
    }
    
    /**
     * Destruir token atual
     */
    public static function destroyToken() {
        if (isset($_SESSION[self::$tokenName])) {
            unset($_SESSION[self::$tokenName]);
        }
    }
}
