<?php 
/**
 * Gerenciamento de Instâncias Evolution API (Admin)
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 15:00:00
 */
$pageTitle = 'Gerenciar Instâncias - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-server mr-2 gradient-text"></i>
                Gerenciar Instâncias Evolution API
            </h1>
            <p class="mt-2 text-gray-600">Monitore e gerencie todas as instâncias WhatsApp dos usuários</p>
        </div>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <?php
            $total = count($instances);
            $connected = count(array_filter($instances, fn($i) => $i['evolution_status'] === 'open'));
            $disconnected = count(array_filter($instances, fn($i) => $i['evolution_status'] !== 'open' && !empty($i['evolution_status'])));
            $pending = $total - $connected - $disconnected;
            ?>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total de Instâncias</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $total; ?></p>
                    </div>
                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-server text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Conectadas</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $connected; ?></p>
                    </div>
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Desconectadas</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $disconnected; ?></p>
                    </div>
                    <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times-circle text-2xl text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Pendentes</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo $pending; ?></p>
                    </div>
                    <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Instâncias -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <?php if (empty($instances)): ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-server text-6xl mb-4 text-gray-300"></i>
                <p class="text-xl font-medium">Nenhuma instância criada ainda</p>
                <p class="text-sm mt-2">As instâncias aparecerão aqui quando os usuários criarem suas conexões WhatsApp</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instância</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criada em</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($instances as $instance): ?>
                        <tr class="hover:bg-gray-50 transition" id="instance-<?php echo $instance['id']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-semibold">
                                        <?php echo strtoupper(substr($instance['name'], 0, 1)); ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($instance['name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($instance['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono">
                                    <?php echo htmlspecialchars($instance['evolution_instance']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if (!empty($instance['evolution_phone_number'])): ?>
                                        <i class="fab fa-whatsapp text-green-600 mr-1"></i>
                                        <?php echo htmlspecialchars($instance['evolution_phone_number']); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="status-badge-<?php echo $instance['id']; ?>">
                                    <?php
                                    $status = $instance['evolution_status'] ?? 'unknown';
                                    $statusConfig = [
                                        'open' => ['text' => 'Conectado', 'class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check-circle'],
                                        'close' => ['text' => 'Desconectado', 'class' => 'bg-red-100 text-red-800', 'icon' => 'fa-times-circle'],
                                        'connecting' => ['text' => 'Conectando', 'class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fa-spinner fa-spin'],
                                        'unknown' => ['text' => 'Desconhecido', 'class' => 'bg-gray-100 text-gray-800', 'icon' => 'fa-question-circle']
                                    ];
                                    $config = $statusConfig[$status] ?? $statusConfig['unknown'];
                                    ?>
                                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full <?php echo $config['class']; ?>">
                                        <i class="fas <?php echo $config['icon']; ?> mr-1"></i>
                                        <?php echo $config['text']; ?>
                                    </span>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo $instance['evolution_created_at'] ? date('d/m/Y H:i', strtotime($instance['evolution_created_at'])) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <button onclick="checkStatus(<?php echo $instance['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900"
                                            title="Verificar status">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button onclick="showQRCode(<?php echo $instance['id']; ?>)" 
                                            class="text-green-600 hover:text-green-900"
                                            title="Gerar QR Code">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <button onclick="disconnectInstance(<?php echo $instance['id']; ?>)" 
                                            class="text-yellow-600 hover:text-yellow-900"
                                            title="Desconectar">
                                        <i class="fas fa-unlink"></i>
                                    </button>
                                    <button onclick="restartInstance(<?php echo $instance['id']; ?>)" 
                                            class="text-purple-600 hover:text-purple-900"
                                            title="Reiniciar">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    <button onclick="deleteInstance(<?php echo $instance['id']; ?>)" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Deletar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
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

<!-- Modal QR Code -->
<div id="qrcodeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="fas fa-qrcode mr-2 text-green-600"></i>
                QR Code WhatsApp
            </h3>
            <button onclick="closeQRModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="qrcodeContent" class="text-center">
            <div class="animate-pulse">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Gerando QR Code...</p>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;

async function checkStatus(userId) {
    try {
        showNotification('Verificando status...', 'info');
        
        const response = await fetch('<?php echo APP_URL; ?>/instances/getStatus', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + userId
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateStatusBadge(userId, data.status);
            showNotification('Status: ' + data.status, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erro ao verificar status', 'error');
    }
}

function updateStatusBadge(userId, status) {
    const statusConfig = {
        'open': {text: 'Conectado', class: 'bg-green-100 text-green-800', icon: 'fa-check-circle'},
        'close': {text: 'Desconectado', class: 'bg-red-100 text-red-800', icon: 'fa-times-circle'},
        'connecting': {text: 'Conectando', class: 'bg-yellow-100 text-yellow-800', icon: 'fa-spinner fa-spin'}
    };
    
    const config = statusConfig[status] || statusConfig['close'];
    const badge = document.querySelector('.status-badge-' + userId);
    
    if (badge) {
        badge.innerHTML = `
            <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full ${config.class}">
                <i class="fas ${config.icon} mr-1"></i>
                ${config.text}
            </span>
        `;
    }
}

async function showQRCode(userId) {
    currentUserId = userId;
    document.getElementById('qrcodeModal').classList.remove('hidden');
    document.getElementById('qrcodeContent').innerHTML = `
        <div class="animate-pulse">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Gerando QR Code...</p>
        </div>
    `;
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/instances/generateQRCode', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + userId
        });
        
        const data = await response.json();
        
        if (data.success && data.qrcode) {
            document.getElementById('qrcodeContent').innerHTML = `
                <img src="${data.qrcode}" alt="QR Code" class="mx-auto mb-4 rounded-lg shadow-lg">
                <p class="text-sm text-gray-600 mb-4">Escaneie com WhatsApp</p>
                <button onclick="showQRCode(${userId})" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-sync-alt mr-1"></i>
                    Atualizar QR Code
                </button>
            `;
        } else {
            document.getElementById('qrcodeContent').innerHTML = `
                <i class="fas fa-exclamation-triangle text-4xl text-yellow-600 mb-4"></i>
                <p class="text-gray-600">${data.message || 'Erro ao gerar QR Code'}</p>
                <button onclick="showQRCode(${userId})" class="mt-4 text-blue-600 hover:text-blue-800">
                    <i class="fas fa-redo mr-1"></i>
                    Tentar novamente
                </button>
            `;
        }
    } catch (error) {
        document.getElementById('qrcodeContent').innerHTML = `
            <i class="fas fa-times-circle text-4xl text-red-600 mb-4"></i>
            <p class="text-gray-600">Erro ao gerar QR Code</p>
        `;
    }
}

function closeQRModal() {
    document.getElementById('qrcodeModal').classList.add('hidden');
}

async function disconnectInstance(userId) {
    if (!confirm('Deseja desconectar esta instância?')) return;
    
    try {
        showNotification('Desconectando...', 'info');
        
        const response = await fetch('<?php echo APP_URL; ?>/instances/disconnect', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + userId
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Instância desconectada!', 'success');
            updateStatusBadge(userId, 'close');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erro ao desconectar', 'error');
    }
}

async function restartInstance(userId) {
    if (!confirm('Deseja reiniciar esta instância?')) return;
    
    try {
        showNotification('Reiniciando...', 'info');
        
        const response = await fetch('<?php echo APP_URL; ?>/instances/restart', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + userId
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Instância reiniciada!', 'success');
            setTimeout(() => checkStatus(userId), 2000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erro ao reiniciar', 'error');
    }
}

async function deleteInstance(userId) {
    if (!confirm('⚠️ ATENÇÃO: Isso irá deletar permanentemente a instância!\n\nTem certeza?')) return;
    
    try {
        showNotification('Deletando...', 'info');
        
        const response = await fetch('<?php echo APP_URL; ?>/instances/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + userId
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Instância deletada!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erro ao deletar', 'error');
    }
}

// Fechar modal ao clicar fora
document.getElementById('qrcodeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQRModal();
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>
