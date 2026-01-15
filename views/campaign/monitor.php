<?php 
$pageTitle = 'Monitorar Campanha - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';

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
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo APP_URL; ?>/campaign" class="text-purple-600 hover:text-purple-700 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-chart-line mr-2 gradient-text"></i>
                        <?php echo htmlspecialchars($campaign['name'] ?: 'Campanha #' . $campaign['id']); ?>
                    </h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span id="campaignStatus" class="px-3 py-1 text-sm font-medium rounded-full <?php echo $statusColors[$campaign['status']] ?? 'bg-gray-100'; ?>">
                            <?php echo $statusLabels[$campaign['status']] ?? $campaign['status']; ?>
                        </span>
                        <span class="text-gray-500 text-sm">
                            Criada em <?php echo date('d/m/Y H:i', strtotime($campaign['created_at'])); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Botões de Controle -->
                <div id="controlButtons" class="flex items-center gap-3 mt-4 md:mt-0">
                    <?php if ($campaign['status'] === 'running'): ?>
                    <button onclick="pauseCampaign()" id="pauseBtn"
                            class="px-5 py-2 bg-yellow-500 text-white font-medium rounded-lg hover:bg-yellow-600 transition">
                        <i class="fas fa-pause mr-2"></i> Pausar
                    </button>
                    <button onclick="cancelCampaign()" 
                            class="px-5 py-2 bg-red-500 text-white font-medium rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-stop mr-2"></i> Cancelar
                    </button>
                    <?php elseif ($campaign['status'] === 'paused'): ?>
                    <button onclick="resumeCampaign()" id="resumeBtn"
                            class="px-5 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 transition">
                        <i class="fas fa-play mr-2"></i> Retomar
                    </button>
                    <button onclick="cancelCampaign()" 
                            class="px-5 py-2 bg-red-500 text-white font-medium rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-stop mr-2"></i> Cancelar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-gray-900" id="statTotal"><?php echo $campaign['total_contacts']; ?></p>
                <p class="text-sm text-gray-500">Total</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-green-600" id="statSent"><?php echo $stats['sent']; ?></p>
                <p class="text-sm text-gray-500">Enviados</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-red-600" id="statFailed"><?php echo $stats['failed']; ?></p>
                <p class="text-sm text-gray-500">Falhas</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-yellow-600" id="statPending"><?php echo $stats['pending']; ?></p>
                <p class="text-sm text-gray-500">Pendentes</p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 text-center">
                <p class="text-3xl font-bold text-blue-600" id="statProcessing"><?php echo $stats['processing']; ?></p>
                <p class="text-sm text-gray-500">Processando</p>
            </div>
        </div>

        <!-- Barra de Progresso Grande -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900">Progresso</h3>
                <span id="progressPercent" class="text-2xl font-bold text-purple-600">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div id="progressBar" class="bg-gradient-to-r from-purple-600 to-blue-600 h-4 rounded-full transition-all duration-500" 
                     style="width: 0%"></div>
            </div>
            <div class="flex items-center justify-between mt-3 text-sm text-gray-500">
                <span id="lastUpdated">Última atualização: --</span>
                <span id="estimatedTime">Tempo estimado: calculando...</span>
            </div>
        </div>

        <!-- Mensagem da Campanha -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                <i class="fas fa-comment-dots mr-2 text-green-600"></i>
                Mensagem
            </h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-800 whitespace-pre-wrap"><?php echo htmlspecialchars($campaign['message']); ?></p>
            </div>
            <?php if ($campaign['media_type'] !== 'text'): ?>
            <div class="mt-3 flex items-center gap-2 text-sm text-gray-600">
                <i class="fas fa-paperclip"></i>
                <span>Mídia: <?php echo ucfirst($campaign['media_type']); ?></span>
                <?php if ($campaign['media_filename']): ?>
                <span class="text-gray-400">|</span>
                <span><?php echo htmlspecialchars($campaign['media_filename']); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Lista de Contatos na Fila -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-list-ol mr-2 text-purple-600"></i>
                Fila de Disparo
            </h3>
            
            <div id="queueList" class="space-y-2 max-h-96 overflow-y-auto">
                <?php foreach ($queueItems as $item): ?>
                <?php
                    $itemStatusColors = [
                        'pending' => 'bg-gray-100 text-gray-600',
                        'processing' => 'bg-blue-100 text-blue-600',
                        'sent' => 'bg-green-100 text-green-600',
                        'failed' => 'bg-red-100 text-red-600',
                        'cancelled' => 'bg-gray-200 text-gray-500'
                    ];
                    $itemStatusIcons = [
                        'pending' => 'fa-clock',
                        'processing' => 'fa-spinner fa-spin',
                        'sent' => 'fa-check-circle',
                        'failed' => 'fa-times-circle',
                        'cancelled' => 'fa-ban'
                    ];
                ?>
                <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 queue-item" 
                     data-id="<?php echo $item['id']; ?>" data-status="<?php echo $item['status']; ?>">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-semibold">
                            <?php echo strtoupper(substr($item['contact_name'] ?: 'S', 0, 1)); ?>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['contact_name'] ?: 'Sem nome'); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['contact_phone']); ?></p>
                        </div>
                    </div>
                    <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo $itemStatusColors[$item['status']] ?? 'bg-gray-100'; ?>">
                        <i class="fas <?php echo $itemStatusIcons[$item['status']] ?? 'fa-clock'; ?> mr-1"></i>
                        <?php echo ucfirst($item['status']); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
const campaignId = <?php echo $campaign['id']; ?>;
let isRunning = <?php echo $campaign['status'] === 'running' ? 'true' : 'false'; ?>;
let pollInterval = null;
let processInterval = null;

// Atualizar status via polling
async function updateStatus() {
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/status/${campaignId}`);
        const data = await response.json();
        
        if (data.success) {
            const c = data.campaign;
            
            // Atualizar estatísticas
            document.getElementById('statSent').textContent = c.sent;
            document.getElementById('statFailed').textContent = c.failed;
            document.getElementById('statPending').textContent = c.pending;
            document.getElementById('statProcessing').textContent = c.processing;
            
            // Atualizar progresso
            document.getElementById('progressPercent').textContent = c.progress + '%';
            document.getElementById('progressBar').style.width = c.progress + '%';
            
            // Atualizar última atualização
            const now = new Date();
            document.getElementById('lastUpdated').textContent = 'Última atualização: ' + now.toLocaleTimeString('pt-BR');
            
            // Calcular tempo estimado
            if (c.pending > 0 && c.sent > 0) {
                const avgTime = 10; // segundos médios
                const remaining = c.pending * avgTime;
                const minutes = Math.ceil(remaining / 60);
                document.getElementById('estimatedTime').textContent = `Tempo estimado: ~${minutes} min`;
            } else if (c.pending === 0) {
                document.getElementById('estimatedTime').textContent = 'Concluído!';
            }
            
            // Verificar se status mudou
            if (c.status !== '<?php echo $campaign['status']; ?>') {
                location.reload();
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar status:', error);
    }
}

// Trigger de processamento (fallback se cron não funcionar)
async function triggerProcess() {
    if (!isRunning) return;
    
    try {
        await fetch(`<?php echo APP_URL; ?>/campaign/process/${campaignId}`);
    } catch (error) {
        console.error('Erro ao processar:', error);
    }
}

// Pausar campanha
async function pauseCampaign() {
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/pause/${campaignId}`, { method: 'POST' });
        const data = await response.json();
        
        if (data.success) {
            isRunning = false;
            location.reload();
        } else {
            alert(data.message || 'Erro ao pausar');
        }
    } catch (error) {
        alert('Erro ao pausar campanha');
    }
}

// Retomar campanha
async function resumeCampaign() {
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/resume/${campaignId}`, { method: 'POST' });
        const data = await response.json();
        
        if (data.success) {
            isRunning = true;
            location.reload();
        } else {
            alert(data.message || 'Erro ao retomar');
        }
    } catch (error) {
        alert('Erro ao retomar campanha');
    }
}

// Cancelar campanha
async function cancelCampaign() {
    if (!confirm('⚠️ Tem certeza que deseja CANCELAR esta campanha?\n\nTodos os envios pendentes serão cancelados.')) {
        return;
    }
    
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/cancel/${campaignId}`, { method: 'POST' });
        const data = await response.json();
        
        if (data.success) {
            isRunning = false;
            location.reload();
        } else {
            alert(data.message || 'Erro ao cancelar');
        }
    } catch (error) {
        alert('Erro ao cancelar campanha');
    }
}

// Iniciar polling
function startPolling() {
    updateStatus();
    pollInterval = setInterval(updateStatus, 3000); // A cada 3 segundos
    
    // Trigger de processamento a cada 5 segundos (fallback do cron)
    if (isRunning) {
        processInterval = setInterval(triggerProcess, 5000);
    }
}

// Parar polling
function stopPolling() {
    if (pollInterval) clearInterval(pollInterval);
    if (processInterval) clearInterval(processInterval);
}

// Iniciar quando página carregar
document.addEventListener('DOMContentLoaded', startPolling);

// Parar quando sair da página
window.addEventListener('beforeunload', stopPolling);
</script>

<?php include 'views/layouts/footer.php'; ?>
