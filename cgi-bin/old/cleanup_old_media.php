<?php
/**
 * Script de Limpeza de Mídias Antigas
 * Remove arquivos de mídia com mais de 1 hora (órfãos)
 * 
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-27
 * 
 * USO:
 * - Manual: php cleanup_old_media.php
 * - Cron: 0 * * * * cd /caminho/do/projeto && php cleanup_old_media.php
 */

// Diretório de uploads
$uploadDir = __DIR__ . '/uploads/media/';

// Verificar se diretório existe
if (!is_dir($uploadDir)) {
    echo "✅ Diretório de uploads não existe ainda. Nada a limpar.\n";
    exit(0);
}

// Tempo máximo de vida do arquivo (1 hora = 3600 segundos)
$maxAge = 3600;
$now = time();

// Contadores
$totalFiles = 0;
$deletedFiles = 0;
$deletedSize = 0;
$errors = 0;

echo "🧹 Iniciando limpeza de mídias antigas...\n";
echo "📁 Diretório: $uploadDir\n";
echo "⏰ Removendo arquivos com mais de " . ($maxAge / 60) . " minutos\n\n";

// Escanear diretório
$files = scandir($uploadDir);

foreach ($files as $file) {
    // Ignorar . e ..
    if ($file === '.' || $file === '..') {
        continue;
    }
    
    $filepath = $uploadDir . $file;
    
    // Verificar se é arquivo
    if (!is_file($filepath)) {
        continue;
    }
    
    $totalFiles++;
    
    // Obter tempo de modificação
    $fileAge = $now - filemtime($filepath);
    $fileSize = filesize($filepath);
    
    // Se arquivo é mais antigo que o limite
    if ($fileAge > $maxAge) {
        echo "🗑️  Removendo: $file\n";
        echo "   ├─ Idade: " . round($fileAge / 60) . " minutos\n";
        echo "   ├─ Tamanho: " . round($fileSize / 1024, 2) . " KB\n";
        
        if (unlink($filepath)) {
            $deletedFiles++;
            $deletedSize += $fileSize;
            echo "   └─ ✅ Removido com sucesso\n\n";
        } else {
            $errors++;
            echo "   └─ ❌ Erro ao remover\n\n";
        }
    } else {
        $remainingMinutes = round(($maxAge - $fileAge) / 60);
        echo "⏳ Mantendo: $file (será removido em ~$remainingMinutes minutos)\n";
    }
}

// Resumo
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RESUMO DA LIMPEZA\n";
echo str_repeat("=", 50) . "\n";
echo "📁 Total de arquivos encontrados: $totalFiles\n";
echo "🗑️  Arquivos removidos: $deletedFiles\n";
echo "💾 Espaço liberado: " . round($deletedSize / 1024 / 1024, 2) . " MB\n";
echo "❌ Erros: $errors\n";
echo "✅ Limpeza concluída!\n";

// Remover diretório se estiver vazio
if ($totalFiles === $deletedFiles && $totalFiles > 0) {
    echo "\n📂 Diretório vazio. Removendo...\n";
    if (rmdir($uploadDir)) {
        echo "✅ Diretório removido com sucesso!\n";
    }
}
