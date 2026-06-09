<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at timestamps to collection entities';
    }

    public function up(Schema $schema): void
    {
        foreach (['brand', '`console`', 'game', 'console_version', 'game_version'] as $table) {
            $this->addSql(sprintf('ALTER TABLE %s ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP', $table));
            $this->addSql(sprintf('ALTER TABLE %s ALTER created_at DROP DEFAULT', $table));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE brand DROP created_at');
        $this->addSql('ALTER TABLE `console` DROP created_at');
        $this->addSql('ALTER TABLE game DROP created_at');
        $this->addSql('ALTER TABLE console_version DROP created_at');
        $this->addSql('ALTER TABLE game_version DROP created_at');
    }
}
