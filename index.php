<?php
require __DIR__.'/ui.php';auth();$p=db();$month=date('Y-m');$today=date('Y-m-d');
$sales=(float)$p->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE substr(created_at,1,7)='$month' AND status<>'Cancelado'")->fetchColumn();
$profit=(float)$p->query("SELECT COALESCE(SUM(profit),0) FROM orders WHERE substr(created_at,1,7)='$month' AND status<>'Cancelado'")->fetchColumn();
$expenses=(float)$p->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE substr(expense_date,1,7)='$month'")->fetchColumn();
$open=(int)$p->query("SELECT COUNT(*) FROM orders WHERE status NOT IN('Entregue','Cancelado')")->fetchColumn();
$deliveries=(int)$p->query("SELECT COUNT(*) FROM orders WHERE delivery_date='$today' AND delivery_type='Entrega local'")->fetchColumn();
$receivable=(float)$p->query("SELECT COALESCE(SUM(amount),0) FROM receivables WHERE status='Em aberto'")->fetchColumn();
$low=$p->query("SELECT name,stock,min_stock FROM products WHERE active=1 AND stock<=min_stock ORDER BY stock LIMIT 8")->fetchAll();
$upcoming=$p->query("SELECT c.name,cd.label,cd.event_date,cd.recipient FROM customer_dates cd JOIN customers c ON c.id=cd.customer_id ORDER BY substr(cd.event_date,6,5) LIMIT 8")->fetchAll();
page_head('Dashboard');?>
<div class="grid g4"><div class="card kpi"><span>Faturamento do mês</span><strong><?=money($sales)?></strong></div><div class="card kpi"><span>Lucro líquido estimado</span><strong><?=money($profit-$expenses)?></strong></div><div class="card kpi"><span>Pedidos abertos</span><strong><?=$open?></strong></div><div class="card kpi"><span>A receber</span><strong><?=money($receivable)?></strong></div></div>
<div class="grid g2"><div class="card"><h2>Operação de hoje</h2><div class="grid g3"><div class="kpi"><span>Entregas</span><strong><?=$deliveries?></strong></div><div class="kpi"><span>Despesas no mês</span><strong><?=money($expenses)?></strong></div><div class="kpi"><span>Margem líquida</span><strong><?=$sales>0?number_format((($profit-$expenses)/$sales)*100,1,',','.'):'0,0'?>%</strong></div></div></div>
<div class="card"><h2>Estoque crítico</h2><?php foreach($low as $x):?><p><b><?=e($x['name'])?></b> — <?=$x['stock']?> / mín. <?=$x['min_stock']?></p><?php endforeach;?></div></div>
<div class="card"><h2>Datas especiais cadastradas</h2><div class="table"><table><tr><th>Cliente</th><th>Ocasião</th><th>Data</th><th>Presenteado</th></tr><?php foreach($upcoming as $x):?><tr><td><?=e($x['name'])?></td><td><?=e($x['label'])?></td><td><?=e($x['event_date'])?></td><td><?=e($x['recipient'])?></td></tr><?php endforeach;?></table></div></div>
<?php page_foot();?>