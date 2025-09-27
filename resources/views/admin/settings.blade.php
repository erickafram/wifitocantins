@extends('layouts.admin')

@section('title', 'Configurações')

@section('breadcrumb')
    <span>›</span>
    <span class="text-tocantins-green font-medium">Configurações</span>
@endsection

@section('page-title', 'Configurações do Sistema - WiFi Tocantins')

@section('content')
    <!-- Cabeçalho da Página -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-tocantins-gray-green flex items-center">
                    <span class="mr-3 text-3xl">⚙️</span>
                    Configurações do Sistema
                </h1>
                <p class="text-gray-600 text-sm mt-1">Configure e personalize o sistema WiFi Tocantins</p>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-200 text-green-800">
            <div class="flex items-center">
                <span class="mr-2">✅</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-100 border border-red-200 text-red-800">
            <div class="flex items-center">
                <span class="mr-2">❌</span>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Grid de Configurações -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Configurações Gerais -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-3">
                    <span class="text-white text-lg">🏢</span>
                </div>
                <h2 class="text-lg font-semibold text-tocantins-gray-green">Configurações Gerais</h2>
            </div>
            
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Empresa</label>
                    <input type="text" value="WiFi Tocantins" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Slogan</label>
                    <input type="text" value="Internet de qualidade para todos" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email de Contato</label>
                    <input type="email" value="contato@wifitocantins.com.br" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefone de Suporte</label>
                    <input type="tel" value="(63) 99999-9999" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white py-2 px-4 rounded-lg hover:shadow-lg transition-all duration-300">
                    💾 Salvar Configurações Gerais
                </button>
            </form>
        </div>

        <!-- Configurações de Rede -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-3">
                    <span class="text-white text-lg">📶</span>
                </div>
                <h2 class="text-lg font-semibold text-tocantins-gray-green">Configurações de Rede</h2>
            </div>
            
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Rede WiFi (SSID)</label>
                    <input type="text" value="WiFi_Tocantins_Free" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tempo de Sessão Padrão (minutos)</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                        <option value="30">30 minutos</option>
                        <option value="60" selected>1 hora</option>
                        <option value="120">2 horas</option>
                        <option value="240">4 horas</option>
                        <option value="480">8 horas</option>
                        <option value="1440">24 horas</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Velocidade (Mbps)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Download</label>
                            <input type="number" value="10" min="1" max="1000" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Upload</label>
                            <input type="number" value="5" min="1" max="1000" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" checked class="rounded border-gray-300 text-tocantins-green focus:ring-tocantins-green">
                        <span class="ml-2 text-sm text-gray-700">Permitir reconexão automática</span>
                    </label>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white py-2 px-4 rounded-lg hover:shadow-lg transition-all duration-300">
                    📶 Salvar Configurações de Rede
                </button>
            </form>
        </div>

        <!-- Configurações de Pagamento -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center mr-3">
                    <span class="text-white text-lg">💳</span>
                </div>
                <h2 class="text-lg font-semibold text-tocantins-gray-green">Configurações de Pagamento</h2>
            </div>
            
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor Padrão (R$)</label>
                    <input type="number" value="5.00" step="0.01" min="0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Planos Disponíveis</label>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <span class="font-medium">1 Hora - R$ 3,00</span>
                                <span class="text-xs text-gray-500 block">Acesso básico</span>
                            </div>
                            <input type="checkbox" checked class="rounded border-gray-300 text-tocantins-green focus:ring-tocantins-green">
                        </div>
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <span class="font-medium">4 Horas - R$ 8,00</span>
                                <span class="text-xs text-gray-500 block">Plano intermediário</span>
                            </div>
                            <input type="checkbox" checked class="rounded border-gray-300 text-tocantins-green focus:ring-tocantins-green">
                        </div>
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <span class="font-medium">24 Horas - R$ 15,00</span>
                                <span class="text-xs text-gray-500 block">Acesso premium</span>
                            </div>
                            <input type="checkbox" checked class="rounded border-gray-300 text-tocantins-green focus:ring-tocantins-green">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white py-2 px-4 rounded-lg hover:shadow-lg transition-all duration-300">
                    💳 Salvar Configurações de Pagamento
                </button>
            </form>
        </div>

        <!-- Configurações de Segurança -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center mr-3">
                    <span class="text-white text-lg">🔒</span>
                </div>
                <h2 class="text-lg font-semibold text-tocantins-gray-green">Configurações de Segurança</h2>
            </div>
            
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tentativas de Login (por hora)</label>
                    <input type="number" value="5" min="1" max="50" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tempo de Bloqueio (minutos)</label>
                    <input type="number" value="15" min="1" max="1440" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sites Bloqueados</label>
                    <textarea rows="3" placeholder="Digite um site por linha (ex: facebook.com)" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent"></textarea>
                </div>
                
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" checked class="rounded border-gray-300 text-tocantins-green focus:ring-tocantins-green">
                        <span class="ml-2 text-sm text-gray-700">Bloquear sites adultos</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-tocantins-green focus:ring-tocantins-green">
                        <span class="ml-2 text-sm text-gray-700">Bloquear redes sociais</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" checked class="rounded border-gray-300 text-tocantins-green focus:ring-tocantins-green">
                        <span class="ml-2 text-sm text-gray-700">Log de acessos</span>
                    </label>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white py-2 px-4 rounded-lg hover:shadow-lg transition-all duration-300">
                    🔒 Salvar Configurações de Segurança
                </button>
            </form>
        </div>

        <!-- Configurações de Aparência -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                    <span class="text-white text-lg">🎨</span>
                </div>
                <h2 class="text-lg font-semibold text-tocantins-gray-green">Configurações de Aparência</h2>
            </div>
            
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo da Empresa</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                        <div class="text-gray-400 mb-2">📷</div>
                        <p class="text-sm text-gray-500">Clique para fazer upload do logo</p>
                        <input type="file" accept="image/*" class="hidden">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cor Principal</label>
                    <div class="flex space-x-2">
                        <input type="color" value="#228B22" class="w-12 h-10 rounded border border-gray-300">
                        <input type="text" value="#228B22" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cor Secundária</label>
                    <div class="flex space-x-2">
                        <input type="color" value="#FFD700" class="w-12 h-10 rounded border border-gray-300">
                        <input type="text" value="#FFD700" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tema do Portal</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-tocantins-green focus:border-transparent">
                        <option value="tocantins" selected>Tocantins (Verde e Dourado)</option>
                        <option value="blue">Azul Profissional</option>
                        <option value="dark">Escuro Moderno</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-tocantins-green to-tocantins-dark-green text-white py-2 px-4 rounded-lg hover:shadow-lg transition-all duration-300">
                    🎨 Salvar Configurações de Aparência
                </button>
            </form>
        </div>

        <!-- Configurações de Backup -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl flex items-center justify-center mr-3">
                    <span class="text-white text-lg">💾</span>
                </div>
                <h2 class="text-lg font-semibold text-tocantins-gray-green">Backup e Manutenção</h2>
            </div>
            
            <div class="space-y-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-medium text-blue-900 mb-2">Backup Automático</h3>
                    <p class="text-sm text-blue-700 mb-3">Último backup: Hoje às 03:00</p>
                    <div class="flex space-x-2">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                            📥 Fazer Backup Agora
                        </button>
                        <button class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 transition-colors">
                            📤 Restaurar Backup
                        </button>
                    </div>
                </div>
                
                <div class="p-4 bg-yellow-50 rounded-lg">
                    <h3 class="font-medium text-yellow-900 mb-2">Limpeza de Dados</h3>
                    <p class="text-sm text-yellow-700 mb-3">Remover logs antigos e otimizar banco de dados</p>
                    <div class="flex space-x-2">
                        <button class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-700 transition-colors">
                            🧹 Limpar Logs (>30 dias)
                        </button>
                        <button class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-700 transition-colors">
                            ⚡ Otimizar Banco
                        </button>
                    </div>
                </div>
                
                <div class="p-4 bg-red-50 rounded-lg">
                    <h3 class="font-medium text-red-900 mb-2">Zona de Perigo</h3>
                    <p class="text-sm text-red-700 mb-3">Ações que podem afetar o funcionamento do sistema</p>
                    <div class="flex space-x-2">
                        <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors">
                            🔄 Reiniciar Sistema
                        </button>
                        <button class="bg-red-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-900 transition-colors">
                            ⚠️ Reset Completo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações do Sistema -->
    <div class="mt-6 bg-white rounded-2xl shadow-lg p-6">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3">
                <span class="text-white text-lg">ℹ️</span>
            </div>
            <h2 class="text-lg font-semibold text-tocantins-gray-green">Informações do Sistema</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-500">Versão do Sistema</div>
                <div class="text-lg font-semibold text-gray-900">v2.1.0</div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-500">Laravel</div>
                <div class="text-lg font-semibold text-gray-900">{{ app()->version() }}</div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-500">PHP</div>
                <div class="text-lg font-semibold text-gray-900">{{ PHP_VERSION }}</div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-500">Última Atualização</div>
                <div class="text-lg font-semibold text-gray-900">{{ now()->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <script>
        // Funcionalidade em desenvolvimento - placeholder para futuras implementações
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar listeners para os formulários
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Simular salvamento
                    const button = form.querySelector('button[type="submit"]');
                    const originalText = button.textContent;
                    
                    button.textContent = '⏳ Salvando...';
                    button.disabled = true;
                    
                    setTimeout(() => {
                        button.textContent = '✅ Salvo!';
                        setTimeout(() => {
                            button.textContent = originalText;
                            button.disabled = false;
                        }, 1500);
                    }, 1000);
                });
            });
            
            // Upload de arquivo
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                const container = input.parentElement;
                container.addEventListener('click', () => input.click());
            });
        });

        // Funções de backup e manutenção
        function performBackup() {
            if (confirm('Deseja fazer backup do sistema agora?')) {
                alert('Funcionalidade em desenvolvimento - Backup será implementado em breve');
            }
        }

        function cleanOldLogs() {
            if (confirm('Deseja limpar logs com mais de 30 dias?')) {
                alert('Funcionalidade em desenvolvimento - Limpeza será implementada em breve');
            }
        }

        function restartSystem() {
            if (confirm('⚠️ ATENÇÃO: Deseja realmente reiniciar o sistema? Isso pode causar interrupção temporária do serviço.')) {
                alert('Funcionalidade em desenvolvimento - Reinicialização será implementada em breve');
            }
        }
    </script>
@endsection
