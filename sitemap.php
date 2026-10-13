<?php require 'config.php'; $pages = require 'pages.php';
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach (CONVERTER_ENABLED ? ['/', '/convert.php'] : ['/'] as $u) echo '<url><loc>'.SITE_URL.$u.'</loc></url>';
foreach ($pages as $s => $p) echo '<url><loc>'.SITE_URL.'/cv/'.$s.'</loc></url>';
echo '</urlset>';