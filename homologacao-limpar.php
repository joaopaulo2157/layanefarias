<?php
require __DIR__.'/config.php';auth();role(['admin']);$p=db();if($_SERVER['REQUEST_METHOD']!=='POST')exit('POST');csrf_check();if(($_POST['confirm']??'')!=='LIMPAR HOMOLOGACAO')go('homologacao.php');$tag='[HOMOLOGAÇÃO]';$p->beginTransaction();try{
$oids=$p->query("SELECT id FROM orders WHERE notes='$tag'")->fetchAll(PDO::FETCH_COLUMN);
foreach($oids as $id){foreach(['order_timeline','receivables','deliveries','order_items'] as $t)$p->prepare("DELETE FROM $t WHERE ".($t==='order_timeline'||$t==='receivables'||$t==='deliveries'?'order_id':'order_id')."=?")->execute([$id]);}
$p->exec("DELETE FROM orders WHERE notes='$tag'");
$p->exec("DELETE FROM quotes WHERE notes='$tag'");
$p->exec("DELETE FROM leads WHERE notes='$tag'");
$p->exec("DELETE FROM accounts_payable WHERE description LIKE '$tag%'");
$p->exec("DELETE FROM products WHERE description='$tag'");
$p->exec("DELETE FROM customers WHERE notes='$tag'");
$p->commit();flash('success','Dados de homologação removidos.');
}catch(Throwable $e){$p->rollBack();flash('error',$e->getMessage());}go('homologacao.php');
