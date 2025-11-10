# 🚀 Instalação Rápida - Painel de Configurações

## ✅ A tabela já existe!

A tabela `system_settings` já foi criada pela migration:
- `database/migrations/2025_09_26_000001_create_system_settings_table.php`

## 📋 Opção 1: Via Seeder (Recomendado)

```bash
php artisan db:seed --class=SystemSettingsSeeder
```

## 📋 Opção 2: Via SQL Direto

Execute o arquivo `inserir_configuracoes.sql` no phpMyAdmin ou MySQL:

```bash
mysql -u root -p tocantinstransportewifi < inserir_configuracoes.sql
```

Ou copie e cole no phpMyAdmin:

```sql
INSERT INTO system_settings (`key`, `value`, created_at, updated_at) 
VALUES 
    ('wifi_price', '5.99', NOW(), NOW()),
    ('pix_gateway', 'pagbank', NOW(), NOW()),
    ('session_duration', '24', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    updated_at = NOW();
```

## 🎯 Verificar Instalação

```bash
# Via Artisan Tinker
php artisan tinker

# Dentro do tinker:
\App\Models\SystemSetting::all();
\App\Helpers\SettingsHelper::getWifiPrice();
```

## 📍 Acessar Painel

1. Login: `http://localhost:8000/login`
2. Painel: `http://localhost:8000/admin/settings`

## ✅ Pronto!

Agora você pode alterar o preço do WiFi diretamente pelo painel admin! 🎉

### Valores Padrão:
- 💰 Preço WiFi: **R$ 5,99**
- 🔌 Gateway: **PagBank**
- ⏱️ Duração: **24 horas**
