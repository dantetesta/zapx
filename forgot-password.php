<?php
/**
 * Recuperação de Senha
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 21:30:00
 */

// Verificar se o sistema está instalado
if (!file_exists(__DIR__ . '/config/installed.lock')) {
    header('Location: install/index.php');
    exit;
}

// Carregar autoload do Composer primeiro
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Depois carregar configurações
require_once 'config/config.php';
require_once 'config/Database.php';

session_start();

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $_SESSION['error'] = 'Por favor, informe seu email.';
        header('Location: forgot-password.php');
        exit;
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Buscar usuário
        $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Por segurança, não revelar se o email existe ou não
            $_SESSION['success'] = 'Se o email existir em nossa base, você receberá instruções para redefinir sua senha.';
            header('Location: forgot-password.php');
            exit;
        }
        
        // Gerar token único
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Salvar token no banco
        $stmt = $db->prepare("
            INSERT INTO password_reset_tokens (user_id, token, expires_at) 
            VALUES (:user_id, :token, :expires_at)
        ");
        $stmt->execute([
            ':user_id' => $user['id'],
            ':token' => $token,
            ':expires_at' => $expiresAt
        ]);
        
        // Enviar email
        $resetLink = APP_URL . '/reset-password.php?token=' . $token;
        $subject = 'Recuperação de Senha - ' . APP_NAME;
        $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Recuperação de Senha</h1>
                    </div>
                    <div class='content'>
                        <p>Olá, <strong>{$user['name']}</strong>!</p>
                        <p>Recebemos uma solicitação para redefinir a senha da sua conta no <strong>" . APP_NAME . "</strong>.</p>
                        <p>Clique no botão abaixo para criar uma nova senha:</p>
                        <p style='text-align: center;'>
                            <a href='{$resetLink}' class='button'>Redefinir Senha</a>
                        </p>
                        <p>Ou copie e cole este link no seu navegador:</p>
                        <p style='word-break: break-all; background: #fff; padding: 10px; border-radius: 5px;'>{$resetLink}</p>
                        <p><strong>Este link expira em 1 hora.</strong></p>
                        <p>Se você não solicitou esta redefinição, ignore este email. Sua senha permanecerá inalterada.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " " . APP_NAME . " - Todos os direitos reservados</p>
                        <p>Desenvolvido por <a href='https://dantetesta.com.br'>Dante Testa</a></p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Enviar email via PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            
            // Usar criptografia configurada pelo usuário
            $encryption = defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls';
            
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS/STARTTLS
            } else {
                // none - sem criptografia
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }
            
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            
            // Remetente e destinatário
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($email, $user['name']);
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);
            
            $mail->send();
        } catch (Exception $e) {
            error_log("ZAPX: Erro ao enviar email: {$mail->ErrorInfo}");
            throw new Exception("Erro ao enviar email");
        }
        
        $_SESSION['success'] = 'Instruções para redefinir sua senha foram enviadas para seu email.';
        header('Location: forgot-password.php');
        exit;
        
    } catch (Exception $e) {
        error_log("ZAPX: Erro ao processar recuperação de senha: " . $e->getMessage());
        $_SESSION['error'] = 'Erro ao processar solicitação. Tente novamente.';
        header('Location: forgot-password.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - <?php echo APP_NAME; ?></title>
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
                    <i class="fas fa-key text-4xl text-white"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Recuperar Senha</h2>
                <p class="mt-2 text-gray-600">Informe seu email para receber instruções</p>
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

            <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                    <p class="text-green-700"><?php echo htmlspecialchars($success); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulário -->
            <div class="bg-white rounded-xl shadow-md p-8">
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-1"></i>
                            Email
                        </label>
                        <input type="email" name="email" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="seu@email.com">
                    </div>

                    <button type="submit" 
                            class="w-full gradient-bg text-white font-semibold py-3 rounded-lg hover:opacity-90 transition">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Instruções
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
</body>
</html>
