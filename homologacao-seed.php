<?php
require __DIR__.'/config.php';auth();role(['admin']);$p=db();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('POST obrigatório.');}
csrf_check();
if(($_POST['confirm']??'')!=='CRIAR DADOS DE TESTE'){flash('error','Confirmação inválida.');go('homologacao.php');}
$tag='[HOMOLOGAÇÃO]';
$p->beginTransaction();
try{
  $customers=[
   ['Ana Teste','82999990001','ana.teste@example.invalid','1990-09-12'],
   ['Carlos Teste','82999990002','carlos.teste@example.invalid','1986-10-05'],
   ['Empresa Teste LTDA','82999990003','compras@example.invalid',null]
  ];
  foreach($customers as $c)$p->prepare("INSERT INTO customers(name,whatsapp,email,birthday,notes)VALUES(?,?,?,?,?)")->execute([$c[0],$c[1],$c[2],$c[3],$tag]);
  $ids=$p->query("SELECT id FROM customers WHERE notes='$tag' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
  $products=[
   ['HOMO-ROSAS','Buquê 12 Rosas - TESTE','Flores',120,55,20,5],
   ['HOMO-CESTA','Cesta Café Premium - TESTE','Cestas',189,82,15,3],
   ['HOMO-CANECA','Caneca Personalizada - TESTE','Personalizados',49.9,18,30,5],
   ['HOMO-CHOC','Chocolate Insumo - TESTE','Insumos',12,7,100,20],
   ['HOMO-CAIXA','Caixa Presente Insumo - TESTE','Insumos',8,4,50,10]
  ];
  foreach($products as $x)$p->prepare("INSERT INTO products(sku,name,category,price,cost,stock,min_stock,active,description)VALUES(?,?,?,?,?,?,?,?,?)")->execute([$x[0],$x[1],$x[2],$x[3],$x[4],$x[5],$x[6],1,$tag]);
  $prod=$p->query("SELECT id,sku,name,price,cost FROM products WHERE description='$tag'")->fetchAll();
  $by=[];foreach($prod as $x)$by[$x['sku']]=$x;
  // Quote
  $qn=next_number('ORC-','quotes');$p->prepare("INSERT INTO quotes(number,customer_id,amount,status,notes)VALUES(?,?,?,'Em aberto',?)")->execute([$qn,$ids[0],189,$tag]);
  // Leads and followup
  $p->prepare("INSERT INTO leads(name,whatsapp,source,interest,stage,estimated_value,next_followup,notes)VALUES(?,?,?,?,?,?,datetime('now','-1 hour'),?)")->execute(['Lead Teste','82999990004','Instagram','Cesta romântica','Orçamento enviado',220,$tag]);
  // Accounts payable
  $p->prepare("INSERT INTO accounts_payable(description,category,cost_center,amount,due_date,status)VALUES(?,?,?,?,date('now'),'Pendente')")->execute([$tag.' Fornecedor de flores','Mercadoria','Produção',350]);
  // Orders: today, future, overdue, delivery, partial balance.
  $cases=[
    [$ids[0],$by['HOMO-ROSAS'],1,'Recebido','Separação','Retirada na loja',date('Y-m-d'),null,0],
    [$ids[1],$by['HOMO-CESTA'],1,'Em produção','Montagem','Entrega local',date('Y-m-d'), '14:00',50],
    [$ids[0],$by['HOMO-CANECA'],2,'Recebido','Personalização','Retirada na loja',date('Y-m-d',strtotime('+2 day')),null,49.9],
    [$ids[1],$by['HOMO-ROSAS'],1,'Recebido','Novo','Entrega local',date('Y-m-d',strtotime('-1 day')),'10:00',0]
  ];
  foreach($cases as $i=>$c){
    [$cid,$pr,$qty,$status,$stage,$dtype,$ddate,$dtime,$paid]=$c;
    $num=next_number('PED-','orders');$sub=$pr['price']*$qty;$fee=$dtype==='Entrega local'?10:0;$total=$sub+$fee;$cost=$pr['cost']*$qty;
    $p->prepare("INSERT INTO orders(number,customer_id,payment_method,subtotal,discount,delivery_fee,total,paid_amount,cost,profit,status,production_stage,art_status,delivery_type,delivery_date,delivery_time,recipient_name,notes)VALUES(?,?,? ,?,0,?,?,?,?,?,?,?,?,?,?,?,?,?)")
      ->execute([$num,$cid,'Pix',$sub,$fee,$total,$paid,$cost,$total-$cost,$status,$stage,'Não se aplica',$dtype,$ddate,$dtime,'Destinatário Teste',$tag]);
    $oid=(int)$p->lastInsertId();
    $p->prepare("INSERT INTO order_items(order_id,product_id,product_name,qty,unit_price,unit_cost)VALUES(?,?,?,?,?,?)")->execute([$oid,$pr['id'],$pr['name'],$qty,$pr['price'],$pr['cost']]);
    if($dtype==='Entrega local')$p->prepare("INSERT INTO deliveries(order_id,status)VALUES(?,'Pendente')")->execute([$oid]);
    if($paid<$total)$p->prepare("INSERT INTO receivables(order_id,description,amount,due_date,status)VALUES(?,?,?,?, 'Em aberto')")->execute([$oid,$tag.' Saldo '.$num,$total-$paid,$ddate]);
    timeline_event($oid,'homologation','Cenário de homologação criado',$tag);
  }
  // Low stock scenario
  $p->prepare("UPDATE products SET stock=2 WHERE sku='HOMO-CAIXA'")->execute();
  $p->commit();flash('success','Dados de homologação criados.');
}catch(Throwable $e){$p->rollBack();flash('error','Falha: '.$e->getMessage());}
go('homologacao.php');
