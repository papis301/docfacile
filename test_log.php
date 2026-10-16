<?php
error_reporting(E_ALL);
ini_set('log_errors', '1');

$log = '/home/pisco/admin/logs/php/php.log';

$result = error_log(
    'TEST DOCSFACILE ' . date('Y-m-d H:i:s')
);

echo '<pre>';
echo 'PHP : ' . PHP_VERSION . "\n";
echo 'ZipArchive : ' .
    (class_exists('ZipArchive') ? 'OK' : 'ABSENTE') . "\n";
echo 'error_log configuré : ' .
    htmlspecialchars((string) ini_get('error_log')) . "\n";
echo 'Écriture du message : ' .
    ($result ? 'envoyé' : 'échec') . "\n";
echo 'Fichier de log accessible : ' .
    (is_readable($log) ? 'oui' : 'non') . "\n";
echo '</pre>';