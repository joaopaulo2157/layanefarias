<?php
require __DIR__.'/config.php';$p=db();
if($_SERVER['REQUEST_METHOD']!=='POST')go('catalog.php');
$items=json_decode($_POST['items']??'[]',true);if(!$items)exit('Carrinho vazio.');
$sub=0;$clean=[];
foreach($items as $i){$s=$p->prepare("SELECT id,name,price,stock FROM products WHERE id=? AND active=1");$s->execute([(int)$i['id']]);$pr=$s->fetch();if(!$pr)continue;$q=max(1,(int)$i['qty']);$sub+=$pr['price']*$q;$clean[]=['id'=>$pr['id'],'name'=>$pr['name'],'price'=>$pr['price'],'qty'=>$q];}
if(!$clean)exit('Nenhum item válido.');
$fee=0;$neighborhood=trim($_POST['neighborhood']??'');if($neighborhood){$s=$p->prepare("SELECT * FROM delivery_zones WHERE neighborhood=? AND active=1");$s->execute([$neighborhood]);if($z=$s->fetch()){$fee=(float)$z['fee'];if($z['min_order']>0&&$sub<$z['min_order'])exit('O pedido não atingiu o valor mínimo para este bairro.');}}
$token=public_token();$number=next_number('WEB-','online_orders');$p->beginTransaction();try{
$p->prepare("INSERT INTO online_orders(public_number,customer_name,whatsapp,email,occasion,delivery_type,delivery_date,delivery_time,recipient_name,recipient_phone,address,neighborhood,notes,subtotal,delivery_fee,total,payment_method,payment_status,status,token)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'Pendente','Novo',?)")->execute([$number,trim($_POST['customer_name']),trim($_POST['whatsapp']),trim($_POST['email']),trim($_POST['occasion']),$_POST['delivery_type'],$_POST['delivery_date']?:null,$_POST['delivery_time']?:null,trim($_POST['recipient_name']),trim($_POST['recipient_phone']),trim($_POST['address']),$neighborhood,trim($_POST['notes']),$sub,$fee,$sub+$fee,'Pix',$token]);$oid=(int)$p->lastInsertId();
foreach($clean as $i)$p->prepare("INSERT INTO online_order_items(online_order_id,product_id,product_name,qty,unit_price)VALUES(?,?,?,?,?)")->execute([$oid,$i['id'],$i['name'],$i['qty'],$i['price']]);
$p->commit();}catch(Throwable $e){$p->rollBack();exit('Não foi possível registrar o pedido.');}
go('online-status.php?token='.$token);
