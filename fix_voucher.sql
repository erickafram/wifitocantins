-- Reseta o uso diário do voucher WIFI-AEIN-GILH
-- Execute este comando no seu banco de dados de produção

UPDATE vouchers 
SET daily_hours_used = 0,
    last_used_date = NULL
WHERE code = 'WIFI-AEIN-GILH';

-- Verificar o resultado
SELECT * FROM vouchers WHERE code = 'WIFI-AEIN-GILH';
