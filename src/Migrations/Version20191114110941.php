<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191114110941 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sudo (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(127) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sudo_host (sudo_id INT NOT NULL, host_id INT NOT NULL, INDEX IDX_19EC35C88DF4A340 (sudo_id), INDEX IDX_19EC35C81FB8D185 (host_id), PRIMARY KEY(sudo_id, host_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sudo_people (sudo_id INT NOT NULL, people_id INT NOT NULL, INDEX IDX_582AFBA68DF4A340 (sudo_id), INDEX IDX_582AFBA63147C936 (people_id), PRIMARY KEY(sudo_id, people_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sudo_command (id INT AUTO_INCREMENT NOT NULL, sudo_group_id INT NOT NULL, command VARCHAR(511) NOT NULL, INDEX IDX_6302556542DF92E2 (sudo_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sudo_host ADD CONSTRAINT FK_19EC35C88DF4A340 FOREIGN KEY (sudo_id) REFERENCES sudo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sudo_host ADD CONSTRAINT FK_19EC35C81FB8D185 FOREIGN KEY (host_id) REFERENCES host (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sudo_people ADD CONSTRAINT FK_582AFBA68DF4A340 FOREIGN KEY (sudo_id) REFERENCES sudo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sudo_people ADD CONSTRAINT FK_582AFBA63147C936 FOREIGN KEY (people_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sudo_command ADD CONSTRAINT FK_6302556542DF92E2 FOREIGN KEY (sudo_group_id) REFERENCES sudo (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sudo_host DROP FOREIGN KEY FK_19EC35C88DF4A340');
        $this->addSql('ALTER TABLE sudo_people DROP FOREIGN KEY FK_582AFBA68DF4A340');
        $this->addSql('ALTER TABLE sudo_command DROP FOREIGN KEY FK_6302556542DF92E2');
        $this->addSql('DROP TABLE sudo');
        $this->addSql('DROP TABLE sudo_host');
        $this->addSql('DROP TABLE sudo_people');
        $this->addSql('DROP TABLE sudo_command');
    }
}
