<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517150454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE models (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_models (user_id INT NOT NULL, models_id INT NOT NULL, INDEX IDX_81842B34A76ED395 (user_id), INDEX IDX_81842B34D966BF49 (models_id), PRIMARY KEY(user_id, models_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_models ADD CONSTRAINT FK_81842B34A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_models ADD CONSTRAINT FK_81842B34D966BF49 FOREIGN KEY (models_id) REFERENCES models (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_models DROP FOREIGN KEY FK_81842B34A76ED395');
        $this->addSql('ALTER TABLE user_models DROP FOREIGN KEY FK_81842B34D966BF49');
        $this->addSql('DROP TABLE models');
        $this->addSql('DROP TABLE user_models');
    }
}
