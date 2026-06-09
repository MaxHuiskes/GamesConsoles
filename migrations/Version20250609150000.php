<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow games to be linked to multiple consoles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE game_console (game_id INT NOT NULL, console_id INT NOT NULL, INDEX IDX_GAME_CONSOLE_GAME (game_id), INDEX IDX_GAME_CONSOLE_CONSOLE (console_id), PRIMARY KEY (game_id, console_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE game_console ADD CONSTRAINT FK_GAME_CONSOLE_GAME FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_console ADD CONSTRAINT FK_GAME_CONSOLE_CONSOLE FOREIGN KEY (console_id) REFERENCES `console` (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO game_console (game_id, console_id) SELECT id, console_id FROM game WHERE console_id IS NOT NULL');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C27F909B7');
        $this->addSql('DROP INDEX IDX_232B318C27F909B7 ON game');
        $this->addSql('ALTER TABLE game DROP console_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game ADD console_id INT NOT NULL');
        $this->addSql('UPDATE game g INNER JOIN game_console gc ON gc.game_id = g.id SET g.console_id = gc.console_id');
        $this->addSql('CREATE INDEX IDX_232B318C27F909B7 ON game (console_id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C27F909B7 FOREIGN KEY (console_id) REFERENCES `console` (id)');
        $this->addSql('ALTER TABLE game_console DROP FOREIGN KEY FK_GAME_CONSOLE_GAME');
        $this->addSql('ALTER TABLE game_console DROP FOREIGN KEY FK_GAME_CONSOLE_CONSOLE');
        $this->addSql('DROP TABLE game_console');
    }
}
