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
    'smtp_user' => '',
    'smtp_pass' => '',
    'smtp_from' => '',
    'smtp_from_name' => 'ZAPX'
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

        <!-- SMTP -->
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-envelope mr-2 text-blue-600"></i>
                Configurações de Email (SMTP)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Servidor SMTP *
                    </label>
                    <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($configData['smtp_host']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="smtp.gmail.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Porta SMTP *
                    </label>
                    <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($configData['smtp_port']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="587">
                    <p class="text-xs text-gray-500 mt-1">587 (TLS) ou 465 (SSL)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Usuário SMTP *
                    </label>
                    <input type="email" name="smtp_user" value="<?php echo htmlspecialchars($configData['smtp_user']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="seu@email.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Senha SMTP *
                    </label>
                    <input type="password" name="smtp_pass" value="<?php echo htmlspecialchars($configData['smtp_pass']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email Remetente *
                    </label>
                    <input type="email" name="smtp_from" value="<?php echo htmlspecialchars($configData['smtp_from']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="noreply@seudominio.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Remetente *
                    </label>
                    <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($configData['smtp_from_name']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="ZAPX">
                </div>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mt-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-xl mr-3"></i>
                    <div>
                        <p class="font-semibold text-yellow-800">Atenção!</p>
                        <p class="text-yellow-700 text-sm mt-1">
                            O SMTP é necessário para o sistema de recuperação de senha funcionar.
                            Certifique-se de usar credenciais válidas.
                        </p>
                    </div>
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
