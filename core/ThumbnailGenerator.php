<?php
/**
 * Gerador de Thumbnails
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-28 10:50:00
 */

class ThumbnailGenerator {
    private $thumbnailPath = __DIR__ . '/../uploads/thumbnails/';
    private $thumbnailSize = 150;
    
    /**
     * Gerar thumbnail de imagem
     */
    public function generateImageThumbnail($sourceFile, $filename) {
        try {
            // Detectar tipo de imagem
            $imageInfo = getimagesize($sourceFile);
            if (!$imageInfo) {
                return false;
            }
            
            $mimeType = $imageInfo['mime'];
            
            // Criar imagem source baseado no tipo
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    $source = imagecreatefromjpeg($sourceFile);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($sourceFile);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($sourceFile);
                    break;
                case 'image/webp':
                    $source = imagecreatefromwebp($sourceFile);
                    break;
                default:
                    return false;
            }
            
            if (!$source) {
                return false;
            }
            
            // Dimensões originais
            $width = imagesx($source);
            $height = imagesy($source);
            
            // Calcular dimensões mantendo proporção
            if ($width > $height) {
                $newWidth = $this->thumbnailSize;
                $newHeight = ($height / $width) * $this->thumbnailSize;
            } else {
                $newHeight = $this->thumbnailSize;
                $newWidth = ($width / $height) * $this->thumbnailSize;
            }
            
            // Criar thumbnail
            $thumb = imagecreatetruecolor($this->thumbnailSize, $this->thumbnailSize);
            
            // Fundo branco
            $white = imagecolorallocate($thumb, 255, 255, 255);
            imagefill($thumb, 0, 0, $white);
            
            // Centralizar imagem
            $offsetX = ($this->thumbnailSize - $newWidth) / 2;
            $offsetY = ($this->thumbnailSize - $newHeight) / 2;
            
            // Redimensionar e copiar
            imagecopyresampled(
                $thumb, $source,
                $offsetX, $offsetY, 0, 0,
                $newWidth, $newHeight, $width, $height
            );
            
            // Salvar como JPEG
            $thumbnailFile = $this->thumbnailPath . $filename . '.jpg';
            imagejpeg($thumb, $thumbnailFile, 85);
            
            // Liberar memória
            imagedestroy($source);
            imagedestroy($thumb);
            
            return 'uploads/thumbnails/' . $filename . '.jpg';
            
        } catch (Exception $e) {
            error_log("Erro ao gerar thumbnail: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gerar thumbnail de vídeo (frame do meio)
     */
    public function generateVideoThumbnail($sourceFile, $filename) {
        try {
            // Verificar se FFmpeg está disponível
            $ffmpegPath = $this->findFFmpeg();
            if (!$ffmpegPath) {
                error_log("FFmpeg não encontrado. Usando ícone padrão para vídeo.");
                return $this->generateIconThumbnail('video', $filename);
            }
            
            // Obter duração do vídeo
            $durationCmd = "$ffmpegPath -i " . escapeshellarg($sourceFile) . " 2>&1 | grep Duration";
            $durationOutput = shell_exec($durationCmd);
            
            if (preg_match('/Duration: (\d{2}):(\d{2}):(\d{2})/', $durationOutput, $matches)) {
                $hours = (int)$matches[1];
                $minutes = (int)$matches[2];
                $seconds = (int)$matches[3];
                $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
                
                // Pegar frame do meio (evita frame preto do início)
                $middleSecond = max(1, floor($totalSeconds / 2));
            } else {
                // Se não conseguir detectar, pega 2 segundos
                $middleSecond = 2;
            }
            
            // Gerar thumbnail
            $thumbnailFile = $this->thumbnailPath . $filename . '.jpg';
            $cmd = "$ffmpegPath -i " . escapeshellarg($sourceFile) . 
                   " -ss $middleSecond -vframes 1 -vf scale=150:150:force_original_aspect_ratio=decrease,pad=150:150:(ow-iw)/2:(oh-ih)/2 " .
                   escapeshellarg($thumbnailFile) . " 2>&1";
            
            exec($cmd, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($thumbnailFile)) {
                return 'uploads/thumbnails/' . $filename . '.jpg';
            } else {
                error_log("Erro ao gerar thumbnail de vídeo: " . implode("\n", $output));
                return $this->generateIconThumbnail('video', $filename);
            }
            
        } catch (Exception $e) {
            error_log("Erro ao gerar thumbnail de vídeo: " . $e->getMessage());
            return $this->generateIconThumbnail('video', $filename);
        }
    }
    
    /**
     * Gerar thumbnail com ícone (para áudio e documentos)
     */
    public function generateIconThumbnail($type, $filename) {
        try {
            // Criar imagem 150x150
            $thumb = imagecreatetruecolor($this->thumbnailSize, $this->thumbnailSize);
            
            // Cores baseadas no tipo
            $colors = [
                'audio' => ['bg' => [220, 252, 231], 'icon' => [34, 197, 94]],  // Verde
                'document' => ['bg' => [219, 234, 254], 'icon' => [59, 130, 246]], // Azul
                'video' => ['bg' => [254, 226, 226], 'icon' => [239, 68, 68]]  // Vermelho
            ];
            
            $colorSet = $colors[$type] ?? $colors['document'];
            
            // Fundo colorido
            $bg = imagecolorallocate($thumb, $colorSet['bg'][0], $colorSet['bg'][1], $colorSet['bg'][2]);
            imagefill($thumb, 0, 0, $bg);
            
            // Cor do ícone
            $iconColor = imagecolorallocate($thumb, $colorSet['icon'][0], $colorSet['icon'][1], $colorSet['icon'][2]);
            
            // Desenhar ícone simples (círculo)
            imagefilledellipse($thumb, 75, 75, 60, 60, $iconColor);
            
            // Salvar
            $thumbnailFile = $this->thumbnailPath . $filename . '.jpg';
            imagejpeg($thumb, $thumbnailFile, 85);
            
            imagedestroy($thumb);
            
            return 'uploads/thumbnails/' . $filename . '.jpg';
            
        } catch (Exception $e) {
            error_log("Erro ao gerar thumbnail de ícone: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Encontrar FFmpeg no sistema
     */
    private function findFFmpeg() {
        $paths = [
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/opt/homebrew/bin/ffmpeg',
            'ffmpeg' // PATH do sistema
        ];
        
        foreach ($paths as $path) {
            if (@exec("which $path 2>/dev/null", $output)) {
                return trim($output[0]);
            }
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Gerar nome único para thumbnail
     */
    public function generateFilename() {
        return uniqid('thumb_', true) . '_' . time();
    }
}
