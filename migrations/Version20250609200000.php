<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add descriptions to versions and optional console version links on games';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE console_version ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_version ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE TABLE game_console_version (game_id INT NOT NULL, console_version_id INT NOT NULL, INDEX IDX_GAME_CONSOLE_VERSION_GAME (game_id), INDEX IDX_GAME_CONSOLE_VERSION_VERSION (console_version_id), PRIMARY KEY (game_id, console_version_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE game_console_version ADD CONSTRAINT FK_GAME_CONSOLE_VERSION_GAME FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_console_version ADD CONSTRAINT FK_GAME_CONSOLE_VERSION_VERSION FOREIGN KEY (console_version_id) REFERENCES console_version (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_console_version DROP FOREIGN KEY FK_GAME_CONSOLE_VERSION_GAME');
        $this->addSql('ALTER TABLE game_console_version DROP FOREIGN KEY FK_GAME_CONSOLE_VERSION_VERSION');
        $this->addSql('DROP TABLE game_console_version');
        $this->addSql('ALTER TABLE game_version DROP description');
        $this->addSql('ALTER TABLE console_version DROP description');
    }
}
