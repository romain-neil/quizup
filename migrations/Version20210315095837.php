<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210315095837 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE record (id INT AUTO_INCREMENT NOT NULL, duration INT NOT NULL, participation_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classe DROP FOREIGN KEY FK_8F87BF9686FA30A2');
        $this->addSql('DROP INDEX IDX_8F87BF9686FA30A2 ON classe');
        $this->addSql('ALTER TABLE classe CHANGE eple_id lycee_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE classe ADD CONSTRAINT FK_8F87BF96D1DC61BF FOREIGN KEY (lycee_id) REFERENCES lycee (id)');
        $this->addSql('CREATE INDEX IDX_8F87BF96D1DC61BF ON classe (lycee_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE record');
        $this->addSql('ALTER TABLE classe DROP FOREIGN KEY FK_8F87BF96D1DC61BF');
        $this->addSql('DROP INDEX IDX_8F87BF96D1DC61BF ON classe');
        $this->addSql('ALTER TABLE classe CHANGE lycee_id eple_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE classe ADD CONSTRAINT FK_8F87BF9686FA30A2 FOREIGN KEY (eple_id) REFERENCES lycee (id)');
        $this->addSql('CREATE INDEX IDX_8F87BF9686FA30A2 ON classe (eple_id)');
    }
}
