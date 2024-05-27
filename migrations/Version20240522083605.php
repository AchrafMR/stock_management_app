<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240522083605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE smodels (id INT AUTO_INCREMENT NOT NULL, models_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, INDEX IDX_15F88462D966BF49 (models_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE smodels ADD CONSTRAINT FK_15F88462D966BF49 FOREIGN KEY (models_id) REFERENCES models (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smodels DROP FOREIGN KEY FK_15F88462D966BF49');
        $this->addSql('DROP TABLE smodels');
    }
}
