FLORAGESTOR PRO V7
==================

NOVOS RECURSOS SOBRE A V6
- Portal público do cliente por token único
- QR Code por pedido apontando para o portal
- Aprovação/reprovação de arte pelo cliente
- Mensagens do cliente pelo portal
- Modelos de WhatsApp configuráveis
- Links rápidos de WhatsApp no pedido
- Agenda unificada de entregas + datas especiais
- Baixa automática de componentes por ficha técnica
- Histórico detalhado de movimentações de estoque
- Prioridade e prazo interno de produção
- Assinaturas recorrentes de flores/presentes
- Dashboard e relatórios ampliados
- Estrutura preparada para API oficial do WhatsApp

ATUALIZAÇÃO V6 -> V7
1. Faça backup do banco.
2. Substitua os arquivos do sistema pelos da V7.
3. Mantenha seu banco em storage/floragestor.sqlite.
4. Entre como administrador e abra migrate.php.
5. Configure APP_URL=https://seu-dominio.com.br para o portal/QR.
6. Revise Configurações e Modelos de mensagens.

INSTALAÇÃO NOVA
- PHP 8+
- PDO SQLite
- pasta storage/ gravável
- HTTPS recomendado
- abra install.php uma única vez

WHATSAPP
A V7 gera links e mensagens prontas para WhatsApp sem exigir credenciais.
Automação total via WhatsApp Business Cloud API exige token, phone_number_id e webhook da Meta; a estrutura da V7 foi preparada para essa evolução sem alterar os pedidos existentes.

QR CODE
O QR Code é renderizado no navegador via qrcodejs. Se a CDN estiver indisponível, o link do portal continua funcionando normalmente.

PORTAL DO CLIENTE
Cada pedido recebe um token público aleatório. O cliente pode acompanhar status, produção e arte, aprovar a arte e enviar mensagens sem precisar criar login.

ESTOQUE POR FICHA TÉCNICA
Quando um produto vendido possui ficha técnica vinculada, a V7 baixa os componentes da receita automaticamente em vez de apenas baixar o produto final.
