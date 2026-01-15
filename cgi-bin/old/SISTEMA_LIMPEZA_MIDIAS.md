# 🧹 Sistema de Limpeza Automática de Mídias

## 📋 Visão Geral

O sistema ZAPX implementa limpeza automática de arquivos de mídia (fotos, vídeos, áudios, documentos) após o envio via WhatsApp. Isso evita acúmulo desnecessário de arquivos no servidor.

---

## 🔄 Como Funciona

### 1. **Upload Temporário**
```
Usuário → Upload → uploads/media/media_xxxxx.ext
```

Quando o usuário envia uma mídia:
- Arquivo é salvo em `uploads/media/`
- Nome único gerado: `media_[timestamp].[extensão]`
- Convertido para Base64 para envio

### 2. **Envio via Evolution API**
```
Base64 → Evolution API → WhatsApp
```

O arquivo é enviado via Evolution API:
- **Imagens/Documentos/Áudio:** Base64 puro
- **Vídeos > 3MB:** URL pública (solução híbrida)
- **Vídeos < 3MB:** Base64 puro

### 3. **Limpeza Automática Imediata**
```
✅ Sucesso → Arquivo removido
❌ Falha → Arquivo removido
⚠️ Exceção → Arquivo removido
```

**Após o envio (sucesso ou falha), o arquivo é IMEDIATAMENTE removido do servidor.**

---

## 🎯 Benefícios

### ✅ Economia de Espaço
- Não acumula arquivos desnecessários
- Servidor mantém apenas arquivos em uso

### ✅ Segurança
- Arquivos não ficam expostos no servidor
- Reduz risco de acesso não autorizado

### ✅ Performance
- Menos arquivos = menos I/O
- Diretório limpo e organizado

### ✅ Privacidade
- Mídias não são armazenadas permanentemente
- Apenas histórico de envio fica no banco

---

## 📂 Estrutura de Arquivos

```
ZAPX/
├── uploads/
│   └── media/                    ← Pasta temporária
│       ├── media_67123abc.jpg    ← Removido após envio
│       ├── media_67123def.mp4    ← Removido após envio
│       └── media_67123ghi.pdf    ← Removido após envio
└── cleanup_old_media.php         ← Script de limpeza (backup)
```

---

## 🔧 Implementação Técnica

### Código no `DispatchController.php`

#### Limpeza em Caso de Sucesso
```php
if ($result['success']) {
    // ... código de sucesso ...
    
    // 🧹 LIMPAR ARQUIVO DE MÍDIA
    if ($mediaData && isset($mediaData['filepath']) && file_exists($mediaData['filepath'])) {
        if (unlink($mediaData['filepath'])) {
            error_log("🗑️ Arquivo removido: " . $mediaData['filepath']);
        }
    }
}
```

#### Limpeza em Caso de Falha
```php
} else {
    // ... código de erro ...
    
    // 🧹 LIMPAR ARQUIVO DE MÍDIA
    if ($mediaData && isset($mediaData['filepath']) && file_exists($mediaData['filepath'])) {
        unlink($mediaData['filepath']);
    }
}
```

#### Limpeza em Caso de Exceção
```php
} catch (Exception $e) {
    // ... código de exceção ...
    
    // 🧹 LIMPAR ARQUIVO DE MÍDIA
    if ($mediaData && isset($mediaData['filepath']) && file_exists($mediaData['filepath'])) {
        unlink($mediaData['filepath']);
    }
}
```

---

## 🛡️ Script de Backup: `cleanup_old_media.php`

### Propósito
Remove arquivos "órfãos" (que não foram removidos por algum motivo) com mais de 1 hora.

### Uso Manual
```bash
php cleanup_old_media.php
```

### Uso Automático (Cron)
```bash
# Executar a cada hora
0 * * * * cd /var/www/zapx && php cleanup_old_media.php

# Executar a cada 6 horas
0 */6 * * * cd /var/www/zapx && php cleanup_old_media.php

# Executar diariamente às 3h da manhã
0 3 * * * cd /var/www/zapx && php cleanup_old_media.php
```

### Saída do Script
```
🧹 Iniciando limpeza de mídias antigas...
📁 Diretório: /var/www/zapx/uploads/media/
⏰ Removendo arquivos com mais de 60 minutos

🗑️  Removendo: media_67123abc.jpg
   ├─ Idade: 125 minutos
   ├─ Tamanho: 2.45 MB
   └─ ✅ Removido com sucesso

==================================================
📊 RESUMO DA LIMPEZA
==================================================
📁 Total de arquivos encontrados: 5
🗑️  Arquivos removidos: 3
💾 Espaço liberado: 8.32 MB
❌ Erros: 0
✅ Limpeza concluída!
```

---

## 📊 Fluxo Completo

```
┌─────────────────────────────────────────────────────┐
│ 1. USUÁRIO FAZ UPLOAD                               │
│    └─ Arquivo salvo em uploads/media/               │
└─────────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────┐
│ 2. CONVERSÃO PARA BASE64                            │
│    └─ Arquivo lido e convertido                     │
└─────────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────┐
│ 3. ENVIO VIA EVOLUTION API                          │
│    └─ Base64 enviado para WhatsApp                  │
└─────────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────┐
│ 4. LIMPEZA IMEDIATA                                 │
│    ├─ ✅ Sucesso → Arquivo removido                 │
│    ├─ ❌ Falha → Arquivo removido                   │
│    └─ ⚠️ Exceção → Arquivo removido                 │
└─────────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────┐
│ 5. BACKUP: Script Cron (opcional)                   │
│    └─ Remove órfãos com > 1 hora                    │
└─────────────────────────────────────────────────────┘
```

---

## ❓ FAQ

### Por que não manter os arquivos?
- **Espaço:** Acumula rapidamente (vídeos podem ter 16MB)
- **Segurança:** Arquivos ficam expostos no servidor
- **Desnecessário:** WhatsApp já tem a mídia

### E se precisar reenviar?
- Usuário faz novo upload
- Sistema não mantém histórico de mídias
- Apenas registra que foi enviado

### E se o envio falhar?
- Arquivo é removido mesmo assim
- Usuário pode tentar novamente
- Evita acúmulo de arquivos de falhas

### Posso desativar a limpeza?
- Não recomendado
- Servidor pode ficar sem espaço rapidamente
- Comentar linhas de `unlink()` se necessário

---

## 🔍 Logs

### Sucesso
```
✅ MENSAGEM ENVIADA COM SUCESSO!
🗑️ Arquivo de mídia removido: uploads/media/media_67123abc.jpg
```

### Falha
```
❌ FALHA NO ENVIO: Instance not found
🗑️ Arquivo de mídia removido (falha): uploads/media/media_67123def.mp4
```

### Exceção
```
❌ EXCEÇÃO NO ENVIO: Connection timeout
🗑️ Arquivo de mídia removido (exceção): uploads/media/media_67123ghi.pdf
```

---

## 📝 Resumo

| Item | Status |
|------|--------|
| **Limpeza Automática** | ✅ Ativa |
| **Limpeza Imediata** | ✅ Após envio |
| **Limpeza em Falha** | ✅ Sim |
| **Script de Backup** | ✅ Disponível |
| **Cron Recomendado** | ⚠️ Opcional |
| **Arquivos Mantidos** | ❌ Não |

---

**Desenvolvido por [Dante Testa](https://dantetesta.com.br)**  
**Data: 2025-10-27**
