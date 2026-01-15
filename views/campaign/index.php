<?php 
$pageTitle = 'Campanhas - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-bullhorn mr-2 gradient-text"></i>
                    Minhas Campanhas
                </h1>
                <p class="mt-2 text-gray-600">Gerencie suas campanhas de disparo em massa</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="<?php echo APP_URL; ?>/campaign/create" 
                   class="inline-flex items-center px-6 py-3 gradient-bg text-white font-semibold rounded-lg hover:opacity-90 transition shadow-lg">
                    <i class="fas fa-plus mr-2"></i>
                    Nova Campanha
                </a>
            </div>
        </div>

        <!-- Lista de Campanhas -->
        <?php if (empty($campaigns)): ?>
        <div class="bg-white rounded-xl shadow-md p-12 text-center">
            <i class="fas fa-bullhorn text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhuma campanha criada</h3>
            <p class="text-gray-500 mb-6">Crie sua primeira campanha de disparo em massa</p>
            <a href="<?php echo APP_URL; ?>/campaign/create" 
               class="inline-flex items-center px-6 py-3 gradient-bg text-white font-semibold rounded-lg hover:opacity-90 transition">
                <i class="fas fa-plus mr-2"></i>
                Criar Campanha
            </a>
        </div>
        <?php else: ?>
        <div class="grid gap-6">
            <?php foreach ($campaigns as $campaign): ?>
            <?php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'running' => 'bg-blue-100 text-blue-800',
                    'paused' => 'bg-orange-100 text-orange-800',
                    'completed' => 'bg-green-100 text-green-800',
                    'cancelled' => 'bg-red-100 text-red-800'
                ];
                $statusLabels = [
                    'pending' => 'Pendente',
                    'running' => 'Em Execução',
                    'paused' => 'Pausada',
                    'completed' => 'Concluída',
                    'cancelled' => 'Cancelada'
                ];
                $statusColor = $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-800';
                $statusLabel = $statusLabels[$campaign['status']] ?? $campaign['status'];
                
                $progress = $campaign['total_contacts'] > 0 
                    ? round((($campaign['sent_count'] + $campaign['failed_count']) / $campaign['total_contacts']) * 100) 
                    : 0;
            ?>
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?php echo htmlspecialchars($campaign['name'] ?: 'Campanha #' . $campaign['id']); ?>
                            </h3>
                            <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo $statusColor; ?>">
                                <?php echo $statusLabel; ?>
                            </span>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                            <?php echo htmlspecialchars(substr($campaign['message'], 0, 100)) . (strlen($campaign['message']) > 100 ? '...' : ''); ?>
                        </p>
                        
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                            <span><i class="fas fa-users mr-1"></i> <?php echo $campaign['total_contacts']; ?> contatos</span>
                            <span><i class="fas fa-check-circle text-green-500 mr-1"></i> <?php echo $campaign['sent_count']; ?> enviados</span>
                            <span><i class="fas fa-times-circle text-red-500 mr-1"></i> <?php echo $campaign['failed_count']; ?> falhas</span>
                            <span><i class="fas fa-clock mr-1"></i> <?php echo date('d/m/Y H:i', strtotime($campaign['created_at'])); ?></span>
                        </div>
                        
                        <!-- Barra de Progresso -->
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-purple-600 to-blue-600 h-2 rounded-full transition-all" 
                                     style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><?php echo $progress; ?>% concluído</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 mt-4 md:mt-0 md:ml-6">
                        <?php if ($campaign['status'] === 'running' || $campaign['status'] === 'paused'): ?>
                        <a href="<?php echo APP_URL; ?>/campaign/monitor/<?php echo $campaign['id']; ?>" 
                           class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-eye mr-1"></i> Monitorar
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($campaign['status'] === 'completed' || $campaign['status'] === 'cancelled'): ?>
                        <button onclick="deleteCampaign(<?php echo $campaign['id']; ?>)" 
                                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-trash mr-1"></i> Excluir
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
async function deleteCampaign(id) {
    if (!confirm('Tem certeza que deseja excluir esta campanha?')) return;
    
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/delete/${id}`, {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao excluir');
        }
    } catch (error) {
        alert('Erro ao excluir campanha');
    }
}
</script>

<?php include 'views/layouts/footer.php'; ?>
