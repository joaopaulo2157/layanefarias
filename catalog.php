<?php
require __DIR__.'/config.php';$p=db();
$cfg=$p->query("SELECT * FROM public_catalog_settings WHERE id=1")->fetch() ?: [];
if((int)setting('catalog_enabled','1')!==1){http_response_code(503);exit('Catálogo temporariamente indisponível.');}
$products=$p->query("SELECT * FROM products WHERE active=1 AND stock>0 ORDER BY category,name")->fetchAll();
$zones=$p->query("SELECT * FROM delivery_zones WHERE active=1 ORDER BY neighborhood")->fetchAll();
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e(setting('store_name','Minha Floricultura'))?></title><link rel="manifest" href="manifest.webmanifest">
<meta name="theme-color" content="<?=e($cfg['primary_color']??'#b45a64')?>"><link rel="stylesheet" href="catalog.css"></head><body>
<header class="hero"><div><h1><?=e($cfg['hero_title']??'Presentes feitos com carinho')?></h1><p><?=e($cfg['hero_subtitle']??'Escolha, personalize e encomende online.')?></p></div></header>
<main class="catalog-wrap"><section><div class="filters"><input id="search" placeholder="Buscar produto..." oninput="filterProducts()"></div><div id="grid" class="product-grid">
<?php foreach($products as $x):?><article class="product" data-search="<?=e(strtolower($x['name'].' '.$x['category']))?>"><div class="photo" <?php if($x['image_url']):?>style="background-image:url('<?=e($x['image_url'])?>')"<?php endif;?>><?=$x['image_url']?'':'🌷'?></div><div class="body"><small><?=e($x['category'])?></small><h3><?=e($x['name'])?></h3><p><?=e($x['description'])?></p><b><?=money($x['price'])?></b><button onclick='addProduct(<?=json_encode(["id"=>$x["id"],"name"=>$x["name"],"price"=>(float)$x["price"]],JSON_HEX_APOS|JSON_HEX_QUOT)?>)'>Adicionar</button></div></article><?php endforeach;?>
</div></section>
<aside class="cart"><h2>Seu pedido</h2><div id="cartItems"></div><div class="row"><span>Subtotal</span><b id="subtotal">R$ 0,00</b></div><form method="post" action="catalog-submit.php" id="orderForm"><input type="hidden" name="items" id="itemsJson">
<label>Seu nome</label><input name="customer_name" required><label>WhatsApp</label><input name="whatsapp" required><label>E-mail</label><input name="email">
<label>Ocasião</label><input name="occasion" placeholder="Aniversário, romântico..."><label>Entrega</label><select name="delivery_type"><option>Retirada na loja</option><option>Entrega local</option></select>
<label>Bairro</label><select name="neighborhood" id="neighborhood" onchange="updateZone()"><option value="">Selecione</option><?php foreach($zones as $z):?><option value="<?=e($z['neighborhood'])?>" data-fee="<?=$z['fee']?>" data-min="<?=$z['min_order']?>"><?=e($z['neighborhood'])?> — <?=money($z['fee'])?></option><?php endforeach;?></select>
<label>Data</label><input type="date" name="delivery_date"><label>Horário</label><input type="time" name="delivery_time"><label>Destinatário</label><input name="recipient_name"><label>Telefone destinatário</label><input name="recipient_phone"><label>Endereço</label><textarea name="address"></textarea><label>Observações</label><textarea name="notes"></textarea>
<div class="row"><span>Entrega</span><b id="deliveryFee">R$ 0,00</b></div><div class="grand"><span>Total</span><b id="grand">R$ 0,00</b></div><button class="checkout">Enviar pedido</button></form></aside></main>
<script src="catalog.js"></script><script>if('serviceWorker'in navigator)navigator.serviceWorker.register('sw.js');</script></body></html>