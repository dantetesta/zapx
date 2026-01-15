# 🕐 Configuração do Cron Job - ZAPX

**Autor:** [Dante Testa](https://dantetesta.com.br)  
**Data:** 2026-01-14  
**Versão:** 3.3.0

---

## 📋 O que é o Cron Job?

O Cron Job é uma tarefa agendada que executa automaticamente o processador de filas do ZAPX. Isso permite que as **Campanhas de Disparo** funcionem em segundo plano, sem precisar manter o navegador aberto.

### Benefícios:
- ✅ Campanhas continuam mesmo com navegador fechado
- ✅ Processamento automático a cada minuto
- ✅ Não depende de interação do usuário
- ✅ Maior confiabilidade nos disparos

---

## 🔧 Comando do Cron

```bash
* * * * * php /caminho/para/seu/site/cron/process_queue.php >> /dev/null 2>&1
```

### Explicação:
| Parte | Significado |
|-------|-------------|
| `* * * * *` | Executa a cada minuto |
| `php` | Interpretador PHP |
| `/caminho/.../cron/process_queue.php` | Script do processador |
| `>> /dev/null 2>&1` | Descarta output (silencioso) |

### Com log (para debug):
```bash
* * * * * php /caminho/para/seu/site/cron/process_queue.php >> /caminho/para/logs/cron.log 2>&1
```

---

## 🖥️ Configuração por Painel

### cPanel

1. Acesse o **cPanel** do seu servidor
2. Procure por **"Cron Jobs"** ou **"Tarefas Cron"**
3. Em **"Adicionar Novo Cron Job"**:
   - **Configuração comum:** Selecione `Uma vez por minuto (* * * * *)`
   - **Comando:**
   ```bash
   php /home/SEU_USUARIO/public_html/cron/process_queue.php >> /dev/null 2>&1
   ```
4. Clique em **"Adicionar Novo Cron Job"**

#### Exemplo para cPanel:
```bash
php /home/dantetesta/public_html/cron/process_queue.php >> /dev/null 2>&1
```

#### Alternativa com caminho completo do PHP:
```bash
/usr/local/bin/php /home/SEU_USUARIO/public_html/cron/process_queue.php >> /dev/null 2>&1
```

---

### DirectAdmin

1. Acesse o **DirectAdmin**
2. Vá em **"Advanced Features"** → **"Cron Jobs"**
3. Clique em **"Create Cron Job"**
4. Configure:
   - **Minuto:** `*`
   - **Hora:** `*`
   - **Dia do Mês:** `*`
   - **Mês:** `*`
   - **Dia da Semana:** `*`
   - **Comando:**
   ```bash
   php /home/SEU_USUARIO/domains/seusite.com.br/public_html/cron/process_queue.php >> /dev/null 2>&1
   ```
5. Clique em **"Create"**

#### Exemplo para DirectAdmin:
```bash
php /home/dantetesta/domains/zap.dantetesta.com.br/public_html/cron/process_queue.php >> /dev/null 2>&1
```

---

### Plesk

1. Acesse o **Plesk**
2. Vá em **"Ferramentas & Configurações"** → **"Tarefas Agendadas (Cron)"**
3. Ou acesse via domínio: **Websites & Domínios** → **Seu Domínio** → **"Tarefas Agendadas"**
4. Clique em **"Adicionar Tarefa"**
5. Configure:
   - **Executar:** `Cron style * * * * *`
   - **Comando:**
   ```bash
   php /var/www/vhosts/seusite.com.br/httpdocs/cron/process_queue.php
   ```
6. Salve a tarefa

#### Exemplo para Plesk:
```bash
php /var/www/vhosts/zap.dantetesta.com.br/httpdocs/cron/process_queue.php >> /dev/null 2>&1
```

---

### Hospedagem Compartilhada (Genérico)

Se sua hospedagem tem um painel diferente, procure por:
- **"Cron Jobs"**
- **"Tarefas Agendadas"**
- **"Scheduled Tasks"**
- **"Agendador de Tarefas"**

E configure com:
```bash
php /caminho/completo/para/cron/process_queue.php >> /dev/null 2>&1
```

> 💡 **Dica:** Para descobrir o caminho completo, crie um arquivo `info.php` com `<?php echo __DIR__; ?>` e acesse pelo navegador.

---

### VPS / Servidor Dedicado (SSH)

1. Conecte via SSH:
   ```bash
   ssh usuario@seu-servidor.com
   ```

2. Edite o crontab:
   ```bash
   crontab -e
   ```

3. Adicione a linha:
   ```bash
   * * * * * php /var/www/html/cron/process_queue.php >> /var/log/zapx-cron.log 2>&1
   ```

4. Salve e saia (`:wq` no vim ou `Ctrl+X` no nano)

5. Verifique se foi salvo:
   ```bash
   crontab -l
   ```

---

## 🔍 Verificando se o Cron está funcionando

### Método 1: Log
Configure o cron com log:
```bash
* * * * * php /caminho/cron/process_queue.php >> /caminho/logs/cron.log 2>&1
```

Depois verifique o arquivo de log.

### Método 2: Banco de Dados
Verifique a tabela `dispatch_campaigns`:
```sql
SELECT last_processed_at FROM dispatch_campaigns WHERE status = 'running';
```

Se `last_processed_at` está sendo atualizado, o cron está funcionando.

### Método 3: Testar manualmente
Execute via SSH:
```bash
php /caminho/cron/process_queue.php
```

---

## ❓ Problemas Comuns

### "php: command not found"
Use o caminho completo do PHP:
```bash
/usr/bin/php /caminho/cron/process_queue.php
# ou
/usr/local/bin/php /caminho/cron/process_queue.php
```

Para descobrir o caminho:
```bash
which php
```

### "Permission denied"
Dê permissão de execução:
```bash
chmod +x /caminho/cron/process_queue.php
```

### "No such file or directory"
Verifique o caminho correto do arquivo. Crie um arquivo de teste:
```php
<?php echo "Caminho: " . __DIR__; ?>
```

### Cron não executa
1. Verifique se o serviço cron está rodando
2. Verifique os logs do sistema: `/var/log/cron` ou `/var/log/syslog`
3. Teste o comando manualmente via SSH

---

## 🔄 Alternativa: Sem Cron Job

Se você **não conseguir** configurar o Cron Job, não se preocupe!

O ZAPX tem um **fallback automático** que processa a fila quando:
- Você acessa a página de **Monitoramento** da campanha
- O JavaScript dispara o processador a cada 5 segundos

**Limitação:** A campanha só processa enquanto a página estiver aberta.

---

## 📞 Suporte

Se precisar de ajuda:
- **Site:** [dantetesta.com.br](https://dantetesta.com.br)
- **Email:** contato@dantetesta.com.br

---

*Documentação ZAPX v3.3.0 - Sistema de Campanhas Back-end*
