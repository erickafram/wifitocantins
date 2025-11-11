# 🎛️ Painel de Configurações Admin

## ✅ O que foi implementado

### 1. Modal PIX com Scroll
- ✅ Adicionado `overflow-y-auto` para permitir scroll
- ✅ Altura máxima de 95vh para evitar corte
- ✅ Funciona em desktop e mobile

### 2. Painel de Configurações no Admin
- ✅ Rota: `http://localhost:8000/admin/settings`
- ✅ Configurações armazenadas no banco de dados (`system_settings`)
- ✅ Cache de 1 hora para performance
- ✅ Interface moderna e responsiva

## 📋 Configurações Disponíveis

### 💰 Preço do WiFi
- Valor em reais (R$)
- Mínimo: R$ 0,01
- Máximo: R$ 999,99
- **Padrão: R$ 5,99**

### 🔌 Gateway PIX
- Woovi (OpenPix)
- PagBank (PagSeguro)
- Santander
- **Padrão: PagBank**

### ⏱️ Duração da Sessão
- Tempo em horas
- Mínimo: 1 hora
- Máximo: 168 horas (7 dias)
- **Padrão: 24 horas**

## 🚀 Como Usar

### 1. Executar Seeder (Primeira Vez)

```bash
php artisan db:seed --class=SystemSettingsSeeder
```

Isso irá inserir os valores padrão no banco.

### 2. Acessar o Painel

1. Faça login como admin: `http://localhost:8000/login`
2. Acesse: `http://localhost:8000/admin/settings`
3. Altere os valores desejados
4. Clique em "💾 Salvar Configurações"

### 3. Valores são Aplicados Automaticamente

Após salvar, os novos valores serão usados em:
- ✅ Página inicial do portal
- ✅ Geração de pagamentos PIX
- ✅ Dashboard do usuário
- ✅ API de pagamentos

## 🔧 Arquivos Criados/Modificados

### Novos Arquivos:
- `app/Http/Controllers/Admin/SettingsController.php` - Controller
- `app/Helpers/SettingsHelper.php` - Helper para cache
- `resources/views/admin/settings/index.blade.php` - View
- `database/seeders/SystemSettingsSeeder.php` - Seeder

### Arquivos Modificados:
- `routes/web.php` - Rotas adicionadas
- `public/js/portal.js` - Modal com scroll
- `app/Services/PixPaymentManager.php` - Usa helper
- `app/Http/Controllers/PortalController.php` - Usa helper
- `app/Http/Controllers/PortalDashboardController.php` - Usa helper

## 📊 Estrutura do Banco

```sql
system_settings
├── id (bigint)
├── key (varchar) - Chave única
├── value (text) - Valor
├── created_at (timestamp)
└── updated_at (timestamp)
```

### Registros:
```sql
INSERT INTO system_settings (key, value) VALUES
('wifi_price', '5.99'),
('pix_gateway', 'pagbank'),
('session_duration', '24');
```

## 🎨 Interface do Painel

### Cards Coloridos:
- 🟢 **Verde**: Preços e Valores
- 🔵 **Azul**: Gateway de Pagamento
- 🟣 **Roxo**: Duração da Sessão

### Recursos:
- ✨ Gradientes modernos
- 📱 Totalmente responsivo
- ✅ Validação em tempo real
- 💾 Feedback visual ao salvar
- 🔄 Cache automático

## ⚠️ Importante

1. **Cache**: As configurações são cacheadas por 1 hora para performance
2. **Limpeza**: O cache é limpo automaticamente ao salvar
3. **Fallback**: Se não houver valor no banco, usa o padrão do `config/wifi.php`

## 🧪 Testar

```bash
# 1. Rodar seeder
php artisan db:seed --class=SystemSettingsSeeder

# 2. Limpar cache
php artisan cache:clear

# 3. Acessar painel
# http://localhost:8000/admin/settings

# 4. Alterar preço para R$ 10,00

# 5. Verificar na página inicial
# http://localhost:8000/
```

## 🔍 Verificar Configurações

```php
// No tinker ou em qualquer lugar do código
use App\Helpers\SettingsHelper;

echo SettingsHelper::getWifiPrice(); // 5.99
echo SettingsHelper::getPixGateway(); // pagbank
echo SettingsHelper::getSessionDuration(); // 24
```

## 📝 Próximos Passos

1. Commitar as alterações
2. Fazer deploy para produção
3. Rodar o seeder no servidor
4. Acessar o painel e configurar os valores desejados

Pronto! Agora você pode gerenciar o preço do WiFi diretamente pelo painel admin, sem precisar editar código ou `.env`! 🎉
