<?php 
$pageTitle = 'Contatos - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-address-book mr-2 gradient-text"></i>
                    Meus Contatos
                </h1>
                <p class="mt-2 text-gray-600">
                    Gerencie sua lista de contatos do WhatsApp
                    <span class="ml-2 px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                        <?php echo number_format($totalContacts ?? 0); ?> contatos
                    </span>
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                <a href="<?php echo APP_URL; ?>/contacts/export<?php echo !empty($search) || !empty($selectedTag) ? '?' . http_build_query(['search' => $search, 'tag' => $selectedTag]) : ''; ?>" 
                   class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition inline-flex items-center">
                    <i class="fas fa-file-export mr-2"></i>
                    Exportar CSV
                </a>
                <button onclick="openImportModal()" 
                        class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-file-import mr-2"></i>
                    Importar CSV
                </button>
                <button onclick="openContactModal()" 
                        class="px-4 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-plus mr-2"></i>
                    Novo Contato
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <form method="GET" action="<?php echo APP_URL; ?>/contacts/index" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search ?? ''); ?>"
                           placeholder="Buscar por nome ou telefone..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div class="w-full md:w-64">
                    <select name="tag" 
                            onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Todas as tags</option>
                        <?php foreach ($tags as $tag): ?>
                        <option value="<?php echo $tag['id']; ?>" <?php echo ($selectedTag == $tag['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tag['name']); ?> (<?php echo $tag['contact_count']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </button>
            </form>
        </div>

        <!-- Barra de Ações em Massa -->
        <div id="bulkActionsBar" class="hidden bg-purple-50 border border-purple-200 rounded-xl shadow-md p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-purple-700 font-medium">
                        <i class="fas fa-check-square mr-2"></i>
                        <span id="selectedCount">0</span> contato(s) selecionado(s)
                    </span>
                </div>
                <div class="flex gap-3">
                    <button onclick="deselectAll()" 
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-2"></i>
                        Desmarcar Todos
                    </button>
                    <button onclick="deleteSelected()" 
                            class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash mr-2"></i>
                        Deletar Selecionados
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Contatos -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <?php if (empty($contacts)): ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-address-book text-6xl mb-4 text-gray-300"></i>
                <p class="text-xl font-medium">Nenhum contato encontrado</p>
                <p class="text-sm mt-2">Adicione seu primeiro contato ou importe uma lista CSV</p>
                <div class="mt-6 flex justify-center gap-3">
                    <button onclick="openContactModal()" class="px-6 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Adicionar Contato
                    </button>
                    <button onclick="openImportModal()" class="px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-file-import mr-2"></i>
                        Importar CSV
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" 
                                       class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500 cursor-pointer">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tags</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($contacts as $contact): ?>
                        <tr class="hover:bg-gray-50 transition" data-contact-id="<?php echo $contact['id']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="contact-checkbox w-4 h-4 text-purple-600 rounded focus:ring-purple-500 cursor-pointer" 
                                       value="<?php echo $contact['id']; ?>" onchange="updateSelection()">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-semibold">
                                        <?php echo strtoupper(substr($contact['name'] ?: 'C', 0, 1)); ?>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($contact['name'] ?: 'Sem nome'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($contact['phone']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap items-center gap-2 contact-tags">
                                    <?php 
                                    if (!empty($contact['tag_ids'])) {
                                        $tagIds = explode(',', $contact['tag_ids']);
                                        $tagNames = explode(',', $contact['tag_names']);
                                        $tagColors = explode(',', $contact['tag_colors']);
                                        
                                        foreach ($tagIds as $index => $tagId) {
                                            $tagName = $tagNames[$index] ?? '';
                                            $tagColor = $tagColors[$index] ?? '#3B82F6';
                                            echo '<span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full text-white" data-tag-id="' . htmlspecialchars($tagId) . '" style="background-color: ' . htmlspecialchars($tagColor) . '">' . htmlspecialchars($tagName) . '</span>';
                                        }
                                    }
                                    ?>
                                    <button onclick="openTagModal(<?php echo $contact['id']; ?>, '<?php echo htmlspecialchars($contact['name'] ?: 'Sem nome'); ?>')" 
                                            class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-purple-600 border border-purple-300 rounded-full hover:bg-purple-50 transition">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editContact(<?php echo $contact['id']; ?>, '<?php echo htmlspecialchars($contact['name']); ?>', '<?php echo htmlspecialchars($contact['phone']); ?>')" 
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteContact(<?php echo $contact['id']; ?>)" 
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-700">
                        Mostrando <span class="font-medium"><?php echo (($currentPage - 1) * $perPage) + 1; ?></span>
                        até <span class="font-medium"><?php echo min($currentPage * $perPage, $totalContacts); ?></span>
                        de <span class="font-medium"><?php echo $totalContacts; ?></span> contatos
                    </div>
                    
                    <nav class="flex items-center gap-2">
                        <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $selectedTag ? '&tag=' . $selectedTag : ''; ?>" 
                           class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $selectedTag ? '&tag=' . $selectedTag : ''; ?>" 
                           class="px-4 py-2 <?php echo $i === $currentPage ? 'gradient-bg text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'; ?> rounded-lg transition font-medium">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $selectedTag ? '&tag=' . $selectedTag : ''; ?>" 
                           class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Adicionar/Editar Contato -->
<div id="contactModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-900 mb-4">Novo Contato</h3>
        <form id="contactForm" onsubmit="saveContact(event)">
            <input type="hidden" id="contactId" name="id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome (opcional)</label>
                    <input type="text" id="contactName" name="name" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="João Silva">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefone *</label>
                    <input type="tel" id="contactPhone" name="phone" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="11 99999-9999">
                    <input type="hidden" id="contactPhoneFull" name="phone_full">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-globe mr-1"></i>
                        Selecione o país e digite o número com DDD
                    </p>
                </div>
                
                <!-- Tags do Contato -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tags mr-1"></i>
                        Tags (opcional)
                    </label>
                    <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto">
                        <div class="grid grid-cols-1 gap-2" id="contactTagsContainer">
                            <?php foreach ($tags as $tag): ?>
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer transition">
                                <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" 
                                       class="contact-tag-checkbox w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                                <span class="ml-3 flex-1 text-sm text-gray-900"><?php echo htmlspecialchars($tag['name']); ?></span>
                                <span class="px-2 py-1 text-xs rounded-full text-white" style="background-color: <?php echo htmlspecialchars($tag['color']); ?>">
                                    <?php echo $tag['contact_count']; ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($tags)): ?>
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-tag text-2xl mb-2"></i>
                            <p class="text-sm">Nenhuma tag criada ainda.</p>
                            <a href="<?php echo APP_URL; ?>/tags" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                Criar primeira tag
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Você pode selecionar múltiplas tags para classificar o contato
                    </p>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeContactModal()" 
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

<!-- Modal Importar CSV -->
<div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Importar Contatos CSV</h3>
        <form id="importForm" onsubmit="importCSV(event)">
            <div class="space-y-4">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <i class="fas fa-file-csv text-4xl text-gray-400 mb-3"></i>
                    <input type="file" id="csvFile" name="csv_file" accept=".csv,.txt" required class="hidden" onchange="updateFileName(this)">
                    <label for="csvFile" class="cursor-pointer">
                        <span class="text-purple-600 hover:text-purple-700 font-medium">Clique para selecionar</span>
                        <span class="text-gray-600"> ou arraste o arquivo</span>
                    </label>
                    <p id="fileName" class="mt-2 text-sm text-gray-500"></p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800 font-medium mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Formato do CSV:
                    </p>
                    <p class="text-xs text-blue-700 mb-3">
                        • <strong>Primeira linha:</strong> nome,telefone,tag<br>
                        • <strong>Nome:</strong> opcional<br>
                        • <strong>Telefone:</strong> obrigatório (COM DDI)<br>
                        • <strong>Tag:</strong> opcional (categoriza automaticamente)<br>
                        <br>
                        • <strong>Formatos aceitos de telefone:</strong><br>
                        &nbsp;&nbsp;- 5511999999999 (Brasil - DDI 55)<br>
                        &nbsp;&nbsp;- 14155551234 (EUA - DDI 1)<br>
                        &nbsp;&nbsp;- 442071234567 (UK - DDI 44)<br>
                        &nbsp;&nbsp;- +5511999999999 (com símbolo + opcional)
                    </p>
                    <div class="bg-amber-100 border border-amber-300 rounded p-2 mb-2">
                        <p class="text-xs text-amber-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>IMPORTANTE:</strong> Números devem incluir o DDI do país!<br>
                            Brasil: 55 | EUA/Canadá: 1 | Portugal: 351 | Argentina: 54
                        </p>
                    </div>
                    <div class="bg-purple-100 border border-purple-300 rounded p-2">
                        <p class="text-xs text-purple-800">
                            <i class="fas fa-tags mr-1"></i>
                            <strong>Tags:</strong> Use | ou ; para múltiplas tags<br>
                            Ex: VIP|Clientes ou Premium;Especial
                        </p>
                    </div>
                    <a href="<?php echo APP_URL; ?>/contacts/downloadTemplate" 
                       class="inline-flex items-center mt-3 text-sm text-blue-600 hover:text-blue-700 font-medium">
                        <i class="fas fa-download mr-1"></i>
                        Baixar template
                    </a>
                </div>
                
                <!-- Barra de Progresso -->
                <div id="importProgress" class="hidden">
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-purple-900">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Importando contatos...
                            </span>
                            <span id="progressPercent" class="text-sm font-bold text-purple-600">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div id="progressBar" class="gradient-bg h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <div class="mt-2 text-xs text-gray-600 text-center">
                            <span id="progressText">Preparando...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeImportModal()" id="cancelImportBtn"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit" id="importBtn"
                        class="flex-1 px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-upload mr-2"></i>
                    Importar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Gerenciar Tags -->
<div id="tagModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Gerenciar Tags</h3>
        <p id="tagContactName" class="text-sm text-gray-600 mb-4"></p>
        <div id="tagList" class="space-y-2 max-h-96 overflow-y-auto">
            <?php foreach ($tags as $tag): ?>
            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                <input type="checkbox" class="tag-checkbox w-4 h-4 text-purple-600 rounded focus:ring-purple-500" 
                       data-tag-id="<?php echo $tag['id']; ?>">
                <span class="ml-3 flex-1 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($tag['name']); ?></span>
                <span class="px-2 py-1 text-xs rounded-full text-white" style="background-color: <?php echo htmlspecialchars($tag['color']); ?>">
                    <?php echo $tag['contact_count']; ?>
                </span>
            </label>
            <?php endforeach; ?>
        </div>
        <div class="mt-6">
            <button onclick="closeTagModal()" 
                    class="w-full px-4 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
let currentContactId = null;

function openContactModal() {
    document.getElementById('modalTitle').textContent = 'Novo Contato';
    document.getElementById('contactForm').reset();
    
    // Limpar todas as tags
    document.querySelectorAll('.contact-tag-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    document.getElementById('contactModal').classList.remove('hidden');
}

function closeContactModal() {
    document.getElementById('contactModal').classList.add('hidden');
}

function editContact(id, name, phone) {
    document.getElementById('modalTitle').textContent = 'Editar Contato';
    document.getElementById('contactId').value = id;
    document.getElementById('contactName').value = name;
    document.getElementById('contactPhone').value = phone;
    
    // Limpar todas as tags primeiro
    document.querySelectorAll('.contact-tag-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Carregar tags do contato
    loadContactTagsForEdit(id);
    
    document.getElementById('contactModal').classList.remove('hidden');
}

async function saveContact(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const id = formData.get('id');
    const url = id ? '<?php echo APP_URL; ?>/contacts/update/' + id : '<?php echo APP_URL; ?>/contacts/create';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            closeContactModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erro ao salvar contato', 'error');
    }
}

async function deleteContact(id) {
    if (!confirmAction('Tem certeza que deseja deletar este contato?')) return;
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/contacts/delete/' + id, {
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
        showNotification('Erro ao deletar contato', 'error');
    }
}

function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}

function updateFileName(input) {
    const fileName = input.files[0]?.name || '';
    document.getElementById('fileName').textContent = fileName;
}

async function importCSV(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // Mostrar barra de progresso
    document.getElementById('importProgress').classList.remove('hidden');
    document.getElementById('importBtn').disabled = true;
    document.getElementById('cancelImportBtn').disabled = true;
    
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const progressText = document.getElementById('progressText');
    
    try {
        // Simular progresso inicial
        updateProgress(10, 'Enviando arquivo...');
        
        const response = await fetch('<?php echo APP_URL; ?>/contacts/import', {
            method: 'POST',
            body: formData
        });
        
        updateProgress(50, 'Processando contatos...');
        
        const data = await response.json();
        
        updateProgress(90, 'Finalizando...');
        
        if (data.success) {
            updateProgress(100, `✅ ${data.imported} contato(s) importado(s)!`);
            
            setTimeout(() => {
                showNotification(data.message, 'success');
                closeImportModal();
                resetImportForm();
                location.reload();
            }, 1500);
        } else {
            updateProgress(0, '❌ Erro na importação');
            showNotification(data.message, 'error');
            resetImportForm();
        }
    } catch (error) {
        updateProgress(0, '❌ Erro ao processar');
        showNotification('Erro ao importar CSV', 'error');
        resetImportForm();
    }
}

function updateProgress(percent, text) {
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const progressText = document.getElementById('progressText');
    
    progressBar.style.width = percent + '%';
    progressPercent.textContent = percent + '%';
    progressText.textContent = text;
}

function resetImportForm() {
    document.getElementById('importProgress').classList.add('hidden');
    document.getElementById('importBtn').disabled = false;
    document.getElementById('cancelImportBtn').disabled = false;
    document.getElementById('importForm').reset();
    document.getElementById('fileName').textContent = '';
    updateProgress(0, 'Preparando...');
}

function openTagModal(contactId, contactName) {
    currentContactId = contactId;
    document.getElementById('tagContactName').textContent = 'Contato: ' + contactName;
    document.getElementById('tagModal').classList.remove('hidden');
    
    // Carregar tags do contato
    loadContactTags(contactId);
}

function closeTagModal() {
    document.getElementById('tagModal').classList.add('hidden');
    currentContactId = null;
}

async function loadContactTags(contactId) {
    try {
        // Primeiro, desmarcar todas as tags
        document.querySelectorAll('.tag-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Buscar tags do contato
        const response = await fetch('<?php echo APP_URL; ?>/contacts/getContactTags', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `contact_id=${contactId}`
        });
        
        const data = await response.json();
        
        if (data.success && data.tags) {
            // Marcar as tags que o contato possui
            data.tags.forEach(tag => {
                const checkbox = document.querySelector(`[data-tag-id="${tag.id}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
    } catch (error) {
        console.error('Erro ao carregar tags do contato:', error);
        showNotification('Erro ao carregar tags do contato', 'error');
    }
}

// Função para carregar tags no modal de edição
async function loadContactTagsForEdit(contactId) {
    try {
        const response = await fetch('<?php echo APP_URL; ?>/contacts/getContactTags', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `contact_id=${contactId}`
        });
        
        const data = await response.json();
        
        if (data.success && data.tags) {
            // Marcar as tags que o contato possui no modal de edição
            data.tags.forEach(tag => {
                const checkbox = document.querySelector(`.contact-tag-checkbox[value="${tag.id}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
    } catch (error) {
        console.error('Erro ao carregar tags do contato:', error);
    }
}

// Event listeners para checkboxes de tags
document.querySelectorAll('.tag-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', async function() {
        const tagId = this.dataset.tagId;
        const isChecked = this.checked;
        const url = isChecked ? '<?php echo APP_URL; ?>/contacts/addTag' : '<?php echo APP_URL; ?>/contacts/removeTag';
        
        // Mostrar loading no checkbox
        const originalPointer = this.style.pointerEvents;
        this.style.pointerEvents = 'none';
        this.style.opacity = '0.6';
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `contact_id=${currentContactId}&tag_id=${tagId}`
            });
            const data = await response.json();
            
            if (data.success) {
                showNotification(data.message, 'success');
                // Atualizar a linha do contato na tabela em tempo real
                updateContactTagsInTable(currentContactId, tagId, isChecked);
            } else {
                showNotification(data.message, 'error');
                this.checked = !isChecked;
            }
        } catch (error) {
            showNotification('Erro ao atualizar tag', 'error');
            this.checked = !isChecked;
        } finally {
            // Restaurar estado do checkbox
            this.style.pointerEvents = originalPointer;
            this.style.opacity = '1';
        }
    });
});

// Função para atualizar tags na tabela sem reload
function updateContactTagsInTable(contactId, tagId, isAdded) {
    const contactRow = document.querySelector(`tr[data-contact-id="${contactId}"]`);
    if (!contactRow) return;
    
    const tagsCell = contactRow.querySelector('.contact-tags');
    if (!tagsCell) return;
    
    if (isAdded) {
        // Verificar se a tag já existe na tabela
        const existingTag = tagsCell.querySelector(`[data-tag-id="${tagId}"]`);
        if (existingTag) return; // Tag já existe
        
        // Buscar informações da tag no modal
        const tagCheckbox = document.querySelector(`#tagModal [data-tag-id="${tagId}"]`);
        if (tagCheckbox) {
            const tagLabel = tagCheckbox.closest('label');
            const tagNameElement = tagLabel.querySelector('.text-gray-900');
            const tagColorSpan = tagLabel.querySelector('[style*="background-color"]');
            
            if (tagNameElement && tagColorSpan) {
                const tagName = tagNameElement.textContent.trim();
                const tagColor = tagColorSpan.style.backgroundColor;
                
                // Criar nova tag
                const newTag = document.createElement('span');
                newTag.className = 'px-2 py-1 text-xs font-medium rounded-full text-white';
                newTag.style.backgroundColor = tagColor;
                newTag.textContent = tagName;
                newTag.setAttribute('data-tag-id', tagId);
                
                // Inserir antes do botão de adicionar
                const addButton = tagsCell.querySelector('button');
                if (addButton) {
                    tagsCell.insertBefore(newTag, addButton);
                } else {
                    tagsCell.appendChild(newTag);
                }
            }
        }
    } else {
        // Remover tag
        const tagSpan = tagsCell.querySelector(`[data-tag-id="${tagId}"]`);
        if (tagSpan) {
            tagSpan.remove();
        }
    }
}

// ============================================
// SELEÇÃO MÚLTIPLA E AÇÕES EM MASSA
// ============================================

// Selecionar/Desselecionar todos
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.contact-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelection();
}

// Atualizar contagem e exibir barra de ações
function updateSelection() {
    const checkboxes = document.querySelectorAll('.contact-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    // Atualizar contador
    document.getElementById('selectedCount').textContent = count;
    
    // Mostrar/ocultar barra de ações
    if (count > 0) {
        bulkBar.classList.remove('hidden');
    } else {
        bulkBar.classList.add('hidden');
    }
    
    // Atualizar estado do checkbox "selecionar todos"
    const allCheckboxes = document.querySelectorAll('.contact-checkbox');
    selectAllCheckbox.checked = allCheckboxes.length > 0 && count === allCheckboxes.length;
    selectAllCheckbox.indeterminate = count > 0 && count < allCheckboxes.length;
}

// Desmarcar todos
function deselectAll() {
    document.querySelectorAll('.contact-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateSelection();
}

// Deletar contatos selecionados
async function deleteSelected() {
    const checkboxes = document.querySelectorAll('.contact-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    if (ids.length === 0) {
        showNotification('Nenhum contato selecionado', 'warning');
        return;
    }
    
    const confirmMsg = `Tem certeza que deseja deletar ${ids.length} contato(s)?`;
    if (!confirmAction(confirmMsg)) return;
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/contacts/deleteMultiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro ao deletar contatos', 'error');
    }
}

// ============================================
// INTL-TEL-INPUT: Seletor Internacional de Telefone
// ============================================
let iti; // Instância global do intl-tel-input

// Carregar CSS da biblioteca
const linkCSS = document.createElement('link');
linkCSS.rel = 'stylesheet';
linkCSS.href = 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/css/intlTelInput.css';
document.head.appendChild(linkCSS);

// Carregar JS da biblioteca
const scriptJS = document.createElement('script');
scriptJS.src = 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/intlTelInput.min.js';
scriptJS.onload = initializePhoneInput;
document.head.appendChild(scriptJS);

function initializePhoneInput() {
    const input = document.querySelector("#contactPhone");
    
    iti = window.intlTelInput(input, {
        initialCountry: "br",  // Brasil como padrão
        preferredCountries: ["br", "us", "pt", "ar", "mx"],
        separateDialCode: true,  // Mostra o DDI separado
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/utils.js",
        formatOnDisplay: true,
        autoPlaceholder: "aggressive",
        customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
            return selectedCountryPlaceholder.replace(/[0-9]/g, "9");
        }
    });
    
    // Atualizar campo hidden com número completo ao digitar
    input.addEventListener('blur', updateFullPhoneNumber);
    input.addEventListener('countrychange', updateFullPhoneNumber);
}

function updateFullPhoneNumber() {
    if (iti) {
        const fullNumber = iti.getNumber(); // Retorna com + e DDI completo
        document.getElementById('contactPhoneFull').value = fullNumber;
        console.log('Número completo:', fullNumber);
    }
}

// Atualizar função de salvar contato para usar número completo
const originalSaveContact = saveContact;
saveContact = async function(e) {
    e.preventDefault();
    
    // Atualizar número completo antes de enviar
    updateFullPhoneNumber();
    
    const formData = new FormData(e.target);
    const fullPhone = document.getElementById('contactPhoneFull').value;
    
    // Remover o + do início se existir (opcional)
    const cleanPhone = fullPhone.replace(/^\+/, '');
    
    // Substituir o phone pelo número completo
    formData.set('phone', cleanPhone);
    
    const id = formData.get('id');
    const url = id ? '<?php echo APP_URL; ?>/contacts/update/' + id : '<?php echo APP_URL; ?>/contacts/create';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            closeContactModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Erro ao salvar contato', 'error');
    }
};

// Atualizar função de editar para carregar país correto
const originalEditContact = editContact;
editContact = function(id, name, phone) {
    document.getElementById('modalTitle').textContent = 'Editar Contato';
    document.getElementById('contactId').value = id;
    document.getElementById('contactName').value = name;
    
    // Aguardar inicialização do intl-tel-input
    setTimeout(() => {
        if (iti) {
            // Definir número (a biblioteca detecta o país automaticamente)
            iti.setNumber(phone);
        } else {
            document.getElementById('contactPhone').value = phone;
        }
    }, 100);
    
    // Limpar todas as tags primeiro
    document.querySelectorAll('.contact-tag-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Carregar tags do contato
    loadContactTagsForEdit(id);
    
    document.getElementById('contactModal').classList.remove('hidden');
};
</script>

<?php include 'views/layouts/footer.php'; ?>
