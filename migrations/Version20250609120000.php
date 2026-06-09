<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create brand, console and game tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1C52F9585E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `console` (id INT AUTO_INCREMENT NOT NULL, brand_id INT NOT NULL, name VARCHAR(255) NOT NULL, `condition` VARCHAR(50) NOT NULL, foto LONGBLOB DEFAULT NULL, INDEX IDX_9BACE7E144F5D008 (brand_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, console_id INT NOT NULL, name VARCHAR(255) NOT NULL, `condition` VARCHAR(50) NOT NULL, foto LONGBLOB DEFAULT NULL, INDEX IDX_232B318C27F909B7 (console_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `console` ADD CONSTRAINT FK_9BACE7E144F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C27F909B7 FOREIGN KEY (console_id) REFERENCES `console` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `console` DROP FOREIGN KEY FK_9BACE7E144F5D008');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C27F909B7');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE `console`');
        $this->addSql('DROP TABLE brand');
    }
}
