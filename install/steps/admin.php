<?php
/**
 * Step 4: Criar Usuário Administrador
 */

$adminData = $_SESSION['admin_data'] ?? [
    'name' => '',
    'email' => '',
    'password' => ''
];
?>

<div class="bg-white rounded-xl shadow-md p-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">
        <i class="fas fa-user-shield mr-2 text-purple-600"></i>
        Criar Administrador
    </h2>
    <p class="text-gray-600 mb-8">Crie a conta do administrador do sistema</p>

    <form action="process.php?action=create_admin" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-1"></i>
                    Nome Completo *
                </label>
                <input type="text" name="admin_name" value="<?php echo htmlspecialchars($adminData['name']); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="João Silva">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-1"></i>
                    Email *
                </label>
                <input type="email" name="admin_email" value="<?php echo htmlspecialchars($adminData['email']); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="admin@seudominio.com">
                <p class="text-xs text-gray-500 mt-1">
                    Este email será usado para login e recuperação de senha
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-1"></i>
                    Senha *
                </label>
                <div class="relative">
                    <input type="password" id="admin_password" name="admin_password" required minlength="8"
                           class="w-full px-4 py-2 pr-24 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="••••••••"
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                           title="Senha deve ter no mínimo 8 caracteres, incluindo maiúsculas, minúsculas e números">
                    <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex gap-1">
                        <button type="button" onclick="togglePassword('admin_password')" 
                                class="p-2 text-gray-500 hover:text-gray-700 transition" title="Mostrar/Ocultar senha">
                            <i id="eye-icon-admin_password" class="fas fa-eye"></i>
                        </button>
                        <button type="button" onclick="generatePassword()" 
                                class="p-2 text-purple-600 hover:text-purple-700 transition" title="Gerar senha forte">
                            <i class="fas fa-key"></i>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-600 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Clique no ícone <i class="fas fa-key text-purple-600"></i> para gerar uma senha forte automaticamente
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-1"></i>
                    Confirmar Senha *
                </label>
                <div class="relative">
                    <input type="password" id="admin_password_confirm" name="admin_password_confirm" required minlength="8"
                           class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="••••••••">
                    <button type="button" onclick="togglePassword('admin_password_confirm')" 
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-500 hover:text-gray-700 transition" 
                            title="Mostrar/Ocultar senha">
                        <i id="eye-icon-admin_password_confirm" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
            <div class="flex">
                <i class="fas fa-info-circle text-blue-500 text-xl mr-3"></i>
                <div>
                    <p class="font-semibold text-blue-800">Informação</p>
                    <p class="text-blue-700 text-sm mt-1">
                        Esta será a conta principal do sistema com acesso total a todas as funcionalidades.
                        Guarde estas credenciais em local seguro!
                    </p>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-between items-center pt-6 border-t">
            <a href="?step=config" id="btnVoltar" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
            <button type="submit" id="btnInstalar" class="px-8 py-3 gradient-bg text-white font-semibold rounded-lg hover:opacity-90 transition">
                <i class="fas fa-check mr-2"></i>
                Instalar Sistema
            </button>
        </div>
    </form>
</div>

<!-- Preloader de Instalação -->
<div id="installLoader" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-cog fa-spin text-4xl text-purple-600"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-gray-900 mb-4">
                Instalando o Sistema...
            </h3>
            
            <p class="text-gray-600 mb-6">
                Por favor, aguarde enquanto configuramos tudo para você.
            </p>
            
            <!-- Barra de Progresso -->
            <div class="w-full bg-gray-200 rounded-full h-3 mb-4 overflow-hidden">
                <div id="progressBar" class="gradient-bg h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
            </div>
            
            <!-- Status -->
            <div id="installStatus" class="text-sm text-gray-600 space-y-2">
                <p class="flex items-center justify-center">
                    <i class="fas fa-circle-notch fa-spin mr-2 text-purple-600"></i>
                    <span id="statusText">Iniciando instalação...</span>
                </p>
            </div>
            
            <p class="text-xs text-gray-500 mt-6">
                <i class="fas fa-info-circle mr-1"></i>
                Não feche esta janela
            </p>
        </div>
    </div>
</div>

<script>
// Gerar senha forte
function generatePassword() {
    const length = 16;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*";
    const lowercase = "abcdefghijklmnopqrstuvwxyz";
    const uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const numbers = "0123456789";
    const special = "!@#$%&*";
    
    let password = "";
    
    // Garantir pelo menos um de cada tipo
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += special[Math.floor(Math.random() * special.length)];
    
    // Completar o resto
    for (let i = password.length; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Embaralhar
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    // Preencher ambos os campos
    const passwordField = document.getElementById('admin_password');
    const confirmField = document.getElementById('admin_password_confirm');
    
    passwordField.value = password;
    confirmField.value = password;
    
    // Mostrar as senhas
    passwordField.type = 'text';
    confirmField.type = 'text';
    document.getElementById('eye-icon-admin_password').classList.remove('fa-eye');
    document.getElementById('eye-icon-admin_password').classList.add('fa-eye-slash');
    document.getElementById('eye-icon-admin_password_confirm').classList.remove('fa-eye');
    document.getElementById('eye-icon-admin_password_confirm').classList.add('fa-eye-slash');
    
    // Feedback visual
    passwordField.classList.add('border-green-500');
    confirmField.classList.add('border-green-500');
    setTimeout(() => {
        passwordField.classList.remove('border-green-500');
        confirmField.classList.remove('border-green-500');
    }, 2000);
    
    // Notificação
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center';
    notification.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Senha forte gerada com sucesso!';
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Mostrar/Ocultar senha
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('eye-icon-' + fieldId);
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validar senhas iguais e mostrar preloader
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.querySelector('input[name="admin_password"]').value;
    const confirm = document.querySelector('input[name="admin_password_confirm"]').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('As senhas não coincidem!');
        return;
    }
    
    // Mostrar preloader
    showInstallLoader();
});

function showInstallLoader() {
    const loader = document.getElementById('installLoader');
    const progressBar = document.getElementById('progressBar');
    const statusText = document.getElementById('statusText');
    const btnInstalar = document.getElementById('btnInstalar');
    const btnVoltar = document.getElementById('btnVoltar');
    
    // Desabilitar botões
    btnInstalar.disabled = true;
    btnVoltar.style.pointerEvents = 'none';
    btnVoltar.style.opacity = '0.5';
    
    // Mostrar loader
    loader.classList.remove('hidden');
    
    // Simular progresso
    const steps = [
        { progress: 20, text: 'Criando tabelas no banco de dados...' },
        { progress: 40, text: 'Configurando sistema...' },
        { progress: 60, text: 'Criando usuário administrador...' },
        { progress: 80, text: 'Aplicando configurações de segurança...' },
        { progress: 95, text: 'Finalizando instalação...' }
    ];
    
    let currentStep = 0;
    
    const interval = setInterval(() => {
        if (currentStep < steps.length) {
            progressBar.style.width = steps[currentStep].progress + '%';
            statusText.textContent = steps[currentStep].text;
            currentStep++;
        } else {
            clearInterval(interval);
        }
    }, 800);
    
    // Timeout de segurança (30 segundos)
    setTimeout(() => {
        // Se ainda estiver na mesma página após 30s, algo deu errado
        if (!loader.classList.contains('hidden')) {
            clearInterval(interval);
            statusText.innerHTML = '<span class="text-red-600"><i class="fas fa-exclamation-triangle mr-2"></i>Tempo esgotado. Verifique os logs do servidor.</span>';
            progressBar.classList.add('bg-red-500');
            
            // Reabilitar botões após 3 segundos
            setTimeout(() => {
                btnInstalar.disabled = false;
                btnVoltar.style.pointerEvents = '';
                btnVoltar.style.opacity = '1';
                loader.classList.add('hidden');
                alert('A instalação demorou muito. Verifique:\n\n1. Configurações SMTP (se email estiver habilitado)\n2. Conexão com banco de dados\n3. Logs do servidor PHP\n\nTente novamente ou desmarque o envio de email.');
            }, 3000);
        }
    }, 30000);
}
</script>
