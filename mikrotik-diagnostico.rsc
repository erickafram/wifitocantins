# ============================================================
# SCRIPT DE DIAGNÓSTICO - MIKROTIK SYNC
# Execute este script no MikroTik para diagnosticar problemas
# ============================================================

:put "=========================================="
:put "DIAGNÓSTICO MIKROTIK SYNC"
:put "=========================================="
:put ""

# 1. Verificar se os scripts existem
:put "1. SCRIPTS INSTALADOS:"
:put "----------------------"
/system script print detail where name~"sync"
:put ""

# 2. Verificar schedulers
:put "2. SCHEDULERS ATIVOS:"
:put "---------------------"
/system scheduler print detail where name~"sync"
:put ""

# 3. Testar conexão com API
:put "3. TESTANDO CONEXÃO COM API:"
:put "-----------------------------"
:local url "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users-lite?token=mikrotik-sync-2024"
:do {
    :local result [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]
    :if (($result->"status") = "finished") do={
        :put "✓ Conexão OK"
        :put ("Dados recebidos: " . [:len ($result->"data")] . " bytes")
        :put ""
        :put "RESPOSTA DA API:"
        :put ($result->"data")
    } else={
        :put "✗ Conexão FALHOU"
        :put ("Status: " . ($result->"status"))
    }
} on-error={
    :put "✗ ERRO ao conectar com API"
}
:put ""

# 4. Verificar bindings existentes
:put "4. BINDINGS PAGOS ATIVOS:"
:put "-------------------------"
:local bindingCount [/ip hotspot ip-binding print count-only where comment="PAGO-AUTO"]
:put ("Total de bindings PAGO-AUTO: " . $bindingCount)
:if ($bindingCount > 0) do={
    /ip hotspot ip-binding print where comment="PAGO-AUTO"
}
:put ""

# 5. Verificar usuários ativos no hotspot
:put "5. USUÁRIOS CONECTADOS NO HOTSPOT:"
:put "-----------------------------------"
:local activeCount [/ip hotspot active print count-only]
:put ("Total de usuários ativos: " . $activeCount)
:if ($activeCount > 0) do={
    /ip hotspot active print
}
:put ""

# 6. Últimos logs de SYNC
:put "6. ÚLTIMOS LOGS DE SINCRONIZAÇÃO:"
:put "----------------------------------"
/log print where message~"SYNC" | tail
:put ""

# 7. Executar sync manualmente
:put "7. EXECUTANDO SYNC MANUALMENTE:"
:put "--------------------------------"
:do {
    /system script run syncPagos
    :delay 2s
    :put "✓ Script executado com sucesso"
} on-error={
    :put "✗ ERRO ao executar script"
}
:put ""

# 8. Verificar bindings após sync
:put "8. BINDINGS APÓS SYNC:"
:put "----------------------"
:local bindingCountAfter [/ip hotspot ip-binding print count-only where comment="PAGO-AUTO"]
:put ("Total de bindings PAGO-AUTO: " . $bindingCountAfter)
:if ($bindingCountAfter > 0) do={
    /ip hotspot ip-binding print where comment="PAGO-AUTO"
}
:put ""

# 9. Verificar se MAC específico está liberado
:put "9. VERIFICAR MAC ESPECÍFICO (3A:F3:EF:AB:D0:3C):"
:put "------------------------------------------------"
:local macToCheck "3A:F3:EF:AB:D0:3C"
:local macBinding [/ip hotspot ip-binding find mac-address=$macToCheck]
:if ([:len $macBinding] > 0) do={
    :put "✓ MAC encontrado nos bindings:"
    /ip hotspot ip-binding print detail where mac-address=$macToCheck
} else={
    :put "✗ MAC NÃO encontrado nos bindings"
    :put "   Tentando criar manualmente..."
    :do {
        /ip hotspot ip-binding add mac-address=$macToCheck type=bypassed comment="PAGO-AUTO-MANUAL"
        :put "✓ Binding criado manualmente"
    } on-error={
        :put "✗ ERRO ao criar binding"
    }
}
:put ""

:put "=========================================="
:put "DIAGNÓSTICO CONCLUÍDO"
:put "=========================================="
