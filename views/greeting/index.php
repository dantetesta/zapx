<?php 
$pageTitle = 'Saudações Personalizadas - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-hand-wave mr-2 gradient-text"></i>
                Saudações Personalizadas
            </h1>
            <p class="text-gray-600 mt-2">
                Configure as saudações que serão usadas com o macro <code class="bg-purple-100 text-purple-700 px-2 py-1 rounded">{saudacao}</code> nos disparos.
            </p>
        </div>

        <!-- Instruções -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">
                <i class="fas fa-info-circle mr-2"></i>
                Como funciona
            </h3>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-blue-700 mb-2">Macros disponíveis:</h4>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li><code class="bg-white px-2 py-1 rounded">{periodo}</code> → Bom dia / Boa tarde / Boa noite (automático)</li>
                        <li><code class="bg-white px-2 py-1 rounded">{nome}</code> → Primeiro nome do contato (ou adjetivo se vazio)</li>
                        <li><code class="bg-white px-2 py-1 rounded">{numero}</code> → Número do telefone do contato</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-blue-700 mb-2">Funcionamento:</h4>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li>✅ A cada disparo, uma saudação diferente é usada</li>
                        <li>✅ As saudações rotacionam em ordem (fila circular)</li>
                        <li>✅ Se o contato não tiver nome, usa "tudo bem", "como vai", etc.</li>
                        <li>✅ O período é detectado automaticamente</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Adicionar Nova Saudação -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-plus-circle mr-2 text-green-600"></i>
                Adicionar Nova Saudação
            </h3>
            <form id="addForm" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" id="newTemplate" name="template" 
                           placeholder="Ex: {periodo}, {nome}, tudo bem?"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           maxlength="500">
                </div>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-medium rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-plus mr-2"></i>
                    Adicionar
                </button>
            </form>
            
            <!-- Preview em tempo real -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">Preview:</p>
                <p id="previewText" class="text-gray-800 font-medium">
                    <span class="text-gray-400 italic">Digite uma saudação para ver o preview...</span>
                </p>
            </div>
        </div>

        <!-- Lista de Saudações -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list mr-2 text-purple-600"></i>
                    Suas Saudações (<span id="greetingCount"><?php echo count($greetings); ?></span>)
                </h3>
                <button onclick="resetGreetings()" 
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-undo mr-1"></i>
                    Restaurar Padrão
                </button>
            </div>
            
            <div id="greetingsList" class="space-y-3">
                <?php foreach ($greetings as $index => $greeting): ?>
                <div class="greeting-item flex items-center gap-4 p-4 rounded-lg border-2 transition-all <?php echo $greeting['is_active'] ? 'bg-white border-gray-200' : 'bg-gray-100 border-gray-300 opacity-60'; ?>"
                     data-id="<?php echo $greeting['id']; ?>">
                    
                    <!-- Número de ordem -->
                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold text-sm">
                        <?php echo $index + 1; ?>
                    </div>
                    
                    <!-- Template -->
                    <div class="flex-1">
                        <input type="text" 
                               class="template-input w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent <?php echo !$greeting['is_active'] ? 'bg-gray-50' : ''; ?>"
                               value="<?php echo htmlspecialchars($greeting['template']); ?>"
                               data-original="<?php echo htmlspecialchars($greeting['template']); ?>"
                               <?php echo !$greeting['is_active'] ? 'disabled' : ''; ?>>
                    </div>
                    
                    <!-- Botões -->
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <!-- Toggle ativo -->
                        <button onclick="toggleGreeting(<?php echo $greeting['id']; ?>)" 
                                class="p-2 rounded-lg transition <?php echo $greeting['is_active'] ? 'bg-green-100 text-green-600 hover:bg-green-200' : 'bg-gray-200 text-gray-500 hover:bg-gray-300'; ?>"
                                title="<?php echo $greeting['is_active'] ? 'Desativar' : 'Ativar'; ?>">
                            <i class="fas <?php echo $greeting['is_active'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                        </button>
                        
                        <!-- Salvar (aparece quando editado) -->
                        <button onclick="saveGreeting(<?php echo $greeting['id']; ?>, this)" 
                                class="save-btn hidden p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition"
                                title="Salvar alterações">
                            <i class="fas fa-save"></i>
                        </button>
                        
                        <!-- Deletar -->
                        <button onclick="deleteGreeting(<?php echo $greeting['id']; ?>)" 
                                class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition"
                                title="Remover saudação">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($greetings)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>Nenhuma saudação configurada.</p>
                    <p class="text-sm">Adicione uma nova ou restaure as saudações padrão.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dica de uso -->
        <div class="mt-8 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl p-6">
            <h3 class="text-lg font-semibold mb-3">
                <i class="fas fa-lightbulb mr-2"></i>
                Como usar no disparo
            </h3>
            <p class="mb-4">No texto da mensagem, use o macro:</p>
            <code class="bg-white/20 px-4 py-2 rounded-lg text-lg">{saudacao}</code>
            <p class="mt-4 text-sm opacity-90">
                Exemplo: <code class="bg-white/10 px-2 py-1 rounded">{saudacao} Tenho uma novidade incrível para você!</code>
            </p>
            <p class="text-sm opacity-75 mt-2">
                → Resultado: "Boa tarde, João, tudo bem? Tenho uma novidade incrível para você!"
            </p>
        </div>
    </div>
</div>

<script>
// Preview em tempo real
document.getElementById('newTemplate').addEventListener('input', function() {
    updatePreview(this.value);
});

function updatePreview(template) {
    if (!template) {
        document.getElementById('previewText').innerHTML = '<span class="text-gray-400 italic">Digite uma saudação para ver o preview...</span>';
        return;
    }
    
    const hora = new Date().getHours();
    let periodo = 'Boa noite';
    if (hora >= 5 && hora < 12) periodo = 'Bom dia';
    else if (hora >= 12 && hora < 18) periodo = 'Boa tarde';
    
    const preview = template
        .replace(/{periodo}/g, periodo)
        .replace(/{nome}/g, 'João')
        .replace(/{numero}/g, '5511999999999');
    
    document.getElementById('previewText').textContent = preview;
}

// Detectar alterações nos inputs
document.querySelectorAll('.template-input').forEach(input => {
    input.addEventListener('input', function() {
        const item = this.closest('.greeting-item');
        const saveBtn = item.querySelector('.save-btn');
        
        if (this.value !== this.dataset.original) {
            saveBtn.classList.remove('hidden');
        } else {
            saveBtn.classList.add('hidden');
        }
    });
});

// Adicionar nova saudação
document.getElementById('addForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const template = document.getElementById('newTemplate').value.trim();
    if (!template) {
        alert('Digite uma saudação');
        return;
    }
    
    const formData = new FormData();
    formData.append('template', template);
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/greeting/store', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao adicionar');
        }
    } catch (error) {
        alert('Erro ao adicionar saudação');
    }
});

// Salvar saudação editada
async function saveGreeting(id, btn) {
    const item = btn.closest('.greeting-item');
    const input = item.querySelector('.template-input');
    const template = input.value.trim();
    
    if (!template) {
        alert('Template não pode ser vazio');
        return;
    }
    
    const formData = new FormData();
    formData.append('template', template);
    
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/greeting/update/${id}`, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            input.dataset.original = template;
            btn.classList.add('hidden');
            
            // Feedback visual
            item.classList.add('border-green-400');
            setTimeout(() => item.classList.remove('border-green-400'), 1000);
        } else {
            alert(data.message || 'Erro ao salvar');
        }
    } catch (error) {
        alert('Erro ao salvar saudação');
    }
}

// Toggle ativar/desativar
async function toggleGreeting(id) {
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/greeting/toggle/${id}`, {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao alterar status');
        }
    } catch (error) {
        alert('Erro ao alterar status');
    }
}

// Deletar saudação
async function deleteGreeting(id) {
    if (!confirm('Tem certeza que deseja remover esta saudação?')) {
        return;
    }
    
    try {
        const response = await fetch(`<?php echo APP_URL; ?>/greeting/delete/${id}`, {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao remover');
        }
    } catch (error) {
        alert('Erro ao remover saudação');
    }
}

// Restaurar saudações padrão
async function resetGreetings() {
    if (!confirm('⚠️ Isso vai REMOVER todas as suas saudações e restaurar as 30 saudações padrão.\n\nTem certeza?')) {
        return;
    }
    
    try {
        const response = await fetch('<?php echo APP_URL; ?>/greeting/reset', {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao restaurar');
        }
    } catch (error) {
        alert('Erro ao restaurar saudações');
    }
}
</script>

<?php include 'views/layouts/footer.php'; ?>
