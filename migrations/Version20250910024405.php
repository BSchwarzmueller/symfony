<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250910024405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, home_club_id INT NOT NULL, away_club_id INT NOT NULL, home_goals INT DEFAULT NULL, away_goals INT DEFAULT NULL, competition VARCHAR(255) NOT NULL, season VARCHAR(255) NOT NULL, matchday INT NOT NULL, date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_232B318CD439C16A (home_club_id), INDEX IDX_232B318CD6D8F9E (away_club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CD439C16A FOREIGN KEY (home_club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CD6D8F9E FOREIGN KEY (away_club_id) REFERENCES club (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CD439C16A');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CD6D8F9E');
        $this->addSql('DROP TABLE game');
    }
}
