<?php
require_once __DIR__.'/config.php';auth();
function page_head(string $title): void {$u=current_user();$flash=take_flash();?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="style.css"><title><?=e($title)?> - FloraGestor</title></head><body>
<aside class="sidebar"><div class="brand">FloraGestor <b>Ultimate 1.0</b><small><?=e(setting('store_name','Minha Floricultura'))?></small></div>
<nav>
<a href="today.php">☀️ Central do Dia</a><a href="search.php">🔎 Busca global</a><a href="index.php">📊 Dashboard</a>
<?php if(can(['admin','gerente','vendedor','caixa'])):?><a href="pdv.php">🧾 PDV / Encomenda</a><?php endif;?>
<?php if(can(['admin','gerente','vendedor'])):?><a href="quotes.php">📄 Orçamentos</a><a href="customers.php">👥 Clientes</a><a href="crm.php">💝 CRM / Datas especiais</a><a href="agenda.php">🗓️ Agenda</a><?php endif;?>
<a href="orders.php">📦 Pedidos</a><?php if(can(['admin','gerente','vendedor'])):?><a href="online-orders.php">🌐 Pedidos online</a><a href="leads.php">🎯 Funil de vendas</a><a href="recovery.php">♻️ Recuperação</a><?php endif;?>
<?php if(can(['admin','gerente','producao'])):?><a href="production.php">🏗️ Produção</a><a href="artwork.php">🎨 Aprovação de arte</a><a href="products.php">🌷 Produtos</a><a href="recipes.php">🧺 Fichas de montagem</a><a href="pricing.php">🏷️ Precificação</a><a href="inventory.php">📦 Estoque</a><a href="stock_movements.php">🔄 Movimentações</a><a href="losses.php">⚠️ Perdas</a><a href="perishables.php">🌿 Perecíveis</a><?php endif;?>
<?php if(can(['admin','gerente','entregador'])):?><a href="deliveries.php">🚚 Entregas</a><?php endif;?>
<?php if(can(['admin','gerente','caixa'])):?><a href="finance.php">💰 Financeiro / DRE</a><a href="cash.php">💵 Caixa</a><?php endif;?>
<?php if(can(['admin','gerente'])):?><a href="suppliers.php">🏭 Fornecedores</a><a href="purchases.php">🛒 Compras</a><a href="loyalty.php">⭐ Fidelidade / Cupons</a><a href="reports.php">📈 Relatórios</a><a href="notifications.php">🔔 Notificações</a><a href="capacity.php">⏱️ Capacidade</a><a href="payables.php">🧾 Contas a pagar</a><a href="giftcards.php">🎁 Vale-presente</a><a href="events.php">📅 Eventos</a><a href="intelligence.php">🧠 Inteligência</a><a href="automations.php">⚙️ Automações</a><a href="backup-center.php">💾 Backup</a><a href="health-center.php">🩺 Diagnóstico</a><a href="homologacao.php">🧪 Homologação</a><a href="audit.php">🛡️ Auditoria</a><a href="catalog-admin.php">🛍️ Catálogo público</a><a href="delivery-zones.php">📍 Taxas por bairro</a><a href="subscriptions.php">🔁 Assinaturas</a><?php endif;?>
<?php if(can(['admin'])):?><a href="users.php">🔐 Usuários</a><a href="settings.php">⚙️ Configurações</a><a href="messages.php">💬 Mensagens</a><?php endif;?>
</nav><div class="userbox"><b><?=e($u['name'])?></b><small><?=e(ucfirst($u['role']))?></small><a href="logout.php">Sair</a></div></aside>
<main class="content"><header class="topbar"><div><h1><?=e($title)?></h1><span><?=date('d/m/Y H:i')?></span></div></header>
<?php if($flash):?><div class="flash <?=e($flash[0])?>"><?=e($flash[1])?></div><?php endif;?>
<?php }
function page_foot(): void {echo '</main><script src="app.js"></script></body></html>';}
function badge_class(string $s): string {return in_array($s,['Entregue','Pronto','Recebido','Aprovada','Aprovado','OK'])?'ok':(in_array($s,['Cancelado','Vencido','Reprovada'])?'bad':'warn');}
?>