/**
 * Sistema de Logs Profissional - ZAPX
 * 
 * Controla exibição de logs baseado no ambiente (dev/prod)
 * Em produção: logs são silenciados
 * Em desenvolvimento: logs são exibidos normalmente
 * 
 * @author Dante Testa - https://dantetesta.com.br
 * @version 1.0.0
 * @date 2025-01-27
 */

class Logger {
    constructor() {
        // Detecta ambiente automaticamente
        this.isDevelopment = this.detectEnvironment();
        
        // Configurações de log
        this.config = {
            showTimestamp: true,
            showCaller: true,
            colors: {
                log: '#6366f1',      // Roxo
                info: '#3b82f6',     // Azul
                warn: '#f59e0b',     // Laranja
                error: '#ef4444',    // Vermelho
                success: '#10b981'   // Verde
            }
        };
        
        // Inicializa
        this.init();
    }
    
    /**
     * Detecta se está em ambiente de desenvolvimento
     * @returns {boolean}
     */
    detectEnvironment() {
        // Verifica hostname
        const hostname = window.location.hostname;
        
        // Ambientes de desenvolvimento
        const devHosts = [
            'localhost',
            '127.0.0.1',
            '::1',
            'dev.',
            'test.',
            'staging.'
        ];
        
        // Verifica se é desenvolvimento
        const isDev = devHosts.some(host => 
            hostname === host || hostname.includes(host)
        );
        
        // Também verifica se tem parâmetro ?debug=1 na URL
        const urlParams = new URLSearchParams(window.location.search);
        const forceDebug = urlParams.get('debug') === '1';
        
        return isDev || forceDebug;
    }
    
    /**
     * Inicializa o logger
     */
    init() {
        if (!this.isDevelopment) {
            // Em produção, mostra aviso único
            console.log(
                '%c🔒 ZAPX - Modo Produção',
                'color: #6366f1; font-weight: bold; font-size: 12px;',
                '\nLogs desabilitados. Use ?debug=1 para ativar.'
            );
        } else {
            // Em desenvolvimento, mostra aviso
            console.log(
                '%c🔧 ZAPX - Modo Desenvolvimento',
                'color: #10b981; font-weight: bold; font-size: 12px;',
                '\nLogs habilitados.'
            );
        }
    }
    
    /**
     * Formata mensagem com timestamp e caller
     * @param {string} type - Tipo do log
     * @param {Array} args - Argumentos
     * @returns {Array}
     */
    formatMessage(type, args) {
        const formatted = [];
        
        // Timestamp
        if (this.config.showTimestamp) {
            const now = new Date();
            const time = now.toLocaleTimeString('pt-BR');
            formatted.push(`[${time}]`);
        }
        
        // Tipo
        const typeLabel = type.toUpperCase().padEnd(7);
        formatted.push(typeLabel);
        
        // Mensagem original
        formatted.push(...args);
        
        return formatted;
    }
    
    /**
     * Log normal
     */
    log(...args) {
        if (!this.isDevelopment) return;
        
        const formatted = this.formatMessage('log', args);
        console.log(
            `%c${formatted[0]} ${formatted[1]}`,
            `color: ${this.config.colors.log}; font-weight: bold;`,
            ...formatted.slice(2)
        );
    }
    
    /**
     * Log de informação
     */
    info(...args) {
        if (!this.isDevelopment) return;
        
        const formatted = this.formatMessage('info', args);
        console.info(
            `%c${formatted[0]} ${formatted[1]}`,
            `color: ${this.config.colors.info}; font-weight: bold;`,
            ...formatted.slice(2)
        );
    }
    
    /**
     * Log de aviso
     */
    warn(...args) {
        if (!this.isDevelopment) return;
        
        const formatted = this.formatMessage('warn', args);
        console.warn(
            `%c${formatted[0]} ${formatted[1]}`,
            `color: ${this.config.colors.warn}; font-weight: bold;`,
            ...formatted.slice(2)
        );
    }
    
    /**
     * Log de erro (sempre exibe, mesmo em produção)
     */
    error(...args) {
        const formatted = this.formatMessage('error', args);
        console.error(
            `%c${formatted[0]} ${formatted[1]}`,
            `color: ${this.config.colors.error}; font-weight: bold;`,
            ...formatted.slice(2)
        );
    }
    
    /**
     * Log de sucesso
     */
    success(...args) {
        if (!this.isDevelopment) return;
        
        const formatted = this.formatMessage('success', args);
        console.log(
            `%c${formatted[0]} ${formatted[1]}`,
            `color: ${this.config.colors.success}; font-weight: bold;`,
            ...formatted.slice(2)
        );
    }
    
    /**
     * Log de tabela
     */
    table(data, columns) {
        if (!this.isDevelopment) return;
        console.table(data, columns);
    }
    
    /**
     * Log de grupo
     */
    group(label) {
        if (!this.isDevelopment) return;
        console.group(label);
    }
    
    /**
     * Fecha grupo
     */
    groupEnd() {
        if (!this.isDevelopment) return;
        console.groupEnd();
    }
    
    /**
     * Limpa console
     */
    clear() {
        if (!this.isDevelopment) return;
        console.clear();
    }
    
    /**
     * Força exibição de log (mesmo em produção)
     * Use apenas para debug crítico
     */
    force(...args) {
        console.log('[FORCE]', ...args);
    }
}

// Instância global
const loggerInstance = new Logger();
window.Logger = loggerInstance;

// Atalhos globais para facilitar uso
window.log = function() { 
    return loggerInstance.log.apply(loggerInstance, arguments); 
};
window.logInfo = function() { 
    return loggerInstance.info.apply(loggerInstance, arguments); 
};
window.logWarn = function() { 
    return loggerInstance.warn.apply(loggerInstance, arguments); 
};
window.logError = function() { 
    return loggerInstance.error.apply(loggerInstance, arguments); 
};
window.logSuccess = function() { 
    return loggerInstance.success.apply(loggerInstance, arguments); 
};
