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
                <input type="password" name="admin_password" required minlength="6"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="••••••••">
                <p class="text-xs text-gray-500 mt-1">
                    Mínimo de 6 caracteres
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-1"></i>
                    Confirmar Senha *
                </label>
                <input type="password" name="admin_password_confirm" required minlength="6"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="••••••••">
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
            <a href="?step=config" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
            <button type="submit" class="px-8 py-3 gradient-bg text-white font-semibold rounded-lg hover:opacity-90 transition">
                <i class="fas fa-check mr-2"></i>
                Instalar Sistema
            </button>
        </div>
    </form>
</div>

<script>
// Validar senhas iguais
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.querySelector('input[name="admin_password"]').value;
    const confirm = document.querySelector('input[name="admin_password_confirm"]').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('As senhas não coincidem!');
    }
});
</script>
