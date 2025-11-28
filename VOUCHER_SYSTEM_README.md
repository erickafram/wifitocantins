# Sistema de Vouchers para Motoristas - WiFi Tocantins

## 📋 Visão Geral

Este sistema permite que motoristas utilizem vouchers para acessar a internet gratuitamente, com controle de tempo de uso diário e integração completa com o MikroTik.

## 🎯 Funcionalidades Principais

### Para Motoristas
- **Ativação de Vouchers**: Interface simples para inserir código do voucher
- **Verificação de Status**: Consulta por telefone para ver vouchers ativos
- **Controle de Tempo**: Limite de horas diárias configurável
- **Acesso Automático**: Liberação automática no MikroTik após validação

### Para Administradores
- **Criação de Vouchers**: Geração de vouchers individuais para motoristas
- **Gerenciamento**: Visualização, ativação/desativação de vouchers
- **Relatórios**: Estatísticas de uso e controle de sessões
- **Tipos de Voucher**: Limitado (horas por dia) ou Ilimitado

## 🗂️ Estrutura do Sistema

### Modelos Principais

#### Voucher
```php
- code: string (código único do voucher)
- driver_name: string (nome do motorista)
- driver_document: string (documento do motorista)
- daily_hours: int (horas permitidas por dia)
- daily_hours_used: int (horas usadas hoje)
- last_used_date: date (última data de uso)
- expires_at: datetime (data de expiração)
- activated_at: datetime (data de primeira ativação)
- is_active: boolean (voucher ativo)
- voucher_type: enum (limited|unlimited)
```

#### VoucherSession
```php
- voucher_id: foreign key
- user_id: foreign key
- mac_address: string
- ip_address: string
- started_at: datetime
- ended_at: datetime
- hours_granted: int
- minutes_used: int
- status: enum (active|expired|disconnected)
```

### Controladores

#### PortalController
- `showVoucher()`: Exibe página de ativação
- `validateVoucher()`: Valida e ativa voucher
- `checkVoucherStatus()`: Verifica status por telefone

#### Admin\VoucherController
- `index()`: Lista vouchers
- `store()`: Cria novo voucher
- `destroy()`: Desativa voucher

## 🔄 Fluxo de Funcionamento

### 1. Criação de Voucher (Admin)
1. Admin acessa painel administrativo
2. Clica em "Criar Vouchers"
3. Preenche dados do motorista
4. Define tipo e horas diárias
5. Sistema gera código único (ex: WIFI-A3B7-K9M2)

### 2. Ativação pelo Motorista
1. Motorista conecta na rede "tocantins transporte"
2. É redirecionado para portal captivo
3. Clica em "Ativar Voucher de Motorista"
4. Insere código do voucher
5. Sistema valida e libera acesso no MikroTik

### 3. Controle de Sessão
1. Sistema cria VoucherSession com tempo concedido
2. Comando `vouchers:manage-sessions` monitora uso
3. Quando tempo expira, desconecta automaticamente
4. Contador diário é resetado a cada novo dia

## 🛠️ Configuração e Instalação

### 1. Executar Migrações
```bash
php artisan migrate
```

### 2. Configurar Cron Job
Adicionar ao crontab para executar a cada minuto:
```bash
* * * * * cd /path/to/project && php artisan vouchers:manage-sessions >> /dev/null 2>&1
```

### 3. Configurar MikroTik
Certifique-se de que o `MikrotikLiberacaoController` está configurado corretamente com:
- IP do MikroTik
- Credenciais de acesso
- Configurações de hotspot

## 📱 Interface do Usuário

### Dashboard Principal
- Botão "🎫 Ativar Voucher de Motorista" adicionado
- Mantém funcionalidades existentes para usuários pagantes

### Página de Voucher
- **Ativação**: Campo para inserir código
- **Verificação**: Consulta por telefone
- **Status**: Exibe informações da sessão ativa

### Painel Admin
- **Lista de Vouchers**: Tabela com todos os vouchers
- **Estatísticas**: Total, ativos, ativados, expirados
- **Filtros**: Busca por código, status
- **Ações**: Copiar código, desativar

## 🔧 APIs e Endpoints

### Rotas Web
```php
GET  /voucher                    # Página de ativação
POST /voucher/check-status       # Verificar status por telefone
```

### Rotas API
```php
POST /api/voucher/validate       # Validar e ativar voucher
```

### Rotas Admin
```php
GET    /admin/vouchers           # Listar vouchers
POST   /admin/vouchers           # Criar voucher
DELETE /admin/vouchers/{id}      # Desativar voucher
```

## 🎛️ Comandos Artisan

### Gerenciamento de Sessões
```bash
php artisan vouchers:manage-sessions
```
- Atualiza tempo usado em sessões ativas
- Expira sessões que excederam limite
- Desconecta usuários do MikroTik
- Reseta contadores diários

## 📊 Tipos de Voucher

### Limitado
- Permite X horas por dia
- Contador reseta diariamente
- Ideal para controle de uso

### Ilimitado
- Sem limite de horas diárias
- Válido até data de expiração
- Para motoristas especiais

## 🔒 Segurança e Validações

### Validações de Voucher
- Código deve existir e estar ativo
- Não pode estar expirado
- Deve ter horas disponíveis (se limitado)
- MAC address é capturado automaticamente

### Controle de Acesso
- Apenas admins podem criar vouchers
- Logs detalhados de todas as operações
- Integração com sistema de autenticação existente

## 🚨 Monitoramento e Logs

### Logs Importantes
- Ativação de vouchers
- Liberação/remoção de acesso MikroTik
- Expiração de sessões
- Erros de conectividade

### Métricas
- Total de vouchers criados
- Vouchers ativos vs inativos
- Tempo médio de uso
- Sessões por dia

## 🔄 Manutenção

### Limpeza de Dados
- Sessões antigas podem ser arquivadas
- Logs podem ser rotacionados
- Vouchers expirados podem ser removidos

### Backup
- Tabelas: `vouchers`, `voucher_sessions`
- Configurações do MikroTik
- Logs de operação

## 📞 Suporte

### Problemas Comuns
1. **Voucher não ativa**: Verificar se está ativo e não expirado
2. **Não libera acesso**: Verificar conectividade com MikroTik
3. **Tempo não conta**: Verificar se comando está rodando no cron

### Debug
```bash
# Testar comando manualmente
php artisan vouchers:manage-sessions

# Ver logs
tail -f storage/logs/laravel.log | grep voucher
```

## 🎉 Conclusão

O sistema de vouchers para motoristas está totalmente integrado ao sistema existente, mantendo todas as funcionalidades para usuários pagantes e adicionando uma camada completa de gerenciamento para motoristas com acesso gratuito controlado.
