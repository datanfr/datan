<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Chaîne IA des résumés d'amendements par lots, portée des scripts
 * `generate_amendements_ia_{submit,process}_batch.php` du legacy.
 *
 * - `amendement.justification_ia` et `amendement.categorie_ia` : les deux
 *   champs du prompt de la rédaction que le port ne gardait pas
 *   (`amendements_ia.justification_ia`, `.categorie`). La justification
 *   s'affiche sous le résumé sur la page du vote, comme sur datan.fr.
 * - `tag` + `amendement_tag` : le vocabulaire des mots-clés, à la place de la
 *   colonne libre `amendements_tags.tag`. Un slug unique fait se rejoindre les
 *   variantes de casse, d'accent et de pluriel ; `synonyme_de_id` mémorise les
 *   rapprochements décidés par le modèle (cf. `App\Entity\Tag`).
 * - `lot_ia` + `lot_ia_element` : le suivi des lots soumis à l'API
 *   (`anthropic_batches`, `anthropic_batch_items`).
 *
 * Écrite à la main, dans la forme que Doctrine produit pour ces entités (noms
 * d'index et de clés compris) : sans quoi `doctrine:migrations:diff` verrait
 * des écarts fantômes.
 */
final class Version20261116110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Résumés IA par lots : justification et catégorie d\'amendement, vocabulaire des tags, suivi des lots.';
    }

    public function up(Schema $schema): void
    {
        // `COLLATE` en toutes lettres sur chaque table : avec le seul
        // `DEFAULT CHARACTER SET utf8mb4`, MariaDB 11.4 leur donne
        // `utf8mb4_general_ci` au lieu de la collation de la base, et la
        // première comparaison d'un slug avec une colonne `unicode_ci`
        // lèverait « Illegal mix of collations ».
        $this->addSql('ALTER TABLE amendement ADD justification_ia LONGTEXT DEFAULT NULL, ADD categorie_ia VARCHAR(60) DEFAULT NULL');

        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, cree_le DATETIME NOT NULL, synonyme_de_id INT DEFAULT NULL, UNIQUE INDEX uniq_tag_slug (slug), INDEX IDX_389B7837D47A304 (synonyme_de_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B7837D47A304 FOREIGN KEY (synonyme_de_id) REFERENCES tag (id) ON DELETE SET NULL');

        $this->addSql('CREATE TABLE amendement_tag (amendement_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_3677E417F4C20978 (amendement_id), INDEX IDX_3677E417BAD26311 (tag_id), PRIMARY KEY (amendement_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE amendement_tag ADD CONSTRAINT FK_3677E417F4C20978 FOREIGN KEY (amendement_id) REFERENCES amendement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE amendement_tag ADD CONSTRAINT FK_3677E417BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE lot_ia (id INT AUTO_INCREMENT NOT NULL, identifiant VARCHAR(100) NOT NULL, modele VARCHAR(60) NOT NULL, statut VARCHAR(20) NOT NULL, nombre_requetes INT NOT NULL, soumis_le DATETIME NOT NULL, termine_le DATETIME DEFAULT NULL, jetons_entree INT DEFAULT NULL, jetons_sortie INT DEFAULT NULL, jetons_cache_lus INT DEFAULT NULL, jetons_cache_ecrits INT DEFAULT NULL, erreur LONGTEXT DEFAULT NULL, UNIQUE INDEX uniq_lot_ia_identifiant (identifiant), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE lot_ia_element (id INT AUTO_INCREMENT NOT NULL, custom_id VARCHAR(64) NOT NULL, statut VARCHAR(20) NOT NULL, reponse LONGTEXT DEFAULT NULL, erreur LONGTEXT DEFAULT NULL, lot_id INT NOT NULL, amendement_id INT NOT NULL, UNIQUE INDEX uniq_lot_ia_element (lot_id, custom_id), INDEX IDX_A4DA21B1A8CBA5F7 (lot_id), INDEX IDX_A4DA21B1F4C20978 (amendement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lot_ia_element ADD CONSTRAINT FK_A4DA21B1A8CBA5F7 FOREIGN KEY (lot_id) REFERENCES lot_ia (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lot_ia_element ADD CONSTRAINT FK_A4DA21B1F4C20978 FOREIGN KEY (amendement_id) REFERENCES amendement (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lot_ia_element');
        $this->addSql('DROP TABLE lot_ia');
        $this->addSql('DROP TABLE amendement_tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('ALTER TABLE amendement DROP justification_ia, DROP categorie_ia');
    }
}
