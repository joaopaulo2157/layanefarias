FLORAGESTOR PRO V8
==================

NOVOS RECURSOS
- Catálogo público responsivo
- Pedido online sem login
- Status público do pedido online
- Chave Pix exibida ao cliente
- Estrutura para payment links/gateways
- PWA instalável do catálogo
- Pedidos online separados e conversão em pedido interno
- Taxa de entrega e pedido mínimo por bairro
- Funil de vendas / CRM de leads
- Recuperação de clientes inativos
- Recuperação de orçamentos parados
- Configuração do catálogo pelo painel
- Estrutura de eventos de segurança

ATUALIZAÇÃO A PARTIR DA V7
1. Faça backup do banco.
2. Substitua os arquivos pela V8.
3. Entre como administrador.
4. Abra migrate_v8.php uma única vez.
5. Configure Catálogo público e Taxas por bairro.

PAGINAS PRINCIPAIS NOVAS
/catalog.php
/online-orders.php
/catalog-admin.php
/delivery-zones.php
/leads.php
/recovery.php

PAGAMENTO
A V8 permite exibir chave Pix e já contém tabela payment_links para integração futura.
Baixa automática por Mercado Pago, PagBank, Asaas, Stripe ou Pix bancário depende das credenciais e webhooks do provedor.

WHATSAPP
A V8 continua preparada para comunicação por WhatsApp.
Envio totalmente automático exige WhatsApp Business Cloud API/Meta e credenciais da conta.

SEGURANÇA
Não coloque credenciais de gateways diretamente no código-fonte público.
Use variáveis de ambiente na hospedagem.
