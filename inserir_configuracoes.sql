-- ========================================
-- Inserir Configurações Iniciais
-- ========================================
-- Execute este SQL no banco de dados
-- ========================================

-- Inserir ou atualizar preço do WiFi
INSERT INTO system_settings (`key`, `value`, created_at, updated_at) 
VALUES ('wifi_price', '5.99', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    `value` = '5.99',
    updated_at = NOW();

-- Inserir ou atualizar gateway PIX
INSERT INTO system_settings (`key`, `value`, created_at, updated_at) 
VALUES ('pix_gateway', 'pagbank', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    `value` = 'pagbank',
    updated_at = NOW();

-- Inserir ou atualizar duração da sessão
INSERT INTO system_settings (`key`, `value`, created_at, updated_at) 
VALUES ('session_duration', '24', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    `value` = '24',
    updated_at = NOW();

-- ========================================
-- Verificar se foi inserido
-- ========================================
SELECT * FROM system_settings;
