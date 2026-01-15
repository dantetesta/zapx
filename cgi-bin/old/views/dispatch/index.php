<?php 
$pageTitle = 'Disparo em Massa - ' . APP_NAME;

// Forçar reload sem cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-paper-plane mr-2 gradient-text"></i>
                Disparo em Massa
            </h1>
            <p class="mt-2 text-gray-600">Envie mensagens para seus contatos via WhatsApp</p>
        </div>

        <!-- Layout: Painel único centralizado -->
        <div class="max-w-4xl mx-auto">
            <div class="space-y-6">
                <!-- Seleção de Contatos -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-users mr-2 text-purple-600"></i>
                        Selecionar Contatos
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Tipo de Disparo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Disparo</label>
                            <select id="dispatchType" onchange="updateContactSelection()" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="all">Todos os Contatos</option>
                                <option value="tag">Por Categoria/Tag</option>
                                <option value="individual">Contato Individual</option>
                            </select>
                        </div>

                        <!-- Seleção de Tag -->
                        <div id="tagSelection" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Selecione a Tag</label>
                            <select id="selectedTag" onchange="loadContactsByTag()" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Selecione uma tag...</option>
                                <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag['id']; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?> (<?php echo $tag['contact_count']; ?> contatos)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Seleção Individual -->
                        <div id="individualSelection" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Selecione o Contato</label>
                            <select id="selectedContact" onchange="loadIndividualContact()" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Selecione um contato...</option>
                                <?php foreach ($contacts as $contact): ?>
                                <option value="<?php echo $contact['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($contact['name'] ?: 'Sem nome'); ?>"
                                        data-phone="<?php echo htmlspecialchars($contact['phone']); ?>">
                                    <?php echo htmlspecialchars($contact['name'] ?: 'Sem nome'); ?> - <?php echo htmlspecialchars($contact['phone']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Contador de Contatos -->
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-purple-900">Contatos Selecionados:</span>
                                <span id="contactCount" class="text-2xl font-bold text-purple-600">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Editor de Mensagem -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-comment-dots mr-2 text-green-600"></i>
                        Mensagem e Mídia
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Tipo de Mensagem -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Mensagem</label>
                            <select id="messageType" onchange="updateMessageType()" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="text">📝 Apenas Texto</option>
                                <option value="image">🖼️ Imagem + Texto</option>
                                <option value="video">🎥 Vídeo + Texto</option>
                                <option value="audio">🎵 Áudio</option>
                                <option value="document">📄 Documento + Texto</option>
                            </select>
                        </div>

                        <!-- Upload de Mídia -->
                        <div id="mediaUpload" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <span id="mediaLabel">Selecionar Arquivo</span>
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-green-500 transition-colors">
                                <input type="file" id="mediaFile" accept="" class="hidden" onchange="handleFileSelect(event)">
                                <div id="uploadArea" onclick="document.getElementById('mediaFile').click()" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Clique para selecionar ou arraste o arquivo aqui</p>
                                    <p id="fileTypes" class="text-xs text-gray-500 mt-1"></p>
                                </div>
                                <div id="filePreview" class="hidden">
                                    <!-- Preview de Imagem -->
                                    <div id="imagePreview" class="hidden relative">
                                        <img id="imagePreviewImg" src="" alt="Preview" class="w-full h-48 object-cover rounded-lg">
                                        <button onclick="removeFile()" class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-700 shadow-lg">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="mt-2 text-sm text-gray-600">
                                            <p id="imageFileName" class="font-medium"></p>
                                            <p id="imageFileSize" class="text-xs"></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Preview de Vídeo -->
                                    <div id="videoPreview" class="hidden relative">
                                        <video id="videoPreviewPlayer" controls class="w-full h-48 rounded-lg bg-black">
                                            <source id="videoPreviewSource" src="" type="">
                                        </video>
                                        <button onclick="removeFile()" class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-700 shadow-lg">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="mt-2 text-sm text-gray-600">
                                            <p id="videoFileName" class="font-medium"></p>
                                            <p id="videoFileSize" class="text-xs"></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Preview de Áudio -->
                                    <div id="audioPreview" class="hidden">
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center mb-3">
                                                <i class="fas fa-music text-3xl text-purple-600 mr-3"></i>
                                                <div class="flex-1">
                                                    <p id="audioFileName" class="font-medium text-gray-900"></p>
                                                    <p id="audioFileSize" class="text-xs text-gray-500"></p>
                                                </div>
                                                <button onclick="removeFile()" class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <audio id="audioPreviewPlayer" controls class="w-full">
                                                <source id="audioPreviewSource" src="" type="">
                                            </audio>
                                        </div>
                                    </div>
                                    
                                    <!-- Preview de Documento -->
                                    <div id="documentPreview" class="hidden">
                                        <div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-file-pdf text-3xl text-red-600 mr-3"></i>
                                                <div>
                                                    <p id="documentFileName" class="font-medium text-gray-900"></p>
                                                    <p id="documentFileSize" class="text-xs text-gray-500"></p>
                                                </div>
                                            </div>
                                            <button onclick="removeFile()" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campo de Texto MAIOR -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-edit mr-1"></i>
                                Mensagem
                            </label>
                            <textarea id="messageText" 
                                      placeholder="Digite sua mensagem aqui...\n\nDica: Use {nome} para personalizar"
                                      rows="10"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none text-base"
                                      oninput="updateMessagePreview()"></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Caracteres: <span id="charCount">0</span>
                            </p>
                        </div>

                        <!-- Preview WhatsApp Realista -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fab fa-whatsapp mr-1 text-green-600"></i>
                                Preview da Mensagem
                            </label>
                            <div class="bg-[#ECE5DD] rounded-lg p-6 border border-gray-300" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0icGF0dGVybiIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgd2lkdGg9IjEwMCIgaGVpZ2h0PSIxMDAiPjxwYXRoIGQ9Ik0wIDUwIEwgNTAgMCBMIDEwMCA1MCBMIDUwIDEwMCBaIiBmaWxsPSJub25lIiBzdHJva2U9IiNkZGRkZGQiIHN0cm9rZS13aWR0aD0iMC41IiBvcGFjaXR5PSIwLjEiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjcGF0dGVybikiLz48L3N2Zz4='); background-size: 100px 100px;">
                                <div class="flex justify-end">
                                    <div class="bg-[#DCF8C6] rounded-lg p-3 shadow-md max-w-md" style="border-radius: 7.5px; position: relative;">
                                        <!-- Preview de Mídia -->
                                        <div id="mediaPreviewWhatsApp" class="hidden mb-2">
                                            <div class="bg-gray-200 rounded-md p-8 text-center">
                                                <i id="previewIconWhatsApp" class="text-4xl text-gray-500 mb-2"></i>
                                                <p id="previewTextWhatsApp" class="text-xs text-gray-600 font-medium"></p>
                                            </div>
                                        </div>
                                        
                                        <!-- Texto da Mensagem -->
                                        <div id="messagePreviewText" class="text-sm text-gray-800 whitespace-pre-wrap break-words" style="font-family: 'Segoe UI', Helvetica, Arial, sans-serif;">
                                            <span class="text-gray-400 italic">Sua mensagem aparecerá aqui...</span>
                                        </div>
                                        
                                        <!-- Hora -->
                                        <div class="flex items-center justify-end gap-1 mt-1">
                                            <span class="text-xs text-gray-600" id="previewTime"></span>
                                            <i class="fas fa-check-double text-blue-500 text-xs"></i>
                                        </div>
                                        
                                        <!-- Triângulo da bolha -->
                                        <div style="position: absolute; right: -8px; top: 0; width: 0; height: 0; border-left: 8px solid #DCF8C6; border-top: 8px solid transparent;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Macros -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm font-medium text-blue-900 mb-2">
                                <i class="fas fa-magic mr-1"></i>
                                Macros Disponíveis:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="insertMacro('{nome}')" 
                                        class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-full hover:bg-blue-700 transition">
                                    {nome}
                                </button>
                            </div>
                            <p class="text-xs text-blue-700 mt-2">
                                Use <code class="bg-blue-100 px-1 rounded">{nome}</code> para inserir o nome do contato
                            </p>
                        </div>

                        <!-- Botão Iniciar -->
                        <button id="startDispatchBtn" onclick="startDispatch()" 
                                class="w-full py-3 gradient-bg text-white font-semibold rounded-lg hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-rocket mr-2"></i>
                            Iniciar Disparo
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Offcanvas da Fila (aparece apenas após iniciar) -->
<div id="queueOffcanvas" class="fixed inset-y-0 right-0 w-full md:w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50" style="display: none;">
    <div class="h-full flex flex-col">
        <!-- Header do Offcanvas -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-white">
                <i class="fas fa-list-ol mr-2"></i>
                Fila de Disparo
            </h3>
            <button onclick="closeQueueOffcanvas()" class="text-white hover:bg-white/20 rounded-lg p-2 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Conteúdo do Offcanvas -->
        <div class="flex-1 overflow-y-auto p-4">

                <!-- Status -->
                <div id="dispatchStatus" class="hidden mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 border-2 border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium">Progresso Geral</span>
                        <span id="overallProgress" class="text-sm font-bold">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div id="overallProgressBar" class="progress-bar bg-gradient-to-r from-purple-600 to-blue-600 h-3 rounded-full" style="width: 0%"></div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-600">
                        <span><i class="fas fa-check-circle text-green-600 mr-1"></i> Enviados: <strong id="sentCount">0</strong></span>
                        <span><i class="fas fa-times-circle text-red-600 mr-1"></i> Falhas: <strong id="failedCount">0</strong></span>
                        <span><i class="fas fa-clock text-yellow-600 mr-1"></i> Pendentes: <strong id="pendingCount">0</strong></span>
                    </div>
                    
                    <!-- Botões de Controle do Disparo -->
                    <div id="dispatchControlButtons" class="mt-4 pt-4 border-t border-blue-200">
                        <div class="grid grid-cols-2 gap-3">
                            <button id="pauseBtn" onclick="pauseDispatch()" 
                                    class="py-3 px-4 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition flex items-center justify-center shadow-md">
                                <i class="fas fa-pause mr-2"></i>
                                <span>Pausar</span>
                            </button>
                            <button id="stopBtn" onclick="stopDispatch()" 
                                    class="py-3 px-4 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition flex items-center justify-center shadow-md">
                                <i class="fas fa-stop mr-2"></i>
                                <span>Parar</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-600 text-center mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Use <strong>Pausar</strong> para continuar depois ou <strong>Parar</strong> para cancelar
                        </p>
                    </div>
                </div>

            <!-- Lista de Fila -->
            <div id="queueList" class="space-y-3">
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-inbox text-5xl mb-3"></i>
                    <p class="text-sm">A fila aparecerá aqui quando você iniciar o disparo</p>
                </div>
            </div>

            <!-- Confete de Conclusão -->
            <div id="completionMessage" class="hidden text-center py-8">
                <div class="text-6xl mb-4">🎉</div>
                <h4 class="text-2xl font-bold text-green-600 mb-2">Parabéns!</h4>
                <p class="text-gray-600">Todos os disparos foram concluídos com sucesso!</p>
            </div>
        </div>
    </div>
</div>

<!-- Backdrop do Offcanvas -->
<div id="queueBackdrop" class="fixed inset-0 bg-black bg-opacity-50 z-40" style="display: none;" onclick="closeQueueOffcanvas()"></div>

<script>
let selectedContacts = [];
let dispatchQueue = [];
let currentIndex = 0;
let isDispatching = false;
let isPaused = false;
let isStopped = false;
let currentTimer = null;

// Atualizar seleção de contatos
function updateContactSelection() {
    const type = document.getElementById('dispatchType').value;
    
    document.getElementById('tagSelection').classList.add('hidden');
    document.getElementById('individualSelection').classList.add('hidden');
    
    if (type === 'tag') {
        document.getElementById('tagSelection').classList.remove('hidden');
    } else if (type === 'individual') {
        document.getElementById('individualSelection').classList.remove('hidden');
    } else if (type === 'all') {
        loadAllContacts();
    }
}

// Carregar todos os contatos
async function loadAllContacts() {
    try {
        const response = await fetch('<?php echo APP_URL; ?>/dispatch/getContacts?type=all');
        const data = await response.json();
        
        if (data.success) {
            selectedContacts = data.contacts;
            updateContactCount();
        }
    } catch (error) {
        showNotification('Erro ao carregar contatos', 'error');
    }
}

// Carregar contatos por tag
async function loadContactsByTag() {
    const tagId = document.getElementById('selectedTag').value;
    if (!tagId) {
        selectedContacts = [];
        updateContactCount();
        return;
    }
    
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/dispatch/getContacts?type=tag&tag_id=${tagId}`);
        const data = await response.json();
        
        if (data.success) {
            selectedContacts = data.contacts;
            updateContactCount();
        }
    } catch (error) {
        showNotification('Erro ao carregar contatos', 'error');
    }
}

// Carregar contato individual
function loadIndividualContact() {
    const select = document.getElementById('selectedContact');
    const option = select.options[select.selectedIndex];
    
    if (!option.value) {
        selectedContacts = [];
        updateContactCount();
        return;
    }
    
    selectedContacts = [{
        id: option.value,
        name: option.dataset.name,
        phone: option.dataset.phone
    }];
    
    updateContactCount();
}

// Atualizar contador
function updateContactCount() {
    document.getElementById('contactCount').textContent = selectedContacts.length;
    document.getElementById('startDispatchBtn').disabled = selectedContacts.length === 0;
}

// Inserir macro
function insertMacro(macro) {
    const textarea = document.getElementById('messageText');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    textarea.value = text.substring(0, start) + macro + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + macro.length;
    
    updateMessagePreview();
}

// Atualizar preview WhatsApp
function updateMessagePreview() {
    const messageText = document.getElementById('messageText').value;
    const previewText = document.getElementById('messagePreviewText');
    const charCount = document.getElementById('charCount');
    const previewTime = document.getElementById('previewTime');
    
    // Atualizar contador de caracteres
    charCount.textContent = messageText.length;
    
    // Atualizar hora atual
    const now = new Date();
    previewTime.textContent = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    
    // Atualizar preview
    if (messageText.trim()) {
        // Substituir {nome} por exemplo
        let preview = messageText.replace(/{nome}/g, '<strong>João Silva</strong>');
        previewText.innerHTML = preview || '<span class="text-gray-400 italic">Sua mensagem aparecerá aqui...</span>';
    } else {
        previewText.innerHTML = '<span class="text-gray-400 italic">Sua mensagem aparecerá aqui...</span>';
    }
    
    // Atualizar preview de mídia se houver
    updateMediaPreviewWhatsApp();
}

// Atualizar preview de mídia no WhatsApp
function updateMediaPreviewWhatsApp() {
    const mediaPreview = document.getElementById('mediaPreviewWhatsApp');
    const previewIcon = document.getElementById('previewIconWhatsApp');
    const previewText = document.getElementById('previewTextWhatsApp');
    
    if (selectedFile) {
        const config = mediaConfig[currentMediaType];
        previewIcon.className = config.icon + ' text-4xl text-gray-500 mb-2';
        previewText.textContent = config.preview;
        mediaPreview.classList.remove('hidden');
    } else {
        mediaPreview.classList.add('hidden');
    }
}

// Abrir offcanvas da fila
function openQueueOffcanvas() {
    const offcanvas = document.getElementById('queueOffcanvas');
    const backdrop = document.getElementById('queueBackdrop');
    
    offcanvas.style.display = 'block';
    backdrop.style.display = 'block';
    
    // Animar entrada
    setTimeout(() => {
        offcanvas.classList.remove('translate-x-full');
    }, 10);
}

// Fechar offcanvas da fila
function closeQueueOffcanvas() {
    const offcanvas = document.getElementById('queueOffcanvas');
    const backdrop = document.getElementById('queueBackdrop');
    
    offcanvas.classList.add('translate-x-full');
    
    setTimeout(() => {
        offcanvas.style.display = 'none';
        backdrop.style.display = 'none';
    }, 300);
}

// Iniciar disparo
async function startDispatch() {
    const message = document.getElementById('messageText').value.trim();
    
    if (!message) {
        showNotification('Por favor, digite uma mensagem', 'warning');
        return;
    }
    
    if (selectedContacts.length === 0) {
        showNotification('Selecione pelo menos um contato', 'warning');
        return;
    }
    
    if (!confirm(`Deseja iniciar o disparo para ${selectedContacts.length} contato(s)?`)) {
        return;
    }
    
    // Preparar fila
    dispatchQueue = selectedContacts.map(contact => ({
        ...contact,
        message: message,
        status: 'pending'
    }));
    
    currentIndex = 0;
    isDispatching = true;
    isPaused = false;
    isStopped = false;
    
    // Mostrar interface de fila (os botões de controle estão dentro do dispatchStatus)
    document.getElementById('dispatchStatus').classList.remove('hidden');
    document.getElementById('startDispatchBtn').disabled = true;
    
    // Abrir offcanvas da fila
    openQueueOffcanvas();
    
    // Renderizar fila
    renderQueue();
    
    // Iniciar processamento
    processQueue();
}

// Pausar disparo
function pauseDispatch() {
    isPaused = !isPaused;
    const pauseBtn = document.getElementById('pauseBtn');
    
    if (isPaused) {
        pauseBtn.innerHTML = '<i class="fas fa-play mr-2"></i>Continuar';
        pauseBtn.classList.remove('bg-yellow-500', 'hover:bg-yellow-600');
        pauseBtn.classList.add('bg-green-500', 'hover:bg-green-600');
        showNotification('Disparo pausado', 'info');
    } else {
        pauseBtn.innerHTML = '<i class="fas fa-pause mr-2"></i>Pausar';
        pauseBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
        pauseBtn.classList.add('bg-yellow-500', 'hover:bg-yellow-600');
        showNotification('Disparo retomado', 'success');
        // Continuar de onde parou
        if (currentTimer) {
            processQueue();
        }
    }
}

// Parar disparo
function stopDispatch() {
    if (!confirm('⚠️ Tem certeza que deseja PARAR o disparo?\n\nIsso cancelará todos os envios pendentes.')) {
        return;
    }
    
    isStopped = true;
    isPaused = false;
    isDispatching = false;
    
    // Limpar timer se existir
    if (currentTimer) {
        clearTimeout(currentTimer);
        currentTimer = null;
    }
    
    // Marcar pendentes como cancelados
    for (let i = currentIndex; i < dispatchQueue.length; i++) {
        if (dispatchQueue[i].status === 'pending') {
            dispatchQueue[i].status = 'cancelled';
            const statusElement = document.getElementById(`status-${i}`);
            if (statusElement) {
                statusElement.innerHTML = `
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                        <i class="fas fa-ban mr-1"></i>
                        Cancelado
                    </span>
                `;
            }
        }
    }
    
    // Atualizar estatísticas
    updateStats();
    
    // Reabilitar botão iniciar
    document.getElementById('startDispatchBtn').disabled = false;
    
    showNotification('Disparo cancelado', 'warning');
}

// Renderizar fila
function renderQueue() {
    const queueList = document.getElementById('queueList');
    queueList.innerHTML = '';
    
    dispatchQueue.forEach((item, index) => {
        const queueItem = document.createElement('div');
        queueItem.id = `queue-item-${index}`;
        queueItem.className = 'border border-gray-200 rounded-lg p-4 transition-all';
        
        queueItem.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-semibold">
                        ${item.name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">${item.name}</p>
                        <p class="text-xs text-gray-600">${item.phone}</p>
                    </div>
                </div>
                <div id="status-${index}" class="flex items-center gap-2">
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                        <i class="fas fa-clock mr-1"></i>
                        Aguardando
                    </span>
                </div>
            </div>
            <div id="progress-${index}" class="hidden mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="progress-bar bg-blue-600 h-2 rounded-full transition-all" style="width: 0%"></div>
                </div>
                <p class="text-xs text-gray-600 mt-1 text-center">Aguardando <span class="font-bold" id="timer-${index}">0</span>s</p>
            </div>
        `;
        
        queueList.appendChild(queueItem);
    });
}

// Processar fila
async function processQueue() {
    // Verificar se foi parado
    if (isStopped) {
        return;
    }
    
    // Verificar se foi pausado
    if (isPaused) {
        currentTimer = setTimeout(() => processQueue(), 1000);
        return;
    }
    
    if (currentIndex >= dispatchQueue.length) {
        finishDispatch();
        return;
    }
    
    const item = dispatchQueue[currentIndex];
    const itemElement = document.getElementById(`queue-item-${currentIndex}`);
    
    // Destacar item atual
    itemElement.classList.add('border-blue-500', 'bg-blue-50');
    
    // Atualizar status para "enviando"
    document.getElementById(`status-${currentIndex}`).innerHTML = `
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-600">
            <i class="fas fa-spinner fa-spin mr-1"></i>
            Enviando
        </span>
    `;
    
    // Mostrar barra de progresso com tempo aleatório
    const waitTime = Math.floor(Math.random() * (<?php echo DISPATCH_MAX_INTERVAL; ?> - <?php echo DISPATCH_MIN_INTERVAL; ?> + 1)) + <?php echo DISPATCH_MIN_INTERVAL; ?>;
    
    document.getElementById(`progress-${currentIndex}`).classList.remove('hidden');
    
    // Animar progresso com tratamento de pause/stop
    try {
        await animateProgress(currentIndex, waitTime);
    } catch (reason) {
        // Se foi pausado ou parado durante a animação
        if (reason === 'paused') {
            setTimeout(() => processQueue(), 1000);
            return;
        }
        if (reason === 'stopped') {
            return;
        }
    }
    
    // Verificar novamente antes de enviar
    if (isStopped) return;
    if (isPaused) {
        setTimeout(() => processQueue(), 1000);
        return;
    }
    
    // Enviar mensagem
    try {
        const formData = new FormData();
        formData.append('contact_id', item.id);
        formData.append('contact_name', item.name);
        formData.append('contact_phone', item.phone);
        formData.append('message', item.message);
        
        const response = await fetch('<?php echo APP_URL; ?>/dispatch/send', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Sucesso
            dispatchQueue[currentIndex].status = 'sent';
            document.getElementById(`status-${currentIndex}`).innerHTML = `
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle mr-1"></i>
                    Enviado
                </span>
            `;
            itemElement.classList.remove('border-blue-500', 'bg-blue-50');
            itemElement.classList.add('border-green-500', 'bg-green-50');
        } else {
            // Falha
            dispatchQueue[currentIndex].status = 'failed';
            document.getElementById(`status-${currentIndex}`).innerHTML = `
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-times-circle mr-1"></i>
                    Falhou
                </span>
            `;
            itemElement.classList.remove('border-blue-500', 'bg-blue-50');
            itemElement.classList.add('border-red-500', 'bg-red-50');
        }
    } catch (error) {
        dispatchQueue[currentIndex].status = 'failed';
        document.getElementById(`status-${currentIndex}`).innerHTML = `
            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-600">
                <i class="fas fa-times-circle mr-1"></i>
                Erro
            </span>
        `;
        itemElement.classList.remove('border-blue-500', 'bg-blue-50');
        itemElement.classList.add('border-red-500', 'bg-red-50');
    }
    
    // Atualizar estatísticas
    updateStats();
    
    // Próximo item
    currentIndex++;
    processQueue();
}

// Animar progresso
function animateProgress(index, seconds) {
    return new Promise((resolve, reject) => {
        const progressBar = document.querySelector(`#progress-${index} .progress-bar`);
        const timerElement = document.getElementById(`timer-${index}`);
        let elapsed = 0;
        const interval = 100; // Atualizar a cada 100ms
        const totalSteps = (seconds * 1000) / interval;
        
        const timer = setInterval(() => {
            // Verificar se foi pausado ou parado
            if (isStopped) {
                clearInterval(timer);
                reject('stopped');
                return;
            }
            
            if (isPaused) {
                clearInterval(timer);
                reject('paused');
                return;
            }
            
            elapsed += interval;
            const progress = (elapsed / (seconds * 1000)) * 100;
            const remaining = Math.ceil((seconds * 1000 - elapsed) / 1000);
            
            progressBar.style.width = progress + '%';
            timerElement.textContent = remaining;
            
            if (elapsed >= seconds * 1000) {
                clearInterval(timer);
                resolve();
            }
        }, interval);
        
        // Salvar referência do timer para poder cancelar
        currentTimer = timer;
    });
}

// Atualizar estatísticas
function updateStats() {
    const sent = dispatchQueue.filter(item => item.status === 'sent').length;
    const failed = dispatchQueue.filter(item => item.status === 'failed').length;
    const pending = dispatchQueue.filter(item => item.status === 'pending').length;
    const total = dispatchQueue.length;
    const progress = ((sent + failed) / total) * 100;
    
    document.getElementById('sentCount').textContent = sent;
    document.getElementById('failedCount').textContent = failed;
    document.getElementById('pendingCount').textContent = pending;
    document.getElementById('overallProgress').textContent = Math.round(progress) + '%';
    document.getElementById('overallProgressBar').style.width = progress + '%';
}

// Finalizar disparo
function finishDispatch() {
    isDispatching = false;
    isPaused = false;
    isStopped = false;
    
    // Reabilitar botão iniciar
    document.getElementById('startDispatchBtn').disabled = false;
    
    // Mostrar mensagem de conclusão
    document.getElementById('completionMessage').classList.remove('hidden');
    
    // Criar confetes
    createConfetti();
    
    // Notificação
    showNotification('Disparo concluído com sucesso! 🎉', 'success');
    
    // Scroll para o topo
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Criar confetes
function createConfetti() {
    const colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b'];
    const confettiCount = 50;
    
    for (let i = 0; i < confettiCount; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'confetti fixed text-3xl';
            confetti.textContent = ['🎉', '🎊', '✨', '🌟', '⭐'][Math.floor(Math.random() * 5)];
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.top = '-50px';
            confetti.style.zIndex = '9999';
            
            document.body.appendChild(confetti);
            
            setTimeout(() => confetti.remove(), 3000);
        }, i * 50);
    }
}

// Variáveis globais para mídia
let selectedFile = null;
let currentMediaType = 'text';

// Configurações de tipos de arquivo
const mediaConfig = {
    text: {
        label: 'Apenas Texto',
        accept: '',
        types: '',
        icon: 'fas fa-comment-dots',
        preview: ''
    },
    image: {
        label: 'Selecionar Imagem',
        accept: 'image/jpeg,image/png,image/gif,image/webp',
        types: 'JPG, PNG, GIF, WEBP (máx. 16MB)',
        icon: 'fas fa-image',
        preview: '🖼️ Imagem'
    },
    video: {
        label: 'Selecionar Vídeo',
        accept: 'video/mp4,video/avi,video/mov,video/wmv,video/webm',
        types: 'MP4, AVI, MOV, WMV, WEBM (máx. 16MB)',
        icon: 'fas fa-video',
        preview: '🎥 Vídeo'
    },
    audio: {
        label: 'Selecionar Áudio',
        accept: 'audio/mp3,audio/wav,audio/ogg,audio/m4a,audio/aac',
        types: 'MP3, WAV, OGG, M4A, AAC (máx. 16MB)',
        icon: 'fas fa-music',
        preview: '🎵 Áudio'
    },
    document: {
        label: 'Selecionar Documento',
        accept: 'application/pdf,.doc,.docx,.txt',
        types: 'PDF, DOC, DOCX, TXT (máx. 16MB)',
        icon: 'fas fa-file-alt',
        preview: '📄 Documento'
    }
};

// Atualizar tipo de mensagem
function updateMessageType() {
    const messageType = document.getElementById('messageType').value;
    const mediaUpload = document.getElementById('mediaUpload');
    const messageText = document.getElementById('messageText');
    
    currentMediaType = messageType;
    console.log('🔄 Tipo de mensagem alterado para:', currentMediaType);
    
    if (messageType === 'text') {
        mediaUpload.classList.add('hidden');
        messageText.placeholder = 'Digite sua mensagem aqui...\n\nDica: Use {nome} para personalizar';
        messageText.rows = 10;
        messageText.disabled = false;
        selectedFile = null;
    } else {
        mediaUpload.classList.remove('hidden');
        
        // Configurar upload baseado no tipo
        const config = mediaConfig[messageType];
        document.getElementById('mediaLabel').textContent = config.label;
        document.getElementById('mediaFile').accept = config.accept;
        
        // Mensagem específica para vídeos
        if (messageType === 'video') {
            document.getElementById('fileTypes').innerHTML = config.types + '<br><span class="text-yellow-600">⚠️ Use MP4 com H.264 + AAC para melhor compatibilidade</span>';
        } else {
            document.getElementById('fileTypes').textContent = config.types;
        }
        
        // Ajustar placeholder e tamanho do textarea
        if (messageType === 'audio') {
            messageText.placeholder = 'Áudio será enviado sem texto adicional';
            messageText.rows = 3;
            messageText.disabled = true;
        } else {
            messageText.placeholder = 'Digite uma legenda (opcional)...';
            messageText.rows = 5;
            messageText.disabled = false;
        }
        
        // Limpar arquivo anterior se mudou o tipo
        removeFile();
    }
    
    // Atualizar preview
    updateMessagePreview();
}

// Manipular seleção de arquivo
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) {
        console.log('Nenhum arquivo selecionado');
        return;
    }
    
    console.log('Arquivo selecionado:', file.name, 'Tamanho:', file.size, 'Tipo:', file.type);
    
    // Verificar tamanho baseado no tipo (limites do WhatsApp)
    const mediaType = currentMediaType; // Usar variável global em vez de buscar elemento
    console.log('Media Type:', mediaType);
    
    const maxSizes = {
        'image': 5 * 1024 * 1024,   // 5MB para imagens
        'video': 16 * 1024 * 1024,  // 16MB para vídeos
        'audio': 16 * 1024 * 1024,  // 16MB para áudio
        'document': 16 * 1024 * 1024 // 16MB para documentos
    };
    
    const maxSize = maxSizes[mediaType] || (5 * 1024 * 1024);
    const maxSizeMB = Math.round(maxSize / 1024 / 1024);
    
    console.log('Tamanho máximo:', maxSizeMB + 'MB', 'Tamanho do arquivo:', Math.round(file.size / 1024 / 1024 * 100) / 100 + 'MB');
    
    if (file.size > maxSize) {
        console.error('Arquivo muito grande!');
        showNotification(`Arquivo muito grande. Máximo ${maxSizeMB}MB para ${mediaType}.`, 'error');
        return;
    }
    
    console.log('✅ Arquivo OK, anexando...');
    selectedFile = file;
    
    // Ocultar área de upload e mostrar preview
    const uploadArea = document.getElementById('uploadArea');
    const filePreview = document.getElementById('filePreview');
    
    uploadArea.classList.add('hidden');
    filePreview.classList.remove('hidden');
    
    // Ocultar todos os previews
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('videoPreview').classList.add('hidden');
    document.getElementById('audioPreview').classList.add('hidden');
    document.getElementById('documentPreview').classList.add('hidden');
    
    // Criar URL temporária do arquivo
    const fileURL = URL.createObjectURL(file);
    const fileSizeText = formatFileSize(file.size);
    
    // Mostrar preview específico baseado no tipo
    if (mediaType === 'image') {
        // Preview de Imagem
        document.getElementById('imagePreviewImg').src = fileURL;
        document.getElementById('imageFileName').textContent = file.name;
        document.getElementById('imageFileSize').textContent = fileSizeText;
        document.getElementById('imagePreview').classList.remove('hidden');
        console.log('✅ Preview de imagem carregado');
        
    } else if (mediaType === 'video') {
        // Preview de Vídeo
        const videoPlayer = document.getElementById('videoPreviewPlayer');
        const videoSource = document.getElementById('videoPreviewSource');
        videoSource.src = fileURL;
        videoSource.type = file.type;
        videoPlayer.load();
        document.getElementById('videoFileName').textContent = file.name;
        document.getElementById('videoFileSize').textContent = fileSizeText;
        document.getElementById('videoPreview').classList.remove('hidden');
        console.log('✅ Preview de vídeo carregado');
        
    } else if (mediaType === 'audio') {
        // Preview de Áudio
        const audioPlayer = document.getElementById('audioPreviewPlayer');
        const audioSource = document.getElementById('audioPreviewSource');
        audioSource.src = fileURL;
        audioSource.type = file.type;
        audioPlayer.load();
        document.getElementById('audioFileName').textContent = file.name;
        document.getElementById('audioFileSize').textContent = fileSizeText;
        document.getElementById('audioPreview').classList.remove('hidden');
        console.log('✅ Preview de áudio carregado');
        
    } else if (mediaType === 'document') {
        // Preview de Documento
        document.getElementById('documentFileName').textContent = file.name;
        document.getElementById('documentFileSize').textContent = fileSizeText;
        document.getElementById('documentPreview').classList.remove('hidden');
        console.log('✅ Preview de documento carregado');
    }
    
    // Atualizar preview WhatsApp
    updateMediaPreviewWhatsApp();
    console.log('✅ Arquivo anexado com sucesso!');
}

// Remover arquivo
function removeFile() {
    selectedFile = null;
    document.getElementById('mediaFile').value = '';
    document.getElementById('uploadArea').classList.remove('hidden');
    document.getElementById('filePreview').classList.add('hidden');
    
    // Limpar previews
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('videoPreview').classList.add('hidden');
    document.getElementById('audioPreview').classList.add('hidden');
    document.getElementById('documentPreview').classList.add('hidden');
    
    // Limpar URLs temporárias
    const imagePreview = document.getElementById('imagePreviewImg');
    if (imagePreview.src) URL.revokeObjectURL(imagePreview.src);
    
    const videoSource = document.getElementById('videoPreviewSource');
    if (videoSource.src) URL.revokeObjectURL(videoSource.src);
    
    const audioSource = document.getElementById('audioPreviewSource');
    if (audioSource.src) URL.revokeObjectURL(audioSource.src);
    
    // Atualizar preview WhatsApp
    updateMediaPreviewWhatsApp();
    
    console.log('✅ Arquivo removido');
}

// Atualizar preview de mídia
function updateMediaPreview() {
    const mediaPreview = document.getElementById('mediaPreview');
    const previewIcon = document.getElementById('previewIcon');
    const previewText = document.getElementById('previewText');
    
    if (selectedFile) {
        const config = mediaConfig[currentMediaType];
        previewIcon.className = config.icon + ' text-gray-500';
        previewText.textContent = config.preview + ': ' + selectedFile.name;
        mediaPreview.classList.remove('hidden');
    } else {
        mediaPreview.classList.add('hidden');
    }
}

// Formatar tamanho do arquivo
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Atualizar função de iniciar disparo para incluir mídia
const originalStartDispatch = startDispatch;
startDispatch = async function() {
    const message = document.getElementById('messageText').value.trim();
    const messageType = document.getElementById('messageType').value;
    
    // Validações específicas para mídia
    if (messageType !== 'text' && !selectedFile) {
        showNotification('Selecione um arquivo para enviar', 'warning');
        return;
    }
    
    if (messageType === 'text' && !message) {
        showNotification('Digite uma mensagem', 'warning');
        return;
    }
    
    if (selectedContacts.length === 0) {
        showNotification('Selecione pelo menos um contato', 'warning');
        return;
    }
    
    const mediaText = messageType !== 'text' ? ' com mídia' : '';
    if (!confirm(`Deseja iniciar o disparo${mediaText} para ${selectedContacts.length} contato(s)?`)) {
        return;
    }
    
    // Preparar fila com informações de mídia
    dispatchQueue = selectedContacts.map(contact => ({
        ...contact,
        message: message,
        mediaType: messageType,
        hasMedia: selectedFile !== null,
        status: 'pending'
    }));
    
    currentIndex = 0;
    isDispatching = true;
    isPaused = false;
    isStopped = false;
    
    // Mostrar interface de fila
    document.getElementById('dispatchStatus').classList.remove('hidden');
    document.getElementById('startDispatchBtn').disabled = true;
    
    // Abrir offcanvas da fila
    openQueueOffcanvas();
    
    // Renderizar fila
    renderQueue();
    
    // Iniciar processamento
    processQueue();
};

// Atualizar função de processamento para incluir mídia
const originalProcessQueue = processQueue;
processQueue = async function() {
    if (currentIndex >= dispatchQueue.length) {
        finishDispatch();
        return;
    }
    
    const item = dispatchQueue[currentIndex];
    const itemElement = document.getElementById(`queue-item-${currentIndex}`);
    
    // Destacar item atual
    itemElement.classList.add('border-blue-500', 'bg-blue-50');
    
    // Atualizar status para "enviando"
    const statusText = item.hasMedia ? 'Enviando mídia' : 'Enviando';
    document.getElementById(`status-${currentIndex}`).innerHTML = `
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-600">
            <i class="fas fa-spinner fa-spin mr-1"></i>
            ${statusText}
        </span>
    `;
    
    // Mostrar barra de progresso com tempo aleatório
    const waitTime = Math.floor(Math.random() * (<?php echo DISPATCH_MAX_INTERVAL; ?> - <?php echo DISPATCH_MIN_INTERVAL; ?> + 1)) + <?php echo DISPATCH_MIN_INTERVAL; ?>;
    
    document.getElementById(`progress-${currentIndex}`).classList.remove('hidden');
    
    // Animar progresso com tratamento de pause/stop
    try {
        await animateProgress(currentIndex, waitTime);
    } catch (reason) {
        // Se foi pausado ou parado durante a animação
        if (reason === 'paused') {
            setTimeout(() => processQueue(), 1000);
            return;
        }
        if (reason === 'stopped') {
            return;
        }
    }
    
    // Verificar novamente antes de enviar
    if (isStopped) return;
    if (isPaused) {
        setTimeout(() => processQueue(), 1000);
        return;
    }
    
    // Enviar mensagem
    try {
        const formData = new FormData();
        formData.append('contact_id', item.id);
        formData.append('contact_name', item.name);
        formData.append('contact_phone', item.phone);
        formData.append('message', item.message);
        formData.append('media_type', item.mediaType);
        
        // Adicionar arquivo se houver
        if (selectedFile && item.hasMedia) {
            formData.append('media_file', selectedFile);
        }
        
        const response = await fetch('<?php echo APP_URL; ?>/dispatch/send', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Sucesso
            dispatchQueue[currentIndex].status = 'sent';
            document.getElementById(`status-${currentIndex}`).innerHTML = `
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle mr-1"></i>
                    Enviado
                </span>
            `;
            itemElement.classList.remove('border-blue-500', 'bg-blue-50');
            itemElement.classList.add('border-green-500', 'bg-green-50');
        } else {
            // Falha
            dispatchQueue[currentIndex].status = 'failed';
            document.getElementById(`status-${currentIndex}`).innerHTML = `
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-times-circle mr-1"></i>
                    Falhou
                </span>
            `;
            itemElement.classList.remove('border-blue-500', 'bg-blue-50');
            itemElement.classList.add('border-red-500', 'bg-red-50');
        }
    } catch (error) {
        dispatchQueue[currentIndex].status = 'failed';
        document.getElementById(`status-${currentIndex}`).innerHTML = `
            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-600">
                <i class="fas fa-times-circle mr-1"></i>
                Erro
            </span>
        `;
        itemElement.classList.remove('border-blue-500', 'bg-blue-50');
        itemElement.classList.add('border-red-500', 'bg-red-50');
    }
    
    // Atualizar estatísticas
    updateStats();
    
    // Próximo item
    currentIndex++;
    processQueue();
};

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    updateContactSelection();
    updateMessageType(); // Inicializar tipo de mensagem
});
</script>

<?php include 'views/layouts/footer.php'; ?>
