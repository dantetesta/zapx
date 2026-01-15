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
    
    try {
        // Tentar conectar
        $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // Testar se pode criar tabelas
        $pdo->exec("CREATE TABLE IF NOT EXISTS _install_test (id INT)");
        $pdo->exec("DROP TABLE _install_test");
        
        $_SESSION['install_success'] = 'Conexão com banco de dados estabelecida com sucesso!';
        header('Location: index.php?step=config');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['install_error'] = 'Erro ao conectar: ' . $e->getMessage();
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
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '587',
        'smtp_user' => $_POST['smtp_user'] ?? '',
        'smtp_pass' => $_POST['smtp_pass'] ?? '',
        'smtp_from' => $_POST['smtp_from'] ?? '',
        'smtp_from_name' => $_POST['smtp_from_name'] ?? ''
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
    
    if (strlen($password) < 6) {
        $_SESSION['install_error'] = 'A senha deve ter no mínimo 6 caracteres!';
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
        installSystem();
        
        $_SESSION['install_success'] = 'Sistema instalado com sucesso!';
        header('Location: index.php?step=finish');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['install_error'] = 'Erro na instalação: ' . $e->getMessage();
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
    
    // 5. Limpar sessão
    unset($_SESSION['db_data'], $_SESSION['config_data'], $_SESSION['admin_data']);
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
    $content .= "define('APP_VERSION', '3.1.0');\n";
    $content .= "define('APP_URL', '" . addslashes($configData['app_url']) . "');\n\n";
    
    $content .= "// Configurações de Sessão\n";
    $content .= "define('SESSION_NAME', 'ZAPX_SESSION');\n";
    $content .= "define('SESSION_LIFETIME', 7200);\n\n";
    
    $content .= "// Configurações de Upload\n";
    $content .= "define('UPLOAD_MAX_SIZE', 5242880);\n";
    $content .= "define('ALLOWED_CSV_EXTENSIONS', ['csv', 'txt']);\n\n";
    
    $content .= "// Configurações de Disparo\n";
    $content .= "define('DISPATCH_MIN_INTERVAL', 2);\n";
    $content .= "define('DISPATCH_MAX_INTERVAL', 10);\n\n";
    
    $content .= "// Configurações da Evolution API\n";
    $content .= "define('EVOLUTION_API_URL', '" . addslashes($configData['evo_url']) . "');\n";
    $content .= "define('EVOLUTION_API_KEY', '" . addslashes($configData['evo_key']) . "');\n\n";
    
    $content .= "// Configurações de Email (SMTP)\n";
    $content .= "define('SMTP_HOST', '" . addslashes($configData['smtp_host']) . "');\n";
    $content .= "define('SMTP_PORT', " . intval($configData['smtp_port']) . ");\n";
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
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignorar erro de tabela já existente
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }
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
    
    // Deletar usuário admin padrão se existir
    $pdo->exec("DELETE FROM users WHERE email = 'admin@zapx.com'");
    
    // Criar novo admin
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
