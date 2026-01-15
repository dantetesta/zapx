<?php 
$pageTitle = 'Tags - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-tags mr-2 gradient-text"></i>
                    Tags/Categorias
                </h1>
                <p class="mt-2 text-gray-600">Organize seus contatos por categorias</p>
            </div>
            <button onclick="openAddModal()" 
                    class="mt-4 md:mt-0 px-4 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                <i class="fas fa-plus mr-2"></i>
                Nova Tag
            </button>
        </div>

        <!-- Grid de Tags -->
        <?php if (empty($tags)): ?>
        <div class="bg-white rounded-xl shadow-md p-12 text-center text-gray-500">
            <i class="fas fa-tags text-6xl mb-4 text-gray-300"></i>
            <p class="text-xl font-medium">Nenhuma tag criada ainda</p>
            <p class="text-sm mt-2">Crie tags para organizar seus contatos</p>
            <button onclick="openAddModal()" 
                    class="mt-6 px-6 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                <i class="fas fa-plus mr-2"></i>
                Criar Primeira Tag
            </button>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($tags as $tag): ?>
            <div class="bg-white rounded-xl shadow-md p-6 hover-scale cursor-pointer border-l-4" 
                 style="border-color: <?php echo htmlspecialchars($tag['color']); ?>">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center" 
                             style="background-color: <?php echo htmlspecialchars($tag['color']); ?>20;">
                            <i class="fas fa-tag text-xl" style="color: <?php echo htmlspecialchars($tag['color']); ?>"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($tag['name']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo $tag['contact_count']; ?> contatos</p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <button onclick="editTag(<?php echo $tag['id']; ?>, '<?php echo htmlspecialchars($tag['name']); ?>', '<?php echo htmlspecialchars($tag['color']); ?>')" 
                            class="flex-1 px-3 py-2 text-sm bg-blue-50 text-blue-600 font-medium rounded-lg hover:bg-blue-100 transition">
                        <i class="fas fa-edit mr-1"></i>
                        Editar
                    </button>
                    <button onclick="deleteTag(<?php echo $tag['id']; ?>)" 
                            class="flex-1 px-3 py-2 text-sm bg-red-50 text-red-600 font-medium rounded-lg hover:bg-red-100 transition">
                        <i class="fas fa-trash mr-1"></i>
                        Deletar
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Adicionar/Editar Tag -->
<div id="tagModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-900 mb-4">Nova Tag</h3>
        <form id="tagForm" onsubmit="saveTag(event)">
            <input type="hidden" id="tagId" name="id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Tag *</label>
                    <input type="text" id="tagName" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Ex: Clientes VIP">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cor</label>
                    <div class="flex gap-3 items-center">
                        <input type="color" id="tagColor" name="color" value="#3B82F6"
                               class="w-16 h-10 border border-gray-300 rounded-lg cursor-pointer">
                        <span id="colorPreview" class="flex-1 px-4 py-2 rounded-lg text-white font-medium text-center" 
                              style="background-color: #3B82F6;">
                            Prévia da Cor
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeTagModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Nova Tag';
    document.getElementById('tagId').value = '';
    document.getElementById('tagName').value = '';
    document.getElementById('tagColor').value = '#3B82F6';
    updateColorPreview('#3B82F6');
    document.getElementById('tagModal').classList.remove('hidden');
}

function closeTagModal() {
    document.getElementById('tagModal').classList.add('hidden');
}

function editTag(id, name, color) {
    document.getElementById('modalTitle').textContent = 'Editar Tag';
    document.getElementById('tagId').value = id;
    document.getElementById('tagName').value = name;
    document.getElementById('tagColor').value = color;
    updateColorPreview(color);
    document.getElementById('tagModal').classList.remove('hidden');
}

async function saveTag(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const id = formData.get('id');
    const url = id ? '<?php echo APP_URL; ?>/tags/update/' + id : '<?php echo APP_URL; ?>/tags/create';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            closeTagModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erro ao salvar tag', 'error');
    }
}

async function deleteTag(id) {
    if (!confirmAction('Tem certeza que deseja deletar esta tag? Ela será removida de todos os contatos.')) return;
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/tags/delete/' + id, {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erro ao deletar tag', 'error');
    }
}

function updateColorPreview(color) {
    document.getElementById('colorPreview').style.backgroundColor = color;
}

document.getElementById('tagColor')?.addEventListener('input', function() {
    updateColorPreview(this.value);
});
</script>

<?php include 'views/layouts/footer.php'; ?>
