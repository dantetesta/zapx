# 📦 Backup 1 - ZAPX v2.0.1

**Data do Backup:** 2025-10-26 08:02:45  
**Versão do Sistema:** 2.0.1  
**Autor:** [Dante Testa](https://dantetesta.com.br)

---

## 📊 Informações do Backup

- **Arquivo:** `backup_1_v2.0.1_20251026_080245.zip`
- **Tamanho:** 312 KB
- **Tipo:** Backup completo do sistema

---

## ✅ O Que Está Incluído

### Código Fonte
- ✅ Todos os controllers
- ✅ Todos os models
- ✅ Todas as views
- ✅ Core do sistema (App, Controller, AntiCache)
- ✅ Instalador completo

### Configurações
- ✅ `.htaccess` (v2.0.0 - Anti-cache)
- ✅ `config.example.php`
- ✅ `database.sql` (estrutura do banco)
- ✅ Scripts de configuração

### Documentação
- ✅ `README.md`
- ✅ `INSTALACAO.md`
- ✅ `SISTEMA_ANTI_CACHE.md`
- ✅ `VERSION.txt` (v2.0.1)
- ✅ Guias da Evolution API
- ✅ Documentação técnica completa

### Dependências
- ✅ Composer (vendor/)
- ✅ PHPMailer

---

## ❌ O Que NÃO Está Incluído

Por segurança e otimização:

- ❌ `config/config.php` (dados sensíveis)
- ❌ `config/installed.lock` (específico da instalação)
- ❌ `uploads/*` (arquivos de usuários)
- ❌ `.git/*` (controle de versão)
- ❌ `node_modules/*` (dependências JS)
- ❌ `.DS_Store` (arquivos do macOS)

---

## 🚀 Principais Funcionalidades (v2.0.1)

### Sistema Anti-Cache Profissional
- ✅ 4 camadas de proteção (Apache, PHP, Controller, Específico)
- ✅ Headers HTTP anti-cache
- ✅ Classe `AntiCache.php` com 7 métodos
- ✅ Limpeza automática de OPcache e StatCache
- ✅ Cache busting em URLs

### WhatsApp Integration
- ✅ Integração completa com Evolution API V2
- ✅ Criação de instâncias
- ✅ Geração de QR Code
- ✅ Envio de mensagens (texto, imagem, vídeo, áudio, documento)
- ✅ Sistema híbrido Base64/URL para vídeos

### Gestão de Contatos
- ✅ Importação CSV
- ✅ Sistema de tags
- ✅ Seleção múltipla
- ✅ Ações em massa
- ✅ Paginação (20 por página)

### Disparo em Massa
- ✅ Disparo com intervalo configurável
- ✅ Suporte a mídias
- ✅ Histórico de disparos
- ✅ Limite mensal de mensagens

### Sistema de Usuários
- ✅ Multi-usuário
- ✅ Autenticação segura
- ✅ Recuperação de senha
- ✅ Níveis de acesso (admin/user)
- ✅ Instâncias isoladas por usuário

### Instalador
- ✅ Instalador visual profissional
- ✅ 5 etapas (Requisitos, Banco, Config, Admin, Finalizar)
- ✅ Validação completa
- ✅ Destruição de sessão ao finalizar

---

## 🔧 Como Restaurar Este Backup

### 1. Extrair Arquivos
```bash
unzip backup_1_v2.0.1_20251026_080245.zip -d /caminho/destino
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

### 5. Configurar Evolution API
- Editar `config/config.php`
- Adicionar URL e API Key da Evolution API

---

## 📝 Changelog v2.0.1

### FIX CRÍTICO
- ✅ Corrigido URL da requisição AJAX em `conectar-whatsapp.php`
- ✅ Estava chamando `/test_criar_direto.php` (inexistente)
- ✅ Agora chama `/whatsapp/createInstance` (endpoint correto)
- ✅ Adicionado header `X-Requested-With` para identificar AJAX
- ✅ Adicionado `cache: 'no-store'` na requisição

### Sistema Anti-Cache (v2.0.0)
- ✅ Criada classe `AntiCache.php` com sistema multicamadas
- ✅ Headers HTTP anti-cache no `.htaccess` (Apache)
- ✅ Controller base aplica anti-cache automaticamente
- ✅ Método `json()` centralizado com limpeza de buffer
- ✅ WhatsAppController refatorado para usar `$this->json()`
- ✅ Cache busting automático em URLs
- ✅ Proteção contra cache de PHP, JS, CSS e JSON

---

## 🎯 Requisitos do Sistema

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

## 🔒 Segurança

### Arquivos Protegidos
- `.htaccess` protege arquivos sensíveis
- `config.php` não incluído no backup
- Senhas hasheadas com `password_hash()`
- Tokens únicos para reset de senha

### Boas Práticas Implementadas
- ✅ Prepared statements (SQL Injection)
- ✅ CSRF protection
- ✅ XSS protection
- ✅ Session security
- ✅ Input validation
- ✅ Output sanitization

---

## 📞 Suporte

**Desenvolvedor:** Dante Testa  
**Website:** [dantetesta.com.br](https://dantetesta.com.br)  
**WhatsApp:** +55 19 99802-1956

---

## 📄 Licença

Este backup contém código proprietário desenvolvido por Dante Testa.

---

**Backup criado automaticamente pelo sistema ZAPX**  
**Mantenha este arquivo seguro e em local protegido**
