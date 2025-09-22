# 🔥 SCRIPT MIKROTIK DEFINITIVO - SOLUÇÃO COMPLETA
# Resolve MAC randomization + Ativação automática + IP Binding

# =====================================================
# 1. SCRIPT: sync-users-definitivo
# =====================================================
/system script add name="sync-users-definitivo" source={
    :log info "🚀 === SYNC DEFINITIVO INICIADO ===";
    
    :local url "https://www.tocantinstransportewifi.com.br/api/mikrotik-sync/pending-users?token=mikrotik-sync-2024";
    
    :do {
        :local response [/tool fetch url=$url http-method=get as-value output=user];
        
        :if ($response->"status" = "finished") do={
            :local data ($response->"data");
            :log info "✅ API Response recebida";
            
            # PARTE 1: Processar MACs para criação de usuários
            :local allowPos [:find $data "\"allow_users\":["];
            :if ($allowPos >= 0) do={
                :local allowStart ($allowPos + 15);
                :local allowEnd [:find $data "]" $allowStart];
                :local allowList [:pick $data $allowStart $allowEnd];
                
                :log info ("📝 Allow MACs: " . $allowList);
                
                # Extrair e criar usuários para cada MAC
                :local pos 0;
                :local userCount 0;
                :while ([:find $allowList "\"" $pos] >= 0) do={
                    :local start ([:find $allowList "\"" $pos] + 1);
                    :local end [:find $allowList "\"" $start];
                    
                    :if ($end > $start) do={
                        :local mac [:pick $allowList $start $end];
                        
                        :if ([:len $mac] = 17) do={
                            :local macUpper [:tostr $mac];
                            
                            :log info ("🔧 Processando MAC: " . $macUpper);
                            
                            # Verificar se usuário já existe
                            :local userExists [/ip hotspot user find name=$macUpper];
                            :if ([:len $userExists] = 0) do={
                                /ip hotspot user add name=$macUpper profile="default" comment="Usuario pago - auto";
                                :log info ("✅ Usuário criado: " . $macUpper);
                                :set userCount ($userCount + 1);
                            } else={
                                :log info ("ℹ️ Usuário já existe: " . $macUpper);
                            };
                        };
                    };
                    :set pos ($end + 1);
                };
                
                :log info ("📊 Usuários criados: " . $userCount);
            };
            
            # PARTE 2: 🚀 IP-BINDING para bypass total (SOLUÇÃO DEFINITIVA)
            :local ipBindPos [:find $data "\"ip_bindings\":["];
            :if ($ipBindPos >= 0) do={
                :local ipBindStart ($ipBindPos + 15);
                :local ipBindEnd [:find $data "]" $ipBindStart];
                :local ipBindList [:pick $data $ipBindStart $ipBindEnd];
                
                :log info ("🎯 IP Bindings: " . $ipBindList);
                
                # Processar cada IP binding
                :local bindPos 0;
                :local bindCount 0;
                :while ([:find $ipBindList "\"ip\":" $bindPos] >= 0) do={
                    :local ipStart ([:find $ipBindList "\"ip\":\"" $bindPos] + 6);
                    :local ipEnd [:find $ipBindList "\"" $ipStart];
                    
                    :if ($ipEnd > $ipStart) do={
                        :local ipAddr [:pick $ipBindList $ipStart $ipEnd];
                        
                        :log info ("🔥 Criando IP-Binding para: " . $ipAddr);
                        
                        # Remover binding antigo se existir
                        :local oldBinding [/ip hotspot ip-binding find address=$ipAddr];
                        :if ([:len $oldBinding] > 0) do={
                            /ip hotspot ip-binding remove $oldBinding;
                        };
                        
                        # Criar novo binding com bypass
                        /ip hotspot ip-binding add address=$ipAddr type="bypassed" comment=("Pago - " . $ipAddr);
                        :log info ("✅ IP-Binding criado: " . $ipAddr . " (BYPASSED)");
                        :set bindCount ($bindCount + 1);
                    };
                    :set bindPos ($ipEnd + 1);
                };
                
                :log info ("🎯 IP-Bindings criados: " . $bindCount);
            };
            
            # PARTE 3: Ativar usuários conectados automaticamente
            :foreach host in=[/ip hotspot host find] do={
                :local hostMac [/ip hotspot host get $host mac-address];
                :local hostIp [/ip hotspot host get $host address];
                
                # Verificar se existe usuário para este MAC
                :local userExists [/ip hotspot user find name=$hostMac];
                :if ([:len $userExists] > 0) do={
                    # Verificar se já está ativo
                    :local activeExists [/ip hotspot active find user=$hostMac];
                    :if ([:len $activeExists] = 0) do={
                        # Ativar usuário
                        :do {
                            /ip hotspot active add user=$hostMac address=$hostIp;
                            :log info ("🚀 Usuário ativado: " . $hostMac . " -> " . $hostIp);
                        } on-error={
                            :log warning ("⚠️ Erro ao ativar: " . $hostMac);
                        };
                    };
                };
            };
            
            :log info "🎉 SYNC DEFINITIVO CONCLUÍDO";
            
        } else={
            :log error ("❌ Erro na API: " . $response->"status");
        };
        
    } on-error={
        :log error "❌ Erro no sync definitivo";
    };
}

# =====================================================
# 2. SCHEDULER: sync-definitivo-scheduler
# =====================================================
/system scheduler remove [find name="sync-definitivo-scheduler"]
/system scheduler add name="sync-definitivo-scheduler" start-time=startup interval=30s on-event="sync-users-definitivo" comment="Sync definitivo com IP-Binding"

# =====================================================
# 3. COMANDO PARA ATIVAÇÃO MANUAL IMEDIATA
# =====================================================
:log info "🔥 EXECUTANDO SYNC DEFINITIVO MANUAL";
/system script run sync-users-definitivo
