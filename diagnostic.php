<?php
// FICHIER TEMPORAIRE : à supprimer dès que le problème est trouvé
ini_set('display_errors', 1); error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');
echo 'PHP '.PHP_VERSION.(version_compare(PHP_VERSION, '7.4', '>=') ? " OK\n\n" : " TROP ANCIEN (7.4 minimum)\n\n");
$files = ['config.php','index.php','services.php','pages.php','contact.php','download.php','seo.php','admin.php','assets/style.css'];
foreach ($files as $f) echo (is_file(__DIR__."/$f") ? 'OK       ' : 'MANQUANT ').$f."\n";
echo "\n";
foreach (['config.php','services.php','pages.php','index.php','contact.php','admin.php'] as $f) {
    if (!is_file(__DIR__."/$f")) continue;
    try { token_get_all(file_get_contents(__DIR__."/$f"), TOKEN_PARSE); echo "Syntaxe OK       $f\n"; }
    catch (Throwable $e) { echo "ERREUR SYNTAXE  $f, ligne ".$e->getLine().' : '.$e->getMessage()."\n"; }
}
echo "\n";
try {
    require __DIR__.'/config.php'; echo "config.php charge : OK\n";
    foreach (['templates','codes','conversions','demandes'] as $t) {
        try { echo "Table $t : OK (".db()->query("SELECT COUNT(*) FROM $t")->fetchColumn()." lignes)\n"; }
        catch (Throwable $e) { echo "Table $t : ERREUR - ".$e->getMessage()."\n"; }
    }
} catch (Throwable $e) { echo 'ERREUR config.php : '.$e->getMessage()."\n"; }