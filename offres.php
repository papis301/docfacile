<?php require 'config.php';
$type = in_array($_GET['type'] ?? '', ['emploi', 'stage'], true) ? $_GET['type'] : '';
$per = 12; $page = max(1, (int)($_GET['page'] ?? 1));
$where = '(expire_le IS NULL OR expire_le >= CURDATE())'; $params = [];
if ($type) { $where .= ' AND type=?'; $params[] = $type; }
$c = db()->prepare("SELECT COUNT(*) FROM offres WHERE $where"); $c->execute($params);
$pages = max(1, (int)ceil($c->fetchColumn() / $per)); $page = min($page, $pages);
$st = db()->prepare("SELECT * FROM offres WHERE $where ORDER BY created_at DESC LIMIT $per OFFSET ".(($page - 1) * $per));
$st->execute($params); $list = $st->fetchAll();
$q = $type ? 'type='.$type.'&' : '';
head('Offres d\'emploi et de stage', '<meta name="description" content="Offres d\'emploi et de stage en Afrique de l\'Ouest, mises à jour régulièrement. Consultez-les et postulez avec votre CV Word.">'); ?>
<section class="hero text-center"><div class="container"><h1 class="h2 fw-bold">Offres d'emploi et de stage</h1>
<p class="mb-0">Trouvez une opportunité, puis préparez votre CV avec nos modèles Word.</p></div></section>
<main class="container mt-4">
<ul class="nav nav-pills mb-3">
<?php foreach (['' => 'Toutes', 'emploi' => 'Emplois', 'stage' => 'Stages'] as $k => $l): ?>
<li class="nav-item"><a class="nav-link <?= $type === $k ? 'active' : '' ?>" href="offres.php<?= $k ? '?type='.$k : '' ?>"><?= $l ?></a></li><?php endforeach; ?></ul>
<?php if (!$list): ?><div class="alert alert-light border">Aucune offre pour le moment. Revenez bientôt !</div><?php endif; ?>
<div class="row g-3"><?php foreach ($list as $o):
  $img = 'uploads/offres/'.e($o['image']); $alt = e($o['titre'] ?: 'Offre d\'emploi'); ?>
<div class="col-6 col-md-4 col-lg-3"><div class="card h-100 shadow-sm">
<?php if ($o['lien']): ?><a href="<?= e($o['lien']) ?>" target="_blank" rel="noopener noreferrer nofollow"><img src="<?= $img ?>" class="card-img-top" alt="<?= $alt ?>" loading="lazy"></a>
<?php else: ?><a href="<?= $img ?>" target="_blank"><img src="<?= $img ?>" class="card-img-top" alt="<?= $alt ?>" loading="lazy"></a><?php endif; ?>
<div class="card-body p-2"><span class="badge <?= $o['type'] === 'stage' ? 'bg-info text-dark' : 'bg-primary' ?>"><?= $o['type'] === 'stage' ? 'Stage' : 'Emploi' ?></span>
<?php if ($o['titre']): ?><div class="small fw-bold mt-1"><?= e($o['titre']) ?></div><?php endif; ?>
<?php if ($o['expire_le']): ?><div class="small text-muted">Jusqu'au <?= e(date('d/m/Y', strtotime($o['expire_le']))) ?></div><?php endif; ?>
<?php if ($o['lien']): ?><a class="btn btn-sm btn-success w-100 mt-2" href="<?= e($o['lien']) ?>" target="_blank" rel="noopener noreferrer nofollow">Voir l'offre</a><?php endif; ?></div>
</div></div><?php endforeach; ?></div>
<?php if ($pages > 1): ?><nav class="d-flex justify-content-between mt-4">
<?php if ($page > 1): ?><a class="btn btn-outline-secondary btn-sm" href="offres.php?<?= $q ?>page=<?= $page - 1 ?>">← Précédent</a><?php else: ?><span></span><?php endif; ?>
<span class="small align-self-center">Page <?= $page ?>/<?= $pages ?></span>
<?php if ($page < $pages): ?><a class="btn btn-outline-secondary btn-sm" href="offres.php?<?= $q ?>page=<?= $page + 1 ?>">Suivant →</a><?php else: ?><span></span><?php endif; ?></nav><?php endif; ?>
<div class="alert alert-info mt-4">Une offre vous intéresse ? <a href="index.php">Préparez votre CV avec nos modèles Word</a>.</div>
</main>
<?php foot();