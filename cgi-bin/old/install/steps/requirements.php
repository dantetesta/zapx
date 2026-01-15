<?php
/**
 * Step 1: Verificação de Requisitos do Sistema
 */

// Verificar requisitos
$requirements = [
    'php_version' => [
        'name' => 'Versão do PHP',
        'required' => '7.4',
        'current' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4', '>=')
    ],
    'pdo' => [
        'name' => 'PDO (MySQL)',
        'required' => 'Habilitado',
        'current' => extension_loaded('pdo') && extension_loaded('pdo_mysql') ? 'Habilitado' : 'Desabilitado',
        'status' => extension_loaded('pdo') && extension_loaded('pdo_mysql')
    ],
    'curl' => [
        'name' => 'cURL',
        'required' => 'Habilitado',
        'current' => extension_loaded('curl') ? 'Habilitado' : 'Desabilitado',
        'status' => extension_loaded('curl')
    ],
    'mbstring' => [
        'name' => 'mbstring',
        'required' => 'Habilitado',
        'current' => extension_loaded('mbstring') ? 'Habilitado' : 'Desabilitado',
        'status' => extension_loaded('mbstring')
    ],
    'json' => [
        'name' => 'JSON',
        'required' => 'Habilitado',
        'current' => extension_loaded('json') ? 'Habilitado' : 'Desabilitado',
        'status' => extension_loaded('json')
    ],
    'fileinfo' => [
        'name' => 'Fileinfo',
        'required' => 'Habilitado',
        'current' => extension_loaded('fileinfo') ? 'Habilitado' : 'Desabilitado',
        'status' => extension_loaded('fileinfo')
    ]
];

// Verificar permissões de escrita
$directories = [
    'config' => [
        'path' => realpath(__DIR__ . '/../../config'),
        'writable' => is_writable(realpath(__DIR__ . '/../../config'))
    ],
    'uploads' => [
        'path' => realpath(__DIR__ . '/../../uploads'),
        'writable' => is_writable(realpath(__DIR__ . '/../../uploads'))
    ]
];

$allRequirementsMet = true;
foreach ($requirements as $req) {
    if (!$req['status']) {
        $allRequirementsMet = false;
        break;
    }
}

foreach ($directories as $dir) {
    if (!$dir['writable']) {
        $allRequirementsMet = false;
        break;
    }
}
?>

<div class="bg-white rounded-xl shadow-md p-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">
        <i class="fas fa-check-circle mr-2 text-purple-600"></i>
        Verificação de Requisitos
    </h2>
    <p class="text-gray-600 mb-8">Verificando se o servidor atende aos requisitos mínimos do sistema</p>

    <!-- Requisitos do PHP -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Requisitos do PHP</h3>
        <div class="space-y-3">
            <?php foreach ($requirements as $req): ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $req['status'] ? 'bg-green-100' : 'bg-red-100'; ?>">
                        <i class="fas <?php echo $req['status'] ? 'fa-check text-green-600' : 'fa-times text-red-600'; ?> text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900"><?php echo $req['name']; ?></p>
                        <p class="text-sm text-gray-600">
                            Requerido: <span class="font-semibold"><?php echo $req['required']; ?></span> | 
                            Atual: <span class="font-semibold <?php echo $req['status'] ? 'text-green-600' : 'text-red-600'; ?>"><?php echo $req['current']; ?></span>
                        </p>
                    </div>
                </div>
                <?php if ($req['status']): ?>
                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">OK</span>
                <?php else: ?>
                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">ERRO</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Permissões de Diretórios -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Permissões de Diretórios</h3>
        <div class="space-y-3">
            <?php foreach ($directories as $name => $dir): ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $dir['writable'] ? 'bg-green-100' : 'bg-red-100'; ?>">
                        <i class="fas <?php echo $dir['writable'] ? 'fa-check text-green-600' : 'fa-times text-red-600'; ?> text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900"><?php echo ucfirst($name); ?></p>
                        <p class="text-sm text-gray-600 font-mono"><?php echo $dir['path']; ?></p>
                    </div>
                </div>
                <?php if ($dir['writable']): ?>
                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">GRAVÁVEL</span>
                <?php else: ?>
                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">SEM PERMISSÃO</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$allRequirementsMet): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
        <div class="flex">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
            <div>
                <p class="font-semibold text-red-800">Requisitos não atendidos!</p>
                <p class="text-red-700 text-sm mt-1">
                    Corrija os problemas acima antes de continuar. 
                    <?php if (!$directories['config']['writable'] || !$directories['uploads']['writable']): ?>
                    <br>Para corrigir permissões, execute: <code class="bg-red-100 px-2 py-1 rounded">chmod 755 config uploads</code>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
        <div class="flex">
            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
            <div>
                <p class="font-semibold text-green-800">Todos os requisitos foram atendidos!</p>
                <p class="text-green-700 text-sm mt-1">Seu servidor está pronto para instalar o ZAPX.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Botões -->
    <div class="flex justify-between items-center pt-6 border-t">
        <div></div>
        <?php if ($allRequirementsMet): ?>
        <a href="?step=database" class="px-8 py-3 gradient-bg text-white font-semibold rounded-lg hover:opacity-90 transition">
            Próximo: Banco de Dados
            <i class="fas fa-arrow-right ml-2"></i>
        </a>
        <?php else: ?>
        <button onclick="location.reload()" class="px-8 py-3 bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400 transition">
            <i class="fas fa-sync-alt mr-2"></i>
            Verificar Novamente
        </button>
        <?php endif; ?>
    </div>
</div>
