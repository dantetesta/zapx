<?php 
$pageTitle = 'Conectar WhatsApp - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 pb-12" style="position: relative; z-index: 1;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto w-20 h-20 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center mb-4 shadow-lg">
                <i class="fab fa-whatsapp text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Conectar WhatsApp
            </h1>
            <p class="text-gray-600">Gerencie sua instância WhatsApp para disparo de mensagens</p>
        </div>

        <?php if (!$hasInstance): ?>
        
        <!-- Notificações (Canto inferior direito) -->
        <div id="notification" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 2147483647; max-width: 24rem; min-width: 300px; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            <div style="display: flex; align-items: center;">
                <i id="notificationIcon" style="margin-right: 0.75rem; font-size: 1.25rem;"></i>
                <p id="notificationMessage" style="font-weight: 500; flex: 1;"></p>
                <button onclick="document.getElementById('notification').style.display='none'" style="margin-left: 0.75rem; color: #9ca3af; cursor: pointer; background: none; border: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <!-- CRIAR NOVA INSTÂNCIA -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-1">
                    <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                    Crie sua Instância WhatsApp
                </h2>
                <p class="text-sm text-gray-600">Configure sua conexão para começar a enviar mensagens</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Coluna 1: Formulário -->
                <div>
                    <form id="formCriarInstancia" class="space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <!-- DDI -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    <i class="fas fa-globe mr-1"></i>
                                    DDI *
                                </label>
                                <input type="number" name="ddi" value="55" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                                       placeholder="55"
                                       min="1"
                                       max="999">
                            </div>
                            
                            <!-- Número -->
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    <i class="fas fa-phone mr-1"></i>
                                    Número do WhatsApp *
                                </label>
                                <input type="tel" name="phone_number" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                                       placeholder="11999999999"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       maxlength="11">
                            </div>
                        </div>
                        
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Exemplo: DDI <strong>55</strong> + Número <strong>11999999999</strong>
                        </p>

                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 rounded-lg hover:shadow-lg transform hover:scale-[1.02] transition-all duration-200 text-sm">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Criar Instância
                        </button>
                    </form>
                </div>

                <!-- Coluna 2: Instruções -->
                <div class="space-y-4">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                        <p class="text-xs font-semibold text-blue-900 mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Como funciona:
                        </p>
                        <ol class="list-decimal list-inside space-y-1 text-xs text-blue-800">
                            <li>Informe DDI e número do WhatsApp</li>
                            <li>Sistema cria sua instância automaticamente</li>
                            <li>Token de acesso é gerado e salvo</li>
                            <li>Escaneie o QR Code com seu celular</li>
                            <li>Pronto! Sua instância estará conectada</li>
                        </ol>
                    </div>

                    <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded">
                        <p class="text-xs font-semibold text-green-900 mb-1">
                            <i class="fas fa-magic mr-1"></i>
                            Nome gerado automaticamente
                        </p>
                        <p class="text-xs text-green-800">
                            Criaremos um nome único para sua instância automaticamente.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- GERENCIAR INSTÂNCIA EXISTENTE -->
        <div class="space-y-6">
            
            <!-- Informações da Instância -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-mobile-alt text-green-600 mr-2"></i>
                            Minha Instância WhatsApp
                        </h2>
                        <p class="text-gray-600 text-sm">Gerencie sua conexão ativa</p>
                    </div>
                    <button onclick="refreshStatus()" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-sync-alt" id="refreshIcon"></i>
                    </button>
                </div>

                <!-- Layout Mobile First - Responsivo -->
                <div class="space-y-4 mb-6">
                    <!-- Instância - Layout especial para nomes longos -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Instância</p>
                        <div class="bg-white rounded border p-3">
                            <p class="text-xs font-mono text-gray-700 break-all leading-relaxed">
                                <?php echo htmlspecialchars($instanceData['name']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Telefone e Status - Grid responsivo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Telefone</p>
                            <p class="text-sm font-semibold text-gray-900">+<?php echo htmlspecialchars($instanceData['phone']); ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</p>
                            <div class="flex items-center">
                                <div id="statusDot" class="w-3 h-3 rounded-full bg-gray-400 mr-2 flex-shrink-0"></div>
                                <span id="statusText" class="text-sm font-semibold text-gray-600">Verificando...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações - Layout Mobile First -->
                <div id="actionsContainer" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button id="btnConectar" onclick="mostrarQRCode()" 
                            class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-colors font-medium">
                        <i class="fas fa-qrcode mr-2"></i>
                        Ver QR Code
                    </button>
                    <button id="btnDesconectar" onclick="desconectar()" 
                            class="w-full bg-yellow-600 text-white px-4 py-3 rounded-lg hover:bg-yellow-700 transition-colors font-medium">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Desconectar
                    </button>
                    <button id="btnDeletar" onclick="deletarInstancia()" 
                            class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 transition-colors font-medium">
                        <i class="fas fa-trash mr-2"></i>
                        Deletar
                    </button>
                </div>
            </div>

            <!-- QR Code -->
            <div id="qrcodeSection" class="bg-white rounded-xl shadow-lg p-6 hidden">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-qrcode text-blue-600 mr-2"></i>
                    QR Code para Conexão
                </h3>
                
                <div class="text-center">
                    <div id="qrcodeContainer" class="bg-gray-50 rounded-lg p-6 mb-4 inline-block">
                        <img id="qrcodeImg" src="" alt="QR Code" class="max-w-xs w-full h-auto">
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Como conectar:</strong><br>
                            1. Abra o WhatsApp no seu celular<br>
                            2. Vá em <strong>Configurações > Aparelhos conectados</strong><br>
                            3. Toque em <strong>"Conectar um aparelho"</strong><br>
                            4. Escaneie este QR Code
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
console.log('🚀 WhatsApp Manager carregado');

// Variáveis globais
let statusInterval;

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM carregado');
    
    <?php if ($hasInstance): ?>
    // Se tem instância, verificar status
    verificarStatus();
    statusInterval = setInterval(verificarStatus, 10000); // A cada 10 segundos
    <?php endif; ?>
    
    // Configurar formulário de criação
    const form = document.getElementById('formCriarInstancia');
    if (form) {
        form.addEventListener('submit', criarInstancia);
    }
});


// Criar instância
async function criarInstancia(e) {
    e.preventDefault();
    console.log('📝 Criando instância...');
    
    const formData = new FormData(e.target);
    const button = e.target.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    // Loading bonito
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Criando instância...';
    button.disabled = true;
    
    // Mostrar loading overlay
    const loading = document.createElement('div');
    loading.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loading.innerHTML = `
        <div class="bg-white rounded-xl p-8 text-center max-w-sm">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-green-600 mx-auto mb-4"></div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Criando instância...</h3>
            <p class="text-sm text-gray-600">Aguarde, conectando com Evolution API</p>
        </div>
    `;
    document.body.appendChild(loading);
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/whatsapp/createInstance', {
            method: 'POST',
            body: formData,
            cache: 'no-store',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        console.log('📊 Resultado:', data);
        
        // Remover loading
        document.body.removeChild(loading);
        
        if (data.success) {
            // Sucesso! Recarregar página para mostrar QR Code
            showNotification('Instância criada com sucesso! Redirecionando...', 'success');
            setTimeout(() => {
                window.location.href = window.location.pathname + '?v=' + Date.now();
            }, 1500);
        } else {
            showNotification(data.message, 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    } catch (error) {
        console.error('❌ Erro:', error);
        document.body.removeChild(loading);
        showNotification('Erro ao criar instância: ' + error.message, 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Gerar QR Code
async function gerarQRCode() {
    console.log('🔄 Gerando QR Code...');
    
    // 🔄 PRELOADER: Mostrar loading no botão
    const btnConectar = document.getElementById('btnConectar');
    const originalText = btnConectar.innerHTML;
    btnConectar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando QR...';
    btnConectar.disabled = true;
    
    const img = document.getElementById('qrcodeImg');
    
    // Mostrar placeholder de loading na imagem
    img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2Y5ZmFmYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNDUlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2YjczODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiPkdlcmFuZG8gUVIgQ29kZS4uLjwvdGV4dD48Y2lyY2xlIGN4PSI1MCUiIGN5PSI2MCUiIHI9IjIwIiBmaWxsPSJub25lIiBzdHJva2U9IiMzYjgyZjYiIHN0cm9rZS13aWR0aD0iMiI+PGFuaW1hdGVUcmFuc2Zvcm0gYXR0cmlidXRlTmFtZT0idHJhbnNmb3JtIiB0eXBlPSJyb3RhdGUiIGZyb209IjAgMTUwIDE4MCIgdG89IjM2MCAxNTAgMTgwIiBkdXI9IjFzIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIvPjwvY2lyY2xlPjwvc3ZnPg==';
    img.alt = 'Gerando QR Code...';
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/whatsapp/getQRCode?_=' + Date.now(), {
            cache: 'no-store'
        });
        const data = await response.json();
        console.log('📊 QR:', data);
        
        if (data.success) {
            img.src = data.qrcode;
            img.alt = 'QR Code WhatsApp';
            
            // ✅ Sucesso: Restaurar botão
            btnConectar.innerHTML = '<i class="fas fa-check mr-2"></i>QR Gerado!';
            btnConectar.className = 'w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-colors font-medium';
            
            showNotification('📱 QR Code gerado! Escaneie com seu WhatsApp.', 'success');
            
            // Restaurar botão após 3 segundos
            setTimeout(() => {
                btnConectar.innerHTML = originalText;
                btnConectar.className = 'w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-colors font-medium';
                btnConectar.disabled = false;
            }, 3000);
            
        } else {
            img.src = '';
            img.alt = 'QR Code não disponível';
            
            // ❌ Erro: Restaurar botão
            btnConectar.innerHTML = originalText;
            btnConectar.disabled = false;
            
            showNotification(data.message || 'Erro ao gerar QR Code', 'error');
            console.log('Debug QR:', data.debug);
        }
    } catch (e) {
        console.error('❌ Erro QR:', e);
        img.src = '';
        
        // ❌ Erro: Restaurar botão
        btnConectar.innerHTML = originalText;
        btnConectar.disabled = false;
        
        showNotification('Erro ao gerar QR Code: ' + e.message, 'error');
    }
}

// Verificar status
async function verificarStatus() {
    console.log('🔍 Verificando status...');
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/whatsapp/checkStatus?_=' + Date.now());
        const data = await response.json();
        console.log('📊 Status:', data);
        
        if (data.success) {
            const dot = document.getElementById('statusDot');
            const text = document.getElementById('statusText');
            const btnConectar = document.getElementById('btnConectar');
            const wasConnecting = text.textContent === 'Conectando...';
            
            if (data.connected) {
                dot.className = 'w-3 h-3 rounded-full bg-green-500 animate-pulse mr-2 flex-shrink-0';
                text.textContent = 'Conectado';
                text.className = 'text-sm font-semibold text-green-600';
                
                // 🔒 OCULTAR BOTÃO QR CODE: WhatsApp já conectado
                if (btnConectar) {
                    btnConectar.style.display = 'none';
                    console.log('🔒 Botão QR Code ocultado - WhatsApp conectado');
                    
                    // Ajustar layout para 2 colunas quando QR Code estiver oculto
                    const actionsContainer = document.getElementById('actionsContainer');
                    if (actionsContainer) {
                        actionsContainer.className = 'grid grid-cols-1 sm:grid-cols-2 gap-3';
                    }
                }
                
                // Esconder seção QR Code se estiver visível
                const qrSection = document.getElementById('qrcodeSection');
                if (qrSection && !qrSection.classList.contains('hidden')) {
                    qrSection.classList.add('hidden');
                }
                
                // 🚀 AUTO-RELOAD: Se estava conectando e agora conectou
                if (wasConnecting) {
                    console.log('🎉 WhatsApp conectado! Recarregando página...');
                    showNotification('🎉 WhatsApp conectado com sucesso!', 'success');
                    
                    // Reload após 2 segundos para mostrar a notificação
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
                
            } else if (data.status === 'connecting') {
                dot.className = 'w-3 h-3 rounded-full bg-yellow-500 animate-pulse mr-2 flex-shrink-0';
                text.textContent = 'Conectando...';
                text.className = 'text-sm font-semibold text-yellow-600';
                
                // 📱 MOSTRAR BOTÃO QR CODE: Pode precisar gerar QR
                if (btnConectar) {
                    btnConectar.style.display = 'block';
                    
                    // Restaurar layout para 3 colunas
                    const actionsContainer = document.getElementById('actionsContainer');
                    if (actionsContainer) {
                        actionsContainer.className = 'grid grid-cols-1 sm:grid-cols-3 gap-3';
                    }
                }
                
            } else {
                dot.className = 'w-3 h-3 rounded-full bg-red-500 mr-2 flex-shrink-0';
                text.textContent = 'Desconectado';
                text.className = 'text-sm font-semibold text-red-600';
                
                // 📱 MOSTRAR BOTÃO QR CODE: Precisa conectar
                if (btnConectar) {
                    btnConectar.style.display = 'block';
                    
                    // Restaurar layout para 3 colunas
                    const actionsContainer = document.getElementById('actionsContainer');
                    if (actionsContainer) {
                        actionsContainer.className = 'grid grid-cols-1 sm:grid-cols-3 gap-3';
                    }
                }
            }
        }
    } catch (error) {
        console.error('❌ Erro status:', error);
    }
}

// Atualizar status manualmente
function refreshStatus() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('fa-spin');
    
    verificarStatus().finally(() => {
        setTimeout(() => {
            icon.classList.remove('fa-spin');
        }, 1000);
    });
}

// Mostrar QR Code
function mostrarQRCode() {
    console.log('📱 Mostrando QR Code...');
    document.getElementById('qrcodeSection').classList.remove('hidden');
    gerarQRCode();
}

// Gerar QR Code
async function gerarQRCode() {
    console.log('🔄 Gerando QR Code...');
    const img = document.getElementById('qrcodeImg');
    
    // Mostrar loading
    img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9ImEiIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0b3AtY29sb3I9IiNmM2Y0ZjYiLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNlNWU3ZWIiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0idXJsKCNhKSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2YjczODAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5DYXJyZWdhbmRvLi4uPC90ZXh0Pjwvc3ZnPg==';
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/whatsapp/getQRCode?_=' + Date.now());
        const data = await response.json();
        console.log('📊 QR:', data);
        
        if (data.success) {
            img.src = data.qrcode;
            showNotification('QR Code gerado! Escaneie com seu WhatsApp.', 'success');
        } else {
            img.src = '';
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('❌ Erro QR:', error);
        showNotification('Erro ao gerar QR Code: ' + error.message, 'error');
    }
}

// Desconectar
async function desconectar() {
    if (!confirm('Deseja desconectar sua instância WhatsApp?')) return;
    
    console.log('🔌 Desconectando...');
    
    // 🔄 PRELOADER: Mostrar loading no botão
    const btnDesconectar = document.getElementById('btnDesconectar');
    const originalText = btnDesconectar.innerHTML;
    btnDesconectar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Desconectando...';
    btnDesconectar.disabled = true;
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/whatsapp/disconnect', {
            method: 'POST'
        });
        
        const data = await response.json();
        console.log('📊 Desconectar:', data);
        
        if (data.success) {
            // ✅ Sucesso: Mostrar feedback visual
            btnDesconectar.innerHTML = '<i class="fas fa-check mr-2"></i>Desconectado!';
            btnDesconectar.className = 'w-full bg-green-600 text-white px-4 py-3 rounded-lg font-medium';
            
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            // ❌ Erro: Restaurar botão
            btnDesconectar.innerHTML = originalText;
            btnDesconectar.disabled = false;
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('❌ Erro:', error);
        // ❌ Erro: Restaurar botão
        btnDesconectar.innerHTML = originalText;
        btnDesconectar.disabled = false;
        showNotification('Erro ao desconectar: ' + error.message, 'error');
    }
}

// Deletar instância
async function deletarInstancia() {
    if (!confirm('⚠️ ATENÇÃO!\n\nIsto irá deletar permanentemente sua instância WhatsApp.\nVocê terá que criar uma nova instância para conectar novamente.\n\nDeseja continuar?')) return;
    
    console.log('🗑️ Deletando instância...');
    
    // 🔄 PRELOADER: Mostrar loading no botão
    const btnDeletar = document.getElementById('btnDeletar');
    const originalText = btnDeletar.innerHTML;
    btnDeletar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deletando...';
    btnDeletar.disabled = true;
    
    // Desabilitar outros botões durante a operação
    const btnDesconectar = document.getElementById('btnDesconectar');
    const btnConectar = document.getElementById('btnConectar');
    btnDesconectar.disabled = true;
    btnConectar.disabled = true;
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/whatsapp/deleteInstance', {
            method: 'POST'
        });
        
        const data = await response.json();
        console.log('📊 Deletar:', data);
        
        if (data.success) {
            // ✅ Sucesso: Mostrar feedback visual
            btnDeletar.innerHTML = '<i class="fas fa-check mr-2"></i>Deletado!';
            btnDeletar.className = 'w-full bg-green-600 text-white px-4 py-3 rounded-lg font-medium';
            
            showNotification('🗑️ ' + data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            // ❌ Erro: Restaurar botões
            btnDeletar.innerHTML = originalText;
            btnDeletar.disabled = false;
            btnDesconectar.disabled = false;
            btnConectar.disabled = false;
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('❌ Erro:', error);
        // ❌ Erro: Restaurar botões
        btnDeletar.innerHTML = originalText;
        btnDeletar.disabled = false;
        btnDesconectar.disabled = false;
        btnConectar.disabled = false;
        showNotification('Erro ao deletar instância: ' + error.message, 'error');
    }
}

// Mostrar modal com informações da instância criada
function mostrarModalInstanciaCriada(instance) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 transform transition-all">
            <div class="text-center mb-4">
                <div class="mx-auto w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-1">
                    ✅ Instância Criada!
                </h2>
                <p class="text-xs text-gray-600">Informações salvas automaticamente</p>
            </div>
            
            <div class="space-y-3 mb-4">
                <!-- Nome da Instância -->
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        <i class="fas fa-server mr-1 text-blue-600"></i>
                        Nome da Instância
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" value="${instance.name}" readonly 
                               class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 font-mono text-xs">
                        <button onclick="copiarTexto('${instance.name}', this)" 
                                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-xs">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Token da Instância -->
                ${instance.token ? `
                <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-300">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        <i class="fas fa-key mr-1 text-yellow-600"></i>
                        Token (salvo automaticamente)
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" value="${instance.token}" readonly 
                               class="flex-1 px-3 py-2 bg-white border border-yellow-300 rounded-lg text-gray-900 font-mono text-xs">
                        <button onclick="copiarTexto('${instance.token}', this)" 
                                class="px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-xs">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                ` : ''}
                
                <!-- Telefone -->
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        <i class="fas fa-phone mr-1 text-green-600"></i>
                        Número do WhatsApp
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" value="${instance.phone}" readonly 
                               class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 font-mono text-xs">
                        <button onclick="copiarTexto('${instance.phone}', this)" 
                                class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 rounded-lg p-3 mb-4 border border-blue-200">
                <p class="text-xs text-blue-800">
                    <i class="fas fa-arrow-right mr-1"></i>
                    <strong>Próximo:</strong> Gere o QR Code e escaneie com seu WhatsApp
                </p>
            </div>
            
            <button onclick="fecharModalEAbrirQRCode()" 
                    class="w-full px-6 py-3 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg hover:opacity-90 transition font-semibold text-sm">
                <i class="fas fa-qrcode mr-2"></i>
                Gerar QR Code Agora
            </button>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Copiar texto para clipboard
function copiarTexto(texto, button) {
    navigator.clipboard.writeText(texto).then(() => {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.add('bg-green-600');
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('bg-green-600');
        }, 2000);
        
        showNotification('Copiado para área de transferência!', 'success');
    }).catch(err => {
        showNotification('Erro ao copiar: ' + err, 'error');
    });
}

// Baixar informações em arquivo TXT
function baixarInformacoes(instance) {
    const conteudo = `
═══════════════════════════════════════════════════════
    ZAPX - Informações da Instância WhatsApp
═══════════════════════════════════════════════════════

📱 DADOS DA INSTÂNCIA
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Nome da Instância:
${instance.name}

${instance.token ? `Token da Instância (API Key):
${instance.token}` : 'Token: Será gerado ao conectar o WhatsApp'}

Número do WhatsApp:
${instance.phone}

Data de Criação:
${new Date().toLocaleString('pt-BR')}

═══════════════════════════════════════════════════════
⚠️  IMPORTANTE - MANTENHA ESSAS INFORMAÇÕES SEGURAS
═══════════════════════════════════════════════════════

• O token é único e permite acesso à sua instância
• Não compartilhe essas informações com terceiros
• Guarde este arquivo em local seguro
• O token já está salvo no sistema ZAPX

═══════════════════════════════════════════════════════
    Gerado por: ZAPX - Disparo em Massa WhatsApp
    https://zap.dantetesta.com.br
═══════════════════════════════════════════════════════
`;
    
    const blob = new Blob([conteudo], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `zapx_instancia_${instance.name}_${Date.now()}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showNotification('Arquivo baixado com sucesso!', 'success');
}

// Fechar modal e recarregar página
function fecharModalERecarregar() {
    // Reload forçado com timestamp para garantir sem cache
    window.location.href = window.location.pathname + '?v=' + Date.now();
}

// Continuar para QR Code
function continuarParaQRCode() {
    // Recarregar e abrir QR Code automaticamente
    window.location.href = window.location.pathname + '?openQRCode=1&v=' + Date.now();
}

// Fechar modal e abrir QR Code diretamente
function fecharModalEAbrirQRCode() {
    // Fechar modal
    const modals = document.querySelectorAll('.fixed.inset-0');
    modals.forEach(modal => modal.remove());
    
    // Recarregar página para atualizar estado
    window.location.href = window.location.pathname + '?openQRCode=1&v=' + Date.now();
}

// Cleanup ao sair da página
window.addEventListener('beforeunload', function() {
    if (statusInterval) {
        clearInterval(statusInterval);
    }
});

// Auto-abrir QR Code se vier da criação da instância ou se status = created
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const autoOpenFromURL = urlParams.get('openQRCode') === '1';
    const autoOpenFromPHP = <?php echo $autoOpenQRCode ? 'true' : 'false'; ?>;
    
    console.log('🔍 Auto-open check:', {
        autoOpenFromURL,
        autoOpenFromPHP,
        shouldOpen: autoOpenFromURL || autoOpenFromPHP
    });
    
    if (autoOpenFromURL || autoOpenFromPHP) {
        console.log('✅ Vai abrir QR Code automaticamente em 800ms...');
        // Aguardar um pouco para garantir que a página carregou
        setTimeout(() => {
            console.log('🎯 Tentando abrir QR Code agora...');
            // Tentar ambas as funções
            if (typeof mostrarQRCode === 'function') {
                console.log('✅ Chamando mostrarQRCode()');
                mostrarQRCode();
            } else if (typeof gerarQRCode === 'function') {
                console.log('✅ Chamando gerarQRCode()');
                gerarQRCode();
            } else {
                console.error('❌ Nenhuma função de QR Code encontrada!');
            }
        }, 800);
    } else {
        console.log('⏭️ Não vai abrir QR Code automaticamente');
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>
