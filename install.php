<?php
require __DIR__.'/config.php';
$lock=__DIR__.'/storage/installed.lock';
if(file_exists($lock)){http_response_code(403);exit('Sistema já instalado. Use migrate.php para atualizar.');}
$p=db();$p->exec(<<<'SQL'

CREATE TABLE IF NOT EXISTS users(
 id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,email TEXT NOT NULL UNIQUE,password_hash TEXT NOT NULL,
 role TEXT NOT NULL DEFAULT 'vendedor',commission_pct REAL NOT NULL DEFAULT 0,active INTEGER NOT NULL DEFAULT 1,created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS customers(
 id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,whatsapp TEXT,email TEXT,birthday TEXT,address TEXT,notes TEXT,
 points INTEGER NOT NULL DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS customer_dates(
 id INTEGER PRIMARY KEY AUTOINCREMENT,customer_id INTEGER NOT NULL,label TEXT NOT NULL,event_date TEXT NOT NULL,recipient TEXT,notes TEXT,
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS products(
 id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,category TEXT NOT NULL,sku TEXT,cost REAL DEFAULT 0,price REAL DEFAULT 0,
 stock REAL DEFAULT 0,min_stock REAL DEFAULT 0,unit TEXT DEFAULT 'un',active INTEGER DEFAULT 1,image_url TEXT,shelf_life_days INTEGER DEFAULT 0,
 labor_cost REAL DEFAULT 0,packaging_cost REAL DEFAULT 0,loss_pct REAL DEFAULT 0,desired_margin REAL DEFAULT 50,description TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS recipes(
 id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,product_id INTEGER,packaging_cost REAL DEFAULT 0,labor_cost REAL DEFAULT 0,notes TEXT,
 FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS recipe_items(
 id INTEGER PRIMARY KEY AUTOINCREMENT,recipe_id INTEGER NOT NULL,component_product_id INTEGER NOT NULL,qty REAL NOT NULL,
 FOREIGN KEY(recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,FOREIGN KEY(component_product_id) REFERENCES products(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS quotes(
 id INTEGER PRIMARY KEY AUTOINCREMENT,number TEXT UNIQUE,customer_id INTEGER,description TEXT,amount REAL DEFAULT 0,valid_days INTEGER DEFAULT 7,
 status TEXT DEFAULT 'Em aberto',notes TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS orders(
 id INTEGER PRIMARY KEY AUTOINCREMENT,number TEXT UNIQUE,customer_id INTEGER,seller_id INTEGER,occasion TEXT,payment_method TEXT,
 subtotal REAL DEFAULT 0,discount REAL DEFAULT 0,delivery_fee REAL DEFAULT 0,total REAL DEFAULT 0,cost REAL DEFAULT 0,profit REAL DEFAULT 0,
 status TEXT DEFAULT 'Recebido',production_stage TEXT DEFAULT 'Novo',art_status TEXT DEFAULT 'Não se aplica',
 delivery_type TEXT DEFAULT 'Retirada na loja',delivery_date TEXT,delivery_time TEXT,recipient_name TEXT,recipient_phone TEXT,delivery_address TEXT,
 notes TEXT,public_token TEXT UNIQUE,customer_approval_status TEXT DEFAULT 'Pendente',customer_approval_notes TEXT,priority TEXT DEFAULT 'Normal',production_due_date TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,FOREIGN KEY(seller_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS order_items(
 id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER NOT NULL,product_id INTEGER,product_name TEXT,qty REAL,unit_price REAL,unit_cost REAL,
 customization TEXT,customization_value REAL DEFAULT 0,personalized_text TEXT,details TEXT,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS order_files(
 id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER NOT NULL,file_path TEXT NOT NULL,original_name TEXT,file_type TEXT,uploaded_by INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,FOREIGN KEY(uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS artwork_history(
 id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER NOT NULL,status TEXT NOT NULL,notes TEXT,user_id INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS deliveries(
 id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER NOT NULL,driver_id INTEGER,status TEXT DEFAULT 'Pendente',route_order INTEGER DEFAULT 0,delivered_at TEXT,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,FOREIGN KEY(driver_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS receivables(
 id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER,customer_id INTEGER,description TEXT,amount REAL,due_date TEXT,status TEXT DEFAULT 'Em aberto',paid_at TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE SET NULL,FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS expenses(
 id INTEGER PRIMARY KEY AUTOINCREMENT,description TEXT,category TEXT,amount REAL,expense_date TEXT,fixed_cost INTEGER DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS cash_movements(
 id INTEGER PRIMARY KEY AUTOINCREMENT,type TEXT NOT NULL,description TEXT,amount REAL NOT NULL,payment_method TEXT,user_id INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS cash_closings(
 id INTEGER PRIMARY KEY AUTOINCREMENT,closing_date TEXT UNIQUE,expected REAL,counted REAL,difference REAL,notes TEXT,user_id INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS suppliers(
 id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,contact TEXT,phone TEXT,email TEXT,category TEXT,notes TEXT
);
CREATE TABLE IF NOT EXISTS purchases(
 id INTEGER PRIMARY KEY AUTOINCREMENT,supplier_id INTEGER,product_id INTEGER,qty REAL,total REAL,unit_cost REAL,reference TEXT,purchase_date TEXT,
 FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS perishables(
 id INTEGER PRIMARY KEY AUTOINCREMENT,product_id INTEGER,qty REAL,received_date TEXT,expiry_date TEXT,status TEXT DEFAULT 'Ativo',
 FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS losses(
 id INTEGER PRIMARY KEY AUTOINCREMENT,product_id INTEGER,qty REAL,cost REAL,reason TEXT,loss_date TEXT,user_id INTEGER,
 FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS coupons(
 id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT UNIQUE,type TEXT,value REAL,expiry_date TEXT,active INTEGER DEFAULT 1
);
CREATE TABLE IF NOT EXISTS loyalty_transactions(
 id INTEGER PRIMARY KEY AUTOINCREMENT,customer_id INTEGER,points INTEGER,description TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS goals(id INTEGER PRIMARY KEY AUTOINCREMENT,month TEXT UNIQUE,amount REAL);
CREATE TABLE IF NOT EXISTS settings(key TEXT PRIMARY KEY,value TEXT);

CREATE TABLE IF NOT EXISTS stock_movements(
 id INTEGER PRIMARY KEY AUTOINCREMENT,product_id INTEGER NOT NULL,type TEXT NOT NULL,qty REAL NOT NULL,reason TEXT,reference_type TEXT,reference_id INTEGER,user_id INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS notifications(
 id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,title TEXT NOT NULL,message TEXT,link TEXT,is_read INTEGER DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS portal_messages(
 id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER NOT NULL,sender TEXT NOT NULL,message TEXT NOT NULL,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS wishlists(
 id INTEGER PRIMARY KEY AUTOINCREMENT,customer_id INTEGER NOT NULL,name TEXT NOT NULL,notes TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS wishlist_items(
 id INTEGER PRIMARY KEY AUTOINCREMENT,wishlist_id INTEGER NOT NULL,product_id INTEGER NOT NULL,qty REAL DEFAULT 1,
 FOREIGN KEY(wishlist_id) REFERENCES wishlists(id) ON DELETE CASCADE,FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS subscriptions(
 id INTEGER PRIMARY KEY AUTOINCREMENT,customer_id INTEGER NOT NULL,product_id INTEGER,description TEXT,frequency TEXT NOT NULL,next_date TEXT,amount REAL DEFAULT 0,status TEXT DEFAULT 'Ativa',notes TEXT,
 FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE,FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS audit_logs(
 id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,action TEXT,entity TEXT,entity_id INTEGER,details TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);

SQL);
if(!(int)$p->query("SELECT COUNT(*) FROM users")->fetchColumn()){
 $s=$p->prepare("INSERT INTO users(name,email,password_hash,role,commission_pct)VALUES(?,?,?,?,?)");
 foreach([
 ['Administrador','admin@floragestor.local','Admin@123','admin',0],
 ['Gerente','gerente@floragestor.local','Gerente@123','gerente',0],
 ['Vendedor','vendedor@floragestor.local','Venda@123','vendedor',3],
 ['Produção','producao@floragestor.local','Producao@123','producao',0],
 ['Caixa','caixa@floragestor.local','Caixa@123','caixa',0],
 ['Entregador','entregador@floragestor.local','Entrega@123','entregador',0]
 ] as $u)$s->execute([$u[0],$u[1],password_hash($u[2],PASSWORD_DEFAULT),$u[3],$u[4]]);
}
if(!(int)$p->query("SELECT COUNT(*) FROM products")->fetchColumn()){
 $s=$p->prepare("INSERT INTO products(name,category,sku,cost,price,stock,min_stock,unit,desired_margin)VALUES(?,?,?,?,?,?,?,?,?)");
 foreach([
 ['Buquê Tradicional','Buquê','BUQ-001',45,95,10,2,'buquê',50],
 ['Cesta Café da Manhã','Cesta','CES-001',70,149.9,6,2,'cesta',50],
 ['Caneca Personalizada','Caneca','CAN-001',18,39.9,20,5,'un',55],
 ['Caixa de Chocolates','Chocolate','CHO-001',22,49.9,12,3,'un',50],
 ['Pelúcia Média','Pelúcia','PEL-001',25,59.9,8,2,'un',50]
 ] as $x)$s->execute($x);
}
foreach([
 ['store_name','Minha Floricultura'],['whatsapp',''],['loyalty_spend_per_point','10'],['loyalty_point_value','0.50'],
 ['card_fee_pct','0'],['tax_pct','0'],['default_loss_pct','5'],['msg_order_received','Olá {cliente}! Recebemos seu pedido {pedido}. Total: {total}. Acompanhe: {portal}'],['msg_art_ready','Olá {cliente}! A arte do pedido {pedido} está pronta para aprovação: {portal}'],['msg_order_ready','Olá {cliente}! Seu pedido {pedido} está pronto.'],['msg_out_for_delivery','Olá {cliente}! Seu pedido {pedido} saiu para entrega.'],['msg_special_date','Olá {cliente}! Está chegando {ocasiao}. Quer que eu separe opções para {presenteado}?']
] as $x)$p->prepare("INSERT OR IGNORE INTO settings(key,value)VALUES(?,?)")->execute($x);
file_put_contents($lock,date('c'));
echo "<h2>FloraGestor Ultimate 1.0 instalado.</h2><p>Administrador: admin@floragestor.local / Admin@123</p><p>Gerente: gerente@floragestor.local / Gerente@123</p><p>Vendedor: vendedor@floragestor.local / Venda@123</p><p>Produção: producao@floragestor.local / Producao@123</p><p>Caixa: caixa@floragestor.local / Caixa@123</p><p>Entregador: entregador@floragestor.local / Entrega@123</p><p><a href='login.php'>Entrar</a></p>";
