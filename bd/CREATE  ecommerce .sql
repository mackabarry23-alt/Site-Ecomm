USE tnphsznjxz_ecommerce;

DROP TABLE IF EXISTS commande_produit;
DROP TABLE IF EXISTS produit_categorie;
DROP TABLE IF EXISTS commande;
DROP TABLE IF EXISTS categorie;
DROP TABLE IF EXISTS produit;

CREATE TABLE produit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description_courte VARCHAR(255) DEFAULT NULL,
    description_longue TEXT DEFAULT NULL,
    prix_ht DECIMAL(10,2) NOT NULL,
    date_enregistrement DATETIME DEFAULT CURRENT_TIMESTAMP,
    disponible TINYINT(1) NOT NULL DEFAULT 1,
    stock INT NOT NULL DEFAULT 0,
    priorite INT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE produit_categorie (
    produit_id INT NOT NULL,
    categorie_id INT NOT NULL,
    PRIMARY KEY (produit_id, categorie_id),
    CONSTRAINT fk_produit_categorie_produit
        FOREIGN KEY (produit_id) REFERENCES produit(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_produit_categorie_categorie
        FOREIGN KEY (categorie_id) REFERENCES categorie(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_ht DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_tvac DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    adresse_livraison TEXT NOT NULL,
    email VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE commande_produit (
    commande_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    PRIMARY KEY (commande_id, produit_id),
    CONSTRAINT fk_commande_produit_commande
        FOREIGN KEY (commande_id) REFERENCES commande(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_commande_produit_produit
        FOREIGN KEY (produit_id) REFERENCES produit(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO produit (nom, description_courte, description_longue, prix_ht, date_enregistrement, stock, priorite)
VALUES
('Bougie Vanille - Petite',
 'Bougie parfum vanille 100g',
 'Bougie parfumée senteur vanille, cire naturelle, durée 20h.',
 9.90, '2026-02-19 00:00:00', 40, 1),

('Bougie Vanille - Grande',
 'Bougie parfum vanille 300g',
 'Bougie parfumée senteur vanille, cire naturelle, durée 60h.',
 19.90, '2026-02-19 00:00:00', 25, 1),

('Bougie Lavande - Petite',
 'Bougie parfum lavande 100g',
 'Bougie parfumée lavande relaxante, cire naturelle, durée 20h.',
 10.90, '2026-02-19 00:00:00', 35, 2),

('Bougie Lavande - Grande',
 'Bougie parfum lavande 300g',
 'Bougie parfumée lavande relaxante, cire naturelle, durée 60h.',
 20.90, '2026-02-19 00:00:00', 20, 2),

('Bougie Bois de Santal - Moyenne',
 'Bougie parfum bois de santal 200g',
 'Bougie parfumée bois de santal, ambiance chaleureuse, durée 40h.',
 15.90, '2026-02-19 00:00:00', 30, 3),

('Bougie Fleur de Coton - Moyenne',
 'Bougie parfum fleur de coton 200g',
 'Bougie parfumée douce et fraîche, cire végétale, durée 40h.',
 14.90, '2026-02-19 00:00:00', 30, 3);