<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191114120219 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sudo_netgroup (sudo_id INT NOT NULL, netgroup_id INT NOT NULL, INDEX IDX_FE17B9088DF4A340 (sudo_id), INDEX IDX_FE17B90868D25711 (netgroup_id), PRIMARY KEY(sudo_id, netgroup_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sudo_netgroup ADD CONSTRAINT FK_FE17B9088DF4A340 FOREIGN KEY (sudo_id) REFERENCES sudo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sudo_netgroup ADD CONSTRAINT FK_FE17B90868D25711 FOREIGN KEY (netgroup_id) REFERENCES netgroup (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sudo_netgroup');
    }
}
