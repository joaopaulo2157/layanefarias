<?php
require __DIR__.'/ui.php';role(['admin']);$p=db();
$tag='[HOMOLOGAÇÃO]';
$metrics=[
 'Clientes teste'=>$p->query("SELECT COUNT(*) FROM customers WHERE notes='$tag'")->fetchColumn(),
 'Produtos teste'=>$p->query("SELECT COUNT(*) FROM products WHERE description='$tag'")->fetchColumn(),
 'Pedidos teste'=>$p->query("SELECT COUNT(*) FROM orders WHERE notes='$tag'")->fetchColumn(),
 'Recebíveis teste'=>$p->query("SELECT COUNT(*) FROM receivables WHERE description LIKE '$tag%'")->fetchColumn(),
 'Leads teste'=>$p->query("SELECT COUNT(*) FROM leads WHERE notes='$tag'")->fetchColumn()
];
$checks=[];
$checks[]=['Pedido atrasado detectável',(int)$p->query("SELECT COUNT(*) FROM orders WHERE notes='$tag' AND status NOT IN ('Concluído','Cancelado','Entregue') AND date(delivery_date)<date('now')")->fetchColumn()>0];
$checks[]=['Estoque crítico detectável',(int)$p->query("SELECT COUNT(*) FROM products WHERE description='$tag' AND stock<=min_stock")->fetchColumn()>0];
$checks[]=['Saldo em aberto detectável',(int)$p->query("SELECT COUNT(*) FROM receivables WHERE description LIKE '$tag%' AND status='Em aberto'")->fetchColumn()>0];
$checks[]=['Entrega vinculada',(int)$p->query("SELECT COUNT(*) FROM deliveries d JOIN orders o ON o.id=d.order_id WHERE o.notes='$tag'")->fetchColumn()>0];
$checks[]=['Timeline vinculada',(int)$p->query("SELECT COUNT(*) FROM order_timeline t JOIN orders o ON o.id=t.order_id WHERE o.notes='$tag'")->fetchColumn()>0];
page_head('Homologação operacional');?>
<div class="card"><h2>Ambiente de testes</h2><p>Cria dados fictícios identificados como HOMOLOGAÇÃO para simular uma semana operacional sem misturar clientes reais.</p><form method="post" action="homologacao-seed.php"><?=csrf_field()?><input type="hidden" name="confirm" value="CRIAR DADOS DE TESTE"><button class="primary">Criar cenários de homologação</button></form><form method="post" action="homologacao-limpar.php" style="margin-top:10px"><?=csrf_field()?><input type="hidden" name="confirm" value="LIMPAR HOMOLOGACAO"><button>Limpar dados de homologação</button></form></div>
<div class="grid g4"><?php foreach($metrics as $k=>$v):?><div class="kpi"><small><?=e($k)?></small><b><?=$v?></b></div><?php endforeach;?></div>
<div class="card"><h2>Testes automáticos de regra</h2><table><tr><th>Teste</th><th>Resultado</th></tr><?php foreach($checks as $c):?><tr><td><?=e($c[0])?></td><td><?=$c[1]?'✅ PASSOU':'⚠️ AGUARDANDO CENÁRIO'?></td></tr><?php endforeach;?></table></div>
<div class="card"><h2>Roteiro manual</h2><ol><li>Abra a Central do Dia e confirme pedidos, estoque e contas.</li><li>Abra um pedido de teste e altere produção/status; confira a Timeline.</li><li>No PDV, tente vender quantidade maior que o estoque e confirme o bloqueio.</li><li>Tente desconto acima do limite com perfil vendedor/caixa.</li><li>Conclua uma entrega e confira pedido + Timeline.</li><li>Baixe um recebível e confira o financeiro.</li><li>Abra Inteligência e confirme alertas de atraso, estoque e caixa.</li><li>Crie backup antes de qualquer teste destrutivo.</li></ol></div><?php page_foot();?>