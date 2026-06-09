<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tags and game_tag join table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_TAG_OWNER (owner_id), UNIQUE INDEX UNIQ_TAG_OWNER_NAME (owner_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game_tag (game_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_GAME_TAG_GAME (game_id), INDEX IDX_GAME_TAG_TAG (tag_id), PRIMARY KEY (game_id, tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_TAG_OWNER FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE game_tag ADD CONSTRAINT FK_GAME_TAG_GAME FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_tag ADD CONSTRAINT FK_GAME_TAG_TAG FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_tag DROP FOREIGN KEY FK_GAME_TAG_GAME');
        $this->addSql('ALTER TABLE game_tag DROP FOREIGN KEY FK_GAME_TAG_TAG');
        $this->addSql('DROP TABLE game_tag');
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_TAG_OWNER');
        $this->addSql('DROP TABLE tag');
    }
}
