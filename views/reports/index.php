<?php
/**
 * View de Relatórios de Disparo
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-28 10:00:00
 */

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <i class="fas fa-chart-line text-purple-600"></i>
                Relatórios de Disparo
            </h1>
            <p class="mt-2 text-gray-600">Acompanhe o histórico completo dos seus disparos</p>
        </div>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total']); ?></p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-paper-plane text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Enviados -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Enviados</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['sent']); ?></p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Falhados -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Falhados</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo number_format($stats['failed']); ?></p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Taxa de Sucesso -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Taxa de Sucesso</p>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php 
                            $successRate = $stats['total'] > 0 ? ($stats['sent'] / $stats['total']) * 100 : 0;
                            echo number_format($successRate, 1) . '%'; 
                            ?>
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-percentage text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="<?php echo APP_URL; ?>/reports/index" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Todos</option>
                        <option value="sent" <?php echo $filters['status'] === 'sent' ? 'selected' : ''; ?>>Enviados</option>
                        <option value="failed" <?php echo $filters['status'] === 'failed' ? 'selected' : ''; ?>>Falhados</option>
                        <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pendentes</option>
                    </select>
                </div>

                <!-- Data Inicial -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <!-- Data Final -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Final</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <!-- Busca -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" 
                           placeholder="Nome ou telefone" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <!-- Botões -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </button>
                    <a href="<?php echo APP_URL; ?>/reports/index" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>

            <!-- Botões de Exportação -->
            <div class="mt-4 pt-4 border-t border-gray-200 flex gap-3">
                <button onclick="exportFile('CSV', '<?php echo APP_URL; ?>/reports/exportCSV?<?php echo http_build_query($filters); ?>')" 
                        id="btnExportCSV"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-file-csv mr-2" id="iconCSV"></i>
                    <span id="textCSV">Exportar CSV</span>
                </button>
                <button onclick="exportFile('PDF', '<?php echo APP_URL; ?>/reports/exportPDF?<?php echo http_build_query($filters); ?>')" 
                        id="btnExportPDF"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-file-pdf mr-2" id="iconPDF"></i>
                    <span id="textPDF">Exportar PDF</span>
                </button>
                
                <?php if ($isAdmin): ?>
                <button onclick="deleteAll()" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Limpar Tudo
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Resumo dos Resultados Filtrados -->
        <?php if (!empty($filters['status']) || !empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['search'])): ?>
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6 mb-6 border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-1">
                        <i class="fas fa-filter text-purple-600 mr-2"></i>
                        Resultados da Busca
                    </h3>
                    <p class="text-sm text-gray-600">
                        <?php echo $filteredStats['total']; ?> registro(s) encontrado(s) com os filtros aplicados
                    </p>
                </div>
                
                <div class="flex gap-4">
                    <!-- Enviados -->
                    <div class="bg-white rounded-lg px-4 py-3 shadow-sm border border-green-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-green-600"><?php echo $filteredStats['sent']; ?></p>
                                <p class="text-xs text-gray-600">Enviados</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Falhados -->
                    <div class="bg-white rounded-lg px-4 py-3 shadow-sm border border-red-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-times-circle text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-red-600"><?php echo $filteredStats['failed']; ?></p>
                                <p class="text-xs text-gray-600">Falhados</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pendentes -->
                    <div class="bg-white rounded-lg px-4 py-3 shadow-sm border border-yellow-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-yellow-600"><?php echo $filteredStats['pending']; ?></p>
                                <p class="text-xs text-gray-600">Pendentes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Barra de Ações em Massa -->
        <div id="bulkActionsBar" class="hidden bg-purple-600 text-white rounded-lg p-4 mb-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <i class="fas fa-check-circle text-2xl"></i>
                    <div>
                        <p class="font-semibold"><span id="selectedCount">0</span> registro(s) selecionado(s)</p>
                        <p class="text-sm text-purple-200">Selecione os registros que deseja deletar</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="unselectAll()" 
                            class="px-4 py-2 bg-purple-700 hover:bg-purple-800 rounded-lg transition">
                        <i class="fas fa-times mr-2"></i>
                        Desmarcar Todos
                    </button>
                    <button onclick="deleteSelected()" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg transition">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Deletar Selecionados
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabela de Relatórios -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" 
                                       id="selectAll" 
                                       onchange="toggleSelectAll(this)"
                                       class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data/Hora
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contato
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mensagem
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($dispatches)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                                    <p>Nenhum disparo encontrado</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dispatches as $dispatch): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" 
                                               class="dispatch-checkbox w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500" 
                                               value="<?php echo $dispatch['id']; ?>"
                                               onchange="updateSelection()">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y H:i', strtotime($dispatch['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($dispatch['contact_name'] ?: 'Sem nome'); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($dispatch['contact_phone']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-md">
                                        <?php
                                        // Tentar decodificar JSON para extrair mídia e mensagem
                                        $messageData = json_decode($dispatch['message'], true);
                                        $hasMedia = false;
                                        $mediaType = '';
                                        $textMessage = '';
                                        
                                        if (is_array($messageData)) {
                                            // É JSON - extrair informações
                                            if (isset($messageData['media_type'])) {
                                                $hasMedia = true;
                                                $mediaType = $messageData['media_type'];
                                            }
                                            $textMessage = $messageData['message'] ?? '';
                                        } else {
                                            // É texto simples
                                            $textMessage = $dispatch['message'];
                                        }
                                        ?>
                                        
                                        <div class="flex items-start gap-2">
                                            <?php if ($hasMedia): ?>
                                                <!-- Thumbnail da mídia -->
                                                <div class="flex-shrink-0">
                                                    <?php if ($mediaType === 'image' && !empty($dispatch['thumbnail_path']) && file_exists(__DIR__ . '/../../' . $dispatch['thumbnail_path'])): ?>
                                                        <!-- Thumbnail real de imagem -->
                                                        <img src="/<?php echo htmlspecialchars($dispatch['thumbnail_path']); ?>" 
                                                             alt="Thumbnail" 
                                                             class="w-10 h-10 object-cover rounded border border-gray-200">
                                                    <?php else: ?>
                                                        <!-- Ícones por tipo de mídia -->
                                                        <?php if ($mediaType === 'image'): ?>
                                                            <div class="w-10 h-10 bg-purple-100 rounded flex items-center justify-center">
                                                                <i class="fas fa-image text-purple-600"></i>
                                                            </div>
                                                        <?php elseif ($mediaType === 'video'): ?>
                                                            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded flex items-center justify-center relative">
                                                                <i class="fas fa-play text-white text-sm"></i>
                                                            </div>
                                                        <?php elseif ($mediaType === 'audio'): ?>
                                                            <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center">
                                                                <i class="fas fa-microphone text-green-600"></i>
                                                            </div>
                                                        <?php elseif ($mediaType === 'document'): ?>
                                                            <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center">
                                                                <i class="fas fa-file-alt text-blue-600"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Mensagem de texto -->
                                            <div class="flex-1 min-w-0">
                                                <?php if ($hasMedia): ?>
                                                    <span class="text-xs text-gray-500 font-medium">
                                                        <?php 
                                                        $mediaLabels = [
                                                            'image' => '📷 Imagem',
                                                            'video' => '🎥 Vídeo',
                                                            'audio' => '🎵 Áudio',
                                                            'document' => '📄 Documento'
                                                        ];
                                                        echo $mediaLabels[$mediaType] ?? 'Mídia';
                                                        ?>
                                                    </span>
                                                    <br>
                                                <?php endif; ?>
                                                <div class="truncate" title="<?php echo htmlspecialchars($textMessage); ?>">
                                                    <?php 
                                                    if (!empty($textMessage)) {
                                                        echo htmlspecialchars(mb_substr($textMessage, 0, 50));
                                                        if (strlen($textMessage) > 50) echo '...';
                                                    } else {
                                                        echo '<span class="text-gray-400 italic">Sem legenda</span>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($dispatch['status'] === 'sent'): ?>
                                            <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Enviado
                                            </span>
                                        <?php elseif ($dispatch['status'] === 'failed'): ?>
                                            <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" 
                                                  title="<?php echo htmlspecialchars($dispatch['error_message']); ?>">
                                                <i class="fas fa-times-circle mr-1"></i> Falhou
                                            </span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i> Pendente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button onclick="viewDetails(<?php echo $dispatch['id']; ?>)" 
                                                class="text-purple-600 hover:text-purple-900">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($currentPage > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $currentPage - 1])); ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Anterior
                            </a>
                        <?php endif; ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $currentPage + 1])); ?>" 
                               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Próxima
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium"><?php echo (($currentPage - 1) * $perPage) + 1; ?></span>
                                até
                                <span class="font-medium"><?php echo min($currentPage * $perPage, $totalDispatches); ?></span>
                                de
                                <span class="font-medium"><?php echo $totalDispatches; ?></span>
                                resultados
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <?php
                                // Paginação inteligente com elipses
                                $range = 2; // Quantas páginas mostrar antes/depois da atual
                                $showPages = [];
                                
                                // Sempre mostrar primeira página
                                $showPages[] = 1;
                                
                                // Páginas ao redor da atual
                                for ($i = max(2, $currentPage - $range); $i <= min($totalPages - 1, $currentPage + $range); $i++) {
                                    $showPages[] = $i;
                                }
                                
                                // Sempre mostrar última página
                                if ($totalPages > 1) {
                                    $showPages[] = $totalPages;
                                }
                                
                                // Remover duplicatas e ordenar
                                $showPages = array_unique($showPages);
                                sort($showPages);
                                
                                // Renderizar com elipses
                                $lastPage = 0;
                                foreach ($showPages as $page):
                                    // Adicionar elipse se houver gap
                                    if ($page - $lastPage > 1):
                                ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                        ...
                                    </span>
                                <?php endif; ?>
                                
                                <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $page])); ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium <?php echo $page === $currentPage ? 'bg-purple-600 text-white border-purple-600 z-10' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                                    <?php echo $page; ?>
                                </a>
                                
                                <?php
                                    $lastPage = $page;
                                endforeach;
                                ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
// Função para exportar com loading
function exportFile(type, url) {
    console.log('exportFile chamado:', type, url);
    
    const btnId = 'btnExport' + type;
    const iconId = 'icon' + type;
    const textId = 'text' + type;
    
    console.log('IDs:', btnId, iconId, textId);
    
    const btn = document.getElementById(btnId);
    const icon = document.getElementById(iconId);
    const text = document.getElementById(textId);
    
    console.log('Elementos:', btn, icon, text);
    
    // Verificar se elementos existem
    if (!btn || !icon || !text) {
        console.error('Elementos não encontrados!');
        // Fallback: abrir em nova aba
        window.open(url, '_blank');
        return;
    }
    
    // Desabilitar botão
    btn.disabled = true;
    btn.style.opacity = '0.5';
    btn.style.cursor = 'not-allowed';
    
    // Mudar para loading (forçar com setAttribute)
    icon.setAttribute('class', 'fas fa-spinner fa-spin mr-2');
    icon.style.animation = 'spin 1s linear infinite';
    text.textContent = 'Gerando ' + type + '...';
    
    console.log('Loading ativado');
    
    // Criar iframe oculto para download
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    document.body.appendChild(iframe);
    
    // Restaurar botão após 3 segundos (tempo estimado de geração)
    setTimeout(() => {
        btn.disabled = false;
        btn.style.opacity = '';
        btn.style.cursor = '';
        
        if (type === 'CSV') {
            icon.setAttribute('class', 'fas fa-file-csv mr-2');
            icon.style.animation = '';
            text.textContent = 'Exportar CSV';
        } else {
            icon.setAttribute('class', 'fas fa-file-pdf mr-2');
            icon.style.animation = '';
            text.textContent = 'Exportar PDF';
        }
        
        console.log('Loading desativado');
        
        // Remover iframe
        if (iframe.parentNode) {
            document.body.removeChild(iframe);
        }
    }, 3000);
}

function viewDetails(id) {
    alert('Detalhes do disparo #' + id + '\n\nFuncionalidade em desenvolvimento...');
}

// Seleção múltipla
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.dispatch-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('.dispatch-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectAll = document.getElementById('selectAll');
    const totalCheckboxes = document.querySelectorAll('.dispatch-checkbox').length;
    
    // Atualizar contador
    document.getElementById('selectedCount').textContent = count;
    
    // Mostrar/esconder barra
    if (count > 0) {
        bulkBar.classList.remove('hidden');
    } else {
        bulkBar.classList.add('hidden');
    }
    
    // Atualizar estado do "Selecionar Todos"
    if (count === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } else if (count === totalCheckboxes) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    }
}

function unselectAll() {
    const checkboxes = document.querySelectorAll('.dispatch-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateSelection();
}

function deleteSelected() {
    const checkboxes = document.querySelectorAll('.dispatch-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        alert('Selecione pelo menos um registro');
        return;
    }
    
    if (!confirm(`⚠️ Tem certeza que deseja deletar ${ids.length} registro(s)?\n\nIsso também deletará as thumbnails associadas.`)) {
        return;
    }
    
    // Criar FormData
    const formData = new FormData();
    ids.forEach(id => {
        formData.append('ids[]', id);
    });
    // Adicionar token CSRF
    formData.append('csrf_token', '<?php require_once __DIR__ . "/../../core/CSRF.php"; echo CSRF::getToken(); ?>');
    
    // Enviar requisição
    fetch('<?php echo APP_URL; ?>/reports/deleteMultiple', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na requisição: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro completo:', error);
        alert('Erro ao deletar registros: ' + error.message);
    });
}

function deleteAll() {
    if (!confirm('⚠️⚠️⚠️ ATENÇÃO! ⚠️⚠️⚠️\n\nVocê está prestes a DELETAR TODOS OS REGISTROS de disparo!\n\nIsso também deletará TODAS as thumbnails.\n\nEsta ação é IRREVERSÍVEL!\n\nTem certeza absoluta?')) {
        return;
    }
    
    if (!confirm('Última confirmação: DELETAR TUDO?')) {
        return;
    }
    
    // Criar FormData com token CSRF
    const formData = new FormData();
    formData.append('csrf_token', '<?php require_once __DIR__ . "/../../core/CSRF.php"; echo CSRF::getToken(); ?>');
    
    // Enviar requisição
    fetch('<?php echo APP_URL; ?>/reports/deleteAll', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erro ao limpar registros: ' + error);
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
