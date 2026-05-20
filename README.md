# Confiar Catalog Mode

Plugin WordPress/WooCommerce que transforma a loja em **modo catálogo B2B**: oculta preços, substitui o botão de compra por "Solicitar Orçamento" e gerencia todo o fluxo de cotação direto no painel — sem plugins de terceiros, sem conflito de CSS com temas customizados.

## Description

Desenvolvido para lojas B2B (distribuidoras, atacadistas, indústrias) que não exibem preços publicamente e operam via cotação. O plugin oferece:

- **Toggle ON/OFF** sem perda de dados — ativa e desativa o modo catálogo com um clique
- **Modal de orçamento rápido** com campos B2B: Nome, E-mail, Telefone, CNPJ, CEP, Produto, Quantidade e Mensagem
- **Máscaras automáticas** nos campos de Telefone, CNPJ e CEP
- **Criação automática de cliente** WooCommerce ao receber o primeiro orçamento
- **Status customizado "Orçamento Pendente"** (wc-rfq) integrado ao painel de pedidos
- **Metabox de resposta** no pedido: admin envia preço e mensagem; cliente recebe e-mail com link para checkout
- **Notificações por e-mail** em português para cliente e administrador, com Reply-To correto para respostas diretas
- **Compatível com HPOS** (High-Performance Order Storage do WooCommerce)
- **Dark mode** via variáveis CSS — sem `!important`, sem conflito com temas Blonwe/customizados
- **Auto-update** via GitHub — notificação de atualização direto no painel WordPress

### Requisitos

- WordPress 5.0+
- WooCommerce 3.9+
- PHP 7.2+

### Instalação

1. Baixe o ZIP da [última release](https://github.com/acacioojunior-maker/wp-catalog-mode/releases)
2. WordPress → Plugins → Adicionar novo → Enviar plugin → Ativar
3. Configurações → Confiar Catalog Mode → marcar **Ativar Modo Catálogo**

### Fluxo de uso

**Cliente:** vê produto sem preço → clica "Solicitar Orçamento" → preenche formulário → recebe confirmação por e-mail.

**Lojista:** recebe e-mail com dados do cliente (telefone, CNPJ, CEP) → abre pedido no painel → preenche preço e mensagem → clica "Enviar Resposta ao Cliente" → cliente recebe proposta com link para checkout.

### Estrutura de arquivos

```
confiar-catalog-mode/
├── confiar-catalog-mode.php
├── uninstall.php
├── includes/
│   ├── class-main.php
│   ├── class-settings.php
│   ├── class-product-display.php
│   ├── class-quote-form.php
│   ├── class-order-handler.php
│   ├── class-email-notifier.php
│   └── class-admin-quote-manager.php
├── public/
│   ├── css/modal.css
│   └── js/modal.js
├── templates/emails/
│   ├── customer-quote-notification.php
│   └── admin-quote-notification.php
└── lib/
    └── plugin-update-checker/
```

### Hook disponível

```php
// Disparado após orçamento enviado — útil para integrações CRM/Zapier
do_action( 'confiar_quote_submitted', $order, $customer_name, $customer_email );
```

## Changelog

### 1.0.5
* Corrige bug crítico: hook de desativação convertia pedidos rfq→pending durante updates, destruindo orçamentos ativos
* Limpeza de dados (rfq→pending + remoção de opções) movida para `uninstall.php` — executa apenas ao deletar o plugin permanentemente

### 1.0.4
* Adiciona campos B2B ao formulário: **Telefone** (obrigatório, nativo WooCommerce), **CNPJ** (meta customizado) e **CEP** (nativo WooCommerce)
* Máscaras automáticas de digitação para Telefone `(00) 00000-0000`, CNPJ `00.000.000/0000-00` e CEP `00000-000`
* CNPJ e CEP em layout lado a lado, responsivo
* Metabox do admin agora exibe Telefone, CNPJ e CEP do cliente
* E-mail para admin inclui telefone clicável (`tel:`), CNPJ e CEP
* Link do e-mail admin atualizado para URL HPOS (`admin.php?page=wc-orders`)
* Todos os templates de e-mail traduzidos para português
* Implementa auto-update via GitHub usando plugin-update-checker v5.6
* Plugin URI atualizado para o repositório GitHub

### 1.0.3
* Traduz todos os textos de UI e e-mails para português (pt-BR)
* Adiciona cabeçalhos `From:` e `Reply-To:` corretos nos e-mails para melhor entregabilidade
* E-mail do admin: Reply-To aponta para o cliente (resposta direta via e-mail)
* E-mail do cliente: Reply-To aponta para a loja
* Placeholder padrão "Sua cotação de hoje." no campo de mensagem do vendedor
* Notas do pedido e labels de status em português

### 1.0.2
* Corrige compatibilidade com HPOS (High-Performance Order Storage)
* Adiciona declaração `FeaturesUtil::declare_compatibility()` antes da inicialização do WooCommerce
* Substitui `get_posts()` por `wc_get_orders()` na desativação (compatível com HPOS e CPT legado)
* Suporte a colunas nas telas de pedidos HPOS e legado simultaneamente

### 1.0.1
* Corrige status `wc-rfq` desaparecendo após primeiro request (mover `register_post_status` para hook `init`)
* Corrige metabox sempre mostrando "não é uma solicitação de orçamento" (`get_status()` retorna `rfq`, não `wc-rfq`)
* Corrige botão de orçamento não aparecendo em produto individual (produtos sem preço não disparam `woocommerce_after_add_to_cart_button`)
* Corrige botão WhatsApp do tema Blonwe não sendo removido corretamente

### 1.0.0
* Versão inicial
* Modo catálogo com toggle ON/OFF
* Modal de orçamento rápido
* Criação automática de cliente WooCommerce
* Status customizado "Orçamento Pendente" (wc-rfq)
* Metabox para resposta de orçamento no painel admin
* Notificações por e-mail para cliente e administrador
* Compatibilidade com dark mode via CSS variables
