<?php
/**
 * Página de Configuração de Branding
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-28 12:02:00
 */

$pageTitle = 'Configurações de Branding - ' . APP_NAME;
include 'views/layouts/header.php';
include 'views/layouts/navbar.php';
?>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-palette text-purple-600 mr-2"></i>
                Configurações de Branding
            </h1>
            <p class="text-gray-600">Personalize o logotipo e nome da sua empresa</p>
        </div>

        <!-- Mensagens -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Preview Atual -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-eye text-purple-600 mr-2"></i>
                Preview Atual
            </h2>
            
            <div class="flex items-center gap-4 p-6 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <?php if ($config['use_default_logo'] || empty($config['company_logo'])): ?>
                    <!-- Logo Padrão ZAPX -->
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fab fa-whatsapp text-white text-3xl"></i>
                    </div>
                <?php else: ?>
                    <!-- Logo Customizado -->
                    <img src="/<?php echo htmlspecialchars($config['company_logo']); ?>" 
                         alt="Logo" 
                         class="w-16 h-16 object-cover rounded-2xl flex-shrink-0">
                <?php endif; ?>
                
                <div>
                    <h3 class="text-2xl font-bold text-purple-600">
                        <?php echo htmlspecialchars($config['company_name']); ?>
                    </h3>
                    <p class="text-sm text-gray-500">Sistema de Disparo em Massa WhatsApp</p>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <form action="<?php echo APP_URL; ?>/branding/save" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            <?php require_once __DIR__ . '/../../core/CSRF.php'; echo CSRF::getTokenField(); ?>
            
            <!-- Nome da Empresa -->
            <div class="mb-6">
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-building text-purple-600 mr-1"></i>
                    Nome da Empresa
                </label>
                <input type="text" 
                       id="company_name" 
                       name="company_name" 
                       value="<?php echo htmlspecialchars($config['company_name']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       required
                       maxlength="50">
                <p class="text-xs text-gray-500 mt-1">Este nome aparecerá ao lado do logotipo</p>
            </div>

            <!-- Upload de Logo -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-image text-purple-600 mr-1"></i>
                    Logotipo da Empresa
                </label>
                
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-500 transition-colors">
                    <input type="file" 
                           id="company_logo" 
                           name="company_logo" 
                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                           class="hidden"
                           onchange="previewLogo(this)">
                    
                    <label for="company_logo" class="cursor-pointer">
                        <div id="logoPreview" class="mb-4">
                            <i class="fas fa-cloud-upload-alt text-6xl text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-600 mb-1">Clique para fazer upload do logotipo</p>
                        <p class="text-xs text-gray-500">JPG, PNG, GIF ou WEBP (máx. 2MB)</p>
                        <p class="text-xs text-purple-600 font-medium mt-2">Recomendado: Imagem quadrada (1:1)</p>
                    </label>
                </div>
                
                <?php if (!empty($config['company_logo'])): ?>
                    <div class="mt-3 flex items-center justify-between bg-gray-50 p-3 rounded">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                            Logo atual: <?php echo basename($config['company_logo']); ?>
                        </span>
                        <button type="button" 
                                onclick="deleteLogo()"
                                class="text-red-600 hover:text-red-700 text-sm">
                            <i class="fas fa-trash mr-1"></i>
                            Deletar
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Usar Logo Padrão -->
            <div class="mb-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" 
                           id="use_default_logo" 
                           name="use_default_logo" 
                           <?php echo $config['use_default_logo'] ? 'checked' : ''; ?>
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <span class="ml-2 text-sm text-gray-700">
                        Usar logotipo padrão do ZAPX
                    </span>
                </label>
                <p class="text-xs text-gray-500 mt-1 ml-6">Marque para usar o logo roxo do WhatsApp ao invés do logo customizado</p>
            </div>

            <!-- Botões -->
            <div class="flex gap-3">
                <button type="submit" 
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Configurações
                </button>
                <a href="<?php echo APP_URL; ?>/dashboard/index" 
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
            </div>
        </form>

        <!-- Dicas -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-800 mb-2">
                <i class="fas fa-lightbulb mr-1"></i>
                Dicas para um bom logotipo:
            </h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>✅ Use imagem quadrada (1:1) para melhor resultado</li>
                <li>✅ Tamanho recomendado: 512x512px ou maior</li>
                <li>✅ Fundo transparente (PNG) fica melhor</li>
                <li>✅ Evite textos muito pequenos na imagem</li>
                <li>✅ Teste em diferentes tamanhos antes de salvar</li>
            </ul>
        </div>
    </div>

    <script>
    // Preview do logo antes de fazer upload
    function previewLogo(input) {
        const preview = document.getElementById('logoPreview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `
                    <img src="${e.target.result}" 
                         alt="Preview" 
                         class="w-32 h-32 object-cover rounded-lg mx-auto border-2 border-purple-500">
                `;
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Deletar logo
    function deleteLogo() {
        if (!confirm('Tem certeza que deseja deletar o logo customizado?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo CSRF::getToken(); ?>');
        
        fetch('<?php echo APP_URL; ?>/branding/deleteLogo', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            alert('Erro ao deletar logo');
            console.error(error);
        });
    }
    </script>

<?php include 'views/layouts/footer.php'; ?>
