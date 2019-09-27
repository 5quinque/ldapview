<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190927162355 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE people (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(127) NOT NULL, uid VARCHAR(127) NOT NULL, gecos VARCHAR(255) DEFAULT NULL, uid_number INT NOT NULL, gid_number INT NOT NULL, home_directory VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE people_netgroup (people_id INT NOT NULL, netgroup_id INT NOT NULL, INDEX IDX_336DDE383147C936 (people_id), INDEX IDX_336DDE3868D25711 (netgroup_id), PRIMARY KEY(people_id, netgroup_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE people_netgroup ADD CONSTRAINT FK_336DDE383147C936 FOREIGN KEY (people_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE people_netgroup ADD CONSTRAINT FK_336DDE3868D25711 FOREIGN KEY (netgroup_id) REFERENCES netgroup (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE people_netgroup DROP FOREIGN KEY FK_336DDE383147C936');
        $this->addSql('DROP TABLE people');
        $this->addSql('DROP TABLE people_netgroup');
    }
}
