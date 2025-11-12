# Estilos CSS - WiFi Tocantins

## 📁 Estrutura de Arquivos

### `admin-styles.css`
Arquivo principal de estilos para o painel administrativo.

## 🎨 Conteúdo

### 1. **Padronização de Fontes**
- Tamanho padrão global: `14px (0.875rem)`
- Títulos: `h1` (18px), `h2` (16px), `h3/h4` (14px)
- Textos pequenos: `12px (0.75rem)`
- Textos grandes: `text-xl` até `text-4xl`

### 2. **Componentes Customizados**
- **Scrollbar**: Estilizada com as cores do Tocantins
- **Cards**: Componentes de card prontos
- **Badges**: Tags coloridas (success, warning, danger, info)
- **Modais**: Estilos para overlays e conteúdo de modais
- **Loading Spinner**: Animação de carregamento

### 3. **Gradientes Personalizados**
- `.gradient-tocantins`: Verde do Tocantins
- `.gradient-tocantins-gold`: Dourado do Tocantins

### 4. **Sombras Personalizadas**
- `.shadow-tocantins`: Sombra com cor verde
- `.shadow-tocantins-lg`: Sombra grande com cor verde

### 5. **Utilitários**
- `.hover-scale`: Efeito de escala no hover
- `.focus-ring`: Anel de foco verde
- `.transition-all`: Transição suave

### 6. **Responsividade**
- Ajustes automáticos para mobile (< 640px)
- Print styles para impressão

## 🔧 Como Usar

### No Layout Admin
O arquivo já está incluído automaticamente no layout:

```blade
<!-- Admin Custom Styles -->
<link href="{{ asset('css/admin-styles.css') }}" rel="stylesheet">
```

### Adicionar Estilos Personalizados
Se precisar adicionar estilos específicos de uma página, use a stack:

```blade
@push('styles')
    <style>
        /* Seus estilos aqui */
    </style>
@endpush
```

## 📝 Boas Práticas

1. **Não adicione styles inline** nas páginas
2. **Use classes do Tailwind** sempre que possível
3. **Adicione novos estilos** neste arquivo CSS centralizado
4. **Documente** novos componentes adicionados
5. **Mantenha** a organização por seções

## 🎯 Cores do Tema

```css
--tocantins-gold: #FFD700
--tocantins-green: #228B22
--tocantins-light-cream: #FFF8DC
--tocantins-dark-green: #006400
--tocantins-light-yellow: #FFE55C
--tocantins-gray-green: #2F4F2F
```

## 🚀 Performance

- Arquivo único e minificado em produção
- Carregamento otimizado com cache
- Sem styles inline duplicados

## 📦 Manutenção

Para adicionar novos estilos:

1. Abra `public/css/admin-styles.css`
2. Adicione na seção apropriada
3. Documente o novo estilo
4. Teste em diferentes navegadores
5. Commit com mensagem descritiva

---

**Última atualização:** 11/11/2025
**Versão:** 1.0.0
