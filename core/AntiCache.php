<?php
/**
 * Sistema Anti-Cache Multicamadas
 * 
 * @author Dante Testa - https://dantetesta.com.br
 * @date 2025-10-26 07:56:00
 * @version 1.0.0
 */

class AntiCache {
    
    /**
     * Aplicar headers anti-cache HTTP
     */
    public static function setHeaders() {
        // Prevenir cache no navegador
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Data no passado
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    }
    
    /**
     * Aplicar headers anti-cache para JSON/AJAX
     */
    public static function setJsonHeaders() {
        self::setHeaders();
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }
    
    /**
     * Limpar OPcache do PHP
     */
    public static function clearOPcache() {
        if (function_exists('opcache_reset')) {
            opcache_reset();
            return true;
        }
        return false;
    }
    
    /**
     * Limpar cache de arquivos
     */
    public static function clearStatCache() {
        if (function_exists('clearstatcache')) {
            clearstatcache(true);
            return true;
        }
        return false;
    }
    
    /**
     * Forçar reload de arquivo específico
     */
    public static function touchFile($filepath) {
        if (file_exists($filepath)) {
            touch($filepath);
            return true;
        }
        return false;
    }
    
    /**
     * Limpar TODOS os caches (usar com cuidado)
     */
    public static function clearAll() {
        $cleared = [];
        
        // 1. OPcache
        if (self::clearOPcache()) {
            $cleared[] = 'OPcache';
        }
        
        // 2. Stat cache
        if (self::clearStatCache()) {
            $cleared[] = 'StatCache';
        }
        
        // 3. Realpath cache
        if (function_exists('clearstatcache')) {
            clearstatcache(true);
            $cleared[] = 'RealpathCache';
        }
        
        return $cleared;
    }
    
    /**
     * Gerar timestamp único para cache busting em URLs
     */
    public static function getCacheBuster() {
        return time() . substr(md5(microtime()), 0, 8);
    }
    
    /**
     * Adicionar cache buster em URL
     */
    public static function addCacheBuster($url) {
        $separator = strpos($url, '?') !== false ? '&' : '?';
        return $url . $separator . 'v=' . self::getCacheBuster();
    }
}
