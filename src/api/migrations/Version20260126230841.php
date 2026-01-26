<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260126230841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userInfo DROP FOREIGN KEY `FK_CDC6E618A76ED395`');
        $this->addSql('DROP INDEX UNIQ_CDC6E618A76ED395 ON userInfo');
        $this->addSql('ALTER TABLE userInfo CHANGE user_id idUser INT NOT NULL');
        $this->addSql('ALTER TABLE userInfo ADD CONSTRAINT FK_CDC6E618FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CDC6E618FE6E88D7 ON userInfo (idUser)');
        $this->addSql('ALTER TABLE userPermissionOverride DROP FOREIGN KEY `FK_51D0D16297305994`');
        $this->addSql('ALTER TABLE userPermissionOverride DROP FOREIGN KEY `FK_51D0D162A76ED395`');
        $this->addSql('DROP INDEX IDX_51D0D16297305994 ON userPermissionOverride');
        $this->addSql('DROP INDEX IDX_51D0D162A76ED395 ON userPermissionOverride');
        $this->addSql('ALTER TABLE userPermissionOverride ADD idUser INT NOT NULL, ADD idUserPermission INT NOT NULL, DROP user_id, DROP userPermission_id, DROP PRIMARY KEY, ADD PRIMARY KEY (idUser, idUserPermission)');
        $this->addSql('ALTER TABLE userPermissionOverride ADD CONSTRAINT FK_51D0D162FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userPermissionOverride ADD CONSTRAINT FK_51D0D1623E745410 FOREIGN KEY (idUserPermission) REFERENCES userPermission (idUserPermission)');
        $this->addSql('CREATE INDEX IDX_51D0D162FE6E88D7 ON userPermissionOverride (idUser)');
        $this->addSql('CREATE INDEX IDX_51D0D1623E745410 ON userPermissionOverride (idUserPermission)');
        $this->addSql('ALTER TABLE userRole DROP FOREIGN KEY `FK_51265D251A540A7E`');
        $this->addSql('ALTER TABLE userRole DROP FOREIGN KEY `FK_51265D25A76ED395`');
        $this->addSql('DROP INDEX IDX_51265D251A540A7E ON userRole');
        $this->addSql('DROP INDEX IDX_51265D25A76ED395 ON userRole');
        $this->addSql('ALTER TABLE userRole ADD idUser INT NOT NULL, ADD idUserRoleType INT NOT NULL, DROP user_id, DROP userRoleType_id, DROP PRIMARY KEY, ADD PRIMARY KEY (idUser, idUserRoleType)');
        $this->addSql('ALTER TABLE userRole ADD CONSTRAINT FK_51265D25FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userRole ADD CONSTRAINT FK_51265D255E485C4F FOREIGN KEY (idUserRoleType) REFERENCES userRoleType (idUserRoleType)');
        $this->addSql('CREATE INDEX IDX_51265D25FE6E88D7 ON userRole (idUser)');
        $this->addSql('CREATE INDEX IDX_51265D255E485C4F ON userRole (idUserRoleType)');
        $this->addSql('ALTER TABLE userRolePermission DROP FOREIGN KEY `FK_3FC6761D1A540A7E`');
        $this->addSql('ALTER TABLE userRolePermission DROP FOREIGN KEY `FK_3FC6761D97305994`');
        $this->addSql('DROP INDEX IDX_3FC6761D97305994 ON userRolePermission');
        $this->addSql('DROP INDEX IDX_3FC6761D1A540A7E ON userRolePermission');
        $this->addSql('ALTER TABLE userRolePermission ADD idUserRoleType INT NOT NULL, ADD idUserPermission INT NOT NULL, DROP userRoleType_id, DROP userPermission_id, DROP PRIMARY KEY, ADD PRIMARY KEY (idUserRoleType, idUserPermission)');
        $this->addSql('ALTER TABLE userRolePermission ADD CONSTRAINT FK_3FC6761D5E485C4F FOREIGN KEY (idUserRoleType) REFERENCES userRoleType (idUserRoleType)');
        $this->addSql('ALTER TABLE userRolePermission ADD CONSTRAINT FK_3FC6761D3E745410 FOREIGN KEY (idUserPermission) REFERENCES userPermission (idUserPermission)');
        $this->addSql('CREATE INDEX IDX_3FC6761D5E485C4F ON userRolePermission (idUserRoleType)');
        $this->addSql('CREATE INDEX IDX_3FC6761D3E745410 ON userRolePermission (idUserPermission)');
        $this->addSql('ALTER TABLE userSubscription DROP FOREIGN KEY `FK_4C51C4403DE71322`');
        $this->addSql('ALTER TABLE userSubscription DROP FOREIGN KEY `FK_4C51C440A76ED395`');
        $this->addSql('DROP INDEX IDX_4C51C4403DE71322 ON userSubscription');
        $this->addSql('DROP INDEX IDX_4C51C440A76ED395 ON userSubscription');
        $this->addSql('ALTER TABLE userSubscription ADD idUser INT NOT NULL, ADD idUserSubscriptionPlan INT NOT NULL, DROP user_id, DROP userSubscriptionPlan_id');
        $this->addSql('ALTER TABLE userSubscription ADD CONSTRAINT FK_4C51C440FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userSubscription ADD CONSTRAINT FK_4C51C4408C7A167C FOREIGN KEY (idUserSubscriptionPlan) REFERENCES userSubscriptionPlan (idUserSubscriptionPlan)');
        $this->addSql('CREATE INDEX IDX_4C51C440FE6E88D7 ON userSubscription (idUser)');
        $this->addSql('CREATE INDEX IDX_4C51C4408C7A167C ON userSubscription (idUserSubscriptionPlan)');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature DROP FOREIGN KEY `FK_A79A097F4E0F0218`');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature DROP FOREIGN KEY `FK_A79A097FB4B61E39`');
        $this->addSql('DROP INDEX IDX_A79A097F4E0F0218 ON userSubscriptionPlanFeature');
        $this->addSql('DROP INDEX IDX_A79A097FB4B61E39 ON userSubscriptionPlanFeature');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD idUserSubscriptionPlan INT NOT NULL, ADD idUserSubscriptionFeature INT NOT NULL, DROP useSubscriptionPlan_id, DROP userSubscriptionFeature_id, DROP PRIMARY KEY, ADD PRIMARY KEY (idUserSubscriptionPlan, idUserSubscriptionFeature)');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD CONSTRAINT FK_A79A097F8C7A167C FOREIGN KEY (idUserSubscriptionPlan) REFERENCES userSubscriptionPlan (idUserSubscriptionPlan)');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD CONSTRAINT FK_A79A097F60AC0E80 FOREIGN KEY (idUserSubscriptionFeature) REFERENCES userSubscriptionFeature (idUserSubscriptionFeature)');
        $this->addSql('CREATE INDEX IDX_A79A097F8C7A167C ON userSubscriptionPlanFeature (idUserSubscriptionPlan)');
        $this->addSql('CREATE INDEX IDX_A79A097F60AC0E80 ON userSubscriptionPlanFeature (idUserSubscriptionFeature)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userInfo DROP FOREIGN KEY FK_CDC6E618FE6E88D7');
        $this->addSql('DROP INDEX UNIQ_CDC6E618FE6E88D7 ON userInfo');
        $this->addSql('ALTER TABLE userInfo CHANGE idUser user_id INT NOT NULL');
        $this->addSql('ALTER TABLE userInfo ADD CONSTRAINT `FK_CDC6E618A76ED395` FOREIGN KEY (user_id) REFERENCES user (idUser) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CDC6E618A76ED395 ON userInfo (user_id)');
        $this->addSql('ALTER TABLE userPermissionOverride DROP FOREIGN KEY FK_51D0D162FE6E88D7');
        $this->addSql('ALTER TABLE userPermissionOverride DROP FOREIGN KEY FK_51D0D1623E745410');
        $this->addSql('DROP INDEX IDX_51D0D162FE6E88D7 ON userPermissionOverride');
        $this->addSql('DROP INDEX IDX_51D0D1623E745410 ON userPermissionOverride');
        $this->addSql('ALTER TABLE userPermissionOverride ADD user_id INT NOT NULL, ADD userPermission_id INT NOT NULL, DROP idUser, DROP idUserPermission, DROP PRIMARY KEY, ADD PRIMARY KEY (user_id, userPermission_id)');
        $this->addSql('ALTER TABLE userPermissionOverride ADD CONSTRAINT `FK_51D0D16297305994` FOREIGN KEY (userPermission_id) REFERENCES userPermission (idUserPermission) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userPermissionOverride ADD CONSTRAINT `FK_51D0D162A76ED395` FOREIGN KEY (user_id) REFERENCES user (idUser) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_51D0D16297305994 ON userPermissionOverride (userPermission_id)');
        $this->addSql('CREATE INDEX IDX_51D0D162A76ED395 ON userPermissionOverride (user_id)');
        $this->addSql('ALTER TABLE userRole DROP FOREIGN KEY FK_51265D25FE6E88D7');
        $this->addSql('ALTER TABLE userRole DROP FOREIGN KEY FK_51265D255E485C4F');
        $this->addSql('DROP INDEX IDX_51265D25FE6E88D7 ON userRole');
        $this->addSql('DROP INDEX IDX_51265D255E485C4F ON userRole');
        $this->addSql('ALTER TABLE userRole ADD user_id INT NOT NULL, ADD userRoleType_id INT NOT NULL, DROP idUser, DROP idUserRoleType, DROP PRIMARY KEY, ADD PRIMARY KEY (user_id, userRoleType_id)');
        $this->addSql('ALTER TABLE userRole ADD CONSTRAINT `FK_51265D251A540A7E` FOREIGN KEY (userRoleType_id) REFERENCES userRoleType (idUserRoleType) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userRole ADD CONSTRAINT `FK_51265D25A76ED395` FOREIGN KEY (user_id) REFERENCES user (idUser) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_51265D251A540A7E ON userRole (userRoleType_id)');
        $this->addSql('CREATE INDEX IDX_51265D25A76ED395 ON userRole (user_id)');
        $this->addSql('ALTER TABLE userRolePermission DROP FOREIGN KEY FK_3FC6761D5E485C4F');
        $this->addSql('ALTER TABLE userRolePermission DROP FOREIGN KEY FK_3FC6761D3E745410');
        $this->addSql('DROP INDEX IDX_3FC6761D5E485C4F ON userRolePermission');
        $this->addSql('DROP INDEX IDX_3FC6761D3E745410 ON userRolePermission');
        $this->addSql('ALTER TABLE userRolePermission ADD userRoleType_id INT NOT NULL, ADD userPermission_id INT NOT NULL, DROP idUserRoleType, DROP idUserPermission, DROP PRIMARY KEY, ADD PRIMARY KEY (userRoleType_id, userPermission_id)');
        $this->addSql('ALTER TABLE userRolePermission ADD CONSTRAINT `FK_3FC6761D1A540A7E` FOREIGN KEY (userRoleType_id) REFERENCES userRoleType (idUserRoleType) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userRolePermission ADD CONSTRAINT `FK_3FC6761D97305994` FOREIGN KEY (userPermission_id) REFERENCES userPermission (idUserPermission) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3FC6761D97305994 ON userRolePermission (userPermission_id)');
        $this->addSql('CREATE INDEX IDX_3FC6761D1A540A7E ON userRolePermission (userRoleType_id)');
        $this->addSql('ALTER TABLE userSubscription DROP FOREIGN KEY FK_4C51C440FE6E88D7');
        $this->addSql('ALTER TABLE userSubscription DROP FOREIGN KEY FK_4C51C4408C7A167C');
        $this->addSql('DROP INDEX IDX_4C51C440FE6E88D7 ON userSubscription');
        $this->addSql('DROP INDEX IDX_4C51C4408C7A167C ON userSubscription');
        $this->addSql('ALTER TABLE userSubscription ADD user_id INT NOT NULL, ADD userSubscriptionPlan_id INT NOT NULL, DROP idUser, DROP idUserSubscriptionPlan');
        $this->addSql('ALTER TABLE userSubscription ADD CONSTRAINT `FK_4C51C4403DE71322` FOREIGN KEY (userSubscriptionPlan_id) REFERENCES userSubscriptionPlan (idUserSubscriptionPlan) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userSubscription ADD CONSTRAINT `FK_4C51C440A76ED395` FOREIGN KEY (user_id) REFERENCES user (idUser) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4C51C4403DE71322 ON userSubscription (userSubscriptionPlan_id)');
        $this->addSql('CREATE INDEX IDX_4C51C440A76ED395 ON userSubscription (user_id)');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature DROP FOREIGN KEY FK_A79A097F8C7A167C');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature DROP FOREIGN KEY FK_A79A097F60AC0E80');
        $this->addSql('DROP INDEX IDX_A79A097F8C7A167C ON userSubscriptionPlanFeature');
        $this->addSql('DROP INDEX IDX_A79A097F60AC0E80 ON userSubscriptionPlanFeature');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD useSubscriptionPlan_id INT NOT NULL, ADD userSubscriptionFeature_id INT NOT NULL, DROP idUserSubscriptionPlan, DROP idUserSubscriptionFeature, DROP PRIMARY KEY, ADD PRIMARY KEY (useSubscriptionPlan_id, userSubscriptionFeature_id)');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD CONSTRAINT `FK_A79A097F4E0F0218` FOREIGN KEY (userSubscriptionFeature_id) REFERENCES userSubscriptionFeature (idUserSubscriptionFeature) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD CONSTRAINT `FK_A79A097FB4B61E39` FOREIGN KEY (useSubscriptionPlan_id) REFERENCES userSubscriptionPlan (idUserSubscriptionPlan) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A79A097F4E0F0218 ON userSubscriptionPlanFeature (userSubscriptionFeature_id)');
        $this->addSql('CREATE INDEX IDX_A79A097FB4B61E39 ON userSubscriptionPlanFeature (useSubscriptionPlan_id)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
