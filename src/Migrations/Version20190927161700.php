<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190927161700 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE netgroup (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(127) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE netgroup_host (netgroup_id INT NOT NULL, host_id INT NOT NULL, INDEX IDX_9C8AFE68D25711 (netgroup_id), INDEX IDX_9C8AFE1FB8D185 (host_id), PRIMARY KEY(netgroup_id, host_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE host (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(127) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE netgroup_host ADD CONSTRAINT FK_9C8AFE68D25711 FOREIGN KEY (netgroup_id) REFERENCES netgroup (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE netgroup_host ADD CONSTRAINT FK_9C8AFE1FB8D185 FOREIGN KEY (host_id) REFERENCES host (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE netgroup_host DROP FOREIGN KEY FK_9C8AFE68D25711');
        $this->addSql('ALTER TABLE netgroup_host DROP FOREIGN KEY FK_9C8AFE1FB8D185');
        $this->addSql('DROP TABLE netgroup');
        $this->addSql('DROP TABLE netgroup_host');
        $this->addSql('DROP TABLE host');
    }
}
