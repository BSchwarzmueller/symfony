<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250913194752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bet ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE bet RENAME INDEX idx_fbf0ec9b4d77e7d8 TO IDX_FBF0EC9BE48FD905');
        $this->addSql('ALTER TABLE bet RENAME INDEX idx_fbf0ec9b9d86650f TO IDX_FBF0EC9BA76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bet DROP status');
        $this->addSql('ALTER TABLE bet RENAME INDEX idx_fbf0ec9be48fd905 TO IDX_FBF0EC9B4D77E7D8');
        $this->addSql('ALTER TABLE bet RENAME INDEX idx_fbf0ec9ba76ed395 TO IDX_FBF0EC9B9D86650F');
    }
}
