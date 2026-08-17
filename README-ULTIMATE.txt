FLORAGESTOR ULTIMATE 1.0 — VERSÃO CONSOLIDADA
================================================
Base consolidada das versões V1–V8 + módulos finais.

MÓDULOS
PDV; clientes/CRM; datas especiais; fidelidade; cupons; orçamentos; pedidos;
portal do cliente; catálogo online; pedidos online; produção Kanban; arte/aprovação;
produtos; estoque; fichas técnicas; baixa de insumos; perecíveis; perdas; fornecedores;
compras; financeiro; caixa; entregas; assinaturas; agenda; funil comercial; recuperação;
taxas por bairro; PWA; notificações; capacidade de produção; contas a pagar;
vale-presente; eventos; auditoria; central de inteligência; lotes; reservas; inventário;
pedidos de compra; transações de pagamento; cashback; metas; centros de custo e canais.

ATUALIZAÇÃO
1. Faça backup de storage/floragestor.sqlite.
2. Substitua os arquivos pela versão Ultimate.
3. Entre como administrador.
4. Execute /upgrade_ultimate.php UMA VEZ.
5. Revise Configurações, Catálogo, bairros, capacidade e permissões.

INTEGRAÇÕES EXTERNAS
A arquitetura contém estruturas para pagamentos e WhatsApp, mas credenciais reais,
webhooks, emissão fiscal e mapas devem ser configurados com o fornecedor escolhido.

BANCO
SQLite continua suportado para operação pequena. Para alto volume/muitos usuários
simultâneos, recomenda-se migração planejada para MySQL/MariaDB/PostgreSQL.

SEGURANÇA
Mantenha storage protegido, HTTPS ativo e backups externos. Nunca publique segredos
de gateways/Meta no código-fonte.
