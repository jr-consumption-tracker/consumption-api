<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204200532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE userPasswordReset (idUserPasswordReset INT AUTO_INCREMENT NOT NULL, token VARCHAR(36) NOT NULL, expiresAt DATETIME NOT NULL, usedAt DATETIME DEFAULT NULL, createdAt DATETIME NOT NULL, idUser VARCHAR(26) NOT NULL, UNIQUE INDEX UNIQ_37C5B2475F37A13B (token), INDEX IDX_37C5B247FE6E88D7 (idUser), PRIMARY KEY (idUserPasswordReset))');
        $this->addSql('CREATE TABLE userVerifyEmail (idUserVerifyEmail INT AUTO_INCREMENT NOT NULL, token VARCHAR(36) NOT NULL, expiresAt DATETIME NOT NULL, usedAt DATETIME DEFAULT NULL, createdAt DATETIME NOT NULL, idUser VARCHAR(26) NOT NULL, UNIQUE INDEX UNIQ_3DA58E535F37A13B (token), INDEX IDX_3DA58E53FE6E88D7 (idUser), PRIMARY KEY (idUserVerifyEmail))');
        $this->addSql('ALTER TABLE userPasswordReset ADD CONSTRAINT FK_37C5B247FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE userVerifyEmail ADD CONSTRAINT FK_3DA58E53FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userPasswordReset DROP FOREIGN KEY FK_37C5B247FE6E88D7');
        $this->addSql('ALTER TABLE userVerifyEmail DROP FOREIGN KEY FK_3DA58E53FE6E88D7');
        $this->addSql('DROP TABLE userPasswordReset');
        $this->addSql('DROP TABLE userVerifyEmail');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
