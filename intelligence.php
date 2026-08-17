<?php require __DIR__.'/ui.php';role(['admin','gerente']);$p=db();
// deterministic intelligence engine
$alerts=[];
$low=$p->query("SELECT name,stock,min_stock FROM products WHERE active=1 AND stock<=min_stock ORDER BY stock")->fetchAll();
foreach($low as $x)$alerts[]=['Estoque','Alta',"Estoque crítico: {$x['name']}","Saldo {$x['stock']} / mínimo {$x['min_stock']}"];
$late=$p->query("SELECT COUNT(*) n FROM orders WHERE status NOT IN ('Concluído','Cancelado','Entregue') AND delivery_date IS NOT NULL AND date(delivery_date)<date('now')")->fetchColumn();
if($late)$alerts[]=['Produção','Alta','Pedidos atrasados',"$late pedido(s) passaram da data prevista."];
$receiv=$p->query("SELECT COALESCE(SUM(amount),0) FROM receivables WHERE status='Em aberto' AND date(due_date)<=date('now','+7 day')")->fetchColumn();
$pay=$p->query("SELECT COALESCE(SUM(amount),0) FROM accounts_payable WHERE status='Pendente' AND date(due_date)<=date('now','+7 day')")->fetchColumn();
$alerts[]=['Financeiro',$pay>$receiv?'Alta':'Info','Próximos 7 dias','Receber '.money($receiv).' / pagar '.money($pay).'.'];
$quotes=$p->query("SELECT COUNT(*) FROM quotes WHERE status='Em aberto' AND date(created_at)<=date('now','-2 day')")->fetchColumn();
if($quotes)$alerts[]=['Comercial','Média','Orçamentos sem retorno',"$quotes orçamento(s) aguardam acompanhamento."];
page_head('Central de Inteligência');?>
<div class="grid g3"><?php foreach($alerts as $a):?><div class="card"><small><?=e($a[0])?> • <?=e($a[1])?></small><h2><?=e($a[2])?></h2><p><?=e($a[3])?></p></div><?php endforeach;?></div>
<div class="card"><h2>O que esta central analisa</h2><p>Estoque crítico, atrasos de produção, fluxo financeiro de curto prazo, recuperação de orçamentos e outros sinais operacionais. O motor usa os dados reais do banco e não depende de API de IA.</p></div><?php page_foot();?>