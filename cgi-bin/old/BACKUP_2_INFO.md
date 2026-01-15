# 📦 Backup 2 - ZAPX v2.1.0

**Data do Backup:** 2025-10-26 08:08:25  
**Versão do Sistema:** 2.1.0  
**Autor:** [Dante Testa](https://dantetesta.com.br)

---

## 📊 Informações do Backup

- **Arquivo:** `backup_2_v2.1.0_20251026_080825.zip`
- **Tamanho:** 320 KB
- **Tipo:** Backup completo do sistema

---

## 🆕 Novidades da v2.1.0

### Sistema de Branding Configurável

**NOVO!** Sistema completo de personalização do rodapé via arquivo de configuração.

#### Arquivos Criados
- ✅ `config/branding.php` - Configuração principal
- ✅ `config/branding.example.php` - 10 exemplos práticos
- ✅ `PERSONALIZACAO_BRANDING.md` - Documentação completa

#### Funcionalidades
- ✅ Ocultar rodapé completamente
- ✅ Personalizar nome do sistema
- ✅ Personalizar descrição
- ✅ Personalizar desenvolvedor/empresa
- ✅ Personalizar versão
- ✅ Cores de link configuráveis
- ✅ Texto completamente customizado
- ✅ Ideal para White Label e Revendas

---

## ✅ O Que Está Incluído

### Código Fonte Completo
- ✅ Controllers (Auth, Contacts, Dashboard, Dispatch, WhatsApp, etc)
- ✅ Models (User, Contact, Tag, DispatchHistory)
- ✅ Views (todas as páginas com rodapé configurável)
- ✅ Core (App, Controller, **AntiCache**)

### Configurações
- ✅ `.htaccess` (v2.0.0 - Sistema anti-cache)
- ✅ `database.sql` (estrutura completa)
- ✅ `config.example.php`
- ✅ **`config/branding.php`** (NOVO - Personalização)
- ✅ **`config/branding.example.php`** (NOVO - Exemplos)

### Documentação
- ✅ `README.md`
- ✅ `INSTALACAO.md`
- ✅ `SISTEMA_ANTI_CACHE.md`
- ✅ **`PERSONALIZACAO_BRANDING.md`** (NOVO)
- ✅ `VERSION.txt` (v2.1.0)
- ✅ Guias completos da Evolution API
- ✅ `BACKUP_1_INFO.md`
- ✅ `BACKUP_2_INFO.md`

### Dependências
- ✅ Composer (vendor/)
- ✅ PHPMailer

---

## ❌ Excluído (Por Segurança)

- ❌ `config/config.php` (dados sensíveis)
- ❌ `config/installed.lock`
- ❌ `uploads/*` (arquivos de usuários)
- ❌ `.git/*`
- ❌ `node_modules/*`
- ❌ `backup_1_*.zip` (backup anterior)

---

## 🎨 Exemplos de Personalização

### Ocultar Rodapé
```php
// config/branding.php
define('BRANDING_SHOW', false);
```

### White Label
```php
define('BRANDING_SYSTEM_NAME', 'MinhaEmpresa Pro');
define('BRANDING_SYSTEM_DESC', 'Plataforma de Marketing');
define('BRANDING_SHOW_DEVELOPER', false);
define('BRANDING_SHOW_VERSION', false);
```

**Resultado:**
```
© 2025 MinhaEmpresa Pro - Plataforma de Marketing
```

### Revendedor
```php
define('BRANDING_DEVELOPER_NAME', 'Minha Revenda');
define('BRANDING_DEVELOPER_URL', 'https://minharevenda.com');
define('BRANDING_DEVELOPER_PREFIX', 'Distribuído por');
```

**Resultado:**
```
© 2025 ZAPX - Sistema de Disparo em Massa WhatsApp
Distribuído por Minha Revenda | Versão 2.1.0
```

### Texto Customizado
```php
define('BRANDING_CUSTOM_TEXT', 'Minha Empresa © 2025 - Todos os direitos reservados');
```

---

## 📋 Changelog Completo v2.1.0

### Sistema de Branding Configurável
- ✅ Criado arquivo `config/branding.php` com configurações personalizáveis
- ✅ Rodapé completamente configurável (nome, descrição, desenvolvedor, versão)
- ✅ Opção para ocultar rodapé completamente
- ✅ Suporte a texto customizado
- ✅ Cores de link personalizáveis (purple, blue, green, red, yellow, indigo, pink)
- ✅ Controle de abertura de link (nova aba ou mesma aba)
- ✅ Arquivo de exemplos (`branding.example.php`)
- ✅ Documentação completa (`PERSONALIZACAO_BRANDING.md`)
- ✅ Ideal para white label e revendas

### Arquivos Modificados
- ✅ `views/layouts/footer.php` - Rodapé dinâmico baseado em configurações
- ✅ `index.php` - Carrega `branding.php` automaticamente
- ✅ `VERSION.txt` - Atualizado para v2.1.0

---

## 🚀 Como Restaurar Este Backup

### 1. Extrair Arquivos
```bash
unzip backup_2_v2.1.0_20251026_080825.zip -d /caminho/destino
```

### 2. Configurar Permissões
```bash
chmod -R 755 /caminho/destino
chmod -R 777 /caminho/destino/uploads
```

### 3. Criar Banco de Dados
```sql
CREATE DATABASE zapx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Executar Instalador
```
http://seu-dominio.com/install
```

### 5. Personalizar Branding (Opcional)
```bash
# Editar arquivo de branding
nano config/branding.php

# Ou consultar exemplos
cat config/branding.example.php
```

---

## 📊 Estado do Sistema no Backup

### Versão 2.1.0 - Sistema de Branding Configurável

#### Funcionalidades Principais
- ✅ Sistema anti-cache profissional (4 camadas)
- ✅ Integração Evolution API V2 completa
- ✅ WhatsApp: criar instância, QR Code, envio de mensagens
- ✅ Gestão de contatos com tags e importação CSV
- ✅ Disparo em massa com mídias
- ✅ Multi-usuário com instâncias isoladas
- ✅ Instalador profissional
- ✅ **Sistema de branding configurável (NOVO)**

#### Personalização
- ✅ Rodapé 100% configurável
- ✅ Suporte a White Label
- ✅ Ideal para revendas
- ✅ Sem necessidade de editar código

---

## 🎯 Casos de Uso

### White Label (Marca Própria)
Remova toda referência ao desenvolvedor original e use sua marca.

### Revenda
Mantenha o sistema original mas adicione sua empresa como distribuidor.

### Uso Interno
Oculte completamente o rodapé para uso corporativo interno.

### SaaS Multi-Tenant
Configure branding diferente para cada cliente (requer customização adicional).

---

## 🔧 Requisitos do Sistema

### Servidor
- **PHP:** 7.4 ou superior
- **MySQL:** 5.7 ou superior
- **Apache:** 2.4 ou superior (com mod_rewrite e mod_headers)
- **Composer:** Instalado

### PHP Extensions
- `pdo_mysql`
- `mbstring`
- `curl`
- `json`
- `opcache` (recomendado)

### Serviços Externos
- **Evolution API V2:** Para integração WhatsApp
- **SMTP:** Para envio de emails (recuperação de senha)

---

## 📞 Suporte

**Desenvolvedor:** Dante Testa  
**Website:** [dantetesta.com.br](https://dantetesta.com.br)  
**WhatsApp:** +55 19 99802-1956

---

## 🔄 Diferenças do Backup 1

| Item | Backup 1 (v2.0.1) | Backup 2 (v2.1.0) |
|------|-------------------|-------------------|
| **Versão** | 2.0.1 | 2.1.0 |
| **Tamanho** | 312 KB | 320 KB |
| **Branding** | ❌ Fixo | ✅ Configurável |
| **White Label** | ❌ Não | ✅ Sim |
| **Documentação** | 5 arquivos | 6 arquivos |

---

## 📄 Licença

Este backup contém código proprietário desenvolvido por Dante Testa.

---

**Backup criado automaticamente pelo sistema ZAPX**  
**Mantenha este arquivo seguro e em local protegido**
