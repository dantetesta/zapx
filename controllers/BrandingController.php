<?php
/**
 * Controller de Branding (Logo e Nome da Empresa)
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-28 12:02:00
 */

class BrandingController extends Controller {
    
    /**
     * Página de configuração de branding (apenas admin)
     */
    public function index() {
        $this->requireAdmin();
        
        // Carregar configurações atuais
        $brandingFile = __DIR__ . '/../config/branding.php';
        $content = file_get_contents($brandingFile);
        
        // Carregar constantes do branding
        require_once $brandingFile;
        
        $currentConfig = [
            'company_name' => defined('COMPANY_NAME') ? COMPANY_NAME : 'ZAPX',
            'company_logo' => defined('COMPANY_LOGO') ? COMPANY_LOGO : '',
            'use_default_logo' => defined('USE_DEFAULT_LOGO') ? USE_DEFAULT_LOGO : true
        ];
        
        $this->view('branding/index', [
            'user' => $this->getCurrentUser(),
            'config' => $currentConfig
        ]);
    }
    
    /**
     * Salvar configurações de branding
     */
    public function save() {
        $this->requireAdmin();
        
        // Validar CSRF
        require_once __DIR__ . '/../core/CSRF.php';
        CSRF::validateOrDie();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        $companyName = $_POST['company_name'] ?? 'ZAPX';
        $useDefaultLogo = isset($_POST['use_default_logo']) ? 'true' : 'false';
        
        // Processar upload de logo
        $logoPath = '';
        $hasNewLogo = false;
        
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->processLogoUpload($_FILES['company_logo']);
            if ($uploadResult['success']) {
                $logoPath = $uploadResult['path'];
                $hasNewLogo = true;
                // Se fez upload de logo, desabilitar logo padrão
                $useDefaultLogo = 'false';
            } else {
                $this->json(['success' => false, 'message' => $uploadResult['message']], 400);
                return;
            }
        } else {
            // Manter logo atual se não enviou novo
            $brandingFile = __DIR__ . '/../config/branding.php';
            $content = file_get_contents($brandingFile);
            preg_match("/define\('COMPANY_LOGO', '(.*)'\);/", $content, $currentLogo);
            $logoPath = $currentLogo[1] ?? '';
        }
        
        // Atualizar arquivo branding.php
        $result = $this->updateBrandingFile($companyName, $logoPath, $useDefaultLogo);
        
        if ($result) {
            $_SESSION['success'] = 'Configurações de branding atualizadas com sucesso!';
            $this->redirect('branding/index');
        } else {
            $_SESSION['error'] = 'Erro ao atualizar configurações.';
            $this->redirect('branding/index');
        }
    }
    
    /**
     * Processar upload de logo
     */
    private function processLogoUpload($file) {
        // Validar tipo de arquivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $file['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP.'];
        }
        
        // Validar tamanho (máx 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Arquivo muito grande. Máximo 2MB.'];
        }
        
        // Validar dimensões (recomendado 1:1)
        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            return ['success' => false, 'message' => 'Arquivo de imagem inválido.'];
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Avisar se não for quadrado (mas permitir)
        $isSquare = abs($width - $height) < 10; // Tolerância de 10px
        
        // Criar pasta se não existir
        $uploadDir = __DIR__ . '/../uploads/branding/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Gerar nome único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        
        // Mover arquivo
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'path' => 'uploads/branding/' . $filename,
                'is_square' => $isSquare
            ];
        } else {
            return ['success' => false, 'message' => 'Erro ao fazer upload do arquivo.'];
        }
    }
    
    /**
     * Atualizar arquivo branding.php
     */
    private function updateBrandingFile($companyName, $logoPath, $useDefaultLogo) {
        $brandingFile = __DIR__ . '/../config/branding.php';
        
        // Ler conteúdo atual
        $content = file_get_contents($brandingFile);
        
        // Escapar aspas simples
        $companyName = str_replace("'", "\\'", $companyName);
        $logoPath = str_replace("'", "\\'", $logoPath);
        
        // Substituir valores
        $content = preg_replace(
            "/define\('COMPANY_NAME', '.*'\);/",
            "define('COMPANY_NAME', '$companyName');",
            $content
        );
        
        $content = preg_replace(
            "/define\('COMPANY_LOGO', '.*'\);/",
            "define('COMPANY_LOGO', '$logoPath');",
            $content
        );
        
        $content = preg_replace(
            "/define\('USE_DEFAULT_LOGO', .*\);/",
            "define('USE_DEFAULT_LOGO', $useDefaultLogo);",
            $content
        );
        
        // Salvar arquivo
        return file_put_contents($brandingFile, $content) !== false;
    }
    
    /**
     * Deletar logo customizado
     */
    public function deleteLogo() {
        $this->requireAdmin();
        
        // Validar CSRF
        require_once __DIR__ . '/../core/CSRF.php';
        CSRF::validateOrDie();
        
        $brandingFile = __DIR__ . '/../config/branding.php';
        $content = file_get_contents($brandingFile);
        
        // Obter caminho do logo atual
        preg_match("/define\('COMPANY_LOGO', '(.*)'\);/", $content, $currentLogo);
        $logoPath = $currentLogo[1] ?? '';
        
        if ($logoPath) {
            $fullPath = __DIR__ . '/../' . $logoPath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        
        // Atualizar config para usar logo padrão
        $this->updateBrandingFile(COMPANY_NAME, '', 'true');
        
        $this->json(['success' => true, 'message' => 'Logo deletado com sucesso!']);
    }
}
