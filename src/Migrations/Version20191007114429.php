<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191007114429 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE netgroup_netgroup (netgroup_source INT NOT NULL, netgroup_target INT NOT NULL, INDEX IDX_AE01766EFD74C8EB (netgroup_source), INDEX IDX_AE01766EE4919864 (netgroup_target), PRIMARY KEY(netgroup_source, netgroup_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE netgroup_netgroup ADD CONSTRAINT FK_AE01766EFD74C8EB FOREIGN KEY (netgroup_source) REFERENCES netgroup (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE netgroup_netgroup ADD CONSTRAINT FK_AE01766EE4919864 FOREIGN KEY (netgroup_target) REFERENCES netgroup (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE netgroup_netgroup');
    }
}
