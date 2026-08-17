<?php
require __DIR__.'/config.php'; auth(); role(['admin']); $p=db();
$sql=<<<'SQL'
CREATE TABLE IF NOT EXISTS app_migrations(version TEXT PRIMARY KEY, applied_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS permissions(id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE, label TEXT);
CREATE TABLE IF NOT EXISTS role_permissions(role TEXT, permission_code TEXT, allowed INTEGER DEFAULT 1, PRIMARY KEY(role,permission_code));
CREATE TABLE IF NOT EXISTS notifications(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,type TEXT,title TEXT,message TEXT,url TEXT,is_read INTEGER DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS production_capacity(id INTEGER PRIMARY KEY AUTOINCREMENT,work_date TEXT UNIQUE,max_orders INTEGER DEFAULT 10,max_minutes INTEGER DEFAULT 480,notes TEXT);
CREATE TABLE IF NOT EXISTS production_checklists(id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER,item TEXT,required INTEGER DEFAULT 1,done INTEGER DEFAULT 0,done_by INTEGER,done_at TEXT);
CREATE TABLE IF NOT EXISTS order_media(id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER,type TEXT,file_path TEXT,caption TEXT,created_by INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS inventory_lots(id INTEGER PRIMARY KEY AUTOINCREMENT,product_id INTEGER,lot_code TEXT,qty REAL DEFAULT 0,unit_cost REAL DEFAULT 0,received_at TEXT,expires_at TEXT,supplier_id INTEGER,status TEXT DEFAULT 'Ativo');
CREATE TABLE IF NOT EXISTS stock_reservations(id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER,product_id INTEGER,qty REAL,reserved_until TEXT,status TEXT DEFAULT 'Reservado',created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS inventory_counts(id INTEGER PRIMARY KEY AUTOINCREMENT,reference TEXT,status TEXT DEFAULT 'Aberto',started_at TEXT DEFAULT CURRENT_TIMESTAMP,finished_at TEXT,created_by INTEGER);
CREATE TABLE IF NOT EXISTS inventory_count_items(id INTEGER PRIMARY KEY AUTOINCREMENT,count_id INTEGER,product_id INTEGER,system_qty REAL,counted_qty REAL,difference REAL);
CREATE TABLE IF NOT EXISTS purchase_orders(id INTEGER PRIMARY KEY AUTOINCREMENT,number TEXT UNIQUE,supplier_id INTEGER,status TEXT DEFAULT 'Rascunho',expected_at TEXT,subtotal REAL DEFAULT 0,total REAL DEFAULT 0,notes TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS purchase_order_items(id INTEGER PRIMARY KEY AUTOINCREMENT,purchase_order_id INTEGER,product_id INTEGER,description TEXT,qty REAL,unit_cost REAL,received_qty REAL DEFAULT 0);
CREATE TABLE IF NOT EXISTS accounts_payable(id INTEGER PRIMARY KEY AUTOINCREMENT,description TEXT,supplier_id INTEGER,purchase_order_id INTEGER,category TEXT,cost_center TEXT,amount REAL,due_date TEXT,paid_at TEXT,status TEXT DEFAULT 'Pendente',recurring INTEGER DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS payment_transactions(id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER,provider TEXT,method TEXT,installments INTEGER DEFAULT 1,gross_amount REAL,fee_amount REAL DEFAULT 0,net_amount REAL,status TEXT DEFAULT 'Pendente',external_id TEXT,paid_at TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS gift_cards(id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT UNIQUE,initial_balance REAL,current_balance REAL,expires_at TEXT,status TEXT DEFAULT 'Ativo',customer_id INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS cashback_ledger(id INTEGER PRIMARY KEY AUTOINCREMENT,customer_id INTEGER,order_id INTEGER,type TEXT,amount REAL,description TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS goals(id INTEGER PRIMARY KEY AUTOINCREMENT,period TEXT,scope TEXT,scope_id INTEGER,metric TEXT,target REAL,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS events(id INTEGER PRIMARY KEY AUTOINCREMENT,title TEXT,event_type TEXT,customer_id INTEGER,start_at TEXT,end_at TEXT,budget REAL,status TEXT DEFAULT 'Planejamento',notes TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS event_tasks(id INTEGER PRIMARY KEY AUTOINCREMENT,event_id INTEGER,title TEXT,due_at TEXT,assigned_to INTEGER,status TEXT DEFAULT 'Pendente');
CREATE TABLE IF NOT EXISTS delivery_proofs(id INTEGER PRIMARY KEY AUTOINCREMENT,delivery_id INTEGER,proof_type TEXT,file_path TEXT,pin_code TEXT,recipient_name TEXT,latitude REAL,longitude REAL,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS audit_log(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,action TEXT,entity TEXT,entity_id INTEGER,before_json TEXT,after_json TEXT,ip TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS intelligence_alerts(id INTEGER PRIMARY KEY AUTOINCREMENT,alert_type TEXT,severity TEXT,title TEXT,message TEXT,entity_type TEXT,entity_id INTEGER,status TEXT DEFAULT 'Novo',created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS cost_centers(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT UNIQUE,active INTEGER DEFAULT 1);
CREATE TABLE IF NOT EXISTS sales_channels(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT UNIQUE,active INTEGER DEFAULT 1);
CREATE TABLE IF NOT EXISTS presented_people(id INTEGER PRIMARY KEY AUTOINCREMENT,customer_id INTEGER,name TEXT,relationship TEXT,birthday TEXT,preferences TEXT,restrictions TEXT,notes TEXT);
INSERT OR IGNORE INTO cost_centers(name) VALUES('Loja'),('Produção'),('Entrega'),('Marketing'),('Administrativo');
INSERT OR IGNORE INTO sales_channels(name) VALUES('Loja'),('WhatsApp'),('Instagram'),('Catálogo Online'),('Corporativo'),('Indicação');
INSERT OR IGNORE INTO app_migrations(version) VALUES('ultimate-1.0');
SQL;
$p->exec($sql);
foreach([
 ['min_margin','30'],['default_daily_capacity','12'],['cashback_percent','2'],['reservation_days','7'],
 ['auto_backup','1'],['require_final_photo','0'],['delivery_pin','1'],['currency','BRL']
] as $x){$p->prepare("INSERT OR IGNORE INTO settings(key,value)VALUES(?,?)")->execute($x);}
flash('success','FloraGestor Ultimate 1.0 atualizado com sucesso.');go('index.php');
