<?php 
$pageTitle = 'Novo Usuário - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-user-plus mr-2 gradient-text"></i>
                Novo Usuário
            </h1>
            <p class="mt-2 text-gray-600">Cadastre um novo usuário no sistema</p>
        </div>

        <?php if (isset($errors) && !empty($errors)): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
            <?php foreach ($errors as $error): ?>
            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo APP_URL; ?>/auth/register" class="bg-white rounded-xl shadow-md p-6 space-y-6">
            <!-- CSRF Token -->
            <?php echo CSRF::getTokenField(); ?>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
            <!-- Senhas com Toggle e Gerador -->
            <div class="space-y-4">
                <!-- Botão Gerar Senha -->
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700">Senha *</label>
                    <button type="button" onclick="generatePassword()" 
                            class="text-xs px-3 py-1 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition flex items-center gap-1">
                        <i class="fas fa-magic"></i>
                        Gerar Senha Forte
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   minlength="6"
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <button type="button" 
                                    onclick="togglePasswordField('password', 'toggleIcon1')" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition">
                                <i id="toggleIcon1" class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Mínimo 6 caracteres</p>
                    </div>
                    <div>
                        <div class="relative">
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required
                                   minlength="6"
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <button type="button" 
                                    onclick="togglePasswordField('confirm_password', 'toggleIcon2')" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition">
                                <i id="toggleIcon2" class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Confirme a senha</p>
                    </div>
                </div>
                
                <!-- Indicador de Força da Senha -->
                <div id="passwordStrength" class="hidden">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-600">Força:</span>
                        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div id="strengthBar" class="h-full transition-all duration-300"></div>
                        </div>
                        <span id="strengthText" class="font-medium"></span>
                    </div>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center">
                    <input type="checkbox" name="is_admin" id="is_admin" 
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                           <?php echo (isset($is_admin) && $is_admin) ? 'checked' : ''; ?>>
                    <label for="is_admin" class="ml-2 text-sm text-gray-700">
                        <i class="fas fa-crown text-purple-600 mr-1"></i>
                        Administrador
                    </label>
                </div>
                
                <div class="flex items-start">
                    <input type="checkbox" name="send_email" id="send_email" 
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                    <label for="send_email" class="ml-2 text-sm text-gray-700">
                        <i class="fas fa-envelope text-blue-600 mr-1"></i>
                        Enviar credenciais por email
                        <span class="block text-xs text-gray-500 mt-1">
                            As credenciais de acesso serão enviadas para o email do usuário
                        </span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-1 text-blue-600"></i>
                    Limite de Mensagens por Mês
                </label>
                <input type="number" 
                       name="message_limit" 
                       value="<?php echo htmlspecialchars($message_limit ?? '1000'); ?>" 
                       min="100" 
                       step="100"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Número de mensagens que o usuário pode enviar por mês. Administradores têm limite ilimitado.
                </p>
            </div>
            <div class="flex gap-4">
                <a href="<?php echo APP_URL; ?>/users/index" id="btnCancel" class="flex-1 px-6 py-3 border text-center rounded-lg">Cancelar</a>
                <button type="submit" id="btnSubmit" class="flex-1 px-6 py-3 gradient-bg text-white rounded-lg">
                    <span id="btnText">Criar Usuário</span>
                    <span id="btnLoader" class="hidden">
                        <i class="fas fa-circle-notch fa-spin mr-2"></i>
                        Criando e enviando email...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Loading ao submeter formulário
document.querySelector('form').addEventListener('submit', function(e) {
    const sendEmailCheckbox = document.getElementById('send_email');
    
    // Só mostra loading se o checkbox de envio de email estiver marcado
    if (sendEmailCheckbox && sendEmailCheckbox.checked) {
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const btnLoader = document.getElementById('btnLoader');
        const btnCancel = document.getElementById('btnCancel');
        
        // Desabilitar botões
        btnSubmit.disabled = true;
        btnCancel.style.pointerEvents = 'none';
        btnCancel.style.opacity = '0.5';
        
        // Mostrar loader
        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
    }
});

// Toggle mostrar/ocultar senha
function togglePasswordField(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    
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

// Gerar senha forte
function generatePassword() {
    const length = 12;
    const charset = {
        lowercase: 'abcdefghijklmnopqrstuvwxyz',
        uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        numbers: '0123456789',
        symbols: '!@#$%&*'
    };
    
    let password = '';
    
    // Garantir pelo menos um de cada tipo
    password += charset.lowercase[Math.floor(Math.random() * charset.lowercase.length)];
    password += charset.uppercase[Math.floor(Math.random() * charset.uppercase.length)];
    password += charset.numbers[Math.floor(Math.random() * charset.numbers.length)];
    password += charset.symbols[Math.floor(Math.random() * charset.symbols.length)];
    
    // Preencher o resto
    const allChars = charset.lowercase + charset.uppercase + charset.numbers + charset.symbols;
    for (let i = password.length; i < length; i++) {
        password += allChars[Math.floor(Math.random() * allChars.length)];
    }
    
    // Embaralhar
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    // Preencher campos
    const passwordField = document.getElementById('password');
    const confirmField = document.getElementById('confirm_password');
    
    passwordField.value = password;
    confirmField.value = password;
    
    // Mostrar senhas temporariamente
    passwordField.type = 'text';
    confirmField.type = 'text';
    document.getElementById('toggleIcon1').classList.remove('fa-eye');
    document.getElementById('toggleIcon1').classList.add('fa-eye-slash');
    document.getElementById('toggleIcon2').classList.remove('fa-eye');
    document.getElementById('toggleIcon2').classList.add('fa-eye-slash');
    
    // Verificar força
    checkPasswordStrength(password);
    
    // Notificação
    showNotification('Senha forte gerada! Copie ou anote em local seguro.', 'success');
}

// Verificar força da senha
function checkPasswordStrength(password) {
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    if (!password) {
        strengthDiv.classList.add('hidden');
        return;
    }
    
    strengthDiv.classList.remove('hidden');
    
    let strength = 0;
    
    // Critérios
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    // Atualizar visual
    const colors = {
        0: { width: '0%', bg: 'bg-gray-300', text: 'Muito Fraca', color: 'text-gray-600' },
        1: { width: '20%', bg: 'bg-red-500', text: 'Fraca', color: 'text-red-600' },
        2: { width: '40%', bg: 'bg-orange-500', text: 'Regular', color: 'text-orange-600' },
        3: { width: '60%', bg: 'bg-yellow-500', text: 'Boa', color: 'text-yellow-600' },
        4: { width: '80%', bg: 'bg-blue-500', text: 'Forte', color: 'text-blue-600' },
        5: { width: '100%', bg: 'bg-green-500', text: 'Muito Forte', color: 'text-green-600' },
        6: { width: '100%', bg: 'bg-green-600', text: 'Excelente', color: 'text-green-700' }
    };
    
    const level = colors[strength] || colors[0];
    
    strengthBar.style.width = level.width;
    strengthBar.className = `h-full transition-all duration-300 ${level.bg}`;
    strengthText.textContent = level.text;
    strengthText.className = `font-medium ${level.color}`;
}

// Monitorar digitação da senha
document.getElementById('password')?.addEventListener('input', function() {
    checkPasswordStrength(this.value);
});
</script>

<?php include 'views/layouts/footer.php'; ?>
