<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add users, owner scoping and prijs fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_USER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE brand ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE brand DROP INDEX UNIQ_1C52F9585E237E06');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BRAND_OWNER_NAME ON brand (owner_id, name)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F9587E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `console` ADD prijs NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD prijs NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F9587E3C61F9');
        $this->addSql('ALTER TABLE brand DROP INDEX UNIQ_BRAND_OWNER_NAME');
        $this->addSql('ALTER TABLE brand DROP owner_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C52F9585E237E06 ON brand (name)');
        $this->addSql('ALTER TABLE `console` DROP prijs');
        $this->addSql('ALTER TABLE game DROP prijs');
        $this->addSql('DROP TABLE `user`');
    }
}
