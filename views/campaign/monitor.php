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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo APP_URL; ?>/campaign" class="text-purple-600 hover:text-purple-700 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
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

        <!-- Layout em Duas Colunas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- COLUNA ESQUERDA - Progresso e Stats -->
            <div class="space-y-6">
                <!-- Cards de Estatísticas -->
                <div class="grid grid-cols-5 gap-2">
                    <div class="bg-white rounded-lg shadow p-3 text-center">
                        <p class="text-2xl font-bold text-gray-900" id="statTotal"><?php echo $campaign['total_contacts']; ?></p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-3 text-center">
                        <p class="text-2xl font-bold text-green-600" id="statSent"><?php echo $stats['sent']; ?></p>
                        <p class="text-xs text-gray-500">Enviados</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-3 text-center">
                        <p class="text-2xl font-bold text-red-600" id="statFailed"><?php echo $stats['failed']; ?></p>
                        <p class="text-xs text-gray-500">Falhas</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-3 text-center">
                        <p class="text-2xl font-bold text-yellow-600" id="statPending"><?php echo $stats['pending']; ?></p>
                        <p class="text-xs text-gray-500">Pendentes</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-3 text-center">
                        <p class="text-2xl font-bold text-blue-600" id="statProcessing"><?php echo $stats['processing']; ?></p>
                        <p class="text-xs text-gray-500">Processando</p>
                    </div>
                </div>

                <!-- Barra de Progresso com Spinner -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-semibold text-gray-900">Progresso</h3>
                            <!-- Spinner de processamento -->
                            <div id="processingSpinner" class="<?php echo ($campaign['status'] === 'running' && $stats['sent'] == 0) ? '' : 'hidden'; ?>">
                                <div class="flex items-center gap-2 text-blue-600 animate-pulse">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                    <span class="text-sm">Iniciando...</span>
                                </div>
                            </div>
                        </div>
                        <span id="progressPercent" class="text-3xl font-bold text-purple-600"><?php echo $stats['sent'] > 0 ? round(($stats['sent'] / $campaign['total_contacts']) * 100) : 0; ?>%</span>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-5 overflow-hidden">
                        <!-- Barra animada quando processando -->
                        <div id="progressBar" class="h-5 rounded-full transition-all duration-500 relative <?php echo ($campaign['status'] === 'running') ? 'bg-gradient-to-r from-purple-600 via-blue-500 to-purple-600 bg-[length:200%_100%] animate-shimmer' : 'bg-gradient-to-r from-purple-600 to-blue-600'; ?>" 
                             style="width: <?php echo $stats['sent'] > 0 ? round(($stats['sent'] / $campaign['total_contacts']) * 100) : 0; ?>%">
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between mt-3 text-sm text-gray-500">
                        <span id="lastUpdated">Última atualização: <?php echo date('H:i:s'); ?></span>
                        <span id="estimatedTime">Tempo estimado: calculando...</span>
                    </div>
                    
                    <!-- Indicador de atividade -->
                    <div id="activityIndicator" class="mt-4 p-3 bg-blue-50 rounded-lg <?php echo $campaign['status'] === 'running' ? '' : 'hidden'; ?>">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="w-3 h-3 bg-blue-500 rounded-full animate-ping absolute"></div>
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            </div>
                            <span id="activityText" class="text-sm text-blue-700">Processando fila de disparo...</span>
                        </div>
                    </div>
                </div>

                <!-- Mensagem da Campanha -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">
                        <i class="fas fa-comment-dots mr-2 text-green-600"></i>
                        Mensagem
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4 max-h-32 overflow-y-auto">
                        <p class="text-gray-800 whitespace-pre-wrap text-sm"><?php echo htmlspecialchars($campaign['message']); ?></p>
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

                <!-- Log de Atividade em Tempo Real -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">
                        <i class="fas fa-stream mr-2 text-purple-600"></i>
                        Log de Atividade
                    </h3>
                    <div id="activityLog" class="bg-gray-900 rounded-lg p-4 h-40 overflow-y-auto font-mono text-xs">
                        <div class="text-green-400">[<?php echo date('H:i:s'); ?>] Sistema iniciado</div>
                        <div class="text-gray-400">[<?php echo date('H:i:s'); ?>] Aguardando processamento...</div>
                    </div>
                </div>
            </div>

            <!-- COLUNA DIREITA - Lista de Contatos em Tempo Real -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-users mr-2 text-purple-600"></i>
                        Fila de Disparo
                    </h3>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="flex items-center gap-1 text-green-600">
                            <i class="fas fa-check-circle"></i>
                            <span id="countSent"><?php echo $stats['sent']; ?></span>
                        </span>
                        <span class="text-gray-300">|</span>
                        <span class="flex items-center gap-1 text-yellow-600">
                            <i class="fas fa-clock"></i>
                            <span id="countPending"><?php echo $stats['pending']; ?></span>
                        </span>
                    </div>
                </div>
                
                <div id="queueList" class="space-y-2 overflow-y-auto" style="max-height: calc(100vh - 300px); min-height: 400px;">
                    <?php foreach ($queueItems as $item): ?>
                    <?php
                        $itemStatusColors = [
                            'pending' => 'bg-gray-50 border-gray-200',
                            'processing' => 'bg-blue-50 border-blue-300 animate-pulse',
                            'sent' => 'bg-green-50 border-green-300',
                            'failed' => 'bg-red-50 border-red-300',
                            'cancelled' => 'bg-gray-100 border-gray-300'
                        ];
                        $itemStatusBadges = [
                            'pending' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-200 text-gray-600"><i class="fas fa-clock mr-1"></i>Aguardando</span>',
                            'processing' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-200 text-blue-700"><i class="fas fa-spinner fa-spin mr-1"></i>Enviando...</span>',
                            'sent' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-200 text-green-700"><i class="fas fa-check mr-1"></i>Enviado</span>',
                            'failed' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-200 text-red-700"><i class="fas fa-times mr-1"></i>Falhou</span>',
                            'cancelled' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-300 text-gray-600"><i class="fas fa-ban mr-1"></i>Cancelado</span>'
                        ];
                    ?>
                    <div class="flex items-center justify-between p-3 rounded-lg border-2 transition-all duration-300 queue-item <?php echo $itemStatusColors[$item['status']] ?? 'bg-gray-50 border-gray-200'; ?>" 
                         data-id="<?php echo $item['id']; ?>" data-status="<?php echo $item['status']; ?>">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm
                                <?php echo $item['status'] === 'sent' ? 'bg-green-500 text-white' : ($item['status'] === 'failed' ? 'bg-red-500 text-white' : ($item['status'] === 'processing' ? 'bg-blue-500 text-white' : 'bg-purple-100 text-purple-600')); ?>">
                                <?php if ($item['status'] === 'sent'): ?>
                                    <i class="fas fa-check"></i>
                                <?php elseif ($item['status'] === 'failed'): ?>
                                    <i class="fas fa-times"></i>
                                <?php elseif ($item['status'] === 'processing'): ?>
                                    <i class="fas fa-paper-plane"></i>
                                <?php else: ?>
                                    <?php echo strtoupper(substr($item['contact_name'] ?: 'S', 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($item['contact_name'] ?: 'Sem nome'); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($item['contact_phone']); ?></p>
                            </div>
                        </div>
                        <?php echo $itemStatusBadges[$item['status']] ?? $itemStatusBadges['pending']; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.animate-shimmer {
    animation: shimmer 2s linear infinite;
}
</style>

<script>
const campaignId = <?php echo $campaign['id']; ?>;
let isRunning = <?php echo $campaign['status'] === 'running' ? 'true' : 'false'; ?>;
let pollInterval = null;
let processInterval = null;
let lastSentCount = <?php echo $stats['sent']; ?>;
let firstProcess = true;
let campaignFinished = false;
let countdownInterval = null;
let currentCountdown = 0;

// Funções de countdown do delay
function startCountdown(nextSendAt, delay, sent, total) {
    if (!nextSendAt) return;
    
    const targetTime = new Date(nextSendAt).getTime();
    const now = Date.now();
    let remaining = Math.max(0, Math.ceil((targetTime - now) / 1000));
    
    if (remaining <= 0) {
        document.getElementById('activityText').textContent = `Preparando envio... (${sent}/${total})`;
        return;
    }
    
    // Evitar reiniciar se já está rodando com o mesmo delay
    if (countdownInterval && currentCountdown === delay) return;
    
    stopCountdown();
    currentCountdown = delay;
    
    const updateDisplay = () => {
        const activityText = document.getElementById('activityText');
        if (remaining > 0) {
            activityText.innerHTML = `<span class="font-bold text-purple-700">⏱️ Próximo envio em ${remaining}s</span> <span class="text-xs opacity-75">(delay: ${delay}s)</span> (${sent}/${total})`;
            remaining--;
        } else {
            activityText.textContent = `Preparando envio... (${sent}/${total})`;
            stopCountdown();
        }
    };
    
    updateDisplay();
    countdownInterval = setInterval(updateDisplay, 1000);
}

function stopCountdown() {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
        currentCountdown = 0;
    }
}

// Adicionar log de atividade
function addLog(message, type = 'info') {
    const log = document.getElementById('activityLog');
    const time = new Date().toLocaleTimeString('pt-BR');
    const colors = {
        'success': 'text-green-400',
        'error': 'text-red-400',
        'info': 'text-gray-400',
        'warning': 'text-yellow-400',
        'process': 'text-blue-400'
    };
    const color = colors[type] || colors.info;
    log.innerHTML += `<div class="${color}">[${time}] ${message}</div>`;
    log.scrollTop = log.scrollHeight;
}

// Atualizar item na lista de contatos
function updateQueueItem(itemId, status, contactName) {
    const item = document.querySelector(`.queue-item[data-id="${itemId}"]`);
    if (!item) return;
    
    const statusClasses = {
        'pending': 'bg-gray-50 border-gray-200',
        'processing': 'bg-blue-50 border-blue-300 animate-pulse',
        'sent': 'bg-green-50 border-green-300',
        'failed': 'bg-red-50 border-red-300',
        'cancelled': 'bg-gray-100 border-gray-300'
    };
    
    const statusBadges = {
        'pending': '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-200 text-gray-600"><i class="fas fa-clock mr-1"></i>Aguardando</span>',
        'processing': '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-200 text-blue-700"><i class="fas fa-spinner fa-spin mr-1"></i>Enviando...</span>',
        'sent': '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-200 text-green-700"><i class="fas fa-check mr-1"></i>Enviado</span>',
        'failed': '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-200 text-red-700"><i class="fas fa-times mr-1"></i>Falhou</span>',
        'cancelled': '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-300 text-gray-600"><i class="fas fa-ban mr-1"></i>Cancelado</span>'
    };
    
    const avatarClasses = {
        'pending': 'bg-purple-100 text-purple-600',
        'processing': 'bg-blue-500 text-white',
        'sent': 'bg-green-500 text-white',
        'failed': 'bg-red-500 text-white',
        'cancelled': 'bg-gray-400 text-white'
    };
    
    const avatarIcons = {
        'processing': '<i class="fas fa-paper-plane"></i>',
        'sent': '<i class="fas fa-check"></i>',
        'failed': '<i class="fas fa-times"></i>',
        'cancelled': '<i class="fas fa-ban"></i>'
    };
    
    // Atualizar classes do item
    item.className = `flex items-center justify-between p-3 rounded-lg border-2 transition-all duration-300 queue-item ${statusClasses[status] || statusClasses.pending}`;
    item.dataset.status = status;
    
    // Atualizar avatar
    const avatar = item.querySelector('.w-10.h-10');
    if (avatar) {
        avatar.className = `w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm ${avatarClasses[status] || avatarClasses.pending}`;
        if (avatarIcons[status]) {
            avatar.innerHTML = avatarIcons[status];
        }
    }
    
    // Atualizar badge
    const badgeContainer = item.querySelector('span[class*="rounded-full"]');
    if (badgeContainer && badgeContainer.parentElement) {
        badgeContainer.outerHTML = statusBadges[status] || statusBadges.pending;
    }
    
    // Scroll para o item atualizado se for enviado
    if (status === 'sent' || status === 'processing') {
        item.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Buscar e atualizar lista de contatos
async function updateQueueList() {
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/queueStatus/${campaignId}`);
        const data = await response.json();
        
        if (data.success && data.items) {
            data.items.forEach(item => {
                updateQueueItem(item.id, item.status, item.contact_name);
            });
        }
    } catch (error) {
        console.error('Erro ao atualizar fila:', error);
    }
}

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
            document.getElementById('countSent').textContent = c.sent;
            document.getElementById('countPending').textContent = c.pending;
            
            // Atualizar progresso
            document.getElementById('progressPercent').textContent = c.progress + '%';
            document.getElementById('progressBar').style.width = c.progress + '%';
            
            // Esconder spinner quando começar a enviar
            if (c.sent > 0) {
                document.getElementById('processingSpinner').classList.add('hidden');
            }
            
            // Atualizar texto de atividade e countdown
            const activityText = document.getElementById('activityText');
            if (c.processing > 0) {
                activityText.textContent = `Enviando mensagem... (${c.sent}/${c.total})`;
                stopCountdown();
            } else if (c.pending > 0 && c.next_send_at) {
                startCountdown(c.next_send_at, c.next_delay, c.sent, c.total);
            } else if (c.pending > 0) {
                activityText.textContent = `Aguardando próximo envio... (${c.sent}/${c.total})`;
            }
            
            // Log quando houver novo envio
            if (c.sent > lastSentCount) {
                const diff = c.sent - lastSentCount;
                addLog(`${diff} mensagem(ns) enviada(s) com sucesso`, 'success');
                if (c.next_delay) {
                    addLog(`Próximo disparo em ${c.next_delay}s (delay sorteado: ${c.min_interval}-${c.max_interval}s)`, 'info');
                }
                lastSentCount = c.sent;
            }
            
            // Atualizar última atualização
            const now = new Date();
            document.getElementById('lastUpdated').textContent = 'Última atualização: ' + now.toLocaleTimeString('pt-BR');
            
            // Calcular tempo estimado
            if (c.pending > 0 && c.sent > 0) {
                const avgTime = 10;
                const remaining = c.pending * avgTime;
                const minutes = Math.ceil(remaining / 60);
                document.getElementById('estimatedTime').textContent = `Tempo estimado: ~${minutes} min`;
            } else if (c.pending === 0 && !campaignFinished) {
                document.getElementById('estimatedTime').textContent = 'Concluído!';
                document.getElementById('activityIndicator').classList.add('hidden');
                document.getElementById('processingSpinner').classList.add('hidden');
                addLog('Campanha concluída!', 'success');
                campaignFinished = true;
                stopPolling();
            }
            
            // Atualizar lista de contatos
            await updateQueueList();
            
            // Verificar se status mudou
            if (c.status !== '<?php echo $campaign['status']; ?>') {
                if (c.status === 'completed') {
                    addLog('Todos os envios foram processados!', 'success');
                } else if (c.status === 'paused') {
                    addLog('Campanha pausada', 'warning');
                }
                setTimeout(() => location.reload(), 1500);
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar status:', error);
        addLog('Erro de conexão', 'error');
    }
}

// Trigger de processamento (fallback se cron não funcionar)
async function triggerProcess() {
    if (!isRunning) return;
    
    try {
        if (firstProcess) {
            addLog('Iniciando processamento da fila...', 'process');
            firstProcess = false;
        }
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/process/${campaignId}`);
        const data = await response.json();
        
        if (data.success && data.processed > 0) {
            addLog(`Processado: ${data.processed} item(ns)`, 'process');
        }
    } catch (error) {
        console.error('Erro ao processar:', error);
    }
}

// Pausar campanha
async function pauseCampaign() {
    addLog('Pausando campanha...', 'warning');
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/pause/${campaignId}`, { method: 'POST' });
        const data = await response.json();
        
        if (data.success) {
            isRunning = false;
            addLog('Campanha pausada com sucesso', 'warning');
            setTimeout(() => location.reload(), 1000);
        } else {
            addLog('Erro ao pausar: ' + (data.message || 'Erro'), 'error');
        }
    } catch (error) {
        addLog('Erro ao pausar campanha', 'error');
    }
}

// Retomar campanha
async function resumeCampaign() {
    addLog('Retomando campanha...', 'process');
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/resume/${campaignId}`, { method: 'POST' });
        const data = await response.json();
        
        if (data.success) {
            isRunning = true;
            addLog('Campanha retomada com sucesso', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            addLog('Erro ao retomar: ' + (data.message || 'Erro'), 'error');
        }
    } catch (error) {
        addLog('Erro ao retomar campanha', 'error');
    }
}

// Cancelar campanha
async function cancelCampaign() {
    if (!confirm('⚠️ Tem certeza que deseja CANCELAR esta campanha?\n\nTodos os envios pendentes serão cancelados.')) {
        return;
    }
    
    addLog('Cancelando campanha...', 'warning');
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/campaign/cancel/${campaignId}`, { method: 'POST' });
        const data = await response.json();
        
        if (data.success) {
            isRunning = false;
            addLog('Campanha cancelada', 'error');
            setTimeout(() => location.reload(), 1000);
        } else {
            addLog('Erro ao cancelar: ' + (data.message || 'Erro'), 'error');
        }
    } catch (error) {
        addLog('Erro ao cancelar campanha', 'error');
    }
}

// Iniciar polling
function startPolling() {
    addLog('Monitoramento iniciado', 'info');
    
    // Primeira atualização
    updateStatus();
    
    // Polling a cada 2 segundos
    pollInterval = setInterval(updateStatus, 2000);
    
    // Trigger de processamento a cada 3 segundos (fallback do cron)
    if (isRunning) {
        // Primeiro processo imediato
        triggerProcess();
        processInterval = setInterval(triggerProcess, 3000);
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
