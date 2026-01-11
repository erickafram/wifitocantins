# ============================================================
# SCRIPT DE SINCRONIZAÇÃO - VERSÃO LIMPA
# Executa a cada 2 minutos (não 30 segundos!)
# NÃO cria arquivos no disco
# ============================================================

# ADICIONAR SCRIPT
/system script
add name=syncPagos owner=admin policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon source={
:local url "https://www.tocantinstransportewifi.com.br/api/mikrotik/check-paid-users?token=mikrotik-sync-2024"
:do {
    :local r [/tool fetch url=$url mode=https http-method=get output=user check-certificate=no as-value]
    :if (($r->"status")="finished") do={
        :local d ($r->"data")
        :local p 0
        :while ([:find $d "\"mac_address\":\"" $p]>=0) do={
            :set p [:find $d "\"mac_address\":\"" $p]
            :if ($p>=0) do={
                :set p ($p+15)
                :local e [:find $d "\"" $p]
                :if ($e>=0) do={
                    :local m [:pick $d $p $e]
                    :set p ($e+1)
                    :if ([:len $m]=17) do={
                        :if ([:len [/ip hotspot ip-binding find mac-address=$m]]=0) do={
                            :do {/ip hotspot ip-binding add mac-address=$m type=bypassed comment="PAGO"} on-error={}
                        }
                    }
                }
            }
        }
    }
} on-error={}
}

# ADICIONAR SCHEDULER - A CADA 2 MINUTOS (não 30 segundos!)
/system scheduler
add name=syncScheduler interval=2m on-event=syncPagos policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon
