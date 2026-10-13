<?php require 'config.php';
$list = db()->query('SELECT * FROM templates ORDER BY is_premium, id')->fetchAll();
if (FREE_MODE) foreach ($list as &$t0) $t0['is_premium'] = 0; unset($t0);
head('Modèles de CV Word gratuits'); ?>
<section class="hero text-center"><div class="container">
<h1 class="h2 fw-bold">Créez votre CV en 5 minutes</h1>
<p class="mb-0"><?= FREE_MODE ? '15 modèles Word gratuits, prêts à remplir.' : '15 modèles Word prêts à remplir. 5 gratuits, 10 premium à '.PRICE.'.' ?></p></div></section>
<main class="container mt-4"><div class="row g-3">
<?php foreach ($list as $t): ?>
<div class="col-6 col-md-4 col-lg-3"><div class="card h-100 shadow-sm">
 <div class="cv-thumb">
  <div class="bar w-75" style="background:#<?= e($t['color']) ?>"></div>
  <div class="l w-50"></div><div class="bar w-100" style="background:#<?= e($t['color']) ?>;opacity:.5;height:6px"></div>
  <div class="l"></div><div class="l w-75"></div><div class="l"></div>
  <div class="bar w-100" style="background:#<?= e($t['color']) ?>;opacity:.5;height:6px"></div><div class="l w-75"></div><div class="l"></div>
  <span class="badge badge-prem <?= $t['is_premium'] ? 'bg-warning text-dark' : 'bg-success' ?>"><?= $t['is_premium'] ? 'Premium' : 'Gratuit' ?></span>
 </div>
 <div class="card-body p-2 d-flex flex-column">
  <h2 class="h6 mb-1"><?= e($t['name']) ?></h2>
  <p class="small text-muted mb-2"><?= e($t['description']) ?></p>
  <a href="download.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-primary mt-auto">Télécharger .docx</a>
 </div></div></div>
<?php endforeach; ?>
</div></main>
<section class="container mt-5"><h2 class="h6">Modèles de CV par pays et par profil</h2><p class="small">
<?php foreach ((require 'pages.php') as $s => $pg): ?><a class="me-2 d-inline-block" href="cv/<?= e($s) ?>"><?= e($pg[0]) ?></a><?php endforeach; ?></p></section>
<?php foot();
