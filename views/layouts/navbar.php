<!-- Navbar -->
<nav class="bg-white shadow-lg fixed top-0 left-0 right-0" style="z-index: 50;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo e Menu -->
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo APP_URL; ?>/dashboard/index" class="flex items-center gap-2">
                        <?php
                        // Carregar branding apenas se não foi carregado ainda
                        if (!defined('COMPANY_NAME')) {
                            require_once __DIR__ . '/../../config/branding.php';
                        }
                        
                        $useDefaultLogo = defined('USE_DEFAULT_LOGO') ? USE_DEFAULT_LOGO : true;
                        $companyLogo = defined('COMPANY_LOGO') ? COMPANY_LOGO : '';
                        $companyName = defined('COMPANY_NAME') ? COMPANY_NAME : 'ZAPX';
                        
                        if ($useDefaultLogo || empty($companyLogo)):
                        ?>
                            <!-- Logo Padrão ZAPX -->
                            <div class="w-10 h-10 gradient-bg rounded-lg flex items-center justify-center">
                                <i class="fab fa-whatsapp text-white text-xl"></i>
                            </div>
                        <?php else: ?>
                            <!-- Logo Customizado -->
                            <img src="/<?php echo htmlspecialchars($companyLogo); ?>" 
                                 alt="Logo" 
                                 class="w-10 h-10 object-cover rounded-lg">
                        <?php endif; ?>
                        <span class="text-xl font-bold gradient-text hidden sm:block"><?php echo htmlspecialchars($companyName); ?></span>
                    </a>
                </div>
                
                <!-- Menu Desktop -->
                <div class="hidden md:ml-8 md:flex md:space-x-4">
                    <a href="<?php echo APP_URL; ?>/dashboard/index" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'text-purple-600 bg-purple-50' : 'text-gray-700 hover:text-purple-600 hover:bg-gray-50'; ?>">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                    
                    <a href="<?php echo APP_URL; ?>/contacts/index" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo (strpos($_SERVER['REQUEST_URI'], '/contacts') !== false) ? 'text-purple-600 bg-purple-50' : 'text-gray-700 hover:text-purple-600 hover:bg-gray-50'; ?>">
                        <i class="fas fa-address-book mr-2"></i>
                        Contatos
                    </a>
                    
                    <a href="<?php echo APP_URL; ?>/tags/index" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo (strpos($_SERVER['REQUEST_URI'], '/tags') !== false) ? 'text-purple-600 bg-purple-50' : 'text-gray-700 hover:text-purple-600 hover:bg-gray-50'; ?>">
                        <i class="fas fa-tags mr-2"></i>
                        Tags
                    </a>
                    
                    <a href="<?php echo APP_URL; ?>/campaign/index" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo (strpos($_SERVER['REQUEST_URI'], '/campaign') !== false) ? 'text-purple-600 bg-purple-50' : 'text-gray-700 hover:text-purple-600 hover:bg-gray-50'; ?>">
                        <i class="fas fa-bullhorn mr-2"></i>
                        Campanhas
                    </a>
                    
                    <a href="<?php echo APP_URL; ?>/campaign/reports" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo (strpos($_SERVER['REQUEST_URI'], '/campaign/reports') !== false) ? 'text-purple-600 bg-purple-50' : 'text-gray-700 hover:text-purple-600 hover:bg-gray-50'; ?>">
                        <i class="fas fa-chart-line mr-2"></i>
                        Relatórios
                    </a>
                </div>
            </div>
            
            <!-- Menu Usuário -->
            <div class="flex items-center gap-4">
                <!-- Perfil -->
                <div class="relative">
                    <button id="profileMenuBtn" onclick="toggleProfileMenu()" class="flex items-center gap-3 text-sm font-medium text-gray-700 hover:text-purple-600 focus:outline-none transition-colors">
                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold shadow-md">
                            <?php echo htmlspecialchars(strtoupper(substr($user['name'], 0, 1))); ?>
                        </div>
                        <span class="hidden md:inline-block font-semibold"><?php echo htmlspecialchars($user['name']); ?></span>
                        <i id="profileMenuIcon" class="fas fa-chevron-down text-xs transition-transform duration-200 hidden md:inline-block"></i>
                    </button>
                    
                    <!-- Dropdown -->
                    <div id="profileMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 hidden border border-gray-200" style="z-index: 9999 !important;">
                        <a href="<?php echo APP_URL; ?>/whatsapp/conectar" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fab fa-whatsapp mr-2 text-green-600"></i>
                            Conectar WhatsApp
                        </a>
                        <a href="<?php echo APP_URL; ?>/users/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-user mr-2"></i>
                            Meu Perfil
                        </a>
                        
                        <?php if (isset($user['is_admin']) && $user['is_admin']): ?>
                        <hr class="my-2">
                        <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Administração
                        </div>
                        <a href="<?php echo APP_URL; ?>/users/index" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 transition-colors">
                            <i class="fas fa-users mr-2 text-purple-600"></i>
                            Usuários
                        </a>
                        <a href="<?php echo APP_URL; ?>/instances/index" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 transition-colors">
                            <i class="fas fa-server mr-2 text-purple-600"></i>
                            Instâncias
                        </a>
                        <a href="<?php echo APP_URL; ?>/branding/index" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 transition-colors">
                            <i class="fas fa-palette mr-2 text-purple-600"></i>
                            Branding
                        </a>
                        <?php endif; ?>
                        
                        <hr class="my-2">
                        <a href="<?php echo APP_URL; ?>/auth/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Sair
                        </a>
                    </div>
                </div>
                
                <!-- Menu Mobile -->
                <button id="mobile-menu-btn" class="md:hidden text-gray-700 hover:text-purple-600 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Menu Mobile -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="<?php echo APP_URL; ?>/dashboard/index" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50">
                <i class="fas fa-home mr-2"></i>
                Dashboard
            </a>
            <a href="<?php echo APP_URL; ?>/contacts/index" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50">
                <i class="fas fa-address-book mr-2"></i>
                Contatos
            </a>
            <a href="<?php echo APP_URL; ?>/tags/index" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50">
                <i class="fas fa-tags mr-2"></i>
                Tags
            </a>
            <a href="<?php echo APP_URL; ?>/campaign/index" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50">
                <i class="fas fa-bullhorn mr-2"></i>
                Campanhas
            </a>
            <a href="<?php echo APP_URL; ?>/campaign/reports" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-purple-600 hover:bg-gray-50">
                <i class="fas fa-chart-line mr-2"></i>
                Relatórios
            </a>
        </div>
    </div>
</nav>

<script>
    // Toggle mobile menu
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });

    // Toggle profile menu
    function toggleProfileMenu() {
        const menu = document.getElementById('profileMenu');
        const icon = document.getElementById('profileMenuIcon');
        
        menu.classList.toggle('hidden');
        
        // Rotacionar ícone
        if (menu.classList.contains('hidden')) {
            icon.style.transform = 'rotate(0deg)';
        } else {
            icon.style.transform = 'rotate(180deg)';
        }
    }

    // Fechar menu ao clicar fora
    document.addEventListener('click', function(event) {
        const profileMenuBtn = document.getElementById('profileMenuBtn');
        const profileMenu = document.getElementById('profileMenu');
        
        if (profileMenuBtn && profileMenu && !profileMenuBtn.contains(event.target) && !profileMenu.contains(event.target)) {
            profileMenu.classList.add('hidden');
            document.getElementById('profileMenuIcon').style.transform = 'rotate(0deg)';
        }
    });

    // Fechar menu mobile ao clicar fora
    document.addEventListener('click', function(event) {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuBtn && mobileMenu && !mobileMenuBtn.contains(event.target) && !mobileMenu.contains(event.target)) {
            mobileMenu.classList.add('hidden');
        }
    });
</script>
