<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190927174434 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE people_netgroup DROP FOREIGN KEY FK_336DDE3868D25711');
        $this->addSql('ALTER TABLE people_netgroup DROP FOREIGN KEY FK_336DDE383147C936');
        $this->addSql('ALTER TABLE people_netgroup ADD CONSTRAINT FK_336DDE3868D25711 FOREIGN KEY (netgroup_id) REFERENCES netgroup (id)');
        $this->addSql('ALTER TABLE people_netgroup ADD CONSTRAINT FK_336DDE383147C936 FOREIGN KEY (people_id) REFERENCES people (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE people_netgroup DROP FOREIGN KEY FK_336DDE383147C936');
        $this->addSql('ALTER TABLE people_netgroup DROP FOREIGN KEY FK_336DDE3868D25711');
        $this->addSql('ALTER TABLE people_netgroup ADD CONSTRAINT FK_336DDE383147C936 FOREIGN KEY (people_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE people_netgroup ADD CONSTRAINT FK_336DDE3868D25711 FOREIGN KEY (netgroup_id) REFERENCES netgroup (id) ON DELETE CASCADE');
    }
}
