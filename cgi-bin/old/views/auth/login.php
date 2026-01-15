<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ZAPX</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="gradient-bg">

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Logo e Título -->
        <div class="text-center mb-8">
            <div class="mx-auto w-24 h-24 bg-white rounded-3xl flex items-center justify-center shadow-2xl mb-4 animate-float">
                <i class="fas fa-bolt text-6xl gradient-text"></i>
            </div>
            <h1 class="text-5xl font-bold text-white mb-2">ZAPX</h1>
            <p class="text-purple-100 text-lg">Sistema de Disparo em Massa WhatsApp</p>
        </div>

        <!-- Card de Login -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 backdrop-blur-lg">
            <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Bem-vindo de volta!</h2>
            <p class="text-gray-500 text-center mb-6">Entre com suas credenciais</p>

            <?php if (isset($error)): ?>
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo APP_URL; ?>/auth/login" class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1"></i>
                        Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>"
                           required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="seu@email.com">
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1"></i>
                        Senha
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                               placeholder="••••••••">
                        <button type="button" 
                                onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition">
                            <i id="toggleIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Botão -->
                <button type="submit" class="w-full gradient-bg text-white font-bold py-4 rounded-lg hover:opacity-90 hover:shadow-xl transition-all transform hover:scale-[1.02]">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Entrar no Sistema
                </button>
            </form>

            <!-- Link Recuperar Senha -->
            <div class="mt-6 text-center">
                <a href="<?php echo APP_URL; ?>/forgot-password.php" class="text-sm text-purple-600 hover:text-purple-800 font-medium transition">
                    <i class="fas fa-key mr-1"></i>
                    Esqueci minha senha
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-sm text-white/80">
                © 2025 ZAPX - Desenvolvido por <a href="https://dantetesta.com.br" target="_blank" class="font-bold hover:text-white transition underline">Dante Testa</a>
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
