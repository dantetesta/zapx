<?php 
$pageTitle = 'Histórico de Disparos - ' . APP_NAME;
include 'views/layouts/header.php'; 
include 'views/layouts/navbar.php';
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-history mr-2 gradient-text"></i>
                Histórico de Disparos
            </h1>
            <p class="mt-2 text-gray-600">Acompanhe todos os seus disparos realizados</p>
        </div>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['total'] ?? 0); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-paper-plane text-xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Enviados</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['sent'] ?? 0); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Falhas</p>
                        <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['failed'] ?? 0); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times-circle text-xl text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Taxa Sucesso</p>
                        <p class="text-3xl font-bold text-gray-900">
                            <?php echo $stats['total'] > 0 ? round(($stats['sent'] / $stats['total']) * 100, 1) : 0; ?>%
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Histórico -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Últimos 100 Disparos</h3>
            </div>
            
            <?php if (empty($history)): ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-inbox text-6xl mb-4 text-gray-300"></i>
                <p class="text-xl font-medium">Nenhum disparo realizado ainda</p>
                <p class="text-sm mt-2">Comece enviando sua primeira mensagem em massa!</p>
                <a href="<?php echo APP_URL; ?>/dispatch/index" 
                   class="inline-flex items-center mt-6 px-6 py-2 gradient-bg text-white font-medium rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Fazer Primeiro Disparo
                </a>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensagem</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($history as $item): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-semibold">
                                        <?php echo strtoupper(substr($item['contact_name'] ?: 'C', 0, 1)); ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($item['contact_name'] ?: 'Sem nome'); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <i class="fab fa-whatsapp text-green-600 mr-1"></i>
                                    <?php echo htmlspecialchars($item['contact_phone']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate" title="<?php echo htmlspecialchars($item['message']); ?>">
                                    <?php echo htmlspecialchars(substr($item['message'], 0, 50)) . (strlen($item['message']) > 50 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'sent' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800'
                                ];
                                $statusIcons = [
                                    'sent' => 'fa-check-circle',
                                    'failed' => 'fa-times-circle',
                                    'pending' => 'fa-clock'
                                ];
                                $statusLabels = [
                                    'sent' => 'Enviado',
                                    'failed' => 'Falhou',
                                    'pending' => 'Pendente'
                                ];
                                ?>
                                <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full <?php echo $statusColors[$item['status']]; ?>">
                                    <i class="fas <?php echo $statusIcons[$item['status']]; ?> mr-1"></i>
                                    <?php echo $statusLabels[$item['status']]; ?>
                                </span>
                                <?php if ($item['status'] === 'failed' && !empty($item['error_message'])): ?>
                                <div class="text-xs text-red-600 mt-1" title="<?php echo htmlspecialchars($item['error_message']); ?>">
                                    <?php echo htmlspecialchars(substr($item['error_message'], 0, 30)) . '...'; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?>
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

<?php include 'views/layouts/footer.php'; ?>
