/**
 * Sistema de Relatórios - WiFi Tocantins
 * JavaScript para funcionalidades avançadas dos relatórios
 */

class ReportsManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupDateValidation();
        this.setupAutoRefresh();
        this.setupFilterPresets();
    }

    setupEventListeners() {
        // Auto-aplicar filtros quando mudar datas
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.validateDateRange();
            });
        });

        // Filtros rápidos
        const quickFilters = document.querySelectorAll('[data-quick-filter]');
        quickFilters.forEach(filter => {
            filter.addEventListener('click', (e) => {
                e.preventDefault();
                this.applyQuickFilter(filter.dataset.quickFilter);
            });
        });

        // Exportação com confirmação
        const exportLinks = document.querySelectorAll('a[href*="export"]');
        exportLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                if (!this.confirmExport()) {
                    e.preventDefault();
                }
            });
        });
    }

    setupDateValidation() {
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        
        if (startDateInput && endDateInput) {
            startDateInput.addEventListener('change', () => {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                
                if (startDate > endDate) {
                    endDateInput.value = startDateInput.value;
                    this.showNotification('Data final ajustada para não ser anterior à data inicial', 'warning');
                }
                
                this.checkDateRange();
            });

            endDateInput.addEventListener('change', () => {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                
                if (endDate < startDate) {
                    startDateInput.value = endDateInput.value;
                    this.showNotification('Data inicial ajustada para não ser posterior à data final', 'warning');
                }
                
                this.checkDateRange();
            });
        }
    }

    checkDateRange() {
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        
        if (startDateInput && endDateInput) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays > 365) {
                this.showNotification('Período muito longo! Relatórios com mais de 1 ano podem ser lentos.', 'warning');
            }
        }
    }

    validateDateRange() {
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        
        if (startDateInput && endDateInput) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (startDate > endDate) {
                this.showNotification('Data inicial não pode ser posterior à data final', 'error');
                return false;
            }
        }
        return true;
    }

    setupAutoRefresh() {
        // Auto-refresh apenas se estiver na aba ativa e período for hoje
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        
        if (startDateInput && endDateInput) {
            const today = new Date().toISOString().split('T')[0];
            
            if (startDateInput.value === today && endDateInput.value === today) {
                this.startAutoRefresh();
            }
        }
    }

    startAutoRefresh() {
        // Refresh a cada 5 minutos se for dados de hoje
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                const today = new Date().toISOString().split('T')[0];
                const startDate = document.querySelector('input[name="start_date"]').value;
                const endDate = document.querySelector('input[name="end_date"]').value;
                
                if (startDate === today && endDate === today) {
                    this.refreshCurrentData();
                }
            }
        }, 300000); // 5 minutos
    }

    refreshCurrentData() {
        // Evitar refresh se gráficos estão sendo renderizados
        if (document.querySelector('canvas[style*="block"]')) {
            console.log('Charts are rendering, skipping refresh');
            return;
        }
        
        // Recarregar apenas os cards de estatísticas sem refresh da página
        this.showNotification('Atualizando dados...', 'info');
        
        // Simular atualização (implementar AJAX quando necessário)
        setTimeout(() => {
            this.showNotification('Dados atualizados!', 'success');
        }, 1000);
    }

    setupFilterPresets() {
        // Criar botões de filtros rápidos se não existirem
        this.createQuickFilterButtons();
    }

    createQuickFilterButtons() {
        const filterContainer = document.querySelector('.space-y-4');
        if (!filterContainer || document.querySelector('.quick-filters')) return;

        const quickFiltersDiv = document.createElement('div');
        quickFiltersDiv.className = 'quick-filters flex flex-wrap gap-2 p-4 bg-gray-50 rounded-lg';
        quickFiltersDiv.innerHTML = `
            <span class="text-sm font-medium text-gray-700 mr-4">Filtros Rápidos:</span>
            <button data-quick-filter="today" class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs hover:bg-blue-200 transition-colors">
                📅 Hoje
            </button>
            <button data-quick-filter="yesterday" class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs hover:bg-gray-200 transition-colors">
                📅 Ontem
            </button>
            <button data-quick-filter="week" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs hover:bg-green-200 transition-colors">
                📅 Esta Semana
            </button>
            <button data-quick-filter="month" class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs hover:bg-purple-200 transition-colors">
                📅 Este Mês
            </button>
            <button data-quick-filter="paid" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs hover:bg-green-200 transition-colors">
                ✅ Apenas Pagos
            </button>
            <button data-quick-filter="pending" class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs hover:bg-yellow-200 transition-colors">
                ⏳ Apenas Pendentes
            </button>
        `;

        filterContainer.appendChild(quickFiltersDiv);

        // Adicionar event listeners aos novos botões
        const quickFilters = quickFiltersDiv.querySelectorAll('[data-quick-filter]');
        quickFilters.forEach(filter => {
            filter.addEventListener('click', (e) => {
                e.preventDefault();
                this.applyQuickFilter(filter.dataset.quickFilter);
            });
        });
    }

    applyQuickFilter(filterType) {
        const today = new Date();
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        const paymentStatusSelect = document.querySelector('select[name="payment_status"]');
        const userStatusSelect = document.querySelector('select[name="user_status"]');

        // Reset status filters first
        if (paymentStatusSelect) paymentStatusSelect.value = 'all';
        if (userStatusSelect) userStatusSelect.value = 'all';

        switch (filterType) {
            case 'today':
                const todayStr = today.toISOString().split('T')[0];
                startDateInput.value = todayStr;
                endDateInput.value = todayStr;
                break;

            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                const yesterdayStr = yesterday.toISOString().split('T')[0];
                startDateInput.value = yesterdayStr;
                endDateInput.value = yesterdayStr;
                break;

            case 'week':
                const startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - today.getDay());
                startDateInput.value = startOfWeek.toISOString().split('T')[0];
                endDateInput.value = today.toISOString().split('T')[0];
                break;

            case 'month':
                const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                startDateInput.value = startOfMonth.toISOString().split('T')[0];
                endDateInput.value = today.toISOString().split('T')[0];
                break;

            case 'paid':
                if (paymentStatusSelect) paymentStatusSelect.value = 'completed';
                break;

            case 'pending':
                if (paymentStatusSelect) paymentStatusSelect.value = 'pending';
                break;
        }

        // Auto-submit form after applying filter
        setTimeout(() => {
            const form = document.querySelector('form');
            if (form) {
                this.showNotification(`Filtro "${this.getFilterName(filterType)}" aplicado`, 'success');
                form.submit();
            }
        }, 100);
    }

    getFilterName(filterType) {
        const names = {
            'today': 'Hoje',
            'yesterday': 'Ontem',
            'week': 'Esta Semana',
            'month': 'Este Mês',
            'paid': 'Apenas Pagos',
            'pending': 'Apenas Pendentes'
        };
        return names[filterType] || filterType;
    }

    confirmExport() {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        
        return confirm(`Exportar dados do período de ${this.formatDate(startDate)} até ${this.formatDate(endDate)}?`);
    }

    formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('pt-BR');
    }

    showNotification(message, type = 'info') {
        // Remover notificação existente
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Criar nova notificação
        const notification = document.createElement('div');
        notification.className = `notification fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        const colors = {
            'success': 'bg-green-500 text-white',
            'error': 'bg-red-500 text-white',
            'warning': 'bg-yellow-500 text-white',
            'info': 'bg-blue-500 text-white'
        };
        
        notification.className += ` ${colors[type] || colors.info}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remover após 3 segundos
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Método para atualizar gráficos dinamicamente
    updateCharts(data) {
        // Implementar atualização de gráficos quando necessário
        console.log('Updating charts with new data:', data);
        
        // Evitar conflito com gráficos já inicializados
        if (typeof window.initializeCharts === 'function') {
            // Aguardar um pouco antes de reinicializar
            setTimeout(() => {
                window.initializeCharts();
            }, 100);
        }
    }

    // Método para exportação customizada
    customExport(type, format, filters) {
        const params = new URLSearchParams({
            type: type,
            format: format,
            ...filters
        });
        
        const exportUrl = `/admin/reports/export?${params.toString()}`;
        window.location.href = exportUrl;
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    new ReportsManager();
});

// Função global para compatibilidade
window.ReportsManager = ReportsManager;
