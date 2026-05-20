# Confiar Catalog Mode

Plugin WordPress para transformar WooCommerce em modo catálogo com sistema de orçamento rápido.

## Descrição

Este plugin permite que lojistas convertam seu WooCommerce para modo catálogo, ocultando preços e substituindo o botão "Adicionar ao Carrinho" por um "Orçamento Rápido". Clientes podem solicitar orçamentos com formulário minimalista, criando automaticamente uma conta WooCommerce e gerando um pedido de orçamento no painel.

## Características

- ✅ **Modo Catálogo Toggle**: Ativar/desativar com um clique nas configurações
- ✅ **Ocultar Preços**: Todos os preços desaparecem quando ativado
- ✅ **Modal Orçamento Rápido**: Formulário minimalista (nome, email, qtd, mensagem)
- ✅ **Criar Cliente Automaticamente**: Cliente novo é criado no WooCommerce
- ✅ **Gravar como Order**: Orçamento salvo como pedido (status: "RFQ")
- ✅ **Emails Automáticos**: Notificações para cliente e lojista
- ✅ **Responder no Painel**: Admin pode enviar proposta de preço customizado
- ✅ **Dark Mode Suportado**: CSS integrado com variáveis tema Blonwe
- ✅ **Performance**: Zero overhead quando desativado
- ✅ **Segurança**: Validação nonce, sanitização, escapagem

## Requisitos

- WordPress 5.0+
- WooCommerce 3.9+
- PHP 7.2+

## Instalação

1. Upload do plugin para `/wp-content/plugins/confiar-catalog-mode/`
2. Ativar plugin em WordPress → Plugins
3. Ir para Configurações → Confiar Catalog Mode
4. Marcar "Ativar Modo Catálogo"
5. Personalizar textos dos botões se desejar

## Como Usar

### Para Lojistas

1. **Ativar Modo Catálogo**: Configurações → Confiar Catalog Mode → Ativar
2. **Ir para Loja**: Preços desaparecem, botão muda para "Orçamento Rápido"
3. **Receber Orçamentos**: Orçamentos chegam via email e no painel de Pedidos
4. **Responder no Painel**: Abra o pedido (status: Orçamento Pendente) → Enviar Proposta de Preço

### Para Clientes

1. Vê produto sem preço
2. Clica "Orçamento Rápido"
3. Preenche: nome, email, quantidade, mensagem (opcional)
4. Clica "Enviar Orçamento"
5. Recebe confirmação por email
6. Admin envia proposta customizada
7. Cliente clica link → vai ao checkout para aceitar

## Estrutura de Arquivos

```
confiar-catalog-mode/
├── confiar-catalog-mode.php          # Arquivo principal
├── includes/
│   ├── class-main.php                # Inicializador
│   ├── class-settings.php            # Painel de opções
│   ├── class-product-display.php     # Hooks para ocultar preço
│   ├── class-quote-form.php          # Renderização modal
│   ├── class-order-handler.php       # AJAX + criar cliente/order
│   ├── class-email-notifier.php      # Notificações email
│   └── class-admin-quote-manager.php # Gerenciador painel
├── public/
│   ├── css/modal.css                 # Estilos modal
│   └── js/modal.js                   # Lógica JavaScript
└── templates/emails/                 # Templates email
```

## Status Customizado "RFQ"

Orçamentos são salvos com status `wc-rfq` (Quote Pending). No painel de pedidos:
- Coluna "Type" mostra ícone de cotação
- Pedidos RFQ aparecem no dropdown de status
- Quando desativa plugin, converte para "draft" (preserva dados)

## Hooks Disponíveis

```php
// Disparado quando orçamento é enviado
do_action( 'confiar_quote_submitted', $order, $customer_name, $customer_email );
```

## Segurança

- ✅ Validação de nonce em AJAX
- ✅ Sanitização de inputs (`sanitize_text_field`, `sanitize_email`, etc)
- ✅ Escapagem de outputs (`esc_html`, `esc_attr`, `wp_kses_post`)
- ✅ Verificação de permissões (`current_user_can`)
- ✅ Validação de email
- ✅ Proteção contra rate limiting (opcional v2)

## Performance

- **CSS/JS carregados apenas em páginas de produto/shop**
- **Hooks executados apenas quando modo catálogo ativo**
- **Zero overhead quando desativado**
- **Minificação recomendada**

## Compatibilidade

- ✅ Tema Confiar/Confiar-child
- ✅ Dark mode (Blonwe)
- ✅ Dispositivos móveis (responsivo)
- ✅ WooCommerce 3.9+
- ✅ Plugins WooCommerce padrão

## Desativação & Dados

- **Ao desativar**: Orçamentos (RFQ) são convertidos para "draft"
- **Ao remover**: Opções plugin removidas, clientes/orders preservados
- **Toggle**: Ativa/desativa modo sem perder dados históricos

## Troubleshooting

**Modal não aparece:**
- Verifique se modo catálogo está ativado
- Verifique console browser (F12 → Console)
- Teste com tema padrão para descartar conflito CSS

**Emails não chegam:**
- Verifique configuração SMTP WordPress
- Teste com plugin "WP Mail SMTP" ou similar
- Verifique pasta spam

**Clientes não são criados:**
- Verifique se "Ativar registros de clientes" está ON (WooCommerce)
- Verifique logs WP-Debug

## Futuras Melhorias (v2)

- [ ] Rate limiting por IP
- [ ] Integração CRM/Zapier
- [ ] Relatórios de orçamentos
- [ ] Descontos automáticos
- [ ] Integração com formulários custom
- [ ] Multi-idioma completo

## Suporte

Para dúvidas ou bugs, reporte em `/CLAUDE.md` ou contate desenvolvedor.

---

**Versão:** 1.0.0  
**Licença:** GPL v2+  
**Autor:** Confiar  
**Compatibilidade WC:** 3.9+
