<?php 
$pageTitle = 'Gerenciar Usuários - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-users mr-2 gradient-text"></i>
                    Gerenciar Usuários
                </h1>
                <p class="mt-2 text-gray-600">Administre os usuários do sistema</p>
            </div>
            <a href="<?php echo APP_URL; ?>/auth/register" 
               class="mt-4 md:mt-0 px-4 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                <i class="fas fa-user-plus mr-2"></i>
                Novo Usuário
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <div>
                    <p class="text-sm text-green-700 font-medium">Usuário criado com sucesso!</p>
                    <?php if (isset($_GET['email_sent'])): ?>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-envelope mr-1"></i>
                        Email com credenciais enviado para o usuário.
                    </p>
                    <?php endif; ?>
                    <?php if (isset($_GET['email_failed'])): ?>
                    <p class="text-xs text-orange-600 mt-1">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Não foi possível enviar o email. Informe as credenciais manualmente.
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Lista de Usuários -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disparos (Mês)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $userItem): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-semibold">
                                        <?php echo strtoupper(substr($userItem['name'], 0, 1)); ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($userItem['name']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                    <?php echo htmlspecialchars($userItem['email']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($userItem['is_admin']): ?>
                                <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    <i class="fas fa-crown mr-1"></i>
                                    Administrador
                                </span>
                                <?php else: ?>
                                <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <i class="fas fa-user mr-1"></i>
                                    Usuário
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $sent = $userItem['messages_sent_this_month'] ?? 0;
                                $limit = $userItem['message_limit'] ?? 1000;
                                $percentage = $limit > 0 ? ($sent / $limit) * 100 : 0;
                                
                                // Cor baseada na porcentagem
                                if ($userItem['is_admin']) {
                                    $colorClass = 'text-purple-600';
                                    $bgClass = 'bg-purple-100';
                                    $progressClass = 'bg-purple-600';
                                } elseif ($percentage >= 90) {
                                    $colorClass = 'text-red-600';
                                    $bgClass = 'bg-red-100';
                                    $progressClass = 'bg-red-600';
                                } elseif ($percentage >= 70) {
                                    $colorClass = 'text-yellow-600';
                                    $bgClass = 'bg-yellow-100';
                                    $progressClass = 'bg-yellow-600';
                                } else {
                                    $colorClass = 'text-green-600';
                                    $bgClass = 'bg-green-100';
                                    $progressClass = 'bg-green-600';
                                }
                                ?>
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-semibold <?php echo $colorClass; ?>">
                                            <?php echo number_format($sent, 0, ',', '.'); ?>
                                        </span>
                                        <span class="text-gray-500 text-xs">
                                            / <?php echo $userItem['is_admin'] ? '∞' : number_format($limit, 0, ',', '.'); ?>
                                        </span>
                                    </div>
                                    <?php if (!$userItem['is_admin']): ?>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="<?php echo $progressClass; ?> h-1.5 rounded-full transition-all" 
                                             style="width: <?php echo min($percentage, 100); ?>%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo number_format($percentage, 1); ?>% usado
                                    </div>
                                    <?php else: ?>
                                    <div class="text-xs text-purple-600 font-medium">
                                        <i class="fas fa-infinity mr-1"></i>Ilimitado
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo date('d/m/Y', strtotime($userItem['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <a href="<?php echo APP_URL; ?>/users/edit/<?php echo $userItem['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900"
                                       title="Editar usuário">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($userItem['id'] != $user['id']): ?>
                                    <button onclick="deleteUser(<?php echo $userItem['id']; ?>)" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Deletar usuário">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php else: ?>
                                    <span class="text-gray-400 text-xs">(Você)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
async function deleteUser(id) {
    if (!confirmAction('Tem certeza que deseja deletar este usuário? Todos os seus dados serão removidos.')) return;
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/users/delete/' + id, {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erro ao deletar usuário', 'error');
    }
}
</script>

<?php include 'views/layouts/footer.php'; ?>
