FLORAGESTOR PRO V6
==================

Principais módulos:
- Login multiusuário e perfis: admin, gerente, vendedor, produção, caixa e entregador
- PDV e encomendas
- Clientes + CRM de datas especiais
- Fidelidade e cupons
- Orçamentos
- Pedidos e anexos de arte/referência
- Aprovação de arte
- Kanban de produção
- Produtos, estoque e fichas de montagem
- Precificação automática
- Perecíveis e perdas
- Entregas e responsável
- Fornecedores e histórico de compras
- Financeiro com DRE simplificada
- Caixa e fechamento
- Comissões por vendedor
- Relatórios
- Backup SQLite
- Migração da estrutura
- HTTPS e proteções básicas de produção

INSTALAÇÃO NOVA
1. Envie a pasta para uma hospedagem com PHP 8+ e PDO SQLite.
2. Dê permissão de escrita para storage/.
3. Acesse /install.php uma única vez.
4. Entre em /login.php.
5. Troque as senhas padrão.

ATUALIZAÇÃO DE V5
1. Faça backup do banco V5.
2. Copie o banco para storage/floragestor.sqlite.
3. Entre como admin.
4. Acesse migrate.php.
5. Revise os novos usuários/perfis e configurações.

USUÁRIOS PADRÃO
admin@floragestor.local / Admin@123
gerente@floragestor.local / Gerente@123
vendedor@floragestor.local / Venda@123
producao@floragestor.local / Producao@123
caixa@floragestor.local / Caixa@123
entregador@floragestor.local / Entrega@123

SEGURANÇA
- password_hash / password_verify
- CSRF
- PDO/prepared statements
- sessão com HttpOnly, SameSite e modo estrito
- upload limitado a JPG/PNG/WEBP/PDF, até 8 MB
- banco e uploads protegidos da web
- HTTPS forçado fora do localhost
- cabeçalhos de segurança

IMPORTANTE
Esta versão é uma base profissional funcional, mas integrações oficiais de WhatsApp, emissão fiscal, gateways de pagamento e mapas/rotas exigem credenciais/API dos respectivos serviços.
