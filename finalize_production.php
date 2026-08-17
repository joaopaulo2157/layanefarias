<?php
require __DIR__.'/config.php';auth();role(['admin']);$p=db();
$p->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS order_timeline(id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER,event_type TEXT,title TEXT,details TEXT,user_id INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS automation_rules(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,trigger_event TEXT,conditions_json TEXT,action_type TEXT,action_json TEXT,active INTEGER DEFAULT 1,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS automation_runs(id INTEGER PRIMARY KEY AUTOINCREMENT,rule_id INTEGER,entity_type TEXT,entity_id INTEGER,status TEXT,message TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS customer_consents(id INTEGER PRIMARY KEY AUTOINCREMENT,customer_id INTEGER,consent_type TEXT,granted INTEGER,source TEXT,recorded_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS system_backups(id INTEGER PRIMARY KEY AUTOINCREMENT,file_name TEXT,file_size INTEGER,status TEXT DEFAULT 'Criado',created_by INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS system_health_checks(id INTEGER PRIMARY KEY AUTOINCREMENT,check_name TEXT,status TEXT,details TEXT,checked_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS onboarding_state(id INTEGER PRIMARY KEY CHECK(id=1),completed INTEGER DEFAULT 0,current_step INTEGER DEFAULT 1,updated_at TEXT DEFAULT CURRENT_TIMESTAMP);
INSERT OR IGNORE INTO onboarding_state(id) VALUES(1);
CREATE TABLE IF NOT EXISTS delivery_time_slots(id INTEGER PRIMARY KEY AUTOINCREMENT,slot_date TEXT,start_time TEXT,end_time TEXT,max_deliveries INTEGER DEFAULT 5,active INTEGER DEFAULT 1);
CREATE TABLE IF NOT EXISTS reviews(id INTEGER PRIMARY KEY AUTOINCREMENT,order_id INTEGER,customer_id INTEGER,rating INTEGER,comment TEXT,public_token TEXT UNIQUE,status TEXT DEFAULT 'Novo',created_at TEXT DEFAULT CURRENT_TIMESTAMP);
SQL
);
foreach([['maintenance_mode','0'],['max_discount_percent','10'],['require_production_checklist','1'],['global_search_limit','30']] as $x)
$p->prepare("INSERT OR IGNORE INTO settings(key,value)VALUES(?,?)")->execute($x);
flash('success','Finalização para Produção aplicada.');go('today.php');
