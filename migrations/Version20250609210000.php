<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow duplicate version names with different condition';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_GAME_VERSION_NAME ON game_version');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_GAME_VERSION_NAME_CONDITION ON game_version (game_id, name, `condition`)');
        $this->addSql('DROP INDEX UNIQ_CONSOLE_VERSION_NAME ON console_version');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CONSOLE_VERSION_NAME_CONDITION ON console_version (console_id, name, `condition`)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_GAME_VERSION_NAME_CONDITION ON game_version');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_GAME_VERSION_NAME ON game_version (game_id, name)');
        $this->addSql('DROP INDEX UNIQ_CONSOLE_VERSION_NAME_CONDITION ON console_version');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CONSOLE_VERSION_NAME ON console_version (console_id, name)');
    }
}
