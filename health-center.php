<?php require __DIR__.'/ui.php';role(['admin']);page_head('Diagnóstico do sistema');$checks=[
['PHP',PHP_VERSION,version_compare(PHP_VERSION,'8.0','>=')],
['PDO SQLite',extension_loaded('pdo_sqlite')?'Ativo':'Ausente',extension_loaded('pdo_sqlite')],
['Storage gravável',is_writable(__DIR__.'/storage')?'Sim':'Não',is_writable(__DIR__.'/storage')],
['Uploads gravável',is_writable(__DIR__.'/storage/uploads')?'Sim':'Não',is_writable(__DIR__.'/storage/uploads')],
['HTTPS',(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'Ativo':'Não detectado',(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')]
];?><div class="card"><table><tr><th>Verificação</th><th>Resultado</th><th>Status</th></tr><?php foreach($checks as $c):?><tr><td><?=$c[0]?></td><td><?=e($c[1])?></td><td><?=$c[2]?'✅':'⚠️'?></td></tr><?php endforeach;?></table></div><?php page_foot();?>