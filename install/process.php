<?php
/**
 * Processador de Ações do Instalador
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 21:12:00
 */

session_start();

// Verificar se já está instalado
if (file_exists('../config/installed.lock')) {
    header('Location: ../');
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'test_database':
        testDatabase();
        break;
    
    case 'save_config':
        saveConfig();
        break;
    
    case 'create_admin':
        createAdmin();
        break;
    
    default:
        header('Location: index.php');
        exit;
}

/**
 * Testar conexão com banco de dados
 */
function testDatabase() {
    $host = $_POST['db_host'] ?? '';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    
    // Salvar dados na sessão
    $_SESSION['db_data'] = [
        'host' => $host,
        'name' => $name,
        'user' => $user,
        'pass' => $pass,
        'charset' => 'utf8mb4'
    ];
    
    // Tentar diferentes configurações de conexão
    $connectionAttempts = [];
    
    // Tentativa 1: Conexão padrão
    $connectionAttempts[] = "mysql:host=$host;dbname=$name;charset=utf8mb4";
    
    // Tentativa 2: Com porta explícita
    if (strpos($host, ':') === false) {
        $connectionAttempts[] = "mysql:host=$host;port=3306;dbname=$name;charset=utf8mb4";
    }
    
    // Tentativa 3: localhost via 127.0.0.1
    if ($host === 'localhost') {
        $connectionAttempts[] = "mysql:host=127.0.0.1;dbname=$name;charset=utf8mb4";
        $connectionAttempts[] = "mysql:host=127.0.0.1;port=3306;dbname=$name;charset=utf8mb4";
    }
    
    // Tentativa 4: Com socket Unix comum
    $commonSockets = [
        '/var/run/mysqld/mysqld.sock',
        '/tmp/mysql.sock',
        '/var/lib/mysql/mysql.sock',
        '/Applications/MAMP/tmp/mysql/mysql.sock'
    ];
    
    foreach ($commonSockets as $socket) {
        if (file_exists($socket)) {
            $connectionAttempts[] = "mysql:unix_socket=$socket;dbname=$name;charset=utf8mb4";
        }
    }
    
    $lastError = '';
    $connected = false;
    
    foreach ($connectionAttempts as $dsn) {
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 5
            ]);
            
            // Testar se pode criar tabelas
            $pdo->exec("CREATE TABLE IF NOT EXISTS _install_test (id INT)");
            $pdo->exec("DROP TABLE _install_test");
            
            // Salvar DSN que funcionou
            $_SESSION['db_data']['dsn'] = $dsn;
            $_SESSION['install_success'] = 'Conexão com banco de dados estabelecida com sucesso!';
            $connected = true;
            header('Location: index.php?step=config');
            exit;
            
        } catch (PDOException $e) {
            $lastError = $e->getMessage();
            continue;
        }
    }
    
    if (!$connected) {
        $_SESSION['install_error'] = 'Erro na instalação: ' . $lastError . '<br><br><strong>Dicas:</strong><br>
        • Verifique se o MySQL está rodando<br>
        • Tente usar "127.0.0.1" ao invés de "localhost"<br>
        • Verifique usuário e senha<br>
        • Certifique-se que o banco de dados existe';
        header('Location: index.php?step=database');
        exit;
    }
}

/**
 * Salvar configurações
 */
function saveConfig() {
    $_SESSION['config_data'] = [
        'app_name' => $_POST['app_name'] ?? '',
        'app_url' => rtrim($_POST['app_url'] ?? '', '/'),
        'evo_url' => rtrim($_POST['evo_url'] ?? '', '/'),
        'evo_key' => $_POST['evo_key'] ?? '',
        'recaptcha_enabled' => isset($_POST['recaptcha_enabled']) ? true : false,
        'recaptcha_site_key' => $_POST['recaptcha_site_key'] ?? '',
        'recaptcha_secret_key' => $_POST['recaptcha_secret_key'] ?? '',
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '587',
        'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
        'smtp_user' => $_POST['smtp_user'] ?? '',
        'smtp_pass' => $_POST['smtp_pass'] ?? '',
        'smtp_from' => $_POST['smtp_from'] ?? '',
        'smtp_from_name' => $_POST['smtp_from_name'] ?? 'ZAPX'
    ];
    
    $_SESSION['install_success'] = 'Configurações salvas! Agora crie o administrador.';
    header('Location: index.php?step=admin');
    exit;
}

/**
 * Criar administrador e instalar sistema
 */
function createAdmin() {
    $name = $_POST['admin_name'] ?? '';
    $email = $_POST['admin_email'] ?? '';
    $password = $_POST['admin_password'] ?? '';
    $passwordConfirm = $_POST['admin_password_confirm'] ?? '';
    
    // Validar
    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['install_error'] = 'Todos os campos são obrigatórios!';
        header('Location: index.php?step=admin');
        exit;
    }
    
    if ($password !== $passwordConfirm) {
        $_SESSION['install_error'] = 'As senhas não coincidem!';
        header('Location: index.php?step=admin');
        exit;
    }
    
    // 🔒 SEGURANÇA: Validar força da senha
    if (strlen($password) < 8) {
        $_SESSION['install_error'] = 'A senha deve ter no mínimo 8 caracteres!';
        header('Location: index.php?step=admin');
        exit;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $_SESSION['install_error'] = 'A senha deve conter pelo menos uma letra maiúscula!';
        header('Location: index.php?step=admin');
        exit;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $_SESSION['install_error'] = 'A senha deve conter pelo menos uma letra minúscula!';
        header('Location: index.php?step=admin');
        exit;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $_SESSION['install_error'] = 'A senha deve conter pelo menos um número!';
        header('Location: index.php?step=admin');
        exit;
    }
    
    // Salvar dados do admin
    $_SESSION['admin_data'] = [
        'name' => $name,
        'email' => $email,
        'password' => $password
    ];
    
    // Executar instalação
    try {
        // Aumentar timeout do PHP
        set_time_limit(120);
        ini_set('max_execution_time', 120);
        
        installSystem();
        
        $_SESSION['install_success'] = 'Sistema instalado com sucesso!';
        header('Location: index.php?step=finish');
        exit;
        
    } catch (Exception $e) {
        // Log detalhado do erro
        error_log("ERRO NA INSTALAÇÃO: " . $e->getMessage());
        error_log("STACK TRACE: " . $e->getTraceAsString());
        
        $_SESSION['install_error'] = 'Erro na instalação: ' . $e->getMessage();
        header('Location: index.php?step=admin');
        exit;
    } catch (Error $e) {
        // Capturar erros fatais do PHP 7+
        error_log("ERRO FATAL NA INSTALAÇÃO: " . $e->getMessage());
        error_log("STACK TRACE: " . $e->getTraceAsString());
        
        $_SESSION['install_error'] = 'Erro fatal na instalação: ' . $e->getMessage();
        header('Location: index.php?step=admin');
        exit;
    }
}

/**
 * Instalar o sistema completo
 */
function installSystem() {
    $dbData = $_SESSION['db_data'];
    $configData = $_SESSION['config_data'];
    $adminData = $_SESSION['admin_data'];
    
    // 1. Criar arquivo de configuração
    createConfigFile($dbData, $configData);
    
    // 2. Criar tabelas do banco
    createDatabaseTables($dbData);
    
    // 3. Criar usuário administrador
    createAdminUser($dbData, $adminData);
    
    // 4. Criar arquivo de lock
    file_put_contents('../config/installed.lock', date('Y-m-d H:i:s'));
    
    // 6. NÃO limpar sessão ainda - será limpo no complete.php
    // Manter para exibir mensagens na tela de finish
}

/**
 * Criar arquivo config.php
 */
function createConfigFile($dbData, $configData) {
    $content = "<?php\n";
    $content .= "/**\n";
    $content .= " * Configurações do Sistema ZAPX\n";
    $content .= " * Gerado automaticamente pelo instalador\n";
    $content .= " * Data: " . date('Y-m-d H:i:s') . "\n";
    $content .= " */\n\n";
    
    $content .= "// Configurações do Banco de Dados\n";
    $content .= "define('DB_HOST', '" . addslashes($dbData['host']) . "');\n";
    $content .= "define('DB_NAME', '" . addslashes($dbData['name']) . "');\n";
    $content .= "define('DB_USER', '" . addslashes($dbData['user']) . "');\n";
    $content .= "define('DB_PASS', '" . addslashes($dbData['pass']) . "');\n";
    $content .= "define('DB_CHARSET', 'utf8mb4');\n\n";
    
    $content .= "// Configurações da Aplicação\n";
    $content .= "define('APP_NAME', '" . addslashes($configData['app_name']) . "');\n";
    $content .= "define('APP_URL', '" . addslashes($configData['app_url']) . "');\n\n";
    $content .= "// Versão do sistema (centralizada em branding.php)\n";
    $content .= "// NÃO altere aqui, altere em config/branding.php\n";
    $content .= "if (!defined('SYSTEM_VERSION')) {\n";
    $content .= "    require_once __DIR__ . '/branding.php';\n";
    $content .= "}\n";
    $content .= "define('APP_VERSION', SYSTEM_VERSION);\n\n";
    
    $content .= "// Configurações de Sessão\n";
    $content .= "define('SESSION_NAME', 'ZAPX_SESSION');\n";
    $content .= "define('SESSION_LIFETIME', 7200);\n\n";
    
    $content .= "// Configurações de Upload\n";
    $content .= "// Limites baseados no WhatsApp Business API\n";
    $content .= "define('UPLOAD_MAX_SIZE_IMAGE', 5 * 1024 * 1024);    // 5MB para imagens\n";
    $content .= "define('UPLOAD_MAX_SIZE_VIDEO', 16 * 1024 * 1024);   // 16MB para vídeos\n";
    $content .= "define('UPLOAD_MAX_SIZE_AUDIO', 16 * 1024 * 1024);   // 16MB para áudios\n";
    $content .= "define('UPLOAD_MAX_SIZE_DOCUMENT', 16 * 1024 * 1024); // 16MB para documentos\n";
    $content .= "define('ALLOWED_CSV_EXTENSIONS', ['csv', 'txt']);\n\n";
    
    $content .= "// Configurações de Disparo\n";
    $content .= "define('DISPATCH_MIN_INTERVAL', 3);\n";
    $content .= "define('DISPATCH_MAX_INTERVAL', 20);\n\n";
    
    $content .= "// Configurações de Segurança - Google reCAPTCHA v2\n";
    $content .= "define('RECAPTCHA_SITE_KEY', '" . addslashes($configData['recaptcha_site_key']) . "');\n";
    $content .= "define('RECAPTCHA_SECRET_KEY', '" . addslashes($configData['recaptcha_secret_key']) . "');\n";
    $content .= "define('RECAPTCHA_ENABLED', " . ($configData['recaptcha_enabled'] ? 'true' : 'false') . ");\n\n";
    
    $content .= "// Configurações da Evolution API\n";
    $content .= "define('EVOLUTION_API_URL', '" . addslashes($configData['evo_url']) . "');\n";
    $content .= "define('EVOLUTION_API_KEY', '" . addslashes($configData['evo_key']) . "');\n\n";
    
    $content .= "// Configurações de Email (SMTP)\n";
    $content .= "define('SMTP_HOST', '" . addslashes($configData['smtp_host']) . "');\n";
    $content .= "define('SMTP_PORT', " . intval($configData['smtp_port']) . ");\n";
    $content .= "define('SMTP_ENCRYPTION', '" . addslashes($configData['smtp_encryption'] ?? 'tls') . "'); // tls, ssl ou none\n";
    $content .= "define('SMTP_USER', '" . addslashes($configData['smtp_user']) . "');\n";
    $content .= "define('SMTP_PASS', '" . addslashes($configData['smtp_pass']) . "');\n";
    $content .= "define('SMTP_FROM', '" . addslashes($configData['smtp_from']) . "');\n";
    $content .= "define('SMTP_FROM_NAME', '" . addslashes($configData['smtp_from_name']) . "');\n\n";
    
    $content .= "// Timezone\n";
    $content .= "date_default_timezone_set('America/Sao_Paulo');\n\n";
    
    $content .= "// Iniciar sessão\n";
    $content .= "if (session_status() === PHP_SESSION_NONE) {\n";
    $content .= "    session_name(SESSION_NAME);\n";
    $content .= "    session_start();\n";
    $content .= "}\n";
    
    file_put_contents('../config/config.php', $content);
}

/**
 * Criar tabelas do banco de dados
 */
function createDatabaseTables($dbData) {
    $dsn = "mysql:host={$dbData['host']};dbname={$dbData['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbData['user'], $dbData['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Desabilitar foreign key checks temporariamente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    try {
        // Limpar tabelas existentes (reinstalação)
        $tables = ['dispatch_history', 'contact_tags', 'contacts', 'tags', 'password_reset_tokens', 'users'];
        foreach ($tables as $table) {
            try {
                $pdo->exec("DROP TABLE IF EXISTS `$table`");
            } catch (PDOException $e) {
                // Ignorar se tabela não existe
            }
        }
        
        // Ler arquivo SQL
        $sql = file_get_contents('../config/database.sql');
        
        // Remover comentários
        $sql = preg_replace('/^--.*$/m', '', $sql);
        
        // Remover comandos CREATE DATABASE e USE (o banco já existe)
        $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
        $sql = preg_replace('/USE\s+\w+;/i', '', $sql);
        
        // Remover linhas vazias
        $sql = preg_replace('/^\s*$/m', '', $sql);
        
        // Executar cada statement
        $statements = explode(';', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^\s*$/', $statement)) {
                $pdo->exec($statement);
            }
        }
    } finally {
        // Reabilitar foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
}

/**
 * Criar usuário administrador
 */
function createAdminUser($dbData, $adminData) {
    $dsn = "mysql:host={$dbData['host']};dbname={$dbData['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbData['user'], $dbData['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Criar administrador
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, is_admin, default_country_code, timezone, created_at) 
        VALUES (:name, :email, :password, 1, '55', 'America/Sao_Paulo', NOW())
    ");
    
    $stmt->execute([
        ':name' => $adminData['name'],
        ':email' => $adminData['email'],
        ':password' => password_hash($adminData['password'], PASSWORD_DEFAULT)
    ]);
}

