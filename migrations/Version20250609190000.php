<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add owner to games so they can exist without consoles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game ADD owner_id INT DEFAULT NULL');
        $this->addSql('UPDATE game g SET owner_id = (
            SELECT b.owner_id
            FROM game_console gc
            INNER JOIN `console` c ON gc.console_id = c.id
            INNER JOIN brand b ON c.brand_id = b.id
            WHERE gc.game_id = g.id
            LIMIT 1
        )');
        $this->addSql('ALTER TABLE game CHANGE owner_id owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_GAME_OWNER FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_GAME_OWNER ON game (owner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_GAME_OWNER');
        $this->addSql('DROP INDEX IDX_GAME_OWNER ON game');
        $this->addSql('ALTER TABLE game DROP owner_id');
    }
}
