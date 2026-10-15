<?php
const DB_HOST = 'localhost';
const DB_NAME = 'cvmodeles';
const DB_USER = 'root';
const DB_PASS = '';
const SITE_NAME = 'CVModèles';
const SITE_URL = 'https://www.votre-domaine.com'; // sans slash final
const PRICE = '500 FCFA';
const FREE_MODE = true; // true = les 15 modèles sont gratuits ; false = les modèles premium demandent un code
const WHATSAPP = '221770000000'; // votre numéro, format international sans +
const ADMIN_PASSWORD_HASH = '$2y$10$pxsRraWtbKaJukSZfhR8BuuT4u3Vk55EJC6gDVbXGb.5r4C115LKy';
const CONVERTER_ENABLED = false; // true = affiche et active le convertisseur PDF → Word
const PYTHON_BIN = 'python3';   // chemin de Python (avec pdf2docx installé)
const MAX_MB = 5;               // taille maximale du PDF
const DAILY_LIMIT = 3;          // conversions gratuites par jour et par adresse IP
function db(): PDO {
    static $pdo;
    return $pdo ??= new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function head($title, $extra = "") { ?><!doctype html>
<html lang="fr"><head><meta charset="utf-8">
<base href="<?= rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/") ?>/">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | <?= SITE_NAME ?></title>
<?php if (!$extra): ?><meta name="description" content="Modèles de CV Word gratuits pour étudiants et professionnels d'Afrique de l'Ouest. Téléchargez, modifiez, postulez."><?php endif; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" href="assets/icon.svg" type="image/svg+xml">
<link href="assets/style.css" rel="stylesheet"><?= $extra ?></head><body>
<nav class="navbar navbar-dark bg-dark"><div class="container">
<a class="navbar-brand" href="index.php"><img src="assets/logo-blanc.svg" alt="<?= SITE_NAME ?>" height="34"></a>
<?php if (CONVERTER_ENABLED): ?><a class="btn btn-sm btn-outline-light" href="convert.php">PDF → Word</a><?php endif; ?></div></nav>
<?php }
function foot() { ?>
<footer class="text-center text-muted small py-4 mt-5 border-top">© <?= date('Y') ?> <?= SITE_NAME ?> – Afrique de l'Ouest</footer>
</body></html><?php }