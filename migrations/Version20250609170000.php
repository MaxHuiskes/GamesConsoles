<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250609170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add connect tokens and friendships';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD connect_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_CONNECT_TOKEN ON `user` (connect_token)');
        $this->addSql('CREATE TABLE friendship (id INT AUTO_INCREMENT NOT NULL, user_low_id INT NOT NULL, user_high_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_FRIENDSHIP_USER_LOW (user_low_id), INDEX IDX_FRIENDSHIP_USER_HIGH (user_high_id), UNIQUE INDEX UNIQ_FRIENDSHIP_PAIR (user_low_id, user_high_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_FRIENDSHIP_USER_LOW FOREIGN KEY (user_low_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_FRIENDSHIP_USER_HIGH FOREIGN KEY (user_high_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_FRIENDSHIP_USER_LOW');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_FRIENDSHIP_USER_HIGH');
        $this->addSql('DROP TABLE friendship');
        $this->addSql('DROP INDEX UNIQ_USER_CONNECT_TOKEN ON `user`');
        $this->addSql('ALTER TABLE `user` DROP connect_token');
    }
}
