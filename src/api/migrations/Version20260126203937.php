<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260126203937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (idUser INT AUTO_INCREMENT NOT NULL, email VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (idUser))');
        $this->addSql('CREATE TABLE userInfo (idUserInfo INT AUTO_INCREMENT NOT NULL, firstName VARCHAR(50) NOT NULL, lastName VARCHAR(50) NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_CDC6E618A76ED395 (user_id), PRIMARY KEY (idUserInfo))');
        $this->addSql('CREATE TABLE userPermission (idUserPermission INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, value SMALLINT NOT NULL, UNIQUE INDEX UNIQ_E268BD8377153098 (code), UNIQUE INDEX UNIQ_E268BD831D775834 (value), PRIMARY KEY (idUserPermission))');
        $this->addSql('CREATE TABLE userPermissionOverride (allow TINYINT NOT NULL, user_id INT NOT NULL, userPermission_id INT NOT NULL, INDEX IDX_51D0D162A76ED395 (user_id), INDEX IDX_51D0D16297305994 (userPermission_id), PRIMARY KEY (user_id, userPermission_id))');
        $this->addSql('CREATE TABLE userRole (user_id INT NOT NULL, userRoleType_id INT NOT NULL, INDEX IDX_51265D25A76ED395 (user_id), INDEX IDX_51265D251A540A7E (userRoleType_id), PRIMARY KEY (user_id, userRoleType_id))');
        $this->addSql('CREATE TABLE userRolePermission (userRoleType_id INT NOT NULL, userPermission_id INT NOT NULL, INDEX IDX_3FC6761D1A540A7E (userRoleType_id), INDEX IDX_3FC6761D97305994 (userPermission_id), PRIMARY KEY (userRoleType_id, userPermission_id))');
        $this->addSql('CREATE TABLE userRoleType (idUserRoleType INT AUTO_INCREMENT NOT NULL, code VARCHAR(25) NOT NULL, value SMALLINT NOT NULL, description VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_D6F8BE77153098 (code), UNIQUE INDEX UNIQ_D6F8BE1D775834 (value), PRIMARY KEY (idUserRoleType))');
        $this->addSql('CREATE TABLE userSubscription (idUserSubscription INT AUTO_INCREMENT NOT NULL, validFrom DATETIME NOT NULL, validTo DATETIME DEFAULT NULL, isActive TINYINT NOT NULL, user_id INT NOT NULL, userSubscriptionPlan_id INT NOT NULL, INDEX IDX_4C51C440A76ED395 (user_id), INDEX IDX_4C51C4403DE71322 (userSubscriptionPlan_id), PRIMARY KEY (idUserSubscription))');
        $this->addSql('CREATE TABLE userSubscriptionFeature (idUserSubscriptionFeature INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_CAA964DF77153098 (code), PRIMARY KEY (idUserSubscriptionFeature))');
        $this->addSql('CREATE TABLE userSubscriptionPlan (idUserSubscriptionPlan INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, durationDays INT NOT NULL, UNIQUE INDEX UNIQ_AC10F34C77153098 (code), PRIMARY KEY (idUserSubscriptionPlan))');
        $this->addSql('CREATE TABLE userSubscriptionPlanFeature (useSubscriptionPlan_id INT NOT NULL, userSubscriptionFeature_id INT NOT NULL, INDEX IDX_A79A097FB4B61E39 (useSubscriptionPlan_id), INDEX IDX_A79A097F4E0F0218 (userSubscriptionFeature_id), PRIMARY KEY (useSubscriptionPlan_id, userSubscriptionFeature_id))');
        $this->addSql('ALTER TABLE userInfo ADD CONSTRAINT FK_CDC6E618A76ED395 FOREIGN KEY (user_id) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userPermissionOverride ADD CONSTRAINT FK_51D0D162A76ED395 FOREIGN KEY (user_id) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userPermissionOverride ADD CONSTRAINT FK_51D0D16297305994 FOREIGN KEY (userPermission_id) REFERENCES userPermission (idUserPermission)');
        $this->addSql('ALTER TABLE userRole ADD CONSTRAINT FK_51265D25A76ED395 FOREIGN KEY (user_id) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userRole ADD CONSTRAINT FK_51265D251A540A7E FOREIGN KEY (userRoleType_id) REFERENCES userRoleType (idUserRoleType)');
        $this->addSql('ALTER TABLE userRolePermission ADD CONSTRAINT FK_3FC6761D1A540A7E FOREIGN KEY (userRoleType_id) REFERENCES userRoleType (idUserRoleType)');
        $this->addSql('ALTER TABLE userRolePermission ADD CONSTRAINT FK_3FC6761D97305994 FOREIGN KEY (userPermission_id) REFERENCES userPermission (idUserPermission)');
        $this->addSql('ALTER TABLE userSubscription ADD CONSTRAINT FK_4C51C440A76ED395 FOREIGN KEY (user_id) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userSubscription ADD CONSTRAINT FK_4C51C4403DE71322 FOREIGN KEY (userSubscriptionPlan_id) REFERENCES userSubscriptionPlan (idUserSubscriptionPlan)');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD CONSTRAINT FK_A79A097FB4B61E39 FOREIGN KEY (useSubscriptionPlan_id) REFERENCES userSubscriptionPlan (idUserSubscriptionPlan)');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD CONSTRAINT FK_A79A097F4E0F0218 FOREIGN KEY (userSubscriptionFeature_id) REFERENCES userSubscriptionFeature (idUserSubscriptionFeature)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userInfo DROP FOREIGN KEY FK_CDC6E618A76ED395');
        $this->addSql('ALTER TABLE userPermissionOverride DROP FOREIGN KEY FK_51D0D162A76ED395');
        $this->addSql('ALTER TABLE userPermissionOverride DROP FOREIGN KEY FK_51D0D16297305994');
        $this->addSql('ALTER TABLE userRole DROP FOREIGN KEY FK_51265D25A76ED395');
        $this->addSql('ALTER TABLE userRole DROP FOREIGN KEY FK_51265D251A540A7E');
        $this->addSql('ALTER TABLE userRolePermission DROP FOREIGN KEY FK_3FC6761D1A540A7E');
        $this->addSql('ALTER TABLE userRolePermission DROP FOREIGN KEY FK_3FC6761D97305994');
        $this->addSql('ALTER TABLE userSubscription DROP FOREIGN KEY FK_4C51C440A76ED395');
        $this->addSql('ALTER TABLE userSubscription DROP FOREIGN KEY FK_4C51C4403DE71322');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature DROP FOREIGN KEY FK_A79A097FB4B61E39');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature DROP FOREIGN KEY FK_A79A097F4E0F0218');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE userInfo');
        $this->addSql('DROP TABLE userPermission');
        $this->addSql('DROP TABLE userPermissionOverride');
        $this->addSql('DROP TABLE userRole');
        $this->addSql('DROP TABLE userRolePermission');
        $this->addSql('DROP TABLE userRoleType');
        $this->addSql('DROP TABLE userSubscription');
        $this->addSql('DROP TABLE userSubscriptionFeature');
        $this->addSql('DROP TABLE userSubscriptionPlan');
        $this->addSql('DROP TABLE userSubscriptionPlanFeature');
    }
}
