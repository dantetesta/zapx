# 📚 Guia Completo de Integração com Evolution API V2

**Autor:** Dante Testa (https://dantetesta.com.br)  
**Data:** 2025-10-25  
**Versão:** 1.0  
**Projeto:** ZAPX - Sistema de Disparo em Massa

---

## 🎯 Sobre Este Guia

Este guia foi criado com base em **experiência real** de desenvolvimento de um sistema completo de disparo em massa (ZAPX). Todos os problemas, soluções e boas práticas aqui documentados foram testados e validados em produção.

### ✨ O que você vai aprender

✅ Como gerenciar instâncias WhatsApp  
✅ Como enviar mensagens de texto  
✅ Como enviar mídias (imagem, vídeo, áudio, documento)  
✅ Como tratar erros comuns  
✅ Como otimizar performance  
✅ Como implementar sistema multi-usuário  
✅ Boas práticas de segurança e logs  

### ⚠️ Pontos Críticos (LEIA PRIMEIRO!)

**🔑 Dois Tipos de Token**
```
Token Global → Gerenciar instâncias (/instance/*)
Token da Instância → Enviar mensagens (/message/*)
NUNCA misture!
```

**📦 Base64 PURO**
```php
// ❌ ERRADO (causa erro 400)
$media = "data:video/mp4;base64," . base64_encode($content);

// ✅ CORRETO
$media = base64_encode($content);
```

**🎬 Vídeos Grandes**
```php
// Vídeos > 3MB → Usar URL
if ($fileSizeMB > 3) {
    $data['media'] = 'https://site.com/video.mp4';
}
```

**📞 Números com DDI**
```php
'number' => '5511999999999'  // DDI + DDD + Número
```

---

# 📚 Guia Completo Evolution API - Parte 1: Fundamentos

**Autor:** Dante Testa (https://dantetesta.com.br)  
**Data:** 2025-10-25 19:50:00  
**Versão:** 1.0

---

## 🎯 Visão Geral

### O que é Evolution API?

Evolution API é uma API REST que permite integrar aplicações com WhatsApp através do protocolo Baileys. Ela gerencia instâncias WhatsApp independentes, permitindo múltiplas conexões simultâneas.

### Conceitos Fundamentais

**1. Instância**
- Representa uma conexão WhatsApp única
- Cada instância tem um nome único (identificador)
- Cada instância tem seu próprio token de autenticação
- Uma instância = Um número WhatsApp conectado

**2. Token (API Key)**
- **Token Global:** Usado para gerenciar instâncias (criar, deletar, listar)
- **Token da Instância:** Usado para enviar mensagens por aquela instância específica
- São diferentes e têm propósitos diferentes!

**3. Estados da Instância**
- `created`: Instância criada, aguardando conexão
- `connecting`: Tentando conectar ao WhatsApp
- `open`: Conectado e pronto para enviar mensagens
- `close`: Desconectado

---

## 🏗️ Arquitetura

### Modelo de Dados Necessário

```sql
-- Campos necessários na tabela de usuários
evolution_instance VARCHAR(100)           -- Nome único da instância
evolution_instance_token VARCHAR(255)     -- Token específico da instância
evolution_phone_number VARCHAR(20)        -- Número WhatsApp conectado
evolution_status VARCHAR(20)              -- Status (open/close/connecting)
evolution_qrcode TEXT                     -- QR Code base64 (temporário)
evolution_created_at DATETIME             -- Data de criação
```

### Fluxo de Autenticação

```
1. Aplicação → Evolution API (Token Global)
   └─> Criar/Gerenciar Instâncias
   
2. Aplicação → Evolution API (Token da Instância)
   └─> Enviar Mensagens/Mídias
```

**⚠️ IMPORTANTE:** Nunca misture os tokens!

---

## ⚙️ Configuração Inicial

### 1. Variáveis de Ambiente

```php
// config/config.php
define('EVOLUTION_API_URL', 'https://sua-api.com');
define('EVOLUTION_API_KEY', 'SEU_TOKEN_GLOBAL');
```

### 2. Estrutura Base de Requisições

```php
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . $apiKey
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
```

### 3. Timeouts Recomendados

```php
// Operações rápidas (status, criar instância)
CURLOPT_CONNECTTIMEOUT => 5   // 5s para conectar
CURLOPT_TIMEOUT => 15          // 15s total

// Envio de mídia
CURLOPT_CONNECTTIMEOUT => 30   // 30s para conectar
CURLOPT_TIMEOUT => 180         // 3 minutos total
```

---

## 🔧 Gerenciamento de Instâncias

### 1. Criar Instância

**Endpoint:** `POST /instance/create`  
**Autenticação:** Token Global

```php
// Gerar nome único
$instanceName = 'zapx_' . $userId . '_' . substr(md5(uniqid()), 0, 8);

// Payload
$data = [
    'instanceName' => $instanceName,
    'integration' => 'WHATSAPP-BAILEYS',
    'qrcode' => true,
    'number' => '5511999999999'
];

// Requisição
$endpoint = $apiUrl . '/instance/create';
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . EVOLUTION_API_KEY  // Token GLOBAL
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

// ⚠️ CRÍTICO: Extrair token da instância
$instanceToken = $result['hash'];  // Campo 'hash' É O TOKEN!

// Salvar no banco:
// - evolution_instance = $instanceName
// - evolution_instance_token = $instanceToken
// - evolution_status = 'created'
```

**Resposta:**
```json
{
  "instance": {
    "instanceName": "zapx_1_abc123",
    "status": "created"
  },
  "hash": "uuid-token-da-instancia",
  "qrcode": {
    "base64": "data:image/png;base64,..."
  }
}
```

---

### 2. Obter QR Code

**Endpoint:** `GET /instance/connect/{instanceName}`  
**Autenticação:** Token Global

```php
$endpoint = $apiUrl . '/instance/connect/' . $instanceName;

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . EVOLUTION_API_KEY
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

$qrcode = $result['qrcode'] ?? $result['base64'];

// Verificar prefixo
if (strpos($qrcode, 'data:image') !== 0) {
    $qrcode = 'data:image/png;base64,' . $qrcode;
}

// Exibir para usuário escanear
```

---

### 3. Verificar Status

**Endpoint:** `GET /instance/connectionState/{instanceName}`  
**Autenticação:** Token Global

```php
$endpoint = $apiUrl . '/instance/connectionState/' . $instanceName;

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . EVOLUTION_API_KEY
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

$status = $result['instance']['state'];
// Valores: 'open', 'close', 'connecting'

// Atualizar banco: evolution_status = $status
```

---

### 4. Desconectar

**Endpoint:** `POST /instance/logout/{instanceName}`  
**Autenticação:** Token Global

```php
$endpoint = $apiUrl . '/instance/logout/' . $instanceName;

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . EVOLUTION_API_KEY
]);

curl_exec($ch);

// Atualizar banco:
// - evolution_status = 'disconnected'
// - evolution_qrcode = null
```

---

### 5. Deletar

**Endpoint:** `DELETE /instance/delete/{instanceName}`  
**Autenticação:** Token Global

```php
$endpoint = $apiUrl . '/instance/delete/' . $instanceName;

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . EVOLUTION_API_KEY
]);

curl_exec($ch);

// Limpar TUDO do banco:
// - evolution_instance = null
// - evolution_instance_token = null
// - evolution_status = null
// - evolution_qrcode = null
// - evolution_phone_number = null
```

---

## 📝 Próxima Parte

Continue em: `GUIA_EVOLUTION_API_PARTE2.md`
- Envio de mensagens
- Formatação de números
- Envio de mídias
- Tratamento de erros
# 📚 Guia Completo Evolution API - Parte 2: Mensagens e Mídias

**Autor:** Dante Testa (https://dantetesta.com.br)  
**Data:** 2025-10-25 19:50:00  
**Versão:** 1.0

---

## 💬 Envio de Mensagens de Texto

### Endpoint

**URL:** `POST /message/sendText/{instanceName}`  
**Autenticação:** Token da Instância (não o global!)

### Implementação Completa

```php
// 1. Formatar número
$phone = preg_replace('/[^0-9]/', '', $phone);

if (strlen($phone) === 11) {
    $phone = '55' . $phone;  // Adicionar DDI Brasil
}

// 2. Preparar payload
$data = [
    'number' => $phone,
    'text' => $message
];

// 3. Fazer requisição
$endpoint = $apiUrl . '/message/sendText/' . $instanceName;

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . $instanceToken  // TOKEN DA INSTÂNCIA!
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 4. Verificar sucesso
if ($httpCode === 200 || $httpCode === 201) {
    // Mensagem enviada!
}
```

### Payload

```json
{
  "number": "5511999999999",
  "text": "Olá! Esta é uma mensagem de teste."
}
```

### Resposta de Sucesso

```json
{
  "key": {
    "remoteJid": "5511999999999@s.whatsapp.net",
    "fromMe": true,
    "id": "3EB0XXXXX"
  },
  "message": {
    "conversation": "Olá! Esta é uma mensagem de teste."
  },
  "messageTimestamp": "1234567890"
}
```

---

## 📞 Formatação de Números

### Função Completa

```php
function formatPhoneNumber($phone) {
    // Remover não-numéricos
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Já tem DDI (55 + 12-13 dígitos)
    if (strlen($phone) >= 12 && substr($phone, 0, 2) === '55') {
        return $phone;
    }
    
    // 11 dígitos (DDD + 9 + número)
    if (strlen($phone) === 11) {
        return '55' . $phone;
    }
    
    // 10 dígitos (DDD + número)
    if (strlen($phone) === 10) {
        $ddd = substr($phone, 0, 2);
        $firstDigit = substr($phone, 2, 1);
        
        // Celular (8 ou 9)
        if ($firstDigit === '8' || $firstDigit === '9') {
            // Adicionar 9 se começa com 8
            if ($firstDigit === '8') {
                $phone = $ddd . '9' . substr($phone, 2);
            }
            return '55' . $phone;
        }
        
        // Fixo
        return '55' . $phone;
    }
    
    // 9 dígitos - adicionar DDD padrão
    if (strlen($phone) === 9) {
        return '5511' . $phone;
    }
    
    // Outros casos
    return '5511' . $phone;
}
```

### Exemplos

| Entrada | Saída | Status |
|---------|-------|--------|
| `11999999999` | `5511999999999` | ✅ |
| `(11) 99999-9999` | `5511999999999` | ✅ |
| `5511999999999` | `5511999999999` | ✅ |
| `999999999` | `5511999999999` | ✅ |
| `1199999999` | `55119999999999` | ✅ |

---

## 📎 Envio de Mídias

### Tipos Suportados

| Tipo | MIME Types | Tamanho Máx |
|------|-----------|-------------|
| **image** | image/jpeg, image/png, image/gif, image/webp | 5MB |
| **video** | video/mp4, video/quicktime, video/avi | 16MB |
| **audio** | audio/mp3, audio/mpeg, audio/wav, audio/ogg | 16MB |
| **document** | application/pdf, application/msword, text/plain | 16MB |

### Métodos de Envio

**1. Base64** - Arquivos pequenos (< 3MB)
- Rápido
- Payload grande
- Limite: ~5-6MB

**2. URL** - Arquivos grandes (> 3MB)
- Payload pequeno
- Sem limite de tamanho
- Recomendado para vídeos

---

## 🖼️ Enviar Imagem/Vídeo/Documento

### Endpoint

**URL:** `POST /message/sendMedia/{instanceName}`  
**Autenticação:** Token da Instância

### Implementação Completa

```php
// 1. Processar upload
$file = $_FILES['media_file'];

// 2. Validar MIME type
$allowedTypes = [
    'image' => ['image/jpeg', 'image/png', 'image/gif'],
    'video' => ['video/mp4', 'video/quicktime'],
    'document' => ['application/pdf']
];

if (!in_array($file['type'], $allowedTypes[$mediaType])) {
    return ['error' => 'Tipo não permitido'];
}

// 3. Validar tamanho
$maxSizes = [
    'image' => 5 * 1024 * 1024,
    'video' => 16 * 1024 * 1024,
    'document' => 16 * 1024 * 1024
];

if ($file['size'] > $maxSizes[$mediaType]) {
    return ['error' => 'Arquivo muito grande'];
}

// 4. Salvar temporariamente
$uploadDir = 'uploads/media/';
$filename = uniqid('media_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
$filepath = $uploadDir . $filename;
move_uploaded_file($file['tmp_name'], $filepath);

// 5. Decidir método (Base64 ou URL)
$fileSizeMB = $file['size'] / 1024 / 1024;

if ($mediaType === 'video' && $fileSizeMB > 3) {
    // VÍDEO GRANDE: Usar URL
    $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $fileUrl = $protocol . '://' . $host . '/' . $filepath;
    
    $data = [
        'number' => $phone,
        'mediatype' => 'video',
        'media' => $fileUrl,
        'fileName' => $filename,
        'caption' => $caption
    ];
    
    $deleteAfter = false;  // Manter arquivo
    
} else {
    // ARQUIVO PEQUENO: Usar Base64
    $fileContent = file_get_contents($filepath);
    $base64 = base64_encode($fileContent);  // PURO, sem prefixo!
    
    $data = [
        'number' => $phone,
        'mediatype' => $mediaType,
        'mimetype' => $file['type'],
        'media' => $base64,  // Base64 PURO
        'fileName' => $filename,
        'caption' => $caption
    ];
    
    $deleteAfter = true;  // Deletar após envio
}

// 6. Enviar
$endpoint = $apiUrl . '/message/sendMedia/' . $instanceName;

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . $instanceToken
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 180);  // 3 minutos

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 7. Limpar arquivo se necessário
if ($deleteAfter) {
    unlink($filepath);
}
```

### Payload (Base64)

```json
{
  "number": "5511999999999",
  "mediatype": "image",
  "mimetype": "image/jpeg",
  "media": "iVBORw0KGgoAAAANSUhEUgAA...",
  "fileName": "foto.jpg",
  "caption": "Legenda da imagem"
}
```

### Payload (URL)

```json
{
  "number": "5511999999999",
  "mediatype": "video",
  "media": "https://seusite.com/uploads/media/video.mp4",
  "fileName": "video.mp4",
  "caption": "Legenda do vídeo"
}
```

---

## 🎵 Enviar Áudio

### Endpoint

**URL:** `POST /message/sendWhatsAppAudio/{instanceName}`  
**Autenticação:** Token da Instância

### Implementação

```php
// 1. Processar arquivo
$file = $_FILES['audio_file'];

// 2. Converter para base64 PURO
$fileContent = file_get_contents($file['tmp_name']);
$base64 = base64_encode($fileContent);

// 3. Preparar payload
$data = [
    'number' => $phone,
    'audio' => $base64,
    'encoding' => true
];

// 4. Enviar
$endpoint = $apiUrl . '/message/sendWhatsAppAudio/' . $instanceName;

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . $instanceToken
]);

$response = curl_exec($ch);
```

### Payload

```json
{
  "number": "5511999999999",
  "audio": "SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2Z...",
  "encoding": true
}
```

---

## ⚠️ Problemas Comuns e Soluções

### 1. "Owned media must be a url or base64"

**Causa:** Base64 com prefixo `data:` incorreto

**Solução:**
```php
// ❌ ERRADO
$base64 = "data:video/mp4;base64," . base64_encode($content);

// ✅ CORRETO
$base64 = base64_encode($content);  // Apenas o base64 puro
```

---

### 2. "Maximum call stack size exceeded"

**Causa:** Payload JSON muito grande (vídeo > 5MB em base64)

**Solução:**
```php
$fileSizeMB = $file['size'] / 1024 / 1024;

if ($mediaType === 'video' && $fileSizeMB > 3) {
    // Usar URL ao invés de base64
    $data['media'] = $fileUrl;
} else {
    // Usar base64
    $data['media'] = $base64;
}
```

---

### 3. "Instance not found"

**Causa:** Instância não existe ou foi deletada

**Solução:**
1. Verificar se instância foi criada
2. Verificar nome no banco de dados
3. Recriar instância se necessário

---

### 4. "WhatsApp not connected"

**Causa:** QR Code não foi escaneado

**Solução:**
```php
// Verificar status
$endpoint = $apiUrl . '/instance/connectionState/' . $instanceName;
$result = makeRequest($endpoint);

if ($result['instance']['state'] !== 'open') {
    // Gerar novo QR Code
    $qrEndpoint = $apiUrl . '/instance/connect/' . $instanceName;
    $qrResult = makeRequest($qrEndpoint);
    
    // Exibir QR Code para usuário
    return $qrResult['qrcode'];
}
```

---

### 5. Timeout na requisição

**Causa:** Arquivo muito grande ou conexão lenta

**Solução:**
```php
// Aumentar timeout
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 180);  // 3 minutos

// Ou usar URL para vídeos grandes
if ($fileSizeMB > 3) {
    $data['media'] = $fileUrl;
}
```

---

## 📝 Próxima Parte

Continue em: `GUIA_EVOLUTION_API_PARTE3.md`
- Códigos HTTP e erros
- Boas práticas
- Logs e monitoramento
- Casos de uso reais
# 📚 Guia Completo Evolution API - Parte 3: Erros e Boas Práticas

**Autor:** Dante Testa (https://dantetesta.com.br)  
**Data:** 2025-10-25 19:50:00  
**Versão:** 1.0

---

## ⚠️ Códigos HTTP e Tratamento de Erros

### Códigos Comuns

```php
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

switch ($httpCode) {
    case 200:
    case 201:
        // ✅ Sucesso
        $result = json_decode($response, true);
        break;
        
    case 400:
        // ❌ Bad Request
        // - Payload inválido
        // - Número de telefone incorreto
        // - Base64 com prefixo (remover!)
        // - Campo obrigatório faltando
        break;
        
    case 401:
        // ❌ Unauthorized
        // - Token inválido ou expirado
        // - Usando token errado (global vs instância)
        break;
        
    case 404:
        // ❌ Not Found
        // - Instância não existe
        // - Endpoint incorreto
        break;
        
    case 500:
        // ❌ Internal Server Error
        // - "Maximum call stack" → Usar URL
        // - Timeout na Evolution API
        // - Erro interno da API
        break;
}
```

### Tratamento Robusto

```php
function makeAPIRequest($endpoint, $apiKey, $data) {
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 180);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Erro de conexão
    if ($error) {
        return [
            'success' => false,
            'error' => 'Erro de conexão: ' . $error
        ];
    }
    
    // Erro HTTP
    if ($httpCode !== 200 && $httpCode !== 201) {
        $responseData = json_decode($response, true);
        $errorMsg = $responseData['message'] ?? 
                    $responseData['error'] ?? 
                    'Erro desconhecido';
        
        return [
            'success' => false,
            'error' => "HTTP $httpCode: $errorMsg",
            'debug' => $responseData
        ];
    }
    
    // Sucesso
    return [
        'success' => true,
        'response' => json_decode($response, true)
    ];
}
```

---

## ✅ Boas Práticas

### 1. Segurança

```php
// ✅ CORRETO: Tokens em variáveis de ambiente
define('EVOLUTION_API_KEY', getenv('EVOLUTION_API_KEY'));

// ❌ ERRADO: Tokens hardcoded
define('EVOLUTION_API_KEY', 'abc123...');

// ✅ CORRETO: Validar entrada
$phone = preg_replace('/[^0-9]/', '', $_POST['phone']);

// ✅ CORRETO: Validar MIME type
$allowedTypes = ['image/jpeg', 'image/png'];
if (!in_array($file['type'], $allowedTypes)) {
    throw new Exception('Tipo não permitido');
}

// ✅ CORRETO: Sanitizar nome de arquivo
$filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
```

### 2. Performance

```php
// ✅ CORRETO: Usar URL para vídeos grandes
if ($mediaType === 'video' && $fileSizeMB > 3) {
    $data['media'] = $fileUrl;  // Rápido
} else {
    $data['media'] = $base64;   // Pequeno
}

// ✅ CORRETO: Timeout adequado
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 180);

// ✅ CORRETO: Limpar arquivos temporários
if ($sentViaBase64) {
    unlink($filepath);
}

// ✅ CORRETO: Reutilizar conexões
curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
```

### 3. Logs e Monitoramento

```php
// ✅ CORRETO: Logs estruturados
error_log("=== ENVIANDO MENSAGEM ===");
error_log("Instance: $instanceName");
error_log("Phone: $phone");
error_log("Media Type: $mediaType");
error_log("File Size: {$fileSizeMB}MB");
error_log("Method: " . ($useUrl ? 'URL' : 'Base64'));

// ✅ CORRETO: Log de tempo
$startTime = microtime(true);
// ... fazer requisição ...
$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);
error_log("Tempo: {$executionTime}s");

// ✅ CORRETO: Log de erros
if (!$result['success']) {
    error_log("❌ ERRO: " . $result['error']);
    error_log("Endpoint: $endpoint");
    error_log("Payload: " . json_encode($data));
}

// ✅ CORRETO: Log de sucesso
error_log("✅ Mensagem enviada com sucesso!");
```

### 4. Banco de Dados

```php
// ✅ CORRETO: Salvar token da instância
$instanceToken = $result['hash'];  // Campo 'hash'
$db->update('users', [
    'evolution_instance' => $instanceName,
    'evolution_instance_token' => $instanceToken,
    'evolution_status' => 'created'
]);

// ✅ CORRETO: Atualizar status
$db->update('users', [
    'evolution_status' => $status
]);

// ✅ CORRETO: Registrar histórico
$db->insert('dispatch_history', [
    'user_id' => $userId,
    'contact_id' => $contactId,
    'message' => $message,
    'status' => 'sent',
    'created_at' => date('Y-m-d H:i:s')
]);
```

### 5. Tratamento de Erros

```php
// ✅ CORRETO: Try-catch em operações críticas
try {
    $result = sendMessage($phone, $message);
    
    if ($result['success']) {
        // Sucesso
        incrementMessageCount($userId);
        updateHistory($historyId, 'sent');
    } else {
        // Erro tratado
        updateHistory($historyId, 'failed', $result['error']);
    }
    
} catch (Exception $e) {
    // Erro crítico
    error_log("ERRO CRÍTICO: " . $e->getMessage());
    updateHistory($historyId, 'failed', $e->getMessage());
}

// ✅ CORRETO: Mensagens amigáveis
if (!$result['success']) {
    $userMessage = "Falha ao enviar mensagem.\n\n";
    $userMessage .= "Verifique:\n";
    $userMessage .= "• WhatsApp está conectado?\n";
    $userMessage .= "• Número está correto?\n";
    $userMessage .= "• Instância está ativa?";
    
    return $userMessage;
}
```

---

## 📊 Casos de Uso Reais

### 1. Sistema Multi-Usuário

```php
// Cada usuário tem sua própria instância
class DispatchController {
    public function send() {
        $user = getCurrentUser();
        
        // Buscar configuração do usuário
        $userData = $this->userModel->findById($user['id']);
        
        // Usar instância e token do usuário
        $instanceName = $userData['evolution_instance'];
        $instanceToken = $userData['evolution_instance_token'];
        
        // Enviar mensagem
        $result = $this->sendMessage(
            $instanceName,
            $instanceToken,
            $phone,
            $message
        );
    }
}
```

### 2. Disparo em Massa

```php
function sendBulkMessages($contacts, $message) {
    $results = [];
    
    foreach ($contacts as $contact) {
        // Delay entre mensagens (evitar ban)
        sleep(2);
        
        // Enviar
        $result = sendMessage($contact['phone'], $message);
        
        // Registrar resultado
        $results[] = [
            'contact' => $contact['name'],
            'status' => $result['success'] ? 'sent' : 'failed',
            'error' => $result['error'] ?? null
        ];
        
        // Parar se muitos erros
        $failures = count(array_filter($results, fn($r) => $r['status'] === 'failed'));
        if ($failures > 5) {
            break;  // Algo está errado
        }
    }
    
    return $results;
}
```

### 3. Webhook para Status

```php
// Receber notificações da Evolution API
public function webhook() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $event = $data['event'];
    $instance = $data['instance'];
    
    switch ($event) {
        case 'connection.update':
            $status = $data['data']['state'];
            
            // Atualizar banco
            $this->userModel->updateByInstance($instance, [
                'evolution_status' => $status
            ]);
            break;
            
        case 'messages.upsert':
            // Nova mensagem recebida
            $message = $data['data']['message'];
            // Processar...
            break;
    }
}
```

### 4. Reconexão Automática

```php
function checkAndReconnect($userId) {
    $user = $this->userModel->findById($userId);
    $instance = $user['evolution_instance'];
    
    // Verificar status
    $status = $this->getInstanceStatus($instance);
    
    if ($status !== 'open') {
        // Gerar novo QR Code
        $qrcode = $this->getQRCode($instance);
        
        // Notificar usuário
        $this->notify($userId, [
            'type' => 'reconnect_needed',
            'qrcode' => $qrcode
        ]);
    }
}
```

### 5. Limite de Mensagens

```php
function sendWithLimit($userId, $phone, $message) {
    // Verificar limite
    $balance = $this->userModel->getMessageBalance($userId);
    
    if ($balance['sent'] >= $balance['limit']) {
        return [
            'success' => false,
            'error' => "Limite atingido: {$balance['sent']}/{$balance['limit']}"
        ];
    }
    
    // Enviar
    $result = $this->sendMessage($phone, $message);
    
    if ($result['success']) {
        // Incrementar contador
        $this->userModel->incrementMessageCount($userId);
    }
    
    return $result;
}
```

---

## 🔍 Troubleshooting

### Checklist de Diagnóstico

```php
// 1. Verificar configuração
function diagnosticConfig() {
    echo "Evolution API URL: " . EVOLUTION_API_URL . "\n";
    echo "Token Global: " . substr(EVOLUTION_API_KEY, 0, 10) . "...\n";
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "cURL: " . (function_exists('curl_version') ? 'OK' : 'FALTANDO') . "\n";
}

// 2. Testar conexão
function diagnosticConnection() {
    $ch = curl_init(EVOLUTION_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Erro de conexão: $error\n";
    } else {
        echo "✅ Conexão OK (HTTP $httpCode)\n";
    }
}

// 3. Verificar instância
function diagnosticInstance($instanceName) {
    $endpoint = EVOLUTION_API_URL . '/instance/connectionState/' . $instanceName;
    
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . EVOLUTION_API_KEY
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $status = $data['instance']['state'];
        echo "✅ Instância: $status\n";
    } else {
        echo "❌ Instância não encontrada\n";
    }
}

// 4. Testar envio
function diagnosticSend($instanceName, $instanceToken, $phone) {
    $data = [
        'number' => $phone,
        'text' => 'Teste de diagnóstico'
    ];
    
    $endpoint = EVOLUTION_API_URL . '/message/sendText/' . $instanceName;
    
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $instanceToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 201) {
        echo "✅ Mensagem enviada!\n";
    } else {
        echo "❌ Erro ao enviar (HTTP $httpCode)\n";
        echo "Resposta: $response\n";
    }
}
```

---

## 📝 Resumo Final

### Pontos Críticos

1. **Tokens:**
   - Token Global → Gerenciar instâncias
   - Token da Instância → Enviar mensagens
   - NUNCA misture!

2. **Base64:**
   - SEMPRE puro (sem prefixo `data:`)
   - Vídeos > 3MB → Usar URL

3. **Números:**
   - Sempre com DDI (55 para Brasil)
   - Formato: 5511999999999

4. **Timeouts:**
   - Connect: 30s
   - Total: 180s (vídeos)

5. **Erros:**
   - Sempre tratar HTTP codes
   - Logs detalhados
   - Mensagens amigáveis

### Arquitetura Recomendada

```
┌─────────────┐
│  Aplicação  │
└──────┬──────┘
       │
       ├─────────────────────┐
       │                     │
       ▼                     ▼
┌──────────────┐    ┌──────────────┐
│ Token Global │    │ Token Inst.  │
└──────┬───────┘    └──────┬───────┘
       │                   │
       ▼                   ▼
┌──────────────┐    ┌──────────────┐
│   Gerenciar  │    │    Enviar    │
│  Instâncias  │    │   Mensagens  │
└──────────────┘    └──────────────┘
```

---

**🎉 Fim do Guia!**

Com este guia, você tem todo o conhecimento necessário para integrar com Evolution API de forma profissional e sem erros.
# 📚 Guia Evolution API - Exemplos Práticos Completos

**Autor:** Dante Testa (https://dantetesta.com.br)  
**Data:** 2025-10-25 19:50:00

---

## 🎯 Classe Completa de Integração

```php
<?php
/**
 * Classe de Integração com Evolution API V2
 * Baseada em implementação real do ZAPX
 */
class EvolutionAPI {
    private $apiUrl;
    private $globalToken;
    
    public function __construct($apiUrl, $globalToken) {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->globalToken = $globalToken;
    }
    
    /**
     * Criar nova instância
     */
    public function createInstance($userId, $phoneNumber) {
        // Gerar nome único
        $instanceName = 'app_' . $userId . '_' . substr(md5(uniqid()), 0, 8);
        
        // Formatar número
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (strlen($phoneNumber) === 11) {
            $phoneNumber = '55' . $phoneNumber;
        }
        
        // Payload
        $data = [
            'instanceName' => $instanceName,
            'integration' => 'WHATSAPP-BAILEYS',
            'qrcode' => true,
            'number' => $phoneNumber
        ];
        
        // Requisição
        $result = $this->makeRequest(
            '/instance/create',
            'POST',
            $data,
            $this->globalToken
        );
        
        if ($result['success']) {
            // Extrair token da instância
            $instanceToken = $result['data']['hash'] ?? null;
            
            return [
                'success' => true,
                'instanceName' => $instanceName,
                'instanceToken' => $instanceToken,
                'qrcode' => $result['data']['qrcode']['base64'] ?? null
            ];
        }
        
        return $result;
    }
    
    /**
     * Obter QR Code
     */
    public function getQRCode($instanceName) {
        $result = $this->makeRequest(
            "/instance/connect/$instanceName",
            'GET',
            null,
            $this->globalToken
        );
        
        if ($result['success']) {
            $qrcode = $result['data']['qrcode'] ?? 
                     $result['data']['base64'] ?? null;
            
            // Adicionar prefixo se necessário
            if ($qrcode && strpos($qrcode, 'data:image') !== 0) {
                $qrcode = 'data:image/png;base64,' . $qrcode;
            }
            
            return [
                'success' => true,
                'qrcode' => $qrcode
            ];
        }
        
        return $result;
    }
    
    /**
     * Verificar status da instância
     */
    public function getStatus($instanceName) {
        $result = $this->makeRequest(
            "/instance/connectionState/$instanceName",
            'GET',
            null,
            $this->globalToken
        );
        
        if ($result['success']) {
            $status = $result['data']['instance']['state'] ?? 'unknown';
            
            return [
                'success' => true,
                'status' => $status,
                'connected' => $status === 'open'
            ];
        }
        
        return $result;
    }
    
    /**
     * Enviar mensagem de texto
     */
    public function sendText($instanceName, $instanceToken, $phone, $message) {
        // Formatar número
        $phone = $this->formatPhone($phone);
        
        // Payload
        $data = [
            'number' => $phone,
            'text' => $message
        ];
        
        // Enviar
        return $this->makeRequest(
            "/message/sendText/$instanceName",
            'POST',
            $data,
            $instanceToken
        );
    }
    
    /**
     * Enviar mídia (imagem, vídeo, documento)
     */
    public function sendMedia($instanceName, $instanceToken, $phone, $filePath, $mediaType, $caption = '') {
        // Formatar número
        $phone = $this->formatPhone($phone);
        
        // Verificar tamanho do arquivo
        $fileSize = filesize($filePath);
        $fileSizeMB = $fileSize / 1024 / 1024;
        
        // Decidir método (Base64 ou URL)
        if ($mediaType === 'video' && $fileSizeMB > 3) {
            // VÍDEO GRANDE: Usar URL
            $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $fileUrl = $protocol . '://' . $host . '/' . $filePath;
            
            $data = [
                'number' => $phone,
                'mediatype' => $mediaType,
                'media' => $fileUrl,
                'fileName' => basename($filePath),
                'caption' => $caption
            ];
        } else {
            // ARQUIVO PEQUENO: Usar Base64
            $fileContent = file_get_contents($filePath);
            $base64 = base64_encode($fileContent);
            $mimeType = mime_content_type($filePath);
            
            $data = [
                'number' => $phone,
                'mediatype' => $mediaType,
                'mimetype' => $mimeType,
                'media' => $base64,  // Base64 PURO
                'fileName' => basename($filePath),
                'caption' => $caption
            ];
        }
        
        // Enviar
        return $this->makeRequest(
            "/message/sendMedia/$instanceName",
            'POST',
            $data,
            $instanceToken,
            180  // Timeout de 3 minutos
        );
    }
    
    /**
     * Enviar áudio
     */
    public function sendAudio($instanceName, $instanceToken, $phone, $audioPath) {
        // Formatar número
        $phone = $this->formatPhone($phone);
        
        // Converter para base64
        $fileContent = file_get_contents($audioPath);
        $base64 = base64_encode($fileContent);
        
        // Payload
        $data = [
            'number' => $phone,
            'audio' => $base64,
            'encoding' => true
        ];
        
        // Enviar
        return $this->makeRequest(
            "/message/sendWhatsAppAudio/$instanceName",
            'POST',
            $data,
            $instanceToken
        );
    }
    
    /**
     * Desconectar instância
     */
    public function disconnect($instanceName) {
        return $this->makeRequest(
            "/instance/logout/$instanceName",
            'POST',
            null,
            $this->globalToken
        );
    }
    
    /**
     * Deletar instância
     */
    public function deleteInstance($instanceName) {
        return $this->makeRequest(
            "/instance/delete/$instanceName",
            'DELETE',
            null,
            $this->globalToken
        );
    }
    
    /**
     * Fazer requisição HTTP
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null, $token = null, $timeout = 30) {
        $url = $this->apiUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . ($token ?? $this->globalToken)
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Erro de conexão
        if ($error) {
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $error
            ];
        }
        
        // Erro HTTP
        if ($httpCode !== 200 && $httpCode !== 201) {
            $responseData = json_decode($response, true);
            $errorMsg = $responseData['message'] ?? 
                       $responseData['error'] ?? 
                       'Erro desconhecido';
            
            return [
                'success' => false,
                'error' => "HTTP $httpCode: $errorMsg",
                'debug' => $responseData
            ];
        }
        
        // Sucesso
        return [
            'success' => true,
            'data' => json_decode($response, true)
        ];
    }
    
    /**
     * Formatar número de telefone
     */
    private function formatPhone($phone) {
        // Remover não-numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Já tem DDI
        if (strlen($phone) >= 12 && substr($phone, 0, 2) === '55') {
            return $phone;
        }
        
        // 11 dígitos
        if (strlen($phone) === 11) {
            return '55' . $phone;
        }
        
        // 10 dígitos
        if (strlen($phone) === 10) {
            $ddd = substr($phone, 0, 2);
            $firstDigit = substr($phone, 2, 1);
            
            if ($firstDigit === '8' || $firstDigit === '9') {
                if ($firstDigit === '8') {
                    $phone = $ddd . '9' . substr($phone, 2);
                }
                return '55' . $phone;
            }
            
            return '55' . $phone;
        }
        
        // Outros casos
        return '5511' . $phone;
    }
}
```

---

## 💡 Exemplo de Uso

```php
<?php
// Inicializar
$api = new EvolutionAPI(
    'https://sua-api.com',
    'SEU_TOKEN_GLOBAL'
);

// 1. CRIAR INSTÂNCIA
$result = $api->createInstance(1, '11999999999');

if ($result['success']) {
    $instanceName = $result['instanceName'];
    $instanceToken = $result['instanceToken'];
    $qrcode = $result['qrcode'];
    
    // Salvar no banco de dados
    $db->update('users', [
        'evolution_instance' => $instanceName,
        'evolution_instance_token' => $instanceToken,
        'evolution_qrcode' => $qrcode,
        'evolution_status' => 'created'
    ]);
    
    echo "QR Code: <img src='$qrcode'>";
}

// 2. VERIFICAR STATUS (polling)
$status = $api->getStatus($instanceName);

if ($status['connected']) {
    echo "WhatsApp conectado!";
    
    // Atualizar banco
    $db->update('users', [
        'evolution_status' => 'open'
    ]);
}

// 3. ENVIAR MENSAGEM DE TEXTO
$result = $api->sendText(
    $instanceName,
    $instanceToken,
    '11999999999',
    'Olá! Mensagem de teste.'
);

if ($result['success']) {
    echo "Mensagem enviada!";
}

// 4. ENVIAR IMAGEM
$result = $api->sendMedia(
    $instanceName,
    $instanceToken,
    '11999999999',
    'uploads/media/foto.jpg',
    'image',
    'Legenda da foto'
);

// 5. ENVIAR VÍDEO
$result = $api->sendMedia(
    $instanceName,
    $instanceToken,
    '11999999999',
    'uploads/media/video.mp4',
    'video',
    'Legenda do vídeo'
);

// 6. ENVIAR ÁUDIO
$result = $api->sendAudio(
    $instanceName,
    $instanceToken,
    '11999999999',
    'uploads/media/audio.mp3'
);

// 7. DESCONECTAR
$result = $api->disconnect($instanceName);

// 8. DELETAR INSTÂNCIA
$result = $api->deleteInstance($instanceName);

// Limpar banco
$db->update('users', [
    'evolution_instance' => null,
    'evolution_instance_token' => null,
    'evolution_status' => null
]);
```

---

## 🎬 Exemplo: Sistema de Disparo em Massa

```php
<?php
class DispatchSystem {
    private $api;
    private $db;
    
    public function __construct($api, $db) {
        $this->api = $api;
        $this->db = $db;
    }
    
    /**
     * Enviar para múltiplos contatos
     */
    public function sendBulk($userId, $contacts, $message, $mediaPath = null, $mediaType = null) {
        // Buscar dados do usuário
        $user = $this->db->query("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (empty($user['evolution_instance'])) {
            return ['error' => 'Instância não configurada'];
        }
        
        $instanceName = $user['evolution_instance'];
        $instanceToken = $user['evolution_instance_token'];
        
        // Verificar se está conectado
        $status = $this->api->getStatus($instanceName);
        if (!$status['connected']) {
            return ['error' => 'WhatsApp não conectado'];
        }
        
        $results = [];
        $sent = 0;
        $failed = 0;
        
        foreach ($contacts as $contact) {
            // Delay entre mensagens (evitar ban)
            sleep(2);
            
            // Enviar
            if ($mediaPath) {
                $result = $this->api->sendMedia(
                    $instanceName,
                    $instanceToken,
                    $contact['phone'],
                    $mediaPath,
                    $mediaType,
                    $message
                );
            } else {
                $result = $this->api->sendText(
                    $instanceName,
                    $instanceToken,
                    $contact['phone'],
                    $message
                );
            }
            
            // Registrar resultado
            $status = $result['success'] ? 'sent' : 'failed';
            
            $this->db->insert('dispatch_history', [
                'user_id' => $userId,
                'contact_id' => $contact['id'],
                'message' => $message,
                'status' => $status,
                'error' => $result['error'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $results[] = [
                'contact' => $contact['name'],
                'phone' => $contact['phone'],
                'status' => $status,
                'error' => $result['error'] ?? null
            ];
            
            if ($status === 'sent') {
                $sent++;
            } else {
                $failed++;
            }
            
            // Parar se muitos erros consecutivos
            if ($failed > 5) {
                break;
            }
        }
        
        return [
            'success' => true,
            'sent' => $sent,
            'failed' => $failed,
            'results' => $results
        ];
    }
}

// Uso
$dispatch = new DispatchSystem($api, $db);

$contacts = [
    ['id' => 1, 'name' => 'João', 'phone' => '11999999999'],
    ['id' => 2, 'name' => 'Maria', 'phone' => '11988888888']
];

$result = $dispatch->sendBulk(
    1,  // userId
    $contacts,
    'Olá {nome}! Mensagem personalizada.',
    'uploads/media/promo.jpg',
    'image'
);

echo "Enviadas: {$result['sent']}\n";
echo "Falharam: {$result['failed']}\n";
```

---

## 🔄 Exemplo: Reconexão Automática

```php
<?php
class AutoReconnect {
    private $api;
    private $db;
    
    public function checkAllInstances() {
        // Buscar todos os usuários com instância
        $users = $this->db->query("
            SELECT * FROM users 
            WHERE evolution_instance IS NOT NULL
        ");
        
        foreach ($users as $user) {
            $this->checkAndReconnect($user);
        }
    }
    
    private function checkAndReconnect($user) {
        $instanceName = $user['evolution_instance'];
        
        // Verificar status
        $status = $this->api->getStatus($instanceName);
        
        if (!$status['connected']) {
            // Gerar novo QR Code
            $qr = $this->api->getQRCode($instanceName);
            
            if ($qr['success']) {
                // Atualizar banco
                $this->db->update('users', [
                    'evolution_status' => 'disconnected',
                    'evolution_qrcode' => $qr['qrcode']
                ], ['id' => $user['id']]);
                
                // Notificar usuário (email, push, etc)
                $this->notifyUser($user, $qr['qrcode']);
            }
        } else {
            // Atualizar status
            $this->db->update('users', [
                'evolution_status' => 'open'
            ], ['id' => $user['id']]);
        }
    }
    
    private function notifyUser($user, $qrcode) {
        // Enviar email, push notification, etc
        mail(
            $user['email'],
            'WhatsApp Desconectado',
            "Seu WhatsApp foi desconectado. Escaneie o QR Code novamente."
        );
    }
}

// Executar via cron a cada 5 minutos
$reconnect = new AutoReconnect($api, $db);
$reconnect->checkAllInstances();
```

---

**🎉 Exemplos Completos!**

Com esses exemplos, você tem código pronto para copiar e adaptar ao seu projeto!
