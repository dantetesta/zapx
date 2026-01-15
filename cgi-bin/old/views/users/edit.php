<?php 
/**
 * Edição de Usuário (Admin)
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 14:46:00
 */
$pageTitle = 'Editar Usuário - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-user-edit mr-2 gradient-text"></i>
                Editar Usuário
            </h1>
            <p class="mt-2 text-gray-600">Altere as informações do usuário</p>
        </div>

        <?php if (isset($errors) && !empty($errors)): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
            <?php foreach ($errors as $error): ?>
            <p class="text-sm text-red-700"><i class="fas fa-exclamation-circle mr-1"></i><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <p class="text-sm text-green-700"><i class="fas fa-check-circle mr-1"></i>Usuário atualizado com sucesso!</p>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo APP_URL; ?>/users/update/<?php echo $userData['id']; ?>" class="bg-white rounded-xl shadow-md p-6 space-y-6">
            <!-- Informações Básicas -->
            <div class="border-b pb-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user mr-2 text-purple-600"></i>
                    Informações Básicas
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <!-- Alterar Senha -->
            <div class="border-b pb-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-lock mr-2 text-yellow-600"></i>
                    Alterar Senha
                </h3>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                    <p class="text-xs text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Deixe em branco para manter a senha atual
                    </p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nova Senha</label>
                        <input type="password" name="password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha</label>
                        <input type="password" name="confirm_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <!-- Permissões e Limites (Apenas Admin) -->
            <div class="border-b pb-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-shield-alt mr-2 text-blue-600"></i>
                    Permissões e Limites
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                        <input type="checkbox" name="is_admin" id="is_admin" 
                               <?php echo $userData['is_admin'] ? 'checked' : ''; ?>
                               class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500">
                        <label for="is_admin" class="ml-3">
                            <span class="text-sm font-medium text-gray-900">Administrador</span>
                            <p class="text-xs text-gray-600">Usuário terá acesso total ao sistema</p>
                        </label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-1 text-blue-600"></i>
                            Limite de Mensagens por Mês
                        </label>
                        <input type="number" 
                               name="message_limit" 
                               value="<?php echo htmlspecialchars($userData['message_limit']); ?>" 
                               min="100" 
                               step="100"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Administradores têm limite ilimitado automaticamente
                        </p>
                    </div>
                </div>
            </div>

            <!-- Estatísticas de Uso -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">
                    <i class="fas fa-chart-bar mr-2 text-purple-600"></i>
                    Estatísticas de Uso
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Mensagens Enviadas</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($userData['messages_sent']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Próximo Reset</p>
                        <p class="text-sm font-semibold text-gray-900">
                            <?php echo date('d/m/Y', strtotime($userData['limit_reset_date'] . ' +1 month')); ?>
                        </p>
                    </div>
                </div>
                
                <?php if (!$userData['is_admin']): ?>
                <div class="mt-3">
                    <button type="button" onclick="resetarContador()" 
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        <i class="fas fa-redo mr-1"></i>
                        Resetar contador de mensagens
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Botões -->
            <div class="flex gap-4">
                <a href="<?php echo APP_URL; ?>/users/index" 
                   class="flex-1 px-6 py-3 border border-gray-300 text-center text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="flex-1 px-6 py-3 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-save mr-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
async function resetarContador() {
    if (!confirm('Deseja resetar o contador de mensagens deste usuário?')) {
        return;
    }
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/users/resetMessageCount/<?php echo $userData['id']; ?>', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Contador resetado com sucesso!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Erro ao resetar contador', 'error');
        }
    } catch (error) {
        showNotification('Erro ao resetar contador', 'error');
    }
}
</script>

<?php include 'views/layouts/footer.php'; ?>
