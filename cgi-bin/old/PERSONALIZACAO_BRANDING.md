# 🎨 Personalização de Branding - ZAPX

**Autor:** [Dante Testa](https://dantetesta.com.br)  
**Data:** 2025-10-26 08:05:00  
**Versão:** 1.0.0

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquivo de Configuração](#arquivo-de-configuração)
3. [Opções Disponíveis](#opções-disponíveis)
4. [Exemplos Práticos](#exemplos-práticos)
5. [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

O sistema ZAPX permite personalizar completamente o rodapé da aplicação através do arquivo `config/branding.php`.

### O Que Pode Ser Personalizado?

- ✅ Nome do sistema
- ✅ Descrição do sistema
- ✅ Ano de copyright
- ✅ Nome do desenvolvedor/empresa
- ✅ Link do desenvolvedor
- ✅ Versão do sistema
- ✅ Cores dos links
- ✅ Texto completamente customizado
- ✅ **Ocultar o rodapé completamente**

---

## 📁 Arquivo de Configuração

**Localização:** `/config/branding.php`

### Estrutura Padrão

```php
// Mostrar rodapé?
define('BRANDING_SHOW', true);

// Nome do sistema
define('BRANDING_SYSTEM_NAME', 'ZAPX');

// Descrição
define('BRANDING_SYSTEM_DESC', 'Sistema de Disparo em Massa WhatsApp');

// Desenvolvedor
define('BRANDING_SHOW_DEVELOPER', true);
define('BRANDING_DEVELOPER_NAME', 'Dante Testa');
define('BRANDING_DEVELOPER_URL', 'https://dantetesta.com.br');

// Versão
define('BRANDING_SHOW_VERSION', true);
```

---

## ⚙️ Opções Disponíveis

### 1. Visibilidade do Rodapé

| Opção | Tipo | Padrão | Descrição |
|-------|------|--------|-----------|
| `BRANDING_SHOW` | boolean | `true` | Mostrar ou ocultar rodapé completamente |

```php
define('BRANDING_SHOW', false); // Oculta o rodapé
```

---

### 2. Informações do Sistema

| Opção | Tipo | Padrão | Descrição |
|-------|------|--------|-----------|
| `BRANDING_SYSTEM_NAME` | string | `'ZAPX'` | Nome do sistema |
| `BRANDING_SYSTEM_DESC` | string | `'Sistema de...'` | Descrição do sistema |
| `BRANDING_COPYRIGHT_YEAR` | string | `''` | Ano de copyright (vazio = ano atual) |

```php
define('BRANDING_SYSTEM_NAME', 'MeuSistema');
define('BRANDING_SYSTEM_DESC', 'Gestão Empresarial');
define('BRANDING_COPYRIGHT_YEAR', '2024-2025');
```

**Resultado:** `© 2024-2025 MeuSistema - Gestão Empresarial`

---

### 3. Desenvolvedor/Empresa

| Opção | Tipo | Padrão | Descrição |
|-------|------|--------|-----------|
| `BRANDING_SHOW_DEVELOPER` | boolean | `true` | Mostrar informações do desenvolvedor |
| `BRANDING_DEVELOPER_NAME` | string | `'Dante Testa'` | Nome do desenvolvedor/empresa |
| `BRANDING_DEVELOPER_URL` | string | `'https://...'` | URL do site (vazio = sem link) |
| `BRANDING_DEVELOPER_PREFIX` | string | `'Desenvolvido por'` | Texto antes do nome |

```php
define('BRANDING_SHOW_DEVELOPER', true);
define('BRANDING_DEVELOPER_NAME', 'Minha Empresa');
define('BRANDING_DEVELOPER_URL', 'https://minhaempresa.com');
define('BRANDING_DEVELOPER_PREFIX', 'Criado por');
```

**Resultado:** `Criado por Minha Empresa`

---

### 4. Versão do Sistema

| Opção | Tipo | Padrão | Descrição |
|-------|------|--------|-----------|
| `BRANDING_SHOW_VERSION` | boolean | `true` | Mostrar versão do sistema |
| `BRANDING_VERSION_PREFIX` | string | `'Versão'` | Texto antes da versão |

```php
define('BRANDING_SHOW_VERSION', true);
define('BRANDING_VERSION_PREFIX', 'v');
```

**Resultado:** `v 2.0.1`

---

### 5. Personalização Avançada

| Opção | Tipo | Padrão | Descrição |
|-------|------|--------|-----------|
| `BRANDING_LINK_COLOR` | string | `'purple'` | Cor do link (purple, blue, green, red, yellow, indigo, pink) |
| `BRANDING_LINK_TARGET` | boolean | `true` | Abrir link em nova aba? |
| `BRANDING_CUSTOM_TEXT` | string | `''` | Texto completamente customizado |

```php
define('BRANDING_LINK_COLOR', 'blue');
define('BRANDING_LINK_TARGET', false); // Mesma aba
```

---

### 6. Texto Customizado

Se você quiser **substituir tudo** por um texto próprio:

```php
define('BRANDING_CUSTOM_TEXT', 'Minha Empresa © 2025 - Todos os direitos reservados');
```

**Importante:** Quando `BRANDING_CUSTOM_TEXT` está definido, **todas as outras configurações são ignoradas**.

---

## 💡 Exemplos Práticos

### Exemplo 1: Ocultar Rodapé Completamente

```php
define('BRANDING_SHOW', false);
```

**Resultado:** Nenhum rodapé é exibido.

---

### Exemplo 2: Apenas Nome da Empresa

```php
define('BRANDING_SHOW', true);
define('BRANDING_SYSTEM_NAME', 'Minha Empresa');
define('BRANDING_SYSTEM_DESC', '');
define('BRANDING_SHOW_DEVELOPER', false);
define('BRANDING_SHOW_VERSION', false);
```

**Resultado:**
```
© 2025 Minha Empresa
```

---

### Exemplo 3: Sem Desenvolvedor, Apenas Versão

```php
define('BRANDING_SHOW', true);
define('BRANDING_SYSTEM_NAME', 'MeuSistema');
define('BRANDING_SYSTEM_DESC', 'Gestão de WhatsApp');
define('BRANDING_SHOW_DEVELOPER', false);
define('BRANDING_SHOW_VERSION', true);
```

**Resultado:**
```
© 2025 MeuSistema - Gestão de WhatsApp
Versão 2.0.1
```

---

### Exemplo 4: Desenvolvedor Sem Link

```php
define('BRANDING_SHOW_DEVELOPER', true);
define('BRANDING_DEVELOPER_NAME', 'Minha Empresa');
define('BRANDING_DEVELOPER_URL', ''); // Sem link
define('BRANDING_DEVELOPER_PREFIX', 'Criado por');
```

**Resultado:**
```
Criado por Minha Empresa
```
(Sem link clicável)

---

### Exemplo 5: Texto Completamente Customizado

```php
define('BRANDING_SHOW', true);
define('BRANDING_CUSTOM_TEXT', 'Sistema Proprietário © 2025 - Minha Empresa LTDA - Todos os direitos reservados');
```

**Resultado:**
```
Sistema Proprietário © 2025 - Minha Empresa LTDA - Todos os direitos reservados
```

---

### Exemplo 6: Link Azul ao Invés de Roxo

```php
define('BRANDING_LINK_COLOR', 'blue');
```

**Cores disponíveis:**
- `purple` (padrão)
- `blue`
- `green`
- `red`
- `yellow`
- `indigo`
- `pink`

---

### Exemplo 7: Configuração Minimalista

```php
define('BRANDING_SHOW', true);
define('BRANDING_SYSTEM_NAME', '');
define('BRANDING_SYSTEM_DESC', '');
define('BRANDING_SHOW_DEVELOPER', false);
define('BRANDING_SHOW_VERSION', true);
define('BRANDING_VERSION_PREFIX', 'v');
```

**Resultado:**
```
v 2.0.1
```

---

## 🔧 Troubleshooting

### Problema: Alterações não aparecem

**Solução:**
```bash
# Limpar cache do servidor
php limpar_cache_completo.php

# Limpar cache do navegador
CMD + SHIFT + R (Mac)
CTRL + SHIFT + R (Windows)
```

---

### Problema: Erro ao carregar a página

**Causa:** Sintaxe incorreta no `branding.php`

**Solução:**
1. Verifique se todas as linhas terminam com `;`
2. Verifique se as aspas estão fechadas corretamente
3. Compare com o arquivo `branding.example.php`

---

### Problema: Rodapé não aparece

**Verificar:**
```php
// Certifique-se de que está true
define('BRANDING_SHOW', true);
```

---

### Problema: Link não funciona

**Verificar:**
```php
// URL deve começar com http:// ou https://
define('BRANDING_DEVELOPER_URL', 'https://seusite.com');
```

---

## 📝 Boas Práticas

### ✅ FAZER

- Fazer backup do `branding.php` antes de editar
- Testar alterações em ambiente de desenvolvimento primeiro
- Usar aspas simples (`'`) para strings
- Deixar campos vazios (`''`) ao invés de remover linhas

### ❌ NÃO FAZER

- Não remover linhas de `define()`
- Não usar caracteres especiais sem escapar
- Não esquecer ponto e vírgula (`;`)
- Não editar o arquivo `footer.php` diretamente

---

## 🎨 Casos de Uso Comuns

### White Label (Marca Própria)

```php
define('BRANDING_SHOW', true);
define('BRANDING_SYSTEM_NAME', 'MinhaEmpresa Pro');
define('BRANDING_SYSTEM_DESC', 'Plataforma de Marketing Digital');
define('BRANDING_SHOW_DEVELOPER', false);
define('BRANDING_SHOW_VERSION', false);
```

---

### Revendedor

```php
define('BRANDING_SHOW', true);
define('BRANDING_SYSTEM_NAME', 'ZAPX');
define('BRANDING_SYSTEM_DESC', 'Sistema de Disparo em Massa WhatsApp');
define('BRANDING_SHOW_DEVELOPER', true);
define('BRANDING_DEVELOPER_NAME', 'Minha Revenda');
define('BRANDING_DEVELOPER_URL', 'https://minharevenda.com');
define('BRANDING_DEVELOPER_PREFIX', 'Distribuído por');
```

---

### Uso Interno (Sem Branding)

```php
define('BRANDING_SHOW', false);
```

---

## 📞 Suporte

**Desenvolvedor Original:** Dante Testa  
**Website:** [dantetesta.com.br](https://dantetesta.com.br)  
**WhatsApp:** +55 19 99802-1956

---

**Documentação criada em:** 2025-10-26  
**Última atualização:** 2025-10-26
