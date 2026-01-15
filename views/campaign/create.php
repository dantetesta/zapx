<?php 
$pageTitle = 'Nova Campanha - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo APP_URL; ?>/campaign" class="text-purple-600 hover:text-purple-700 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-bullhorn mr-2 gradient-text"></i>
                Nova Campanha
            </h1>
            <p class="mt-2 text-gray-600">Configure e inicie uma campanha de disparo em massa</p>
        </div>

        <form id="campaignForm" enctype="multipart/form-data" class="space-y-6">
            <!-- Nome da Campanha -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-tag mr-2 text-purple-600"></i>
                    Identificação
                </h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Campanha (opcional)</label>
                    <input type="text" name="name" id="campaignName" 
                           placeholder="Ex: Promoção Black Friday"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>

            <!-- Seleção de Contatos -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-users mr-2 text-purple-600"></i>
                    Selecionar Contatos
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Disparo</label>
                        <select name="dispatch_type" id="dispatchType" onchange="updateContactCount()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="all">Todos os Contatos (<?php echo $totalContacts; ?>)</option>
                            <option value="tag">Por Tag/Categoria</option>
                        </select>
                    </div>

                    <div id="tagSelection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selecione a Tag</label>
                        <select name="tag_id" id="selectedTag" onchange="updateContactCount()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Selecione...</option>
                            <?php foreach ($tags as $tag): ?>
                            <option value="<?php echo $tag['id']; ?>" data-count="<?php echo $tagCounts[$tag['id']] ?? 0; ?>">
                                <?php echo htmlspecialchars($tag['name']); ?> (<?php echo $tagCounts[$tag['id']] ?? 0; ?> contatos)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-purple-900">Contatos Selecionados:</span>
                            <span id="contactCount" class="text-2xl font-bold text-purple-600"><?php echo $totalContacts; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensagem -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-comment-dots mr-2 text-green-600"></i>
                    Mensagem
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Coluna Esquerda: Formulário -->
                    <div class="space-y-4">
                        <!-- Tipo de Mídia -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Mensagem</label>
                            <select name="media_type" id="mediaType" onchange="updateMediaType()"
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo</label>
                            <input type="file" name="media_file" id="mediaFile" onchange="updateFilePreview()"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <p id="mediaHint" class="text-xs text-gray-500 mt-1"></p>
                        </div>

                        <!-- Texto da Mensagem -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Texto da Mensagem</label>
                            <textarea name="message" id="messageText" rows="6"
                                      placeholder="Digite sua mensagem aqui...&#10;&#10;Use {nome} para personalizar"
                                      oninput="updateMessagePreview()"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Use os macros abaixo para personalizar
                                </p>
                                <span class="text-xs text-gray-400">
                                    <span id="charCount">0</span> caracteres
                                </span>
                            </div>
                        </div>
                        
                        <!-- Macros Rápidas -->
                        <div class="flex flex-wrap gap-2 items-center">
                            <span class="text-xs text-gray-500">Inserir:</span>
                            <button type="button" onclick="insertMacro('{saudacao}')" 
                                    class="px-3 py-1.5 text-xs bg-gradient-to-r from-purple-100 to-blue-100 text-purple-700 hover:from-purple-200 hover:to-blue-200 rounded-lg transition font-medium border border-purple-200"
                                    title="Saudação personalizada com período do dia e nome">
                                <i class="fas fa-hand-wave mr-1"></i>
                                {saudacao}
                            </button>
                            <button type="button" onclick="insertMacro('{nome}')" 
                                    class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition">
                                {nome}
                            </button>
                            <button type="button" onclick="insertMacro('{numero}')" 
                                    class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition">
                                {numero}
                            </button>
                            <a href="<?php echo APP_URL; ?>/greeting/index" target="_blank"
                               class="text-xs text-purple-600 hover:text-purple-800 ml-2"
                               title="Configurar suas saudações personalizadas">
                                <i class="fas fa-cog"></i> Configurar
                            </a>
                        </div>
                        
                        <!-- Dica sobre {saudacao} -->
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-xs">
                            <p class="text-purple-700">
                                <i class="fas fa-lightbulb mr-1"></i>
                                <strong>{saudacao}</strong> gera automaticamente: "Bom dia, João, tudo bem?" - rotacionando entre suas saudações configuradas.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Coluna Direita: Preview WhatsApp -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fab fa-whatsapp mr-1 text-green-600"></i>
                            Preview da Mensagem
                        </label>
                        <div class="bg-[#ECE5DD] rounded-lg p-4 min-h-[280px]" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9InAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCI+PHBhdGggZD0iTTAgMzAgTDMwIDAgTDYwIDMwIEwzMCA2MCBaIiBmaWxsPSJub25lIiBzdHJva2U9IiNkNWQ1ZDUiIHN0cm9rZS13aWR0aD0iMC41IiBvcGFjaXR5PSIwLjMiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjcCkiLz48L3N2Zz4=');">
                            <div class="flex justify-end">
                                <div class="bg-[#DCF8C6] rounded-lg p-3 shadow-md max-w-[280px] relative" style="border-radius: 7.5px;">
                                    <!-- Preview de Mídia -->
                                    <div id="mediaPreviewWhatsApp" class="hidden mb-2">
                                        <div id="mediaPreviewContent" class="bg-gray-200 rounded-md overflow-hidden">
                                            <!-- Imagem preview -->
                                            <img id="imagePreviewWA" src="" alt="" class="hidden w-full h-32 object-cover">
                                            <!-- Placeholder mídia -->
                                            <div id="mediaPlaceholder" class="p-6 text-center">
                                                <i id="previewIconWhatsApp" class="fas fa-image text-3xl text-gray-400 mb-2"></i>
                                                <p id="previewFileName" class="text-xs text-gray-500 truncate"></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Texto da Mensagem -->
                                    <div id="messagePreviewText" class="text-sm text-gray-800 whitespace-pre-wrap break-words" style="font-family: 'Segoe UI', Helvetica, Arial, sans-serif;">
                                        <span class="text-gray-400 italic">Sua mensagem aparecerá aqui...</span>
                                    </div>
                                    
                                    <!-- Hora e Check -->
                                    <div class="flex items-center justify-end gap-1 mt-1">
                                        <span class="text-xs text-gray-500" id="previewTime"></span>
                                        <i class="fas fa-check-double text-blue-500 text-xs"></i>
                                    </div>
                                    
                                    <!-- Triângulo da bolha -->
                                    <div style="position: absolute; right: -8px; top: 0; width: 0; height: 0; border-left: 8px solid #DCF8C6; border-top: 8px solid transparent;"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2 text-center">
                            <i class="fas fa-eye mr-1"></i>
                            Visualização aproximada de como ficará no WhatsApp
                        </p>
                    </div>
                </div>
            </div>

            <!-- Aviso Importante -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                <h4 class="font-semibold text-blue-900 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>
                    Como funciona?
                </h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>✅ A campanha será processada em <strong>segundo plano</strong></li>
                    <li>✅ Você pode <strong>fechar o navegador</strong> após iniciar</li>
                    <li>✅ Intervalo aleatório de <strong><?php echo DISPATCH_MIN_INTERVAL; ?>-<?php echo DISPATCH_MAX_INTERVAL; ?> segundos</strong> entre mensagens</li>
                    <li>✅ Você pode <strong>pausar ou cancelar</strong> a qualquer momento</li>
                </ul>
            </div>

            <!-- Botão Iniciar -->
            <button type="submit" id="submitBtn"
                    class="w-full py-4 gradient-bg text-white font-semibold text-lg rounded-xl hover:opacity-90 transition shadow-lg">
                <i class="fas fa-rocket mr-2"></i>
                Iniciar Campanha
            </button>
        </form>
    </div>
</div>

<script>
const totalContacts = <?php echo $totalContacts; ?>;
let currentMediaType = 'text';
let selectedFileName = '';

// Atualizar hora do preview
function updatePreviewTime() {
    const now = new Date();
    document.getElementById('previewTime').textContent = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}
updatePreviewTime();
setInterval(updatePreviewTime, 60000);

function updateContactCount() {
    const type = document.getElementById('dispatchType').value;
    const tagSelection = document.getElementById('tagSelection');
    const countEl = document.getElementById('contactCount');
    
    if (type === 'tag') {
        tagSelection.classList.remove('hidden');
        const tagSelect = document.getElementById('selectedTag');
        const selected = tagSelect.options[tagSelect.selectedIndex];
        countEl.textContent = selected.dataset.count || 0;
    } else {
        tagSelection.classList.add('hidden');
        countEl.textContent = totalContacts;
    }
}

function updateMediaType() {
    const type = document.getElementById('mediaType').value;
    const mediaUpload = document.getElementById('mediaUpload');
    const mediaFile = document.getElementById('mediaFile');
    const mediaHint = document.getElementById('mediaHint');
    
    currentMediaType = type;
    
    if (type === 'text') {
        mediaUpload.classList.add('hidden');
        mediaFile.removeAttribute('required');
        mediaFile.value = '';
        selectedFileName = '';
    } else {
        mediaUpload.classList.remove('hidden');
        mediaFile.setAttribute('required', 'required');
        
        const hints = {
            'image': 'Formatos: JPG, PNG, GIF, WEBP (máx. 5MB)',
            'video': 'Formatos: MP4, MOV (máx. 16MB)',
            'audio': 'Formatos: MP3, WAV, OGG (máx. 16MB)',
            'document': 'Formatos: PDF, DOC, DOCX (máx. 16MB)'
        };
        
        const accepts = {
            'image': 'image/jpeg,image/png,image/gif,image/webp',
            'video': 'video/mp4,video/quicktime',
            'audio': 'audio/mp3,audio/mpeg,audio/wav,audio/ogg',
            'document': 'application/pdf,.doc,.docx'
        };
        
        mediaHint.textContent = hints[type] || '';
        mediaFile.accept = accepts[type] || '';
    }
    
    updateMediaPreviewWhatsApp();
}

// Atualizar preview da mensagem
function updateMessagePreview() {
    const messageText = document.getElementById('messageText').value;
    const previewText = document.getElementById('messagePreviewText');
    const charCount = document.getElementById('charCount');
    
    charCount.textContent = messageText.length;
    
    if (messageText.trim()) {
        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        // Detectar período do dia
        const hora = new Date().getHours();
        let periodo = 'Boa noite';
        if (hora >= 5 && hora < 12) periodo = 'Bom dia';
        else if (hora >= 12 && hora < 18) periodo = 'Boa tarde';
        
        // Simular saudação
        const saudacaoExemplo = `${periodo}, <strong>João</strong>, tudo bem?`;
        
        let preview = escapeHtml(messageText);
        preview = preview.replace(/{saudacao}/g, saudacaoExemplo);
        preview = preview.replace(/{nome}/g, '<strong>João</strong>');
        preview = preview.replace(/{numero}/g, '5511999999999');
        preview = preview.replace(/\n/g, '<br>');
        previewText.innerHTML = preview;
    } else {
        previewText.innerHTML = '<span class="text-gray-400 italic">Sua mensagem aparecerá aqui...</span>';
    }
}

// Inserir macro no texto
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

// Atualizar preview do arquivo selecionado
function updateFilePreview() {
    const fileInput = document.getElementById('mediaFile');
    const file = fileInput.files[0];
    
    if (file) {
        selectedFileName = file.name;
        
        // Se for imagem, mostrar preview real
        if (currentMediaType === 'image' && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgPreview = document.getElementById('imagePreviewWA');
                imgPreview.src = e.target.result;
                imgPreview.classList.remove('hidden');
                document.getElementById('mediaPlaceholder').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('imagePreviewWA').classList.add('hidden');
            document.getElementById('mediaPlaceholder').classList.remove('hidden');
        }
    } else {
        selectedFileName = '';
        document.getElementById('imagePreviewWA').classList.add('hidden');
        document.getElementById('mediaPlaceholder').classList.remove('hidden');
    }
    
    updateMediaPreviewWhatsApp();
}

// Atualizar preview de mídia no WhatsApp
function updateMediaPreviewWhatsApp() {
    const mediaPreview = document.getElementById('mediaPreviewWhatsApp');
    const iconEl = document.getElementById('previewIconWhatsApp');
    const fileNameEl = document.getElementById('previewFileName');
    
    if (currentMediaType === 'text') {
        mediaPreview.classList.add('hidden');
        return;
    }
    
    mediaPreview.classList.remove('hidden');
    
    const icons = {
        'image': 'fas fa-image',
        'video': 'fas fa-video',
        'audio': 'fas fa-music',
        'document': 'fas fa-file-alt'
    };
    
    iconEl.className = (icons[currentMediaType] || 'fas fa-file') + ' text-3xl text-gray-400 mb-2';
    fileNameEl.textContent = selectedFileName || 'Selecione um arquivo...';
}

document.getElementById('campaignForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Criando campanha...';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('<?php echo APP_URL; ?>/campaign/store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Erro ao criar campanha');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        alert('Erro ao criar campanha');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>
