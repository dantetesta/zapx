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
                        <input type="file" name="media_file" id="mediaFile" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <p id="mediaHint" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    <!-- Texto da Mensagem -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Texto da Mensagem</label>
                        <textarea name="message" id="messageText" rows="6"
                                  placeholder="Digite sua mensagem aqui...&#10;&#10;Use {nome} para personalizar"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Use <code class="bg-gray-100 px-1 rounded">{nome}</code> para inserir o nome do contato
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
    
    if (type === 'text') {
        mediaUpload.classList.add('hidden');
        mediaFile.removeAttribute('required');
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
