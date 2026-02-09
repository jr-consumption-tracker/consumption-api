<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208212402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE userAdminLoginHistory (idUserAdminLoginHistory INT AUTO_INCREMENT NOT NULL, loginAttemptAt DATETIME NOT NULL, isSuccessful TINYINT NOT NULL, idUser VARCHAR(26) NOT NULL, INDEX IDX_C7B1AC52FE6E88D7 (idUser), PRIMARY KEY (idUserAdminLoginHistory))');
        $this->addSql('CREATE TABLE userWebLoginHistory (idUserWebLoginHistory INT AUTO_INCREMENT NOT NULL, loginAttemptAt DATETIME NOT NULL, isSuccessful TINYINT NOT NULL, idUser VARCHAR(26) NOT NULL, INDEX IDX_96D27011FE6E88D7 (idUser), PRIMARY KEY (idUserWebLoginHistory))');
        $this->addSql('ALTER TABLE userAdminLoginHistory ADD CONSTRAINT FK_C7B1AC52FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userWebLoginHistory ADD CONSTRAINT FK_96D27011FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userAdminLoginHistory DROP FOREIGN KEY FK_C7B1AC52FE6E88D7');
        $this->addSql('ALTER TABLE userWebLoginHistory DROP FOREIGN KEY FK_96D27011FE6E88D7');
        $this->addSql('DROP TABLE userAdminLoginHistory');
        $this->addSql('DROP TABLE userWebLoginHistory');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
