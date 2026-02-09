<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209220119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE userLoginHistory (idUserLoginHistory INT AUTO_INCREMENT NOT NULL, context VARCHAR(10) NOT NULL, loginAttemptAt DATETIME NOT NULL, isSuccessful TINYINT NOT NULL, idUser VARCHAR(26) NOT NULL, INDEX IDX_9C7295DFE6E88D7 (idUser), PRIMARY KEY (idUserLoginHistory))');
        $this->addSql('CREATE TABLE userToken (idUserToken INT AUTO_INCREMENT NOT NULL, domain VARCHAR(10) NOT NULL, refreshToken VARCHAR(255) DEFAULT NULL, idUser VARCHAR(26) NOT NULL, INDEX IDX_B952B2EDFE6E88D7 (idUser), PRIMARY KEY (idUserToken))');
        $this->addSql('ALTER TABLE userLoginHistory ADD CONSTRAINT FK_9C7295DFE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userToken ADD CONSTRAINT FK_B952B2EDFE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userAdminLoginHistory DROP FOREIGN KEY `FK_C7B1AC52FE6E88D7`');
        $this->addSql('ALTER TABLE userWebLoginHistory DROP FOREIGN KEY `FK_96D27011FE6E88D7`');
        $this->addSql('DROP TABLE userAdminLoginHistory');
        $this->addSql('DROP TABLE userWebLoginHistory');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE userAdminLoginHistory (idUserAdminLoginHistory INT AUTO_INCREMENT NOT NULL, loginAttemptAt DATETIME NOT NULL, isSuccessful TINYINT NOT NULL, idUser VARCHAR(26) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, INDEX IDX_C7B1AC52FE6E88D7 (idUser), PRIMARY KEY (idUserAdminLoginHistory)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE userWebLoginHistory (idUserWebLoginHistory INT AUTO_INCREMENT NOT NULL, loginAttemptAt DATETIME NOT NULL, isSuccessful TINYINT NOT NULL, idUser VARCHAR(26) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, INDEX IDX_96D27011FE6E88D7 (idUser), PRIMARY KEY (idUserWebLoginHistory)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE userAdminLoginHistory ADD CONSTRAINT `FK_C7B1AC52FE6E88D7` FOREIGN KEY (idUser) REFERENCES user (idUser) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userWebLoginHistory ADD CONSTRAINT `FK_96D27011FE6E88D7` FOREIGN KEY (idUser) REFERENCES user (idUser) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userLoginHistory DROP FOREIGN KEY FK_9C7295DFE6E88D7');
        $this->addSql('ALTER TABLE userToken DROP FOREIGN KEY FK_B952B2EDFE6E88D7');
        $this->addSql('DROP TABLE userLoginHistory');
        $this->addSql('DROP TABLE userToken');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
