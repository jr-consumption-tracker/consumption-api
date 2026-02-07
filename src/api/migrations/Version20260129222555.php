<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260129222555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consumptionPlace (idConsumptionPlace INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, idUser VARCHAR(26) NOT NULL, INDEX IDX_25096645FE6E88D7 (idUser), PRIMARY KEY (idConsumptionPlace))');
        $this->addSql('CREATE TABLE energyPrice (idEnergyPrice INT AUTO_INCREMENT NOT NULL, unitPrice NUMERIC(10, 4) NOT NULL, validFrom DATE NOT NULL, idMeasuredEnergy INT NOT NULL, INDEX IDX_D2BC39F1F9D3AC5C (idMeasuredEnergy), PRIMARY KEY (idEnergyPrice))');
        $this->addSql('CREATE TABLE energyPriceComponent (idEnergyPriceComponent INT AUTO_INCREMENT NOT NULL, value NUMERIC(10, 4) NOT NULL, perUnit TINYINT NOT NULL, validFrom DATE NOT NULL, idEnergyPrice INT NOT NULL, idEnergyPriceComponentType INT NOT NULL, INDEX IDX_6823248B2F4EF015 (idEnergyPriceComponentType), INDEX IDX_6823248B92E1C689 (idEnergyPrice), PRIMARY KEY (idEnergyPriceComponent))');
        $this->addSql('CREATE TABLE energyPriceComponentType (idEnergyPriceComponentType INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, description VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_D430B91A77153098 (code), PRIMARY KEY (idEnergyPriceComponentType))');
        $this->addSql('CREATE TABLE energyType (idEnergyType INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, variant VARCHAR(25) DEFAULT NULL, UNIQUE INDEX UNIQ_4F8208FE77153098 (code), PRIMARY KEY (idEnergyType))');
        $this->addSql('CREATE TABLE energyVariant (idEnergyVariant INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, sortOrder SMALLINT NOT NULL, active TINYINT DEFAULT 0 NOT NULL, idEnergyType INT NOT NULL, INDEX IDX_267BC09FCD761614 (idEnergyType), UNIQUE INDEX UNIQ_267BC09FD547CDEC77153098 (IdEnergyType, code), PRIMARY KEY (idEnergyVariant))');
        $this->addSql('CREATE TABLE localeType (idLocaleType INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_FB6232E377153098 (code), PRIMARY KEY (idLocaleType))');
        $this->addSql('CREATE TABLE measuredEnergy (idMeasuredEnergy INT AUTO_INCREMENT NOT NULL, idConsumptionPlace INT NOT NULL, idEnergyType INT NOT NULL, idUnitType INT NOT NULL, idEnergyVariant INT DEFAULT NULL, INDEX IDX_25CF45CFA6B3395C (idConsumptionPlace), INDEX IDX_25CF45CFCD761614 (idEnergyType), INDEX IDX_25CF45CF27B962E8 (idUnitType), INDEX IDX_25CF45CFEC9F9CB1 (idEnergyVariant), UNIQUE INDEX UNIQ_25CF45CFA6B3395CCD761614 (idConsumptionPlace, idEnergyType), PRIMARY KEY (idMeasuredEnergy))');
        $this->addSql('CREATE TABLE meterReading (idMeterReading INT AUTO_INCREMENT NOT NULL, year INT NOT NULL, month INT NOT NULL, value NUMERIC(12, 3) NOT NULL, measuredAt DATETIME DEFAULT NULL, idMeasuredEnergy INT NOT NULL, INDEX IDX_7843FCB4F9D3AC5C (idMeasuredEnergy), UNIQUE INDEX UNIQ_7843FCB4F9D3AC5CBB8273378EB61006 (idMeasuredEnergy, year, month), PRIMARY KEY (idMeterReading))');
        $this->addSql('CREATE TABLE meterReplacement (idMeter INT AUTO_INCREMENT NOT NULL, year SMALLINT NOT NULL, month SMALLINT NOT NULL, oldMeterFinalValue NUMERIC(12, 3) NOT NULL, newMeterInitialValue NUMERIC(12, 3) NOT NULL, replacedAt DATETIME DEFAULT NULL, idMeasuredEnergy INT NOT NULL, INDEX IDX_3CA9FFEAF9D3AC5C (idMeasuredEnergy), INDEX IDX_3CA9FFEAF9D3AC5CBB8273378EB61006 (idMeasuredEnergy, year, month), PRIMARY KEY (idMeter))');
        $this->addSql('CREATE TABLE subscription (idUserSubscription INT AUTO_INCREMENT NOT NULL, validFrom DATETIME NOT NULL, validTo DATETIME DEFAULT NULL, isActive TINYINT NOT NULL, idUser VARCHAR(26) NOT NULL, idUserSubscriptionPlan INT NOT NULL, INDEX IDX_A3C664D3FE6E88D7 (idUser), INDEX IDX_A3C664D38C7A167C (idUserSubscriptionPlan), PRIMARY KEY (idUserSubscription))');
        $this->addSql('CREATE TABLE subscriptionFeature (idUserSubscriptionFeature INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_E0AFB3FE77153098 (code), PRIMARY KEY (idUserSubscriptionFeature))');
        $this->addSql('CREATE TABLE subscriptionPlan (idUserSubscriptionPlan INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, durationDays INT NOT NULL, UNIQUE INDEX UNIQ_D428035277153098 (code), PRIMARY KEY (idUserSubscriptionPlan))');
        $this->addSql('CREATE TABLE subscriptionPlanFeature (idUserSubscriptionPlan INT NOT NULL, idUserSubscriptionFeature INT NOT NULL, INDEX IDX_BA338CC18C7A167C (idUserSubscriptionPlan), INDEX IDX_BA338CC160AC0E80 (idUserSubscriptionFeature), PRIMARY KEY (idUserSubscriptionPlan, idUserSubscriptionFeature))');
        $this->addSql('CREATE TABLE timezoneType (idTimezoneType INT AUTO_INCREMENT NOT NULL, code VARCHAR(25) NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_6B079F3A77153098 (code), PRIMARY KEY (idTimezoneType))');
        $this->addSql('CREATE TABLE unitType (idUnitType INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, conversionToBase NUMERIC(10, 5) NOT NULL, UNIQUE INDEX UNIQ_741A48B377153098 (code), PRIMARY KEY (idUnitType))');
        $this->addSql('CREATE TABLE user (idUser VARCHAR(26) NOT NULL, email VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (idUser))');
        $this->addSql('CREATE TABLE userInfo (idUserInfo INT AUTO_INCREMENT NOT NULL, firstName VARCHAR(50) NOT NULL, lastName VARCHAR(50) NOT NULL, idLocaleType INT DEFAULT NULL, idTimezoneType INT DEFAULT NULL, idUser VARCHAR(26) NOT NULL, INDEX IDX_CDC6E61879962C09 (idLocaleType), INDEX IDX_CDC6E61835993BCB (idTimezoneType), UNIQUE INDEX UNIQ_CDC6E618FE6E88D7 (idUser), PRIMARY KEY (idUserInfo))');
        $this->addSql('CREATE TABLE userPermission (idUserPermission INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, value SMALLINT NOT NULL, UNIQUE INDEX UNIQ_E268BD8377153098 (code), UNIQUE INDEX UNIQ_E268BD831D775834 (value), PRIMARY KEY (idUserPermission))');
        $this->addSql('CREATE TABLE userPermissionOverride (allow TINYINT NOT NULL, idUser VARCHAR(26) NOT NULL, idUserPermission INT NOT NULL, INDEX IDX_51D0D162FE6E88D7 (idUser), INDEX IDX_51D0D1623E745410 (idUserPermission), PRIMARY KEY (idUser, idUserPermission))');
        $this->addSql('CREATE TABLE userRole (idUser VARCHAR(26) NOT NULL, idUserRoleType INT NOT NULL, INDEX IDX_51265D25FE6E88D7 (idUser), INDEX IDX_51265D255E485C4F (idUserRoleType), PRIMARY KEY (idUser, idUserRoleType))');
        $this->addSql('CREATE TABLE userRolePermission (idUserRoleType INT NOT NULL, idUserPermission INT NOT NULL, INDEX IDX_3FC6761D5E485C4F (idUserRoleType), INDEX IDX_3FC6761D3E745410 (idUserPermission), PRIMARY KEY (idUserRoleType, idUserPermission))');
        $this->addSql('CREATE TABLE userRoleType (idUserRoleType INT AUTO_INCREMENT NOT NULL, code VARCHAR(25) NOT NULL, value SMALLINT NOT NULL, description VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_D6F8BE77153098 (code), UNIQUE INDEX UNIQ_D6F8BE1D775834 (value), PRIMARY KEY (idUserRoleType))');
        $this->addSql('ALTER TABLE consumptionPlace ADD CONSTRAINT FK_25096645FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE energyPrice ADD CONSTRAINT FK_D2BC39F1F9D3AC5C FOREIGN KEY (idMeasuredEnergy) REFERENCES measuredEnergy (idMeasuredEnergy)');
        $this->addSql('ALTER TABLE energyPriceComponent ADD CONSTRAINT FK_6823248B92E1C689 FOREIGN KEY (idEnergyPrice) REFERENCES energyPrice (idEnergyPrice)');
        $this->addSql('ALTER TABLE energyPriceComponent ADD CONSTRAINT FK_6823248B2F4EF015 FOREIGN KEY (idEnergyPriceComponentType) REFERENCES energyPriceComponentType (idEnergyPriceComponentType)');
        $this->addSql('ALTER TABLE energyVariant ADD CONSTRAINT FK_267BC09FCD761614 FOREIGN KEY (idEnergyType) REFERENCES energyType (idEnergyType)');
        $this->addSql('ALTER TABLE measuredEnergy ADD CONSTRAINT FK_25CF45CFA6B3395C FOREIGN KEY (idConsumptionPlace) REFERENCES consumptionPlace (idConsumptionPlace)');
        $this->addSql('ALTER TABLE measuredEnergy ADD CONSTRAINT FK_25CF45CFCD761614 FOREIGN KEY (idEnergyType) REFERENCES energyType (idEnergyType)');
        $this->addSql('ALTER TABLE measuredEnergy ADD CONSTRAINT FK_25CF45CF27B962E8 FOREIGN KEY (idUnitType) REFERENCES unitType (idUnitType)');
        $this->addSql('ALTER TABLE measuredEnergy ADD CONSTRAINT FK_25CF45CFEC9F9CB1 FOREIGN KEY (idEnergyVariant) REFERENCES energyVariant (idEnergyVariant)');
        $this->addSql('ALTER TABLE meterReading ADD CONSTRAINT FK_7843FCB4F9D3AC5C FOREIGN KEY (idMeasuredEnergy) REFERENCES measuredEnergy (idMeasuredEnergy)');
        $this->addSql('ALTER TABLE meterReplacement ADD CONSTRAINT FK_3CA9FFEAF9D3AC5C FOREIGN KEY (idMeasuredEnergy) REFERENCES measuredEnergy (idMeasuredEnergy)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D38C7A167C FOREIGN KEY (idUserSubscriptionPlan) REFERENCES subscriptionPlan (idUserSubscriptionPlan)');
        $this->addSql('ALTER TABLE subscriptionPlanFeature ADD CONSTRAINT FK_BA338CC18C7A167C FOREIGN KEY (idUserSubscriptionPlan) REFERENCES subscriptionPlan (idUserSubscriptionPlan)');
        $this->addSql('ALTER TABLE subscriptionPlanFeature ADD CONSTRAINT FK_BA338CC160AC0E80 FOREIGN KEY (idUserSubscriptionFeature) REFERENCES subscriptionFeature (idUserSubscriptionFeature)');
        $this->addSql('ALTER TABLE userInfo ADD CONSTRAINT FK_CDC6E61879962C09 FOREIGN KEY (idLocaleType) REFERENCES localeType (idLocaleType)');
        $this->addSql('ALTER TABLE userInfo ADD CONSTRAINT FK_CDC6E61835993BCB FOREIGN KEY (idTimezoneType) REFERENCES timezoneType (idTimezoneType)');
        $this->addSql('ALTER TABLE userInfo ADD CONSTRAINT FK_CDC6E618FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userPermissionOverride ADD CONSTRAINT FK_51D0D162FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userPermissionOverride ADD CONSTRAINT FK_51D0D1623E745410 FOREIGN KEY (idUserPermission) REFERENCES userPermission (idUserPermission)');
        $this->addSql('ALTER TABLE userRole ADD CONSTRAINT FK_51265D25FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE userRole ADD CONSTRAINT FK_51265D255E485C4F FOREIGN KEY (idUserRoleType) REFERENCES userRoleType (idUserRoleType)');
        $this->addSql('ALTER TABLE userRolePermission ADD CONSTRAINT FK_3FC6761D5E485C4F FOREIGN KEY (idUserRoleType) REFERENCES userRoleType (idUserRoleType)');
        $this->addSql('ALTER TABLE userRolePermission ADD CONSTRAINT FK_3FC6761D3E745410 FOREIGN KEY (idUserPermission) REFERENCES userPermission (idUserPermission)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consumptionPlace DROP FOREIGN KEY FK_25096645FE6E88D7');
        $this->addSql('ALTER TABLE energyPrice DROP FOREIGN KEY FK_D2BC39F1F9D3AC5C');
        $this->addSql('ALTER TABLE energyPriceComponent DROP FOREIGN KEY FK_6823248B92E1C689');
        $this->addSql('ALTER TABLE energyPriceComponent DROP FOREIGN KEY FK_6823248B2F4EF015');
        $this->addSql('ALTER TABLE energyVariant DROP FOREIGN KEY FK_267BC09FCD761614');
        $this->addSql('ALTER TABLE measuredEnergy DROP FOREIGN KEY FK_25CF45CFA6B3395C');
        $this->addSql('ALTER TABLE measuredEnergy DROP FOREIGN KEY FK_25CF45CFCD761614');
        $this->addSql('ALTER TABLE measuredEnergy DROP FOREIGN KEY FK_25CF45CF27B962E8');
        $this->addSql('ALTER TABLE measuredEnergy DROP FOREIGN KEY FK_25CF45CFEC9F9CB1');
        $this->addSql('ALTER TABLE meterReading DROP FOREIGN KEY FK_7843FCB4F9D3AC5C');
        $this->addSql('ALTER TABLE meterReplacement DROP FOREIGN KEY FK_3CA9FFEAF9D3AC5C');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3FE6E88D7');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D38C7A167C');
        $this->addSql('ALTER TABLE subscriptionPlanFeature DROP FOREIGN KEY FK_BA338CC18C7A167C');
        $this->addSql('ALTER TABLE subscriptionPlanFeature DROP FOREIGN KEY FK_BA338CC160AC0E80');
        $this->addSql('ALTER TABLE userInfo DROP FOREIGN KEY FK_CDC6E61879962C09');
        $this->addSql('ALTER TABLE userInfo DROP FOREIGN KEY FK_CDC6E61835993BCB');
        $this->addSql('ALTER TABLE userInfo DROP FOREIGN KEY FK_CDC6E618FE6E88D7');
        $this->addSql('ALTER TABLE userPermissionOverride DROP FOREIGN KEY FK_51D0D162FE6E88D7');
        $this->addSql('ALTER TABLE userPermissionOverride DROP FOREIGN KEY FK_51D0D1623E745410');
        $this->addSql('ALTER TABLE userRole DROP FOREIGN KEY FK_51265D25FE6E88D7');
        $this->addSql('ALTER TABLE userRole DROP FOREIGN KEY FK_51265D255E485C4F');
        $this->addSql('ALTER TABLE userRolePermission DROP FOREIGN KEY FK_3FC6761D5E485C4F');
        $this->addSql('ALTER TABLE userRolePermission DROP FOREIGN KEY FK_3FC6761D3E745410');
        $this->addSql('DROP TABLE consumptionPlace');
        $this->addSql('DROP TABLE energyPrice');
        $this->addSql('DROP TABLE energyPriceComponent');
        $this->addSql('DROP TABLE energyPriceComponentType');
        $this->addSql('DROP TABLE energyType');
        $this->addSql('DROP TABLE energyVariant');
        $this->addSql('DROP TABLE localeType');
        $this->addSql('DROP TABLE measuredEnergy');
        $this->addSql('DROP TABLE meterReading');
        $this->addSql('DROP TABLE meterReplacement');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE subscriptionFeature');
        $this->addSql('DROP TABLE subscriptionPlan');
        $this->addSql('DROP TABLE subscriptionPlanFeature');
        $this->addSql('DROP TABLE timezoneType');
        $this->addSql('DROP TABLE unitType');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE userInfo');
        $this->addSql('DROP TABLE userPermission');
        $this->addSql('DROP TABLE userPermissionOverride');
        $this->addSql('DROP TABLE userRole');
        $this->addSql('DROP TABLE userRolePermission');
        $this->addSql('DROP TABLE userRoleType');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
