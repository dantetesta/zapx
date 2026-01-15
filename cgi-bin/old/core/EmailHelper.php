<?php
/**
 * Helper para Envio de Emails via SMTP usando PHPMailer
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-27
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    
    /**
     * Enviar email via SMTP usando PHPMailer
     */
    public static function send($to, $subject, $body, $isHtml = true) {
        // Carregar PHPMailer
        require_once __DIR__ . '/../vendor/autoload.php';
        
        try {
            // Validar configurações
            if (!defined('SMTP_HOST') || !defined('SMTP_USER') || !defined('SMTP_PASS')) {
                error_log("❌ Configurações SMTP não definidas no config.php");
                return false;
            }
            
            if (!defined('SMTP_FROM') || !defined('SMTP_FROM_NAME')) {
                error_log("❌ SMTP_FROM ou SMTP_FROM_NAME não definidos");
                return false;
            }
            
            error_log("📧 Iniciando envio de email via PHPMailer");
            error_log("   Para: $to");
            error_log("   Assunto: $subject");
            error_log("   SMTP: " . SMTP_HOST . ":" . SMTP_PORT);
            
            // Criar instância do PHPMailer
            $mail = new PHPMailer(true);
            
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            
            // Remetente
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addReplyTo(SMTP_FROM, SMTP_FROM_NAME);
            
            // Destinatário
            $mail->addAddress($to);
            
            // Conteúdo
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            if ($isHtml) {
                $mail->AltBody = strip_tags($body);
            }
            
            // Enviar
            $mail->send();
            
            error_log("✅ Email enviado com sucesso via PHPMailer para: $to");
            return true;
            
        } catch (Exception $e) {
            error_log("❌ Erro PHPMailer: " . $e->getMessage());
            if (isset($mail)) {
                error_log("   Debug: " . $mail->ErrorInfo);
            }
            return false;
        } catch (Error $e) {
            error_log("❌ Erro fatal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar credenciais de novo usuário
     */
    public static function sendUserCredentials($name, $email, $password, $loginUrl) {
        $subject = "Bem-vindo ao " . APP_NAME;
        
        $body = self::getCredentialsEmailTemplate($name, $email, $password, $loginUrl);
        
        return self::send($email, $subject, $body, true);
    }
    
    /**
     * Template HTML para email de credenciais
     */
    private static function getCredentialsEmailTemplate($name, $email, $password, $loginUrl) {
        return '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao ' . APP_NAME . '</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                🎉 Bem-vindo ao ZAPX!
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px; color: #374151; font-size: 16px; line-height: 1.6;">
                                Olá <strong>' . htmlspecialchars($name) . '</strong>,
                            </p>
                            
                            <p style="margin: 0 0 20px; color: #374151; font-size: 16px; line-height: 1.6;">
                                Sua conta foi criada com sucesso! Abaixo estão suas credenciais de acesso:
                            </p>
                            
                            <!-- Credenciais Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border: 2px solid #e5e7eb; border-radius: 8px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 15px; color: #6b7280; font-size: 14px; font-weight: bold; text-transform: uppercase;">
                                            📧 Email de Acesso
                                        </p>
                                        <p style="margin: 0 0 20px; color: #1f2937; font-size: 16px; font-family: monospace; background-color: #ffffff; padding: 12px; border-radius: 6px; border: 1px solid #e5e7eb;">
                                            ' . htmlspecialchars($email) . '
                                        </p>
                                        
                                        <p style="margin: 0 0 15px; color: #6b7280; font-size: 14px; font-weight: bold; text-transform: uppercase;">
                                            🔒 Senha
                                        </p>
                                        <p style="margin: 0; color: #1f2937; font-size: 16px; font-family: monospace; background-color: #ffffff; padding: 12px; border-radius: 6px; border: 1px solid #e5e7eb;">
                                            ' . htmlspecialchars($password) . '
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Alerta de Segurança -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 6px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 15px;">
                                        <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.5;">
                                            <strong>⚠️ Importante:</strong> Por segurança, recomendamos que você altere sua senha no primeiro acesso.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Botão de Acesso -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="' . htmlspecialchars($loginUrl) . '" 
                                           style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 8px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                            🚀 Acessar Sistema
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 20px 0 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Se você tiver alguma dúvida, entre em contato com o administrador do sistema.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 10px; color: #6b7280; font-size: 14px;">
                                <strong>' . APP_NAME . '</strong>
                            </p>
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                © ' . date('Y') . ' - Desenvolvido por <a href="https://dantetesta.com.br" style="color: #667eea; text-decoration: none;">Dante Testa</a>
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}
