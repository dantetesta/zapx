<?php
/**
 * Configurações de Branding do Sistema
 * 
 * @author Dante Testa - https://dantetesta.com.br
 * @date 2025-10-26 08:05:00
 * @version 1.0.0
 * 
 * INSTRUÇÕES:
 * - Edite este arquivo para personalizar o rodapé do sistema
 * - Defina BRANDING_SHOW como false para ocultar completamente o rodapé
 * - Deixe campos vazios ('') para não exibir aquela informação
 */

// ========================================
// VISIBILIDADE DO RODAPÉ
// ========================================

// Mostrar ou ocultar o rodapé completamente
define('BRANDING_SHOW', true);

// ========================================
// INFORMAÇÕES DA EMPRESA/SISTEMA
// ========================================

// Nome do sistema (ex: "ZAPX", "MeuSistema", etc)
define('BRANDING_SYSTEM_NAME', 'ZAPX');

// Descrição do sistema (ex: "Sistema de Disparo em Massa WhatsApp")
define('BRANDING_SYSTEM_DESC', 'Sistema de Disparo em Massa WhatsApp');

// Ano de copyright (deixe vazio para usar ano atual automaticamente)
define('BRANDING_COPYRIGHT_YEAR', ''); // Vazio = ano atual

// ========================================
// DESENVOLVEDOR/EMPRESA
// ========================================

// Mostrar informações do desenvolvedor?
define('BRANDING_SHOW_DEVELOPER', true);

// Nome do desenvolvedor/empresa
define('BRANDING_DEVELOPER_NAME', 'Dante Testa');

// URL do desenvolvedor/empresa (deixe vazio para não criar link)
define('BRANDING_DEVELOPER_URL', 'https://dantetesta.com.br');

// Texto antes do nome do desenvolvedor (ex: "Desenvolvido por", "Criado por", "Powered by")
define('BRANDING_DEVELOPER_PREFIX', 'Desenvolvido por');

// ========================================
// VERSÃO DO SISTEMA
// ========================================

// Mostrar versão do sistema?
define('BRANDING_SHOW_VERSION', true);

// Texto antes da versão (ex: "Versão", "v", "Ver.")
define('BRANDING_VERSION_PREFIX', 'Versão');

// ========================================
// PERSONALIZAÇÃO AVANÇADA
// ========================================

// Cor do link do desenvolvedor (classe Tailwind)
// Opções: 'purple', 'blue', 'green', 'red', 'yellow', 'indigo', 'pink'
define('BRANDING_LINK_COLOR', 'purple');

// Abrir link em nova aba?
define('BRANDING_LINK_TARGET', true); // true = _blank, false = _self

// ========================================
// TEXTO CUSTOMIZADO (OPCIONAL)
// ========================================

// Se quiser substituir TUDO por um texto customizado, defina aqui
// Deixe vazio ('') para usar as configurações acima
define('BRANDING_CUSTOM_TEXT', '');

// Exemplo de texto customizado:
// define('BRANDING_CUSTOM_TEXT', 'Minha Empresa © 2025 - Todos os direitos reservados');
