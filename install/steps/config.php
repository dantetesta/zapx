<?php
/**
 * Step 3: Configurações Gerais
 */

// Detectar URL automaticamente
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host . str_replace('/install/index.php', '', $_SERVER['SCRIPT_NAME']);

$configData = $_SESSION['config_data'] ?? [
    'app_name' => 'ZAPX - Disparo em Massa WhatsApp',
    'app_url' => $baseUrl,
    'evo_url' => '',
    'evo_key' => '',
    'smtp_host' => '',
    'smtp_port' => '587',
    'smtp_encryption' => 'tls',
    'smtp_user' => '',
    'smtp_pass' => '',
    'smtp_from' => '',
    'smtp_from_name' => 'ZAPX',
    'recaptcha_enabled' => false,
    'recaptcha_site_key' => '',
    'recaptcha_secret_key' => ''
];
?>

<div class="bg-white rounded-xl shadow-md p-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">
        <i class="fas fa-cog mr-2 text-purple-600"></i>
        Configurações do Sistema
    </h2>
    <p class="text-gray-600 mb-8">Configure as integrações e serviços do sistema</p>

    <form action="process.php?action=save_config" method="POST" class="space-y-8">
        <!-- Configurações da Aplicação -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-rocket mr-2 text-purple-600"></i>
                Aplicação
            </h3>
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nome da Aplicação *
                    </label>
                    <input type="text" name="app_name" value="<?php echo htmlspecialchars($configData['app_name']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="ZAPX - Disparo em Massa WhatsApp">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        URL da Aplicação *
                    </label>
                    <input type="url" name="app_url" value="<?php echo htmlspecialchars($configData['app_url']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="https://seudominio.com.br">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        URL detectada automaticamente. Ajuste se necessário.
                    </p>
                </div>
            </div>
        </div>

        <!-- Evolution API -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fab fa-whatsapp mr-2 text-green-600"></i>
                Evolution API (WhatsApp)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        URL da Evolution API *
                    </label>
                    <input type="url" name="evo_url" value="<?php echo htmlspecialchars($configData['evo_url']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="http://seu-servidor-evolution.com">
                    <p class="text-xs text-gray-500 mt-1">
                        URL completa da sua instância Evolution API
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        API Key (Token Global) *
                    </label>
                    <input type="text" name="evo_key" value="<?php echo htmlspecialchars($configData['evo_key']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono"
                           placeholder="SuaChaveAPIGlobal">
                    <p class="text-xs text-gray-500 mt-1">
                        Token global para gerenciar instâncias
                    </p>
                </div>
            </div>
        </div>

        <!-- Google reCAPTCHA -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-shield-alt mr-2 text-red-600"></i>
                Google reCAPTCHA v2 (Segurança)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="flex items-center space-x-3 mb-4">
                        <input type="checkbox" name="recaptcha_enabled" value="1" 
                               <?php echo isset($configData['recaptcha_enabled']) && $configData['recaptcha_enabled'] ? 'checked' : ''; ?>
                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                               onchange="toggleRecaptchaFields(this.checked)">
                        <span class="text-sm font-medium text-gray-700">
                            Ativar Google reCAPTCHA no login (recomendado)
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 mb-4">
                        <i class="fas fa-info-circle mr-1"></i>
                        Protege contra ataques de força bruta. Obtenha suas chaves em: 
                        <a href="https://www.google.com/recaptcha/admin" target="_blank" class="text-purple-600 hover:underline">
                            google.com/recaptcha/admin
                        </a>
                    </p>
                </div>

                <div id="recaptcha-fields" style="display: none;" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Site Key (Chave do Site)
                        </label>
                        <input type="text" name="recaptcha_site_key" value="<?php echo htmlspecialchars($configData['recaptcha_site_key'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm"
                               placeholder="6Lc...">
                        <p class="text-xs text-gray-500 mt-1">Chave pública (visível no HTML)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Secret Key (Chave Secreta)
                        </label>
                        <input type="text" name="recaptcha_secret_key" value="<?php echo htmlspecialchars($configData['recaptcha_secret_key'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm"
                               placeholder="6Lc...">
                        <p class="text-xs text-gray-500 mt-1">Chave privada (servidor)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMTP (Opcional) -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-envelope mr-2 text-blue-600"></i>
                Configurações de Email - SMTP (Opcional)
            </h3>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Opcional:</strong> Configure agora ou deixe em branco para configurar depois manualmente no arquivo <code>config/config.php</code>. 
                    Necessário apenas para recuperação de senha.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Servidor SMTP
                    </label>
                    <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($configData['smtp_host']); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="smtp.gmail.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Porta SMTP
                    </label>
                    <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($configData['smtp_port']); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="587">
                    <p class="text-xs text-gray-500 mt-1">Comum: 587 (TLS), 465 (SSL), 25 (Sem criptografia)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Criptografia SMTP
                    </label>
                    <select name="smtp_encryption" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="tls" <?php echo ($configData['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS/STARTTLS (Recomendado - Porta 587)</option>
                        <option value="ssl" <?php echo ($configData['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL (Porta 465)</option>
                        <option value="none" <?php echo ($configData['smtp_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>Sem Criptografia (Porta 25)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <strong>TLS:</strong> Gmail, Outlook, Hostinger | 
                        <strong>SSL:</strong> Alguns servidores antigos | 
                        <strong>Nenhum:</strong> Localhost/testes
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Usuário SMTP
                    </label>
                    <input type="email" name="smtp_user" value="<?php echo htmlspecialchars($configData['smtp_user']); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="seu@email.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Senha SMTP
                    </label>
                    <div class="relative">
                        <input type="password" id="smtp_pass" name="smtp_pass" value="<?php echo htmlspecialchars($configData['smtp_pass']); ?>"
                               class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="••••••••">
                        <button type="button" onclick="toggleSmtpPassword()" 
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-500 hover:text-gray-700 transition" 
                                title="Mostrar/Ocultar senha">
                            <i id="eye-icon-smtp" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email Remetente
                    </label>
                    <input type="email" name="smtp_from" value="<?php echo htmlspecialchars($configData['smtp_from']); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="noreply@seudominio.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Remetente
                    </label>
                    <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($configData['smtp_from_name']); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="ZAPX">
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-between items-center pt-6 border-t">
            <a href="?step=database" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
            <button type="submit" class="px-8 py-3 gradient-bg text-white font-semibold rounded-lg hover:opacity-90 transition">
                Próximo: Administrador
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>

<script>
// Mostrar/ocultar campos do reCAPTCHA
function toggleRecaptchaFields(enabled) {
    const fields = document.getElementById('recaptcha-fields');
    if (enabled) {
        fields.style.display = 'grid';
    } else {
        fields.style.display = 'none';
    }
}

// Toggle senha SMTP
function toggleSmtpPassword() {
    const input = document.getElementById('smtp_pass');
    const icon = document.getElementById('eye-icon-smtp');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Verificar estado inicial ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.querySelector('input[name="recaptcha_enabled"]');
    if (checkbox && checkbox.checked) {
        toggleRecaptchaFields(true);
    }
});
</script>
