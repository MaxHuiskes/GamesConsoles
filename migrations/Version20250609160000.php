<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add invite table for invite-only registration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE invite (id INT AUTO_INCREMENT NOT NULL, invited_by_id INT NOT NULL, used_by_id INT DEFAULT NULL, token VARCHAR(64) NOT NULL, email VARCHAR(180) DEFAULT NULL, created_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_INVITE_TOKEN (token), INDEX IDX_INVITE_INVITED_BY (invited_by_id), INDEX IDX_INVITE_USED_BY (used_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE invite ADD CONSTRAINT FK_INVITE_INVITED_BY FOREIGN KEY (invited_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE invite ADD CONSTRAINT FK_INVITE_USED_BY FOREIGN KEY (used_by_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invite DROP FOREIGN KEY FK_INVITE_INVITED_BY');
        $this->addSql('ALTER TABLE invite DROP FOREIGN KEY FK_INVITE_USED_BY');
        $this->addSql('DROP TABLE invite');
    }
}
