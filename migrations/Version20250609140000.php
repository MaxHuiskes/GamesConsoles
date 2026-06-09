<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add console_version and game_version tables, migrate existing data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE console_version (id INT AUTO_INCREMENT NOT NULL, console_id INT NOT NULL, name VARCHAR(255) NOT NULL, `condition` VARCHAR(50) NOT NULL, prijs NUMERIC(10, 2) DEFAULT NULL, foto LONGBLOB DEFAULT NULL, INDEX IDX_CONSOLE_VERSION_CONSOLE (console_id), UNIQUE INDEX UNIQ_CONSOLE_VERSION_NAME (console_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game_version (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, name VARCHAR(255) NOT NULL, `condition` VARCHAR(50) NOT NULL, prijs NUMERIC(10, 2) DEFAULT NULL, foto LONGBLOB DEFAULT NULL, INDEX IDX_GAME_VERSION_GAME (game_id), UNIQUE INDEX UNIQ_GAME_VERSION_NAME (game_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE console_version ADD CONSTRAINT FK_CONSOLE_VERSION_CONSOLE FOREIGN KEY (console_id) REFERENCES `console` (id)');
        $this->addSql('ALTER TABLE game_version ADD CONSTRAINT FK_GAME_VERSION_GAME FOREIGN KEY (game_id) REFERENCES game (id)');

        $this->addSql("INSERT INTO console_version (console_id, name, `condition`, prijs, foto) SELECT id, 'Default', `condition`, prijs, foto FROM `console`");
        $this->addSql("INSERT INTO game_version (game_id, name, `condition`, prijs, foto) SELECT id, 'Default', `condition`, prijs, foto FROM game");

        $this->addSql('ALTER TABLE `console` DROP `condition`, DROP prijs, DROP foto');
        $this->addSql('ALTER TABLE game DROP `condition`, DROP prijs, DROP foto');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `console` ADD `condition` VARCHAR(50) NOT NULL DEFAULT \'good\', ADD prijs NUMERIC(10, 2) DEFAULT NULL, ADD foto LONGBLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD `condition` VARCHAR(50) NOT NULL DEFAULT \'good\', ADD prijs NUMERIC(10, 2) DEFAULT NULL, ADD foto LONGBLOB DEFAULT NULL');

        $this->addSql('UPDATE `console` c INNER JOIN console_version v ON v.console_id = c.id AND v.name = \'Default\' SET c.`condition` = v.`condition`, c.prijs = v.prijs, c.foto = v.foto');
        $this->addSql('UPDATE game g INNER JOIN game_version v ON v.game_id = g.id AND v.name = \'Default\' SET g.`condition` = v.`condition`, g.prijs = v.prijs, g.foto = v.foto');

        $this->addSql('ALTER TABLE `console` ALTER `condition` DROP DEFAULT');
        $this->addSql('ALTER TABLE game ALTER `condition` DROP DEFAULT');

        $this->addSql('ALTER TABLE game_version DROP FOREIGN KEY FK_GAME_VERSION_GAME');
        $this->addSql('ALTER TABLE console_version DROP FOREIGN KEY FK_CONSOLE_VERSION_CONSOLE');
        $this->addSql('DROP TABLE game_version');
        $this->addSql('DROP TABLE console_version');
    }
}
