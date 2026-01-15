<?php
/**
 * Step 2: Configuração do Banco de Dados
 */

$dbData = $_SESSION['db_data'] ?? [
    'host' => 'localhost',
    'name' => 'zapx',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
];
?>

<div class="bg-white rounded-xl shadow-md p-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">
        <i class="fas fa-database mr-2 text-purple-600"></i>
        Configuração do Banco de Dados
    </h2>
    <p class="text-gray-600 mb-8">Informe os dados de conexão com o MySQL</p>

    <form action="process.php?action=test_database" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-server mr-1"></i>
                    Host do Banco *
                </label>
                <input type="text" name="db_host" value="<?php echo htmlspecialchars($dbData['host']); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="localhost ou IP">
                <p class="text-xs text-gray-500 mt-1">Geralmente é "localhost"</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-database mr-1"></i>
                    Nome do Banco *
                </label>
                <input type="text" name="db_name" value="<?php echo htmlspecialchars($dbData['name']); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="zapx">
                <p class="text-xs text-gray-500 mt-1">Nome do banco de dados</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-1"></i>
                    Usuário *
                </label>
                <input type="text" name="db_user" value="<?php echo htmlspecialchars($dbData['user']); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="root">
                <p class="text-xs text-gray-500 mt-1">Usuário do MySQL</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-key mr-1"></i>
                    Senha
                </label>
                <input type="password" name="db_pass" value="<?php echo htmlspecialchars($dbData['pass']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="••••••••">
                <p class="text-xs text-gray-500 mt-1">Deixe em branco se não houver senha</p>
            </div>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
            <div class="flex">
                <i class="fas fa-info-circle text-blue-500 text-xl mr-3"></i>
                <div>
                    <p class="font-semibold text-blue-800">Importante!</p>
                    <p class="text-blue-700 text-sm mt-1">
                        O banco de dados deve estar criado antes de continuar. 
                        O instalador criará automaticamente as tabelas necessárias.
                    </p>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-between items-center pt-6 border-t">
            <a href="?step=requirements" id="btnVoltar" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
            <button type="submit" id="btnTestar" class="px-8 py-3 gradient-bg text-white font-semibold rounded-lg hover:opacity-90 transition">
                <i class="fas fa-check mr-2" id="iconTestar"></i>
                <span id="textTestar">Testar Conexão</span>
            </button>
        </div>
    </form>
</div>

<script>
// Mostrar preloader ao testar conexão
document.querySelector('form').addEventListener('submit', function(e) {
    const btnTestar = document.getElementById('btnTestar');
    const iconTestar = document.getElementById('iconTestar');
    const textTestar = document.getElementById('textTestar');
    const btnVoltar = document.getElementById('btnVoltar');
    
    // Desabilitar botões
    btnTestar.disabled = true;
    btnVoltar.style.pointerEvents = 'none';
    btnVoltar.style.opacity = '0.5';
    
    // Mudar visual do botão
    iconTestar.className = 'fas fa-circle-notch fa-spin mr-2';
    textTestar.textContent = 'Testando Conexão...';
    btnTestar.style.opacity = '0.7';
});
</script>
