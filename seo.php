<?php require 'config.php';
$pages = require 'pages.php';
$slug = $_GET['slug'] ?? '';
if (!isset($pages[$slug])) { http_response_code(404); head('Page introuvable'); echo '<main class="container mt-5"><h1 class="h4">Page introuvable</h1><a href="index.php">Voir les modèles</a></main>'; foot(); exit; }
[$h1, $title, $intro, $tips] = $pages[$slug];
$url = SITE_URL.'/cv/'.$slug;
$desc = mb_substr($intro, 0, 155);
$tpl = db()->query('SELECT * FROM templates ORDER BY is_premium, id LIMIT 8')->fetchAll();
if (FREE_MODE) foreach ($tpl as &$t0) $t0['is_premium'] = 0; unset($t0);
$ld = json_encode(['@context'=>'https://schema.org','@type'=>'WebPage','name'=>$title,'description'=>$desc,'url'=>$url], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
head($title, '<meta name="description" content="'.e($desc).'"><link rel="canonical" href="'.e($url).'"><script type="application/ld+json">'.$ld.'</script>');
?>
<section class="hero"><div class="container"><h1 class="h2 fw-bold"><?= e($h1) ?></h1><p class="mb-0"><?= e($intro) ?></p></div></section>
<main class="container mt-4">
<h2 class="h5">3 conseils pour réussir votre CV</h2>
<ol class="mb-4"><?php foreach ($tips as $t): ?><li class="mb-1"><?= e($t) ?></li><?php endforeach; ?></ol>
<h2 class="h5">Choisissez votre modèle Word</h2>
<div class="row g-3 mb-4"><?php foreach ($tpl as $t): ?>
<div class="col-6 col-md-3"><div class="card h-100 shadow-sm"><div class="cv-thumb"><div class="bar w-75" style="background:#<?= e($t['color']) ?>"></div><div class="l"></div><div class="l w-75"></div><div class="l"></div>
<span class="badge badge-prem <?= $t['is_premium'] ? 'bg-warning text-dark' : 'bg-success' ?>"><?= $t['is_premium'] ? 'Premium' : 'Gratuit' ?></span></div>
<div class="card-body p-2"><div class="small fw-bold mb-1"><?= e($t['name']) ?></div><a class="btn btn-sm btn-primary w-100" href="download.php?id=<?= (int)$t['id'] ?>">Télécharger</a></div></div></div>
<?php endforeach; ?></div>
<?php if (CONVERTER_ENABLED): ?><div class="alert alert-info">Vous avez déjà un CV en PDF ? <a href="convert.php">Convertissez-le en Word gratuitement</a> pour le modifier.</div><?php endif; ?>
<h2 class="h6 mt-4">Autres pages utiles</h2>
<p class="small"><?php foreach ($pages as $s => $p): if ($s !== $slug): ?><a class="me-2 d-inline-block" href="cv/<?= e($s) ?>"><?= e($p[0]) ?></a><?php endif; endforeach; ?></p>
</main>
<?php foot();