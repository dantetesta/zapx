<?php
// Carregar branding
if (!defined('COMPANY_NAME')) {
    require_once __DIR__ . '/../../config/branding.php';
}

$companyName = defined('COMPANY_NAME') ? COMPANY_NAME : 'ZAPX';
$useDefaultLogo = defined('USE_DEFAULT_LOGO') ? USE_DEFAULT_LOGO : true;
$companyLogo = defined('COMPANY_LOGO') ? COMPANY_LOGO : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($companyName); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php if (defined('RECAPTCHA_ENABLED') && RECAPTCHA_ENABLED && defined('RECAPTCHA_SITE_KEY') && !empty(RECAPTCHA_SITE_KEY)): ?>
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">

<!-- Botão Voltar -->
<div class="absolute top-4 left-4">
    <a href="<?php echo APP_URL; ?>/home/index" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition backdrop-blur-sm">
        <i class="fas fa-arrow-left mr-2"></i>
        Voltar
    </a>
</div>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Logo e Título -->
        <div class="text-center mb-6">
            <?php if ($useDefaultLogo): ?>
                <div class="mx-auto w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl mb-3">
                    <i class="fas fa-bolt text-4xl gradient-text"></i>
                </div>
            <?php else: ?>
                <div class="mx-auto w-16 h-16 mb-3">
                    <img src="<?php echo APP_URL; ?>/<?php echo htmlspecialchars($companyLogo); ?>" 
                         alt="<?php echo htmlspecialchars($companyName); ?>" 
                         class="w-full h-full object-contain">
                </div>
            <?php endif; ?>
            <h1 class="text-3xl font-bold text-white mb-1"><?php echo htmlspecialchars($companyName); ?></h1>
            <p class="text-purple-100 text-sm">Sistema de Disparo em Massa WhatsApp</p>
        </div>

        <!-- Card de Login -->
        <div class="bg-white rounded-2xl shadow-xl p-6 backdrop-blur-lg">
            <h2 class="text-2xl font-bold text-gray-900 mb-1 text-center">Bem-vindo!</h2>
            <p class="text-gray-500 text-center mb-4 text-sm">Entre com suas credenciais</p>

            <?php if (isset($error)): ?>
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo APP_URL; ?>/auth/login" class="space-y-4">
                <!-- CSRF Token -->
                <?php echo CSRF::getTokenField(); ?>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-envelope mr-1"></i>
                        Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>"
                           required 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="seu@email.com">
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock mr-1"></i>
                        Senha
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                               placeholder="••••••••">
                        <button type="button" 
                                onclick="togglePassword()" 
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition text-sm">
                            <i id="toggleIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <?php if (defined('RECAPTCHA_ENABLED') && RECAPTCHA_ENABLED && defined('RECAPTCHA_SITE_KEY') && !empty(RECAPTCHA_SITE_KEY)): ?>
                <!-- Google reCAPTCHA -->
                <div class="flex justify-center">
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                </div>
                <?php endif; ?>

                <!-- Botão -->
                <button type="submit" class="w-full gradient-bg text-white font-semibold py-2.5 text-sm rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Entrar
                </button>
            </form>

            <!-- Link Recuperar Senha -->
            <div class="mt-4 text-center">
                <a href="<?php echo APP_URL; ?>/forgot-password.php" class="text-xs text-purple-600 hover:text-purple-800 font-medium transition">
                    <i class="fas fa-key mr-1"></i>
                    Esqueci minha senha
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-xs text-white/80">
                © 2025 <?php echo htmlspecialchars($companyName); ?> - Desenvolvido por <a href="https://dantetesta.com.br" target="_blank" class="font-semibold hover:text-white transition underline">Dante Testa</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>
