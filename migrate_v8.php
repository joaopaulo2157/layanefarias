<?php
require __DIR__.'/config.php'; auth(); role(['admin']); $p=db();
$schema=<<<'SQL'
CREATE TABLE IF NOT EXISTS public_catalog_settings(
 id INTEGER PRIMARY KEY CHECK(id=1),
 enabled INTEGER DEFAULT 1,
 hero_title TEXT DEFAULT 'Presentes feitos com carinho',
 hero_subtitle TEXT DEFAULT 'Escolha, personalize e encomende online.',
 primary_color TEXT DEFAULT '#b45a64',
 delivery_notice TEXT DEFAULT '',
 pix_key TEXT DEFAULT '',
 pix_receiver TEXT DEFAULT '',
 order_confirmation_text TEXT DEFAULT 'Recebemos seu pedido e entraremos em contato para confirmar.'
);
INSERT OR IGNORE INTO public_catalog_settings(id) VALUES(1);

CREATE TABLE IF NOT EXISTS online_orders(
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 public_number TEXT UNIQUE,
 customer_name TEXT NOT NULL,
 whatsapp TEXT NOT NULL,
 email TEXT,
 occasion TEXT,
 delivery_type TEXT,
 delivery_date TEXT,
 delivery_time TEXT,
 recipient_name TEXT,
 recipient_phone TEXT,
 address TEXT,
 neighborhood TEXT,
 notes TEXT,
 subtotal REAL DEFAULT 0,
 delivery_fee REAL DEFAULT 0,
 discount REAL DEFAULT 0,
 total REAL DEFAULT 0,
 payment_method TEXT DEFAULT 'Pix',
 payment_status TEXT DEFAULT 'Pendente',
 status TEXT DEFAULT 'Novo',
 source TEXT DEFAULT 'Catálogo online',
 converted_order_id INTEGER,
 token TEXT UNIQUE,
 created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS online_order_items(
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 online_order_id INTEGER NOT NULL,
 product_id INTEGER,
 product_name TEXT,
 qty REAL,
 unit_price REAL,
 customization TEXT,
 personalized_text TEXT,
 FOREIGN KEY(online_order_id) REFERENCES online_orders(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS delivery_zones(
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 neighborhood TEXT NOT NULL UNIQUE,
 fee REAL DEFAULT 0,
 min_order REAL DEFAULT 0,
 active INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS leads(
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 name TEXT NOT NULL,
 whatsapp TEXT,
 source TEXT,
 interest TEXT,
 stage TEXT DEFAULT 'Novo lead',
 estimated_value REAL DEFAULT 0,
 next_followup TEXT,
 notes TEXT,
 customer_id INTEGER,
 quote_id INTEGER,
 created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS followups(
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 lead_id INTEGER,
 customer_id INTEGER,
 quote_id INTEGER,
 channel TEXT DEFAULT 'WhatsApp',
 scheduled_for TEXT,
 status TEXT DEFAULT 'Pendente',
 notes TEXT,
 created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS payment_links(
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 order_id INTEGER,
 online_order_id INTEGER,
 provider TEXT DEFAULT 'manual',
 external_id TEXT,
 payment_url TEXT,
 qr_payload TEXT,
 amount REAL,
 status TEXT DEFAULT 'Pendente',
 expires_at TEXT,
 created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS security_events(
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 user_id INTEGER,
 event_type TEXT,
 ip TEXT,
 user_agent TEXT,
 details TEXT,
 created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
SQL;
$p->exec($schema);
foreach([
 ['catalog_enabled','1'],['catalog_title','Presentes feitos com carinho'],['catalog_subtitle','Escolha, personalize e encomende online.'],
 ['inactive_days','90'],['quote_followup_days','2'],['online_order_whatsapp','']
] as $x){$p->prepare("INSERT OR IGNORE INTO settings(key,value)VALUES(?,?)")->execute($x);}
flash('success','Migração V8 concluída.'); go('index.php');
