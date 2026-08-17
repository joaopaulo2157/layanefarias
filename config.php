<?php
declare(strict_types=1);

ini_set('session.use_strict_mode','1');
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_samesite','Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ini_set('session.cookie_secure','1');
session_start();

define('APP_NAME','FloraGestor Ultimate 1.0');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_URL', rtrim(getenv('APP_URL') ?: '', '/'));
define('DB_PATH', __DIR__.'/storage/floragestor.sqlite');
define('UPLOAD_DIR', __DIR__.'/storage/uploads');
define('MAX_UPLOAD', 8 * 1024 * 1024);

function db(): PDO {
    static $pdo=null;
    if ($pdo instanceof PDO) return $pdo;
    $pdo=new PDO('sqlite:'.DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys=ON');
    $pdo->exec('PRAGMA journal_mode=WAL');
    return $pdo;
}
function e($v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function money($v): string { return 'R$ '.number_format((float)$v,2,',','.'); }
function go(string $url): never { header('Location: '.$url); exit; }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function auth(): void { if(!current_user()) go('login.php'); }
function role(array $roles): void { auth(); if(!in_array(current_user()['role'],$roles,true)){http_response_code(403);exit('Acesso negado.');} }
function can(array $roles): bool { return current_user() && in_array(current_user()['role'],$roles,true); }
function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf()).'">'; }
function csrf_check(): void { if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit('Token CSRF inválido.');} }
function flash(string $type,string $msg): void { $_SESSION['flash']=[$type,$msg]; }
function take_flash(): ?array { $f=$_SESSION['flash']??null;unset($_SESSION['flash']);return $f; }
function next_number(string $prefix,string $table): string {
    $n=(int)db()->query("SELECT COALESCE(MAX(id),0)+1 FROM {$table}")->fetchColumn();
    return $prefix.str_pad((string)$n,5,'0',STR_PAD_LEFT);
}
function audit(string $action,string $entity,?int $entityId=null,array $details=[]): void {
    $u=current_user();
    $s=db()->prepare("INSERT INTO audit_logs(user_id,action,entity,entity_id,details,created_at)VALUES(?,?,?,?,?,datetime('now','localtime'))");
    $s->execute([$u['id']??null,$action,$entity,$entityId,json_encode($details,JSON_UNESCAPED_UNICODE)]);
}
function setting(string $key,string $default=''): string {
    try{$s=db()->prepare("SELECT value FROM settings WHERE key=?");$s->execute([$key]);$v=$s->fetchColumn();return $v===false?$default:(string)$v;}catch(Throwable $e){return $default;}
}
function set_setting(string $key,string $value): void {
    db()->prepare("INSERT INTO settings(key,value)VALUES(?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value")->execute([$key,$value]);
}
function app_url(string $path=''): string { return APP_URL ? APP_URL.'/'.ltrim($path,'/') : $path; }
function upload_file(array $file,int $orderId): ?string {
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) return null;
    if(($file['error']??1)!==UPLOAD_ERR_OK) throw new RuntimeException('Falha no upload.');
    if(($file['size']??0)>MAX_UPLOAD) throw new RuntimeException('Arquivo maior que 8 MB.');
    $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if(!isset($allowed[$mime])) throw new RuntimeException('Tipo de arquivo não permitido.');
    $dir=UPLOAD_DIR.'/order-'.$orderId;if(!is_dir($dir))mkdir($dir,0775,true);
    $name=bin2hex(random_bytes(12)).'.'.$allowed[$mime];
    if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name)) throw new RuntimeException('Não foi possível salvar o arquivo.');
    return 'order-'.$orderId.'/'.$name;
}

function public_order_url(string $token): string { return app_url('portal.php?token='.urlencode($token)); }
function whatsapp_digits(string $phone): string { return preg_replace('/\\D+/','',$phone); }
function whatsapp_link(string $phone,string $message): string {
    $digits=whatsapp_digits($phone);
    return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
}
function ensure_order_token(int $orderId): string {
    $s=db()->prepare("SELECT public_token FROM orders WHERE id=?");$s->execute([$orderId]);$token=(string)($s->fetchColumn() ?: '');
    if($token===''){$token=bin2hex(random_bytes(24));db()->prepare("UPDATE orders SET public_token=? WHERE id=?")->execute([$token,$orderId]);}
    return $token;
}
function recipe_for_product(int $productId): ?array {
    $s=db()->prepare("SELECT * FROM recipes WHERE product_id=? ORDER BY id DESC LIMIT 1");$s->execute([$productId]);$r=$s->fetch();return $r?:null;
}
function consume_product_stock(int $productId,float $qty,string $refType,int $refId): void {
    $pdo=db();$recipe=recipe_for_product($productId);
    if($recipe){
        $s=$pdo->prepare("SELECT ri.*,p.name,p.cost FROM recipe_items ri JOIN products p ON p.id=ri.component_product_id WHERE ri.recipe_id=?");$s->execute([$recipe['id']]);
        foreach($s->fetchAll() as $c){$used=(float)$c['qty']*$qty;$pdo->prepare("UPDATE products SET stock=MAX(0,stock-?) WHERE id=?")->execute([$used,$c['component_product_id']]);$pdo->prepare("INSERT INTO stock_movements(product_id,type,qty,reason,reference_type,reference_id,user_id)VALUES(?,'Saída',?,?,?,?,?)")->execute([$c['component_product_id'],$used,'Consumo por ficha técnica',$refType,$refId,current_user()['id']??null]);}
    } else {
        $pdo->prepare("UPDATE products SET stock=MAX(0,stock-?) WHERE id=?")->execute([$qty,$productId]);
        $pdo->prepare("INSERT INTO stock_movements(product_id,type,qty,reason,reference_type,reference_id,user_id)VALUES(?,'Saída',?,?,?,?,?)")->execute([$productId,$qty,'Venda/encomenda',$refType,$refId,current_user()['id']??null]);
    }
}
function template_message(string $key,array $vars=[]): string {
    $default=[
      'order_received'=>'Olá {cliente}! Recebemos seu pedido {pedido}. Total: {total}. Acompanhe: {portal}',
      'art_ready'=>'Olá {cliente}! A arte do pedido {pedido} está pronta para sua aprovação: {portal}',
      'order_ready'=>'Olá {cliente}! Seu pedido {pedido} está pronto.',
      'out_for_delivery'=>'Olá {cliente}! Seu pedido {pedido} saiu para entrega.',
      'special_date'=>'Olá {cliente}! Está chegando {ocasiao}. Quer que eu separe algumas opções de presente para {presenteado}?'
    ];
    $tpl=setting('msg_'.$key,$default[$key]??'');
    foreach($vars as $k=>$v)$tpl=str_replace('{'.$k.'}',(string)$v,$tpl);
    return $tpl;
}

function secure_headers(): void {
    if(headers_sent())return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
secure_headers();

function public_token(): string { return bin2hex(random_bytes(20)); }
function security_event(string $type,array $details=[]): void {
    try{
        $u=current_user();$ip=$_SERVER['REMOTE_ADDR']??'';$ua=$_SERVER['HTTP_USER_AGENT']??'';
        db()->prepare("INSERT INTO security_events(user_id,event_type,ip,user_agent,details)VALUES(?,?,?,?,?)")
          ->execute([$u['id']??null,$type,$ip,$ua,json_encode($details,JSON_UNESCAPED_UNICODE)]);
    }catch(Throwable $e){}
}

function timeline_event(int $orderId,string $type,string $title,string $details=''): void {
    try{
        db()->prepare("INSERT INTO order_timeline(order_id,event_type,title,details,user_id)VALUES(?,?,?,?,?)")
          ->execute([$orderId,$type,$title,$details,current_user()['id']??null]);
    }catch(Throwable $e){}
}
function available_stock_for_product(int $productId,float $qty): array {
    $pdo=db();$recipe=recipe_for_product($productId);
    if(!$recipe){
        $s=$pdo->prepare("SELECT name,stock FROM products WHERE id=?");$s->execute([$productId]);$p=$s->fetch();
        return ['ok'=>$p && (float)$p['stock'] >= $qty,'message'=>$p ? $p['name'].' possui '.(float)$p['stock'].' em estoque.' : 'Produto não encontrado.'];
    }
    $s=$pdo->prepare("SELECT p.name,p.stock,ri.qty FROM recipe_items ri JOIN products p ON p.id=ri.component_product_id WHERE ri.recipe_id=?");$s->execute([$recipe['id']]);
    foreach($s->fetchAll() as $c){$need=(float)$c['qty']*$qty;if((float)$c['stock']<$need)return ['ok'=>false,'message'=>'Estoque insuficiente de '.$c['name'].'. Necessário '.$need.', disponível '.(float)$c['stock'].'.'];}
    return ['ok'=>true,'message'=>'OK'];
}
