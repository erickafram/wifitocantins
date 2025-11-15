# Sistema de Vouchers para Motoristas

## 📋 Visão Geral

O Sistema de Vouchers para Motoristas permite que motoristas da empresa Tocantins Transporte acessem a internet Wi-Fi gratuitamente através de códigos voucher, sem necessidade de pagamento. O sistema controla automaticamente o tempo de uso diário e integra-se perfeitamente com o Mikrotik para liberação e bloqueio de acesso.

## 🎯 Funcionalidades

### Para Motoristas
- ✅ **Ativação de Voucher**: Motoristas podem ativar vouchers usando código fornecido pela empresa
- ✅ **Verificação de Status**: Acompanhar tempo restante e status do voucher em tempo real
- ✅ **Limite Diário**: Controle automático de horas disponíveis por dia
- ✅ **Reconexão Simples**: Usar mesmo telefone para reconectar automaticamente
- ✅ **Auto-detecção**: Sistema detecta automaticamente MAC address e IP

### Para Administradores
- ✅ **Gerenciamento via Admin**: Criar, editar e desativar vouchers
- ✅ **Tipos de Voucher**: Ilimitado ou Limitado (com horas diárias)
- ✅ **Monitoramento**: Acompanhar uso e conexões de cada voucher
- ✅ **Relatórios**: Visualizar estatísticas de uso por motorista
- ✅ **Integração Mikrotik**: Liberação automática no firewall

## 🚀 Como Usar

### Para Motoristas

#### 1. Ativar Voucher

1. Conecte-se à rede Wi-Fi **"Tocantins Transporte"**
2. Abra o navegador (será redirecionado automaticamente)
3. No dashboard, clique em **"Ativar Voucher"**
4. Digite:
   - **Código do Voucher** (fornecido pela empresa)
   - **Seu Telefone** (para verificações futuras)
5. Clique em **"Ativar Voucher"**
6. Pronto! Você está conectado ✅

#### 2. Verificar Status

1. Acesse: `http://login.tocantinswifi.local/voucher/status`
2. Digite seu telefone
3. Veja:
   - Tempo restante de conexão
   - Horas disponíveis hoje
   - Data de expiração do voucher
   - Status da conexão

#### 3. Reconectar

- Basta ativar o voucher novamente com o mesmo telefone
- O sistema reconhece automaticamente e libera o acesso
- Não perde as horas já disponíveis do dia

### Para Administradores

#### 1. Criar Voucher

```bash
# Acessar painel admin
https://seu-dominio.com/admin/vouchers

# Clicar em "Criar Voucher"
# Preencher:
- Código: ABC123 (único)
- Nome do Motorista: João Silva
- CPF/Documento (opcional)
- Tipo: Limitado ou Ilimitado
- Horas Diárias: 8 (se limitado)
- Data de Expiração (opcional)
- Descrição (opcional)
```

#### 2. Monitorar Uso

```bash
# Visualizar vouchers ativos
GET /admin/vouchers

# Ver detalhes de um voucher
GET /admin/vouchers/{id}

# Ver relatório de conexões
GET /admin/reports?voucher_id={id}
```

## 🔧 Rotas da API

### Vouchers de Motoristas (Público)

```php
// Ativar voucher
GET  /voucher/ativar
POST /voucher/ativar

// Verificar status
GET  /voucher/status
POST /voucher/status

// Desconectar
POST /voucher/desconectar
```

### Administração (Requer autenticação)

```php
// CRUD de vouchers
GET    /admin/vouchers              // Listar
GET    /admin/vouchers/create       // Formulário criar
POST   /admin/vouchers              // Criar
GET    /admin/vouchers/{id}/edit    // Formulário editar
PUT    /admin/vouchers/{id}         // Atualizar
DELETE /admin/vouchers/{id}         // Deletar

// Ações especiais
POST /admin/vouchers/{id}/toggle    // Ativar/Desativar
POST /admin/vouchers/{id}/reset     // Resetar contador diário
```

## 🔄 Integração com Mikrotik

O sistema se integra automaticamente com o Mikrotik através do endpoint existente:

### Fluxo de Liberação

1. **Motorista ativa voucher** → Sistema valida código e telefone
2. **Sistema registra MAC** → Insere na tabela `mikrotik_mac_reports`
3. **Sistema atualiza usuário** → Define `status='connected'` e `expires_at`
4. **Mikrotik consulta API** (a cada 10 segundos)
   ```
   GET /api/mikrotik/check-paid-users?format=routeros
   ```
5. **API retorna MACs para liberar** → Inclui motoristas com voucher ativo
6. **Mikrotik libera acesso** → Adiciona regra no firewall
7. **Ao expirar** → API retorna MAC na lista de REMOVE
8. **Mikrotik bloqueia acesso** → Remove regra do firewall

### Endpoint Mikrotik

```php
// O Mikrotik consulta este endpoint
GET /api/mikrotik/check-paid-users?format=routeros

// Retorna:
LIBERATE|MAC|IP|EXPIRES_AT|USER_ID  // Para vouchers ativos
REMOVE|MAC|IP|EXPIRES_AT|USER_ID     // Para vouchers expirados
```

## 📊 Banco de Dados

### Novos Campos em `users`

```sql
ALTER TABLE users ADD COLUMN (
    voucher_id BIGINT UNSIGNED NULL,
    driver_phone VARCHAR(20) NULL,
    voucher_activated_at TIMESTAMP NULL,
    voucher_last_connection TIMESTAMP NULL,
    voucher_daily_minutes_used INT DEFAULT 0,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id)
);
```

### Tabela `vouchers` (já existente)

```sql
- code: Código único do voucher
- driver_name: Nome do motorista
- driver_document: CPF/Documento
- daily_hours: Horas disponíveis por dia
- daily_hours_used: Horas já usadas hoje
- last_used_date: Última data de uso
- expires_at: Data de expiração do voucher
- activated_at: Primeira ativação
- is_active: Ativo/Inativo
- voucher_type: unlimited | limited
```

## ⚙️ Comandos Artisan

### Gerenciar Vouchers

```bash
# Executar todas as tarefas de manutenção
php artisan vouchers:manage

# Resetar contadores diários (executar à meia-noite)
php artisan vouchers:manage --reset-daily

# Expirar sessões antigas
php artisan vouchers:manage --expire-old

# Verificar limites diários
php artisan vouchers:manage --check-limits
```

### Agendar no Crontab

```cron
# Resetar contadores à meia-noite
0 0 * * * cd /caminho/projeto && php artisan vouchers:manage --reset-daily

# Verificar limites a cada hora
0 * * * * cd /caminho/projeto && php artisan vouchers:manage --check-limits

# Expirar sessões a cada 10 minutos
*/10 * * * * cd /caminho/projeto && php artisan vouchers:manage --expire-old
```

Ou adicionar ao `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('vouchers:manage --reset-daily')->dailyAt('00:00');
    $schedule->command('vouchers:manage --check-limits')->hourly();
    $schedule->command('vouchers:manage --expire-old')->everyTenMinutes();
}
```

## 🔐 Segurança

- ✅ Vouchers têm código único e não podem ser duplicados
- ✅ Vinculação por telefone impede uso compartilhado
- ✅ MAC address registrado para controle no Mikrotik
- ✅ Limite diário automático para vouchers tipo "limited"
- ✅ Expiração automática após período definido
- ✅ Logs completos de todas as ações

## 📱 Fluxo de Usuário Completo

```
┌─────────────────────────────────────┐
│ 1. Motorista conecta no Wi-Fi      │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 2. Redirecionado para portal       │
│    http://login.tocantinswifi.local│
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 3. Clica em "Ativar Voucher"       │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 4. Digita código e telefone        │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 5. Sistema valida voucher          │
│    - Verifica se existe            │
│    - Verifica se está ativo        │
│    - Verifica limite diário        │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 6. Sistema registra acesso         │
│    - Salva MAC + IP                │
│    - Define expires_at             │
│    - Atualiza status=connected     │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 7. Mikrotik libera no firewall     │
│    (automático em 10 segundos)     │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 8. Motorista navega livremente     │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│ 9. Ao expirar, Mikrotik bloqueia   │
│    (automático)                    │
└─────────────────────────────────────┘
```

## 🎨 Interface do Usuário

### Tela de Ativação
- Design moderno com gradiente verde/azul
- Auto-detecção de MAC e IP
- Validação em tempo real
- Feedback visual de sucesso/erro
- Formatação automática de telefone

### Tela de Status
- Badge visual de status (ATIVO/EXPIRADO)
- Tempo restante em destaque
- Informações completas do voucher
- Atualização automática a cada 30 segundos
- Botão de reconexão rápida

## 🐛 Troubleshooting

### Voucher não ativa

1. Verificar se o código está correto (case-insensitive)
2. Verificar se o voucher está ativo no admin
3. Verificar se não expirou
4. Verificar se não atingiu limite diário
5. Ver logs: `storage/logs/laravel.log`

### Acesso não libera no Mikrotik

1. Verificar se MAC está registrado: `SELECT * FROM mikrotik_mac_reports WHERE mac_address = 'XX:XX:XX:XX:XX:XX'`
2. Verificar se usuário está com status=connected
3. Verificar se expires_at é futuro
4. Testar endpoint manualmente: `GET /api/mikrotik/check-paid-users?format=routeros`
5. Ver logs do Mikrotik

### Tempo expira antes do esperado

1. Verificar configuração `daily_hours` do voucher
2. Verificar se é fim do dia (vouchers limitados resetam à meia-noite)
3. Verificar campo `expires_at` no banco
4. Executar: `php artisan vouchers:manage --check-limits`

## 📞 Suporte

Para dúvidas ou problemas:
- Email: admin@wifitocantins.com.br
- Telefone: (63) 98101-3050
- Admin Panel: https://seu-dominio.com/admin

## 📄 Licença

Sistema proprietário - WiFi Tocantins Transporte
© 2024 - Todos os direitos reservados


