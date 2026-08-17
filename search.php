<?php require __DIR__.'/ui.php';auth();$p=db();$q=trim($_GET['q']??'');$res=[];if(mb_strlen($q)>=2){$like='%'.$q.'%';
foreach([
['Clientes',"SELECT id,name,whatsapp FROM customers WHERE name LIKE ? OR whatsapp LIKE ? LIMIT 10",2],
['Pedidos',"SELECT id,number,recipient_name FROM orders WHERE number LIKE ? OR recipient_name LIKE ? LIMIT 10",2],
['Produtos',"SELECT id,name,sku FROM products WHERE name LIKE ? OR sku LIKE ? LIMIT 10",2],
['Orçamentos',"SELECT id,number,status FROM quotes WHERE number LIKE ? OR notes LIKE ? LIMIT 10",2]
] as $s){$st=$p->prepare($s[1]);$st->execute(array_fill(0,$s[2],$like));$res[$s[0]]=$st->fetchAll();}}
page_head('Busca global');?><div class="card"><form><input autofocus name="q" value="<?=e($q)?>" placeholder="Cliente, telefone, pedido, produto, orçamento..."><button class="primary">Buscar</button></form></div>
<?php foreach($res as $title=>$rows):?><div class="card"><h2><?=e($title)?></h2><?php foreach($rows as $r):?><pre style="white-space:pre-wrap"><?=e(implode(' • ',array_values($r)))?></pre><?php endforeach;?></div><?php endforeach;?><?php page_foot();?>