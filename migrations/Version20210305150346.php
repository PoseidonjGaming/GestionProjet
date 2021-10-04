<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210305150346 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE famille_tache CHANGE id_projet_id id_projet_id INT NOT NULL');
        $this->addSql('ALTER TABLE planning ADD duree_est TIME NOT NULL');
        $this->addSql('ALTER TABLE projet CHANGE client_id client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE taches CHANGE duree_est duree_est VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE famille_tache CHANGE id_projet_id id_projet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE planning DROP duree_est');
        $this->addSql('ALTER TABLE projet CHANGE client_id client_id INT NOT NULL');
        $this->addSql('ALTER TABLE taches CHANGE duree_est duree_est TIME NOT NULL');
    }
}
