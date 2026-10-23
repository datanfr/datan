<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Retrait des commentaires de type `(DC2Type:…)`, que DBAL 4 n'écrit plus.
 *
 * DBAL 3 marquait d'un commentaire les colonnes dont le type SQL ne suffisait
 * pas à retrouver le type Doctrine (`datetime_immutable`, `json`…). DBAL 4,
 * arrivé avec Symfony 8, s'en passe et voit dans chaque commentaire restant un
 * écart de schéma — `doctrine:schema:validate`, contrôlé par la CI, échoue.
 *
 * Seuls les commentaires changent : types, nullabilité et données restent
 * identiques, et MariaDB applique ce changement sans recopier la table, `vote`
 * comprise. `utilisateur.roles` passe de LONGTEXT + `json_valid` à JSON, qui en
 * est l'alias exact sous MariaDB.
 */
final class Version20261023085810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DBAL 4 : retrait des commentaires DC2Type.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE amendement CHANGE date_sort date_sort DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE article CHANGE cree_le cree_le DATETIME DEFAULT NULL, CHANGE modifie_le modifie_le DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE campagne CHANGE date_debut date_debut DATE NOT NULL, CHANGE date_fin date_fin DATE NOT NULL, CHANGE cree_le cree_le DATETIME DEFAULT NULL, CHANGE modifie_le modifie_le DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE classement CHANGE calcule_le calcule_le DATE NOT NULL');
        $this->addSql('ALTER TABLE commission CHANGE date_fin date_fin DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE compte_rendu CHANGE date_seance date_seance DATETIME DEFAULT NULL, CHANGE cree_le cree_le DATETIME DEFAULT NULL, CHANGE modifie_le modifie_le DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_depute CHANGE mis_a_jour_le mis_a_jour_le DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE decryptage CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE modified_at modified_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_compte_depute CHANGE demandee_le demandee_le DATETIME NOT NULL, CHANGE traitee_le traitee_le DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE depute CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE election CHANGE date_tour1 date_tour1 DATE DEFAULT NULL, CHANGE date_tour2 date_tour2 DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE explication CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE modified_at modified_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE faq_post CHANGE cree_le cree_le DATETIME DEFAULT NULL, CHANGE modifie_le modifie_le DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE fonction_commission CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE fonction_groupe CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE groupe CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE mandat CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL, CHANGE date_prise_fonction date_prise_fonction DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE mandat_organe CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE newsletter CHANGE inscrit_le inscrit_le DATETIME NOT NULL, CHANGE modifie_le modifie_le DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE organe CHANGE date_debut date_debut DATE DEFAULT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE parrainage CHANGE date_publication date_publication DATE DEFAULT NULL, CHANGE date_maj date_maj DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE parti CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE date_fin date_fin DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE partielle_legislative CHANGE date_scrutin date_scrutin DATE NOT NULL');
        $this->addSql('ALTER TABLE profil_social CHANGE date_naissance date_naissance DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE question_quiz CHANGE cree_le cree_le DATETIME DEFAULT NULL, CHANGE modifie_le modifie_le DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reinitialisation_mot_de_passe CHANGE cree_le cree_le DATETIME NOT NULL');
        $this->addSql('ALTER TABLE scrutin CHANGE date_scrutin date_scrutin DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL, CHANGE cree_le cree_le DATETIME NOT NULL');
        $this->addSql('ALTER TABLE vote CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE scrutin_date scrutin_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE amendement CHANGE date_sort date_sort DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE article CHANGE cree_le cree_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modifie_le modifie_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE campagne CHANGE date_debut date_debut DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_fin date_fin DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE cree_le cree_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modifie_le modifie_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE classement CHANGE calcule_le calcule_le DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE commission CHANGE date_fin date_fin DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE compte_rendu CHANGE date_seance date_seance DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE cree_le cree_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modifie_le modifie_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE contact_depute CHANGE mis_a_jour_le mis_a_jour_le DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE decryptage CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE demande_compte_depute CHANGE demandee_le demandee_le DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE traitee_le traitee_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE depute CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE election CHANGE date_tour1 date_tour1 DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_tour2 date_tour2 DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE explication CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE faq_post CHANGE cree_le cree_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modifie_le modifie_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE fonction_commission CHANGE date_debut date_debut DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_fin date_fin DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE fonction_groupe CHANGE date_debut date_debut DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_fin date_fin DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE groupe CHANGE date_debut date_debut DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_fin date_fin DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE mandat CHANGE date_debut date_debut DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_prise_fonction date_prise_fonction DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_fin date_fin DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE mandat_organe CHANGE date_debut date_debut DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_fin date_fin DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE newsletter CHANGE inscrit_le inscrit_le DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modifie_le modifie_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE organe CHANGE date_debut date_debut DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_fin date_fin DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE parrainage CHANGE date_publication date_publication DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE date_maj date_maj DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE parti CHANGE date_fin date_fin DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE partielle_legislative CHANGE date_scrutin date_scrutin DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE profil_social CHANGE date_naissance date_naissance DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE question_quiz CHANGE cree_le cree_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modifie_le modifie_le DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE reinitialisation_mot_de_passe CHANGE cree_le cree_le DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE scrutin CHANGE date_scrutin date_scrutin DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE cree_le cree_le DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE vote CHANGE scrutin_date scrutin_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
