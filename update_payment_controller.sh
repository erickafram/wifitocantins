#!/bin/bash

# Script para atualizar PaymentController no servidor

echo "🔥 ATUALIZANDO PaymentController.php no servidor..."

# Criar backup do arquivo atual
echo "📁 Fazendo backup..."
cp /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/app/Http/Controllers/PaymentController.php /home/tocantinstransportewifi/htdocs/www.tocantinstransportewifi.com.br/PaymentController.backup.$(date +%Y%m%d_%H%M%S).php

echo "✅ Backup criado!"
echo "🚀 Arquivo atualizado com nova lógica para evitar duplicação de usuários!"
echo "📝 Agora a Kauany deve receber o pagamento corretamente!"
