<?php
/**
 * EXEMPLOS DE CONFIGURAÇÃO DE BRANDING
 * 
 * Copie os exemplos abaixo para o arquivo branding.php
 */

// ========================================
// EXEMPLO 1: OCULTAR RODAPÉ COMPLETAMENTE
// ========================================
/*
define('BRANDING_SHOW', false);
*/

// ========================================
// EXEMPLO 2: APENAS NOME DA EMPRESA
// ========================================
/*
define('BRANDING_SHOW', true);
define('BRANDING_SYSTEM_NAME', 'Minha Empresa');
define('BRANDING_SYSTEM_DESC', '');
define('BRANDING_SHOW_DEVELOPER', false);
define('BRANDING_SHOW_VERSION', false);
*/

// ========================================
// EXEMPLO 3: TEXTO COMPLETAMENTE CUSTOMIZADO
// ========================================
/*
define('BRANDING_SHOW', true);
define('BRANDING_CUSTOM_TEXT', 'Minha Empresa © 2025 - Todos os direitos reservados');
*/

// ========================================
// EXEMPLO 4: SEM DESENVOLVEDOR, APENAS VERSÃO
// ========================================
/*
define('BRANDING_SHOW', true);
define('BRANDING_SYSTEM_NAME', 'MeuSistema');
define('BRANDING_SYSTEM_DESC', 'Gestão de WhatsApp');
define('BRANDING_SHOW_DEVELOPER', false);
define('BRANDING_SHOW_VERSION', true);
*/

// ========================================
// EXEMPLO 5: DESENVOLVEDOR SEM LINK
// ========================================
/*
define('BRANDING_SHOW', true);
define('BRANDING_SHOW_DEVELOPER', true);
define('BRANDING_DEVELOPER_NAME', 'Minha Empresa');
define('BRANDING_DEVELOPER_URL', ''); // Sem link
define('BRANDING_DEVELOPER_PREFIX', 'Criado por');
*/

// ========================================
// EXEMPLO 6: LINK AZUL AO INVÉS DE ROXO
// ========================================
/*
define('BRANDING_LINK_COLOR', 'blue');
*/

// ========================================
// EXEMPLO 7: LINK ABRINDO NA MESMA ABA
// ========================================
/*
define('BRANDING_LINK_TARGET', false);
*/

// ========================================
// EXEMPLO 8: ANO FIXO DE COPYRIGHT
// ========================================
/*
define('BRANDING_COPYRIGHT_YEAR', '2024-2025');
*/

// ========================================
// EXEMPLO 9: VERSÃO SEM PREFIXO
// ========================================
/*
define('BRANDING_VERSION_PREFIX', ''); // Apenas o número
*/

// ========================================
// EXEMPLO 10: CONFIGURAÇÃO MINIMALISTA
// ========================================
/*
define('BRANDING_SHOW', true);
define('BRANDING_SYSTEM_NAME', '');
define('BRANDING_SYSTEM_DESC', '');
define('BRANDING_SHOW_DEVELOPER', false);
define('BRANDING_SHOW_VERSION', true);
define('BRANDING_VERSION_PREFIX', 'v');
*/
