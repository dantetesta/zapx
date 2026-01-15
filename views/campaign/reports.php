<?php 
$pageTitle = 'Relatórios - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-chart-line mr-2 gradient-text"></i>
                Relatórios de Campanhas
            </h1>
            <p class="mt-2 text-gray-600">Acompanhe o desempenho dos seus disparos em massa</p>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-purple-600"><?php echo $stats['totalCampaigns']; ?></p>
                <p class="text-xs text-gray-500 mt-1">Total Campanhas</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-blue-600"><?php echo $stats['runningCampaigns']; ?></p>
                <p class="text-xs text-gray-500 mt-1">Em Execução</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-green-600"><?php echo $stats['completedCampaigns']; ?></p>
                <p class="text-xs text-gray-500 mt-1">Concluídas</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['totalContacts']); ?></p>
                <p class="text-xs text-gray-500 mt-1">Total Contatos</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-green-600"><?php echo number_format($stats['totalSent']); ?></p>
                <p class="text-xs text-gray-500 mt-1">Enviados</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-red-600"><?php echo number_format($stats['totalFailed']); ?></p>
                <p class="text-xs text-gray-500 mt-1">Falhas</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold <?php echo $stats['successRate'] >= 90 ? 'text-green-600' : ($stats['successRate'] >= 70 ? 'text-yellow-600' : 'text-red-600'); ?>">
                    <?php echo $stats['successRate']; ?>%
                </p>
                <p class="text-xs text-gray-500 mt-1">Taxa Sucesso</p>
            </div>
        </div>

        <!-- Gráfico de Taxa de Sucesso -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-pie mr-2 text-purple-600"></i>
                Distribuição de Envios
            </h3>
            <div class="flex flex-col md:flex-row items-center justify-center gap-8">
                <div class="relative w-48 h-48">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <?php 
                        $total = $stats['totalSent'] + $stats['totalFailed'];
                        $sentPercent = $total > 0 ? ($stats['totalSent'] / $total) * 100 : 0;
                        $failedPercent = 100 - $sentPercent;
                        $sentDash = $sentPercent * 2.51327;
                        $failedDash = $failedPercent * 2.51327;
                        ?>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="20"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#22c55e" stroke-width="20" 
                                stroke-dasharray="<?php echo $sentDash; ?> 251.327" stroke-linecap="round"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#ef4444" stroke-width="20" 
                                stroke-dasharray="<?php echo $failedDash; ?> 251.327" 
                                stroke-dashoffset="-<?php echo $sentDash; ?>" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-800"><?php echo $stats['successRate']; ?>%</span>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                        <span class="text-gray-700">Enviados: <strong><?php echo number_format($stats['totalSent']); ?></strong></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 bg-red-500 rounded"></div>
                        <span class="text-gray-700">Falhas: <strong><?php echo number_format($stats['totalFailed']); ?></strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Campanhas Recentes -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-history mr-2 text-purple-600"></i>
                    Campanhas Recentes
                </h3>
                <a href="<?php echo APP_URL; ?>/campaign/index" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                    Ver todas <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <?php if (empty($recentCampaigns)): ?>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p>Nenhuma campanha encontrada</p>
                <a href="<?php echo APP_URL; ?>/campaign/create" class="inline-block mt-4 px-4 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                    Criar Primeira Campanha
                </a>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Campanha</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Contatos</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Enviados</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Falhas</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Taxa</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Data</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentCampaigns as $campaign): ?>
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
                                'running' => 'Executando',
                                'paused' => 'Pausada',
                                'completed' => 'Concluída',
                                'cancelled' => 'Cancelada'
                            ];
                            $rate = ($campaign['sent_count'] + $campaign['failed_count']) > 0 
                                ? round(($campaign['sent_count'] / ($campaign['sent_count'] + $campaign['failed_count'])) * 100) 
                                : 0;
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($campaign['name'] ?: 'Campanha #' . $campaign['id']); ?></p>
                                <p class="text-xs text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars(substr($campaign['message'], 0, 50)); ?>...</p>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusColors[$campaign['status']] ?? 'bg-gray-100'; ?>">
                                    <?php echo $statusLabels[$campaign['status']] ?? $campaign['status']; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-medium"><?php echo $campaign['total_contacts']; ?></td>
                            <td class="py-3 px-4 text-center text-green-600 font-medium"><?php echo $campaign['sent_count']; ?></td>
                            <td class="py-3 px-4 text-center text-red-600 font-medium"><?php echo $campaign['failed_count']; ?></td>
                            <td class="py-3 px-4 text-center">
                                <span class="font-medium <?php echo $rate >= 90 ? 'text-green-600' : ($rate >= 70 ? 'text-yellow-600' : 'text-red-600'); ?>">
                                    <?php echo $rate; ?>%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($campaign['created_at'])); ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button onclick="showCampaignDetail(<?php echo $campaign['id']; ?>)" 
                                        class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition">
                                    <i class="fas fa-eye mr-1"></i> Detalhes
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div id="detailModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" onclick="closeModal(event)">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden m-4" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-white">
                <i class="fas fa-chart-bar mr-2"></i>
                Detalhes da Campanha
            </h3>
            <button onclick="closeModal()" class="text-white hover:bg-white/20 rounded-lg p-2 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modalContent" class="p-6 overflow-y-auto max-h-[calc(90vh-60px)]">
            <div class="flex items-center justify-center py-12">
                <i class="fas fa-spinner fa-spin text-3xl text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<script>
async function showCampaignDetail(id) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('modalContent');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    content.innerHTML = '<div class="flex items-center justify-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-purple-600"></i></div>';
    
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/reportDetail/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const c = data.campaign;
            const stats = data.stats;
            const items = data.queueItems;
            
            const rate = (stats.sent + stats.failed) > 0 
                ? Math.round((stats.sent / (stats.sent + stats.failed)) * 100) 
                : 0;
            
            let html = `
                <div class="mb-6">
                    <h4 class="text-xl font-bold text-gray-900">${c.name || 'Campanha #' + c.id}</h4>
                    <p class="text-sm text-gray-500 mt-1">Criada em ${new Date(c.created_at).toLocaleString('pt-BR')}</p>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-gray-800">${c.total_contacts}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-green-600">${stats.sent}</p>
                        <p class="text-xs text-gray-500">Enviados</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-red-600">${stats.failed}</p>
                        <p class="text-xs text-gray-500">Falhas</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-yellow-600">${stats.pending}</p>
                        <p class="text-xs text-gray-500">Pendentes</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold ${rate >= 90 ? 'text-green-600' : (rate >= 70 ? 'text-yellow-600' : 'text-red-600')}">${rate}%</p>
                        <p class="text-xs text-gray-500">Taxa</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h5 class="font-semibold text-gray-700 mb-2">Mensagem:</h5>
                    <p class="text-gray-600 whitespace-pre-wrap">${escapeHtml(c.message)}</p>
                </div>
                
                <h5 class="font-semibold text-gray-700 mb-3">Contatos (${items.length}):</h5>
                <div class="max-h-64 overflow-y-auto border rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="text-left py-2 px-3">Contato</th>
                                <th class="text-left py-2 px-3">Telefone</th>
                                <th class="text-center py-2 px-3">Status</th>
                                <th class="text-left py-2 px-3">Erro</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            items.forEach(item => {
                const statusClasses = {
                    'sent': 'bg-green-100 text-green-700',
                    'failed': 'bg-red-100 text-red-700',
                    'pending': 'bg-yellow-100 text-yellow-700',
                    'processing': 'bg-blue-100 text-blue-700',
                    'cancelled': 'bg-gray-100 text-gray-700'
                };
                
                html += `
                    <tr class="border-b border-gray-100">
                        <td class="py-2 px-3">${escapeHtml(item.contact_name || 'Sem nome')}</td>
                        <td class="py-2 px-3 font-mono text-xs">${item.contact_phone}</td>
                        <td class="py-2 px-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full ${statusClasses[item.status] || 'bg-gray-100'}">${item.status}</span>
                        </td>
                        <td class="py-2 px-3 text-xs text-red-600">${item.error_message || '-'}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            content.innerHTML = html;
        } else {
            content.innerHTML = '<p class="text-red-600 text-center py-8">Erro ao carregar detalhes</p>';
        }
    } catch (error) {
        content.innerHTML = '<p class="text-red-600 text-center py-8">Erro ao carregar detalhes</p>';
    }
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    const modal = document.getElementById('detailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include 'views/layouts/footer.php'; ?>
