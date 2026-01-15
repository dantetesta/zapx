<?php 
$pageTitle = 'Dashboard - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-chart-line mr-2 gradient-text"></i>
                Dashboard
            </h1>
            <p class="mt-2 text-gray-600">Bem-vindo de volta, <?php echo htmlspecialchars($user['name']); ?>! 👋</p>
        </div>

        <!-- Saldo de Mensagens (apenas para usuários não-admin) -->
        <?php if ($user['is_admin'] != 1 && $messageBalance['limit'] !== 'ilimitado'): ?>
        <div class="mb-6 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold mb-1">
                        <i class="fas fa-envelope mr-2"></i>
                        Saldo de Mensagens
                    </h3>
                    <p class="text-blue-100 text-sm">Limite mensal de disparos</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold"><?php echo number_format($messageBalance['remaining']); ?></div>
                    <div class="text-blue-100 text-sm">disponíveis</div>
                </div>
            </div>
            
            <!-- Barra de Progresso -->
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span><?php echo number_format($messageBalance['sent']); ?> enviadas</span>
                    <span><?php echo number_format($messageBalance['limit']); ?> total</span>
                </div>
                <div class="w-full bg-blue-900 bg-opacity-30 rounded-full h-3">
                    <?php 
                    $percentage = $messageBalance['limit'] > 0 ? ($messageBalance['sent'] / $messageBalance['limit']) * 100 : 0;
                    $barColor = $percentage >= 90 ? 'bg-red-500' : ($percentage >= 70 ? 'bg-yellow-500' : 'bg-green-500');
                    ?>
                    <div class="<?php echo $barColor; ?> h-3 rounded-full transition-all duration-500" 
                         style="width: <?php echo min(100, $percentage); ?>%"></div>
                </div>
            </div>
            
            <div class="flex items-center justify-between text-sm">
                <span class="text-blue-100">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Renova em: <?php echo date('d/m/Y', strtotime($messageBalance['reset_date'] . ' +1 month')); ?>
                </span>
                <?php if ($percentage >= 80): ?>
                <span class="bg-yellow-500 text-yellow-900 px-3 py-1 rounded-full text-xs font-semibold">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Atenção ao limite!
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total de Contatos -->
            <div class="bg-white rounded-xl shadow-md p-6 hover-scale cursor-pointer border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total de Contatos</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['contacts']); ?></p>
                    </div>
                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-address-book text-2xl text-blue-600"></i>
                    </div>
                </div>
                <a href="<?php echo APP_URL; ?>/contacts/index" class="mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium inline-flex items-center">
                    Ver contatos <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Total de Tags -->
            <div class="bg-white rounded-xl shadow-md p-6 hover-scale cursor-pointer border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Tags/Categorias</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['tags']); ?></p>
                    </div>
                    <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-tags text-2xl text-purple-600"></i>
                    </div>
                </div>
                <a href="<?php echo APP_URL; ?>/tags/index" class="mt-4 text-sm text-purple-600 hover:text-purple-700 font-medium inline-flex items-center">
                    Gerenciar tags <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Mensagens Enviadas -->
            <div class="bg-white rounded-xl shadow-md p-6 hover-scale cursor-pointer border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Mensagens Enviadas</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['dispatches']['sent'] ?? 0); ?></p>
                    </div>
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-2xl text-green-600"></i>
                    </div>
                </div>
                <p class="mt-4 text-sm text-gray-500">
                    <i class="fas fa-chart-line mr-1"></i>
                    Taxa de sucesso: <?php echo $stats['dispatches']['total'] > 0 ? round(($stats['dispatches']['sent'] / $stats['dispatches']['total']) * 100, 1) : 0; ?>%
                </p>
            </div>

            <!-- Total de Disparos -->
            <div class="bg-white rounded-xl shadow-md p-6 hover-scale cursor-pointer border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total de Disparos</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['dispatches']['total'] ?? 0); ?></p>
                    </div>
                    <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-paper-plane text-2xl text-orange-600"></i>
                    </div>
                </div>
                <a href="<?php echo APP_URL; ?>/dispatch/history" class="mt-4 text-sm text-orange-600 hover:text-orange-700 font-medium inline-flex items-center">
                    Ver histórico <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Novo Disparo -->
            <div class="bg-gradient-to-br from-purple-600 to-blue-600 rounded-xl shadow-lg p-8 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Novo Disparo</h3>
                        <p class="text-purple-100">Envie mensagens em massa para seus contatos</p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-rocket text-3xl"></i>
                    </div>
                </div>
                <a href="<?php echo APP_URL; ?>/dispatch/index" 
                   class="inline-flex items-center px-6 py-3 bg-white text-purple-600 font-semibold rounded-lg hover:bg-opacity-90 transition hover-scale">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Iniciar Disparo
                </a>
            </div>

            <!-- Importar Contatos -->
            <div class="bg-gradient-to-br from-green-600 to-teal-600 rounded-xl shadow-lg p-8 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Importar Contatos</h3>
                        <p class="text-green-100">Adicione contatos em massa via CSV</p>
                    </div>
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-import text-3xl"></i>
                    </div>
                </div>
                <a href="<?php echo APP_URL; ?>/contacts/index" 
                   class="inline-flex items-center px-6 py-3 bg-white text-green-600 font-semibold rounded-lg hover:bg-opacity-90 transition hover-scale">
                    <i class="fas fa-upload mr-2"></i>
                    Importar Agora
                </a>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gráfico de Disparos por Dia -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-chart-line mr-2 text-purple-600"></i>
                    Disparos dos Últimos 7 Dias
                </h3>
                <div id="dailyChartContainer" style="height: 200px;">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Taxa de Sucesso -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-purple-600"></i>
                    Taxa de Sucesso
                </h3>
                <div id="successChartContainer" style="height: 200px;">
                    <canvas id="successChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Últimos Disparos -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-history mr-2 text-gray-600"></i>
                    Últimos Disparos
                </h3>
            </div>
            <div class="overflow-x-auto">
                <?php if (empty($recentDispatches)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-5xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-medium">Nenhum disparo realizado ainda</p>
                    <p class="text-sm mt-2">Comece enviando sua primeira mensagem em massa!</p>
                    <a href="<?php echo APP_URL; ?>/dispatch/index" 
                       class="inline-flex items-center mt-4 px-6 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Fazer Primeiro Disparo
                    </a>
                </div>
                <?php else: ?>
                <table class="min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensagem</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentDispatches as $dispatch): 
                            // Decodificar mensagem JSON
                            $messageData = json_decode($dispatch['message'], true);
                            
                            // Se json_decode retornou array, é mensagem estruturada
                            if (is_array($messageData)) {
                                $hasMedia = !empty($messageData['media_type']) && $messageData['media_type'] !== 'text';
                                $mediaType = $messageData['media_type'] ?? 'text';
                                $textMessage = $messageData['message'] ?? '';
                            } else {
                                // É texto puro (não é JSON)
                                $hasMedia = false;
                                $mediaType = 'text';
                                $textMessage = $dispatch['message'];
                            }
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <!-- Data/Hora -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($dispatch['created_at'])); ?>
                            </td>
                            
                            <!-- Contato -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($dispatch['contact_name'] ?: 'Sem nome'); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($dispatch['contact_phone']); ?>
                                </div>
                            </td>
                            
                            <!-- Mensagem com Mídia -->
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <?php if ($hasMedia): ?>
                                        <!-- Thumbnail/Ícone -->
                                        <div class="flex-shrink-0">
                                            <?php if ($mediaType === 'image' && !empty($dispatch['thumbnail_path']) && file_exists(__DIR__ . '/../../' . $dispatch['thumbnail_path'])): ?>
                                                <img src="/<?php echo htmlspecialchars($dispatch['thumbnail_path']); ?>" 
                                                     alt="Thumbnail" 
                                                     class="w-10 h-10 object-cover rounded border border-gray-200">
                                            <?php else: ?>
                                                <?php if ($mediaType === 'image'): ?>
                                                    <div class="w-10 h-10 bg-purple-100 rounded flex items-center justify-center">
                                                        <i class="fas fa-image text-purple-600"></i>
                                                    </div>
                                                <?php elseif ($mediaType === 'video'): ?>
                                                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded flex items-center justify-center">
                                                        <i class="fas fa-play text-white text-sm"></i>
                                                    </div>
                                                <?php elseif ($mediaType === 'audio'): ?>
                                                    <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center">
                                                        <i class="fas fa-microphone text-green-600"></i>
                                                    </div>
                                                <?php elseif ($mediaType === 'document'): ?>
                                                    <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center">
                                                        <i class="fas fa-file-alt text-blue-600"></i>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Texto da mensagem -->
                                    <div class="flex-1 min-w-0">
                                        <?php if ($hasMedia): ?>
                                            <div class="flex items-center gap-2 mb-1">
                                                <i class="fas fa-paperclip text-gray-400 text-xs"></i>
                                                <span class="text-xs font-medium text-gray-600">
                                                    <?php 
                                                    $mediaLabels = [
                                                        'image' => 'Imagem',
                                                        'video' => 'Vídeo',
                                                        'audio' => 'Áudio',
                                                        'document' => 'Documento'
                                                    ];
                                                    echo $mediaLabels[$mediaType] ?? 'Mídia';
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($textMessage)): ?>
                                            <p class="text-sm text-gray-700 line-clamp-2">
                                                <?php echo htmlspecialchars(substr($textMessage, 0, 100)) . (strlen($textMessage) > 100 ? '...' : ''); ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-400 italic">Mídia</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'sent' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800'
                                ];
                                $statusIcons = [
                                    'sent' => 'fa-check-circle',
                                    'failed' => 'fa-times-circle',
                                    'pending' => 'fa-clock'
                                ];
                                $statusLabels = [
                                    'sent' => 'Enviado',
                                    'failed' => 'Falhou',
                                    'pending' => 'Pendente'
                                ];
                                ?>
                                <span class="px-3 py-1 inline-flex items-center text-xs font-semibold rounded-full <?php echo $statusColors[$dispatch['status']]; ?>">
                                    <i class="fas <?php echo $statusIcons[$dispatch['status']]; ?> mr-1"></i>
                                    <?php echo $statusLabels[$dispatch['status']]; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js (Local) -->
<script src="<?php echo APP_URL; ?>/public/js/chart.min.js"></script>

<script>
// Aguardar carregamento do Chart.js
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se Chart.js foi carregado
    if (typeof Chart === 'undefined') {
        console.error('Chart.js não foi carregado!');
        return;
    }

    // Dados dos gráficos do PHP
    const chartData = <?php echo json_encode($chartData ?? ['daily' => [], 'success_rate' => ['sent' => 0, 'failed' => 0, 'pending' => 0]]); ?>;
    console.log('📊 Chart Data:', chartData);
    console.log('📅 Daily dispatches:', chartData.daily);
    console.log('📈 Success rate:', chartData.success_rate);

    // Gráfico de Disparos por Dia
    const dailyCtx = document.getElementById('dailyChart');
    const dailyContainer = document.getElementById('dailyChartContainer');
    
    console.log('🎨 Daily canvas:', dailyCtx);
    console.log('📦 Daily container:', dailyContainer);
    
    if (dailyCtx && chartData.daily && chartData.daily.length > 0) {
        console.log('✅ Renderizando gráfico diário com', chartData.daily.length, 'dias');
        // Preparar dados
        const dailyLabels = chartData.daily.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        });
        const dailySent = chartData.daily.map(d => parseInt(d.sent || 0));
        const dailyFailed = chartData.daily.map(d => parseInt(d.failed || 0));
    
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [
                {
                    label: 'Enviados',
                    data: dailySent,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Falhados',
                    data: dailyFailed,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    } else if (dailyContainer) {
        console.log('⚠️ Sem dados para gráfico diário');
        // Mostrar mensagem quando não há dados
        dailyContainer.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-gray-400 py-8">
                <i class="fas fa-chart-line text-5xl mb-3 opacity-30"></i>
                <p class="text-sm font-medium text-gray-500">Nenhum disparo nos últimos 7 dias</p>
                <p class="text-xs text-gray-400 mt-1">Faça seu primeiro disparo para ver os gráficos</p>
            </div>
        `;
    }

    // Gráfico de Taxa de Sucesso (Donut)
    const successCtx = document.getElementById('successChart');
    const successContainer = document.getElementById('successChartContainer');
    
    if (successCtx && chartData.success_rate) {
        const sent = chartData.success_rate.sent || 0;
        const failed = chartData.success_rate.failed || 0;
        const pending = chartData.success_rate.pending || 0;
        const total = sent + failed + pending;
        
        if (total > 0) {
    
    new Chart(successCtx, {
        type: 'doughnut',
        data: {
            labels: ['Enviados', 'Falhados', 'Pendentes'],
            datasets: [{
                data: [sent, failed, pending],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(234, 179, 8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
        } else if (successContainer) {
            console.log('⚠️ Sem dados para gráfico de sucesso (total = 0)');
            // Mostrar mensagem quando não há dados
            successContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full text-gray-400 py-8">
                    <i class="fas fa-chart-pie text-5xl mb-3 opacity-30"></i>
                    <p class="text-sm font-medium text-gray-500">Nenhum disparo realizado</p>
                    <p class="text-xs text-gray-400 mt-1">Comece enviando mensagens</p>
                </div>
            `;
        }
    } else if (successContainer) {
        console.log('⚠️ Sem dados de success_rate');
        // Mostrar mensagem quando não há dados
        successContainer.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-gray-400 py-8">
                <i class="fas fa-chart-pie text-5xl mb-3 opacity-30"></i>
                <p class="text-sm font-medium text-gray-500">Nenhum disparo realizado</p>
                <p class="text-xs text-gray-400 mt-1">Comece enviando mensagens</p>
            </div>
        `;
    }
    
    console.log('✅ Gráficos processados');
});
</script>

<?php include 'views/layouts/footer.php'; ?>
