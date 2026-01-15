<?php
/**
 * Redefinir Senha
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 21:30:00
 */

// Verificar se o sistema está instalado
if (!file_exists(__DIR__ . '/config/installed.lock')) {
    header('Location: install/index.php');
    exit;
}

require_once 'config/config.php';
require_once 'config/Database.php';

session_start();

$token = $_GET['token'] ?? '';
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

if (empty($token)) {
    $_SESSION['error'] = 'Token inválido.';
    header('Location: login.php');
    exit;
}

// Verificar token
try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT t.*, u.name, u.email 
        FROM password_reset_tokens t
        JOIN users u ON t.user_id = u.id
        WHERE t.token = :token 
        AND t.used = 0 
        AND t.expires_at > NOW()
    ");
    $stmt->execute([':token' => $token]);
    $tokenData = $stmt->fetch();
    
    if (!$tokenData) {
        $_SESSION['error'] = 'Token inválido ou expirado.';
        header('Location: login.php');
        exit;
    }
    
} catch (Exception $e) {
    // 🔒 SEGURANÇA: Não logar detalhes que podem conter tokens
    error_log("ZAPX: Erro ao verificar token de reset de senha");
    $_SESSION['error'] = 'Erro ao processar solicitação.';
    header('Location: login.php');
    exit;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    if (empty($password) || empty($passwordConfirm)) {
        $error = 'Todos os campos são obrigatórios.';
    } elseif (strlen($password) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'As senhas não coincidem.';
    } else {
        try {
            // Atualizar senha
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute([
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':id' => $tokenData['user_id']
            ]);
            
            // Marcar token como usado
            $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = :token");
            $stmt->execute([':token' => $token]);
            
            $_SESSION['success'] = 'Senha redefinida com sucesso! Faça login com sua nova senha.';
            header('Location: login.php');
            exit;
            
        } catch (Exception $e) {
            error_log("ZAPX: Erro ao redefinir senha: " . $e->getMessage());
            $error = 'Erro ao redefinir senha. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 gradient-bg rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-4xl text-white"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Redefinir Senha</h2>
                <p class="mt-2 text-gray-600">Olá, <?php echo htmlspecialchars($tokenData['name']); ?>!</p>
                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($tokenData['email']); ?></p>
            </div>

            <!-- Mensagens -->
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                    <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulário -->
            <div class="bg-white rounded-xl shadow-md p-8">
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-1"></i>
                            Nova Senha
                        </label>
                        <input type="password" name="password" required minlength="6"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="••••••••">
                        <p class="text-xs text-gray-500 mt-1">Mínimo de 6 caracteres</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-1"></i>
                            Confirmar Nova Senha
                        </label>
                        <input type="password" name="password_confirm" required minlength="6"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="••••••••">
                    </div>

                    <button type="submit" 
                            class="w-full gradient-bg text-white font-semibold py-3 rounded-lg hover:opacity-90 transition">
                        <i class="fas fa-check mr-2"></i>
                        Redefinir Senha
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="login.php" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Voltar para o Login
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-gray-600 text-sm">
                <p>
                    <i class="fas fa-code mr-1"></i>
                    Desenvolvido por <a href="https://dantetesta.com.br" target="_blank" class="text-purple-600 hover:text-purple-700 font-semibold">Dante Testa</a>
                </p>
            </div>
        </div>
    </div>

    <script>
    // Validar senhas iguais
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        const confirm = document.querySelector('input[name="password_confirm"]').value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('As senhas não coincidem!');
        }
    });
    </script>
</body>
</html>
