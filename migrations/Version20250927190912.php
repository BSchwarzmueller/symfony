<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250927190912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('CREATE TABLE IF NOT EXISTS bet (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, user_id INT NOT NULL, home_goals INT NOT NULL, away_goals INT NOT NULL, points INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, INDEX IDX_FBF0EC9BE48FD905 (game_id), INDEX IDX_FBF0EC9BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('CREATE TABLE IF NOT EXISTS club (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, logo_url VARCHAR(255) DEFAULT NULL, open_liga_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('CREATE TABLE IF NOT EXISTS game (id INT AUTO_INCREMENT NOT NULL, home_club_id INT NOT NULL, away_club_id INT NOT NULL, home_goals INT DEFAULT NULL, away_goals INT DEFAULT NULL, competition VARCHAR(255) NOT NULL, season VARCHAR(255) NOT NULL, matchday INT NOT NULL, date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', processed TINYINT(1) NOT NULL, open_liga_id INT DEFAULT NULL, INDEX IDX_232B318CD439C16A (home_club_id), INDEX IDX_232B318CD6D8F9E (away_club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('CREATE TABLE IF NOT EXISTS game_stats (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, avg_home_goals DOUBLE PRECISION NOT NULL, avg_away_goals DOUBLE PRECISION NOT NULL, number_of_votes INT NOT NULL, UNIQUE INDEX UNIQ_65741E25E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('CREATE TABLE IF NOT EXISTS system_config (id INT AUTO_INCREMENT NOT NULL, config_key VARCHAR(255) NOT NULL, config_value VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('CREATE TABLE IF NOT EXISTS user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('CREATE TABLE IF NOT EXISTS user_profile (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, description VARCHAR(500) DEFAULT NULL, picture_url VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('CREATE TABLE IF NOT EXISTS user_stats (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, points INT NOT NULL, number_of_bets INT NOT NULL, cash INT NOT NULL, UNIQUE INDEX UNIQ_B5859CF2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
//        $this->addSql('ALTER TABLE bet ADD CONSTRAINT FK_FBF0EC9BE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
//        $this->addSql('ALTER TABLE bet ADD CONSTRAINT FK_FBF0EC9BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
//        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CD439C16A FOREIGN KEY (home_club_id) REFERENCES club (id)');
//        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CD6D8F9E FOREIGN KEY (away_club_id) REFERENCES club (id)');
//        $this->addSql('ALTER TABLE game_stats ADD CONSTRAINT FK_65741E25E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
//        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
//        $this->addSql('ALTER TABLE user_stats ADD CONSTRAINT FK_B5859CF2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
//        $this->addSql('INSERT IGNORE INTO system_config (config_key, config_value) VALUES ("currentMatchday", "1")');
//        $this->addSql('INSERT IGNORE INTO system_config (config_key, config_value) VALUES ("BL1_TABLE_API_URL", "https://api.openligadb.de/getbltable/bl1/2025")');
//        $this->addSql('INSERT IGNORE INTO system_config (config_key, config_value) VALUES ("LEADERBOARD_TABLE_URL", "/user/stats/leaderboard")');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bet DROP FOREIGN KEY FK_FBF0EC9BE48FD905');
        $this->addSql('ALTER TABLE bet DROP FOREIGN KEY FK_FBF0EC9BA76ED395');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CD439C16A');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CD6D8F9E');
        $this->addSql('ALTER TABLE game_stats DROP FOREIGN KEY FK_65741E25E48FD905');
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('ALTER TABLE user_stats DROP FOREIGN KEY FK_B5859CF2A76ED395');
        $this->addSql('DROP TABLE bet');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_stats');
        $this->addSql('DROP TABLE system_config');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('DROP TABLE user_stats');
    }
}
