CREATE DATABASE IF NOT EXISTS cvmodeles CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cvmodeles;
CREATE TABLE templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255),
  color CHAR(6) NOT NULL,
  file VARCHAR(100) NOT NULL,
  is_premium TINYINT(1) DEFAULT 0,
  downloads INT DEFAULT 0
);
CREATE TABLE codes (
  code VARCHAR(20) PRIMARY KEY,
  used TINYINT(1) DEFAULT 0,
  used_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO templates (name,description,color,file,is_premium) VALUES
('Classique Bleu','Sobre et professionnel','1F4E79','modele-01.docx',0),
('Vert Moderne','Titres en bandeau','2E7D32','modele-02.docx',0),
('Rouge Élégant','Centré, police Georgia','B71C1C','modele-03.docx',0),
('Noir Académique','Idéal étudiants et stages','000000','modele-04.docx',0),
('Violet Créatif','Pour métiers créatifs','6A1B9A','modele-05.docx',0),
('Turquoise Clair','Centré et aéré','00695C','modele-06.docx',1),
('Orange Dynamique','Commerce et vente','E65100','modele-07.docx',1),
('Ardoise Executive','Cadres et managers','263238','modele-08.docx',1),
('Bleu Ciel','Frais et lisible','0277BD','modele-09.docx',1),
('Bordeaux Premium','Administration, droit','880E4F','modele-10.docx',1),
('Gris Minimal','Minimaliste','37474F','modele-11.docx',1),
('Jaune Solaire','Marketing, communication','F9A825','modele-12.docx',1),
('Vert Forêt','Agriculture, ONG','1B5E20','modele-13.docx',1),
('Indigo Tech','Informatique, ingénierie','4527A0','modele-14.docx',1),
('Noir Bandeau','Fort contraste','212121','modele-15.docx',1);
-- Codes d'accès à vendre (Wave / Orange Money) : un code = un téléchargement premium
INSERT INTO codes (code) VALUES ('CV-A1B2C3'),('CV-D4E5F6'),('CV-G7H8I9');
CREATE TABLE conversions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (ip, created_at)
);
