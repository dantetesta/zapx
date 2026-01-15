<?php
/**
 * Instalador Profissional ZAPX
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 21:12:00
 */

session_start();

// Verificar se já está instalado
if (file_exists('../config/installed.lock')) {
    header('Location: ../');
    exit;
}

$step = $_GET['step'] ?? 'requirements';
$error = $_SESSION['install_error'] ?? null;
$success = $_SESSION['install_success'] ?? null;
unset($_SESSION['install_error'], $_SESSION['install_success']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador ZAPX - Sistema de Disparo em Massa WhatsApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .step-active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .step-completed {
            background: #10b981;
            color: white;
        }
        .step-pending {
            background: #e5e7eb;
            color: #9ca3af;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <div class="gradient-bg text-white py-6">
            <div class="max-w-4xl mx-auto px-4">
                <h1 class="text-3xl font-bold flex items-center">
                    <i class="fas fa-rocket mr-3"></i>
                    Instalador ZAPX
                </h1>
                <p class="mt-2 text-purple-100">Sistema de Disparo em Massa WhatsApp</p>
            </div>
        </div>

        <!-- Steps -->
        <div class="bg-white border-b">
            <div class="max-w-4xl mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <?php
                    $steps = [
                        'requirements' => ['icon' => 'fa-check-circle', 'title' => 'Requisitos'],
                        'database' => ['icon' => 'fa-database', 'title' => 'Banco de Dados'],
                        'config' => ['icon' => 'fa-cog', 'title' => 'Configurações'],
                        'admin' => ['icon' => 'fa-user-shield', 'title' => 'Administrador'],
                        'finish' => ['icon' => 'fa-flag-checkered', 'title' => 'Finalizar']
                    ];
                    
                    $stepOrder = array_keys($steps);
                    $currentIndex = array_search($step, $stepOrder);
                    
                    foreach ($steps as $key => $data) {
                        $index = array_search($key, $stepOrder);
                        $class = 'step-pending';
                        if ($index < $currentIndex) $class = 'step-completed';
                        if ($index === $currentIndex) $class = 'step-active';
                        
                        echo '<div class="flex flex-col items-center">';
                        echo '<div class="w-12 h-12 rounded-full flex items-center justify-center ' . $class . ' mb-2">';
                        echo '<i class="fas ' . $data['icon'] . ' text-xl"></i>';
                        echo '</div>';
                        echo '<span class="text-xs font-medium text-gray-600">' . $data['title'] . '</span>';
                        echo '</div>';
                        
                        if ($key !== 'finish') {
                            echo '<div class="flex-1 h-1 bg-gray-200 mx-2 mt-6"></div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 py-12">
            <div class="max-w-4xl mx-auto px-4">
                <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                        <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                        <p class="text-green-700"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                // Incluir a página do step atual
                $stepFile = __DIR__ . '/steps/' . $step . '.php';
                if (file_exists($stepFile)) {
                    include $stepFile;
                } else {
                    include __DIR__ . '/steps/requirements.php';
                }
                ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-white border-t py-6">
            <div class="max-w-4xl mx-auto px-4 text-center text-gray-600">
                <p>
                    <i class="fas fa-code mr-2"></i>
                    Desenvolvido por <a href="https://dantetesta.com.br" target="_blank" class="text-purple-600 hover:text-purple-700 font-semibold">Dante Testa</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
