<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Un mandat de député s'identifie par son uid à l'Assemblée (`PM…`), et non
 * plus par (député, législature, date de début).
 *
 * Cette ancienne clé fusionnait les mandats successifs d'un même député dans
 * une même législature. Un député nommé au Gouvernement perd son mandat ; quand
 * il revient siéger, l'Assemblée lui en ouvre un second, avec la MÊME date de
 * début (celle de l'élection) et une prise de fonction plus tardive. L'upsert
 * de l'import écrasait alors le premier par le second : 120 couples
 * député-législature dans la source (27 en 17e : Borne, Rist, Armand,
 * Panosyan-Bouvet…), d'où une ancienneté et des périodes d'activité fausses.
 *
 * Passage des lignes existantes, sans perte ni doublon :
 * - la colonne `uid` arrive vide. Un index unique admet plusieurs NULL, sur
 *   MariaDB comme sur MySQL : les lignes actuelles restent valides ;
 * - `app:import:acteurs --tout` les « adopte » ensuite une à une
 *   (ImportActeursCommand::adopteMandatsSansUid) : avant l'upsert, chaque
 *   mandat de la source sans ligne à son uid reprend la ligne sans uid de même
 *   (député, législature, date de début), s'il en reste une. Le premier mandat
 *   d'un couple fusionné reprend la ligne existante, le second s'insère ;
 * - ce qui reste sans uid après ce réimport n'existe pas dans la source
 *   (amorçage TSV de `app:import:mandats`) et le reste.
 *
 * L'ancienne clé devient un index simple : elle sert toujours les lectures par
 * député et législature, et l'appariement de `app:import:mandats`.
 *
 * SQL commun à MariaDB 11.4 (production) et MySQL 8.0 (base locale).
 */
final class Version20261116140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'mandat.uid (uid du mandat à l\'Assemblée) remplace la clé (député, législature, date de début), qui fusionnait les mandats successifs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mandat ADD uid VARCHAR(30) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_mandat_uid ON mandat (uid)');
        // L'index simple d'abord : la clé étrangère sur depute_id a le sien
        // (IDX_1E53EFD551A07B9C), mais on ne laisse jamais la table sans index
        // sur la triple clé de lecture.
        $this->addSql('CREATE INDEX idx_mandat_depute_legislature_debut ON mandat (depute_id, legislature, date_debut)');
        $this->addSql('DROP INDEX uniq_depute_legislature_debut ON mandat');
    }

    public function down(Schema $schema): void
    {
        // Revenir à l'ancienne clé impose de refusionner : pour chaque
        // (député, législature, date de début), seule la ligne la plus récente
        // (la reprise de mandat, d'id le plus grand) survit — c'est l'état que
        // produisait l'ancien upsert.
        $this->addSql(<<<'SQL'
            DELETE m1 FROM mandat m1
            JOIN mandat m2 ON m2.depute_id = m1.depute_id
                          AND m2.legislature = m1.legislature
                          AND m2.date_debut = m1.date_debut
                          AND m2.id > m1.id
            SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_depute_legislature_debut ON mandat (depute_id, legislature, date_debut)');
        $this->addSql('DROP INDEX idx_mandat_depute_legislature_debut ON mandat');
        $this->addSql('DROP INDEX uniq_mandat_uid ON mandat');
        $this->addSql('ALTER TABLE mandat DROP uid');
    }
}
