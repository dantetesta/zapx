    <!-- Footer -->
    <?php if (BRANDING_SHOW): ?>
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="text-center text-sm text-gray-500">
                <?php if (!empty(BRANDING_CUSTOM_TEXT)): ?>
                    <!-- Texto customizado -->
                    <p><?php echo BRANDING_CUSTOM_TEXT; ?></p>
                <?php else: ?>
                    <!-- Texto padrão configurável -->
                    <?php 
                    $parts = [];
                    
                    // Copyright + Nome do Sistema + Descrição
                    $copyright = '';
                    if (BRANDING_SYSTEM_NAME || BRANDING_SYSTEM_DESC) {
                        $year = !empty(BRANDING_COPYRIGHT_YEAR) ? BRANDING_COPYRIGHT_YEAR : date('Y');
                        $copyright .= '&copy; ' . $year;
                        
                        if (BRANDING_SYSTEM_NAME) {
                            $copyright .= ' ' . BRANDING_SYSTEM_NAME;
                        }
                        
                        if (BRANDING_SYSTEM_DESC) {
                            $copyright .= ' - ' . BRANDING_SYSTEM_DESC;
                        }
                        
                        $parts[] = $copyright;
                    }
                    
                    // Linha 1: Copyright
                    if (!empty($parts)) {
                        echo '<p>' . implode(' ', $parts) . '</p>';
                    }
                    
                    // Linha 2: Desenvolvedor + Versão
                    $line2 = [];
                    
                    if (BRANDING_SHOW_DEVELOPER && BRANDING_DEVELOPER_NAME) {
                        $dev = '';
                        if (BRANDING_DEVELOPER_PREFIX) {
                            $dev .= BRANDING_DEVELOPER_PREFIX . ' ';
                        }
                        
                        if (BRANDING_DEVELOPER_URL) {
                            $target = BRANDING_LINK_TARGET ? ' target="_blank"' : '';
                            $color = BRANDING_LINK_COLOR;
                            $dev .= '<a href="' . BRANDING_DEVELOPER_URL . '"' . $target . ' class="text-' . $color . '-600 hover:text-' . $color . '-700 font-medium">' . BRANDING_DEVELOPER_NAME . '</a>';
                        } else {
                            $dev .= '<span class="font-medium">' . BRANDING_DEVELOPER_NAME . '</span>';
                        }
                        
                        $line2[] = $dev;
                    }
                    
                    if (BRANDING_SHOW_VERSION && defined('APP_VERSION')) {
                        $version = '';
                        if (BRANDING_VERSION_PREFIX) {
                            $version .= BRANDING_VERSION_PREFIX . ' ';
                        }
                        $version .= APP_VERSION;
                        $line2[] = $version;
                    }
                    
                    if (!empty($line2)) {
                        echo '<p class="mt-1">' . implode(' | ', $line2) . '</p>';
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- Scripts -->
    <script>
        // Função global para mostrar notificações (usa o toast flutuante)
        function showNotification(message, type = 'info') {
            if (typeof window.showGlobalToast === 'function') {
                window.showGlobalToast(message, type);
                return;
            }
            
            // Fallback simples caso o componente não esteja disponível
            alert(message);
        }

        // Função para confirmar ações
        function confirmAction(message) {
            return confirm(message);
        }

        // Função para formatar telefone
        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else {
                value = value.replace(/^(\d*)/, '($1');
            }
            
            input.value = value;
        }
    </script>
</body>
</html>
