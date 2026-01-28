<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128232735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consumptionPlace (idConsumptionPlace INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, idUser INT NOT NULL, INDEX IDX_25096645FE6E88D7 (idUser), PRIMARY KEY (idConsumptionPlace))');
        $this->addSql('CREATE TABLE energyType (idEnergyType INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, variant VARCHAR(25) DEFAULT NULL, UNIQUE INDEX UNIQ_4F8208FE77153098 (code), PRIMARY KEY (idEnergyType))');
        $this->addSql('CREATE TABLE energyVariant (idEnergyVariant INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, sortOrder SMALLINT NOT NULL, active TINYINT DEFAULT 0 NOT NULL, idEnergyType INT NOT NULL, INDEX IDX_267BC09FCD761614 (idEnergyType), UNIQUE INDEX UNIQ_267BC09FD547CDEC77153098 (IdEnergyType, code), PRIMARY KEY (idEnergyVariant))');
        $this->addSql('CREATE TABLE localeType (idLocaleType INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_FB6232E377153098 (code), PRIMARY KEY (idLocaleType))');
        $this->addSql('CREATE TABLE measuredEnergy (idMeasuredEnergy INT AUTO_INCREMENT NOT NULL, idConsumptionPlace INT NOT NULL, idEnergyType INT NOT NULL, idUnitType INT NOT NULL, idEnergyVariant INT DEFAULT NULL, INDEX IDX_25CF45CFA6B3395C (idConsumptionPlace), INDEX IDX_25CF45CFCD761614 (idEnergyType), INDEX IDX_25CF45CF27B962E8 (idUnitType), INDEX IDX_25CF45CFEC9F9CB1 (idEnergyVariant), UNIQUE INDEX UNIQ_25CF45CFA6B3395CCD761614 (idConsumptionPlace, idEnergyType), PRIMARY KEY (idMeasuredEnergy))');
        $this->addSql('CREATE TABLE meterReading (idMeterReading INT AUTO_INCREMENT NOT NULL, year INT NOT NULL, month INT NOT NULL, value NUMERIC(12, 3) NOT NULL, measuredAt DATETIME DEFAULT NULL, idMeasuredEnergy INT NOT NULL, INDEX IDX_7843FCB4F9D3AC5C (idMeasuredEnergy), UNIQUE INDEX UNIQ_7843FCB4F9D3AC5CBB8273378EB61006 (idMeasuredEnergy, year, month), PRIMARY KEY (idMeterReading))');
        $this->addSql('CREATE TABLE meterReplacement (idMeter INT AUTO_INCREMENT NOT NULL, year SMALLINT NOT NULL, month SMALLINT NOT NULL, oldMeterFinalValue NUMERIC(12, 3) NOT NULL, newMeterInitialValue NUMERIC(12, 3) NOT NULL, replacedAt DATETIME DEFAULT NULL, idMeasuredEnergy INT NOT NULL, INDEX IDX_3CA9FFEAF9D3AC5C (idMeasuredEnergy), INDEX IDX_3CA9FFEAF9D3AC5CBB8273378EB61006 (idMeasuredEnergy, year, month), PRIMARY KEY (idMeter))');
        $this->addSql('CREATE TABLE subscription (idUserSubscription INT AUTO_INCREMENT NOT NULL, validFrom DATETIME NOT NULL, validTo DATETIME DEFAULT NULL, isActive TINYINT NOT NULL, idUser INT NOT NULL, idUserSubscriptionPlan INT NOT NULL, INDEX IDX_A3C664D3FE6E88D7 (idUser), INDEX IDX_A3C664D38C7A167C (idUserSubscriptionPlan), PRIMARY KEY (idUserSubscription))');
        $this->addSql('CREATE TABLE subscriptionFeature (idUserSubscriptionFeature INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_E0AFB3FE77153098 (code), PRIMARY KEY (idUserSubscriptionFeature))');
        $this->addSql('CREATE TABLE subscriptionPlan (idUserSubscriptionPlan INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, durationDays INT NOT NULL, UNIQUE INDEX UNIQ_D428035277153098 (code), PRIMARY KEY (idUserSubscriptionPlan))');
        $this->addSql('CREATE TABLE subscriptionPlanFeature (idUserSubscriptionPlan INT NOT NULL, idUserSubscriptionFeature INT NOT NULL, INDEX IDX_BA338CC18C7A167C (idUserSubscriptionPlan), INDEX IDX_BA338CC160AC0E80 (idUserSubscriptionFeature), PRIMARY KEY (idUserSubscriptionPlan, idUserSubscriptionFeature))');
        $this->addSql('CREATE TABLE timezoneType (idTimezoneType INT AUTO_INCREMENT NOT NULL, code VARCHAR(25) NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_6B079F3A77153098 (code), PRIMARY KEY (idTimezoneType))');
        $this->addSql('CREATE TABLE unitType (idUnitType INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, conversionToBase NUMERIC(10, 5) NOT NULL, UNIQUE INDEX UNIQ_741A48B377153098 (code), PRIMARY KEY (idUnitType))');
        $this->addSql('ALTER TABLE consumptionPlace ADD CONSTRAINT FK_25096645FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
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
        $this->addSql('ALTER TABLE userSubscription DROP FOREIGN KEY `FK_4C51C4408C7A167C`');
        $this->addSql('ALTER TABLE userSubscription DROP FOREIGN KEY `FK_4C51C440FE6E88D7`');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature DROP FOREIGN KEY `FK_A79A097F60AC0E80`');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature DROP FOREIGN KEY `FK_A79A097F8C7A167C`');
        $this->addSql('DROP TABLE userSubscription');
        $this->addSql('DROP TABLE userSubscriptionFeature');
        $this->addSql('DROP TABLE userSubscriptionPlan');
        $this->addSql('DROP TABLE userSubscriptionPlanFeature');
        $this->addSql('ALTER TABLE userInfo ADD idLocaleType INT DEFAULT NULL, ADD idTimezoneType INT DEFAULT NULL');
        $this->addSql('ALTER TABLE userInfo ADD CONSTRAINT FK_CDC6E61879962C09 FOREIGN KEY (idLocaleType) REFERENCES localeType (idLocaleType)');
        $this->addSql('ALTER TABLE userInfo ADD CONSTRAINT FK_CDC6E61835993BCB FOREIGN KEY (idTimezoneType) REFERENCES timezoneType (idTimezoneType)');
        $this->addSql('CREATE INDEX IDX_CDC6E61879962C09 ON userInfo (idLocaleType)');
        $this->addSql('CREATE INDEX IDX_CDC6E61835993BCB ON userInfo (idTimezoneType)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE userSubscription (idUserSubscription INT AUTO_INCREMENT NOT NULL, validFrom DATETIME NOT NULL, validTo DATETIME DEFAULT NULL, isActive TINYINT NOT NULL, idUser INT NOT NULL, idUserSubscriptionPlan INT NOT NULL, INDEX IDX_4C51C4408C7A167C (idUserSubscriptionPlan), INDEX IDX_4C51C440FE6E88D7 (idUser), PRIMARY KEY (idUserSubscription)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE userSubscriptionFeature (idUserSubscriptionFeature INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, description VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, UNIQUE INDEX UNIQ_CAA964DF77153098 (code), PRIMARY KEY (idUserSubscriptionFeature)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE userSubscriptionPlan (idUserSubscriptionPlan INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, price NUMERIC(10, 2) NOT NULL, durationDays INT NOT NULL, UNIQUE INDEX UNIQ_AC10F34C77153098 (code), PRIMARY KEY (idUserSubscriptionPlan)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE userSubscriptionPlanFeature (idUserSubscriptionPlan INT NOT NULL, idUserSubscriptionFeature INT NOT NULL, INDEX IDX_A79A097F60AC0E80 (idUserSubscriptionFeature), INDEX IDX_A79A097F8C7A167C (idUserSubscriptionPlan), PRIMARY KEY (idUserSubscriptionPlan, idUserSubscriptionFeature)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE userSubscription ADD CONSTRAINT `FK_4C51C4408C7A167C` FOREIGN KEY (idUserSubscriptionPlan) REFERENCES userSubscriptionPlan (idUserSubscriptionPlan) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userSubscription ADD CONSTRAINT `FK_4C51C440FE6E88D7` FOREIGN KEY (idUser) REFERENCES user (idUser) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD CONSTRAINT `FK_A79A097F60AC0E80` FOREIGN KEY (idUserSubscriptionFeature) REFERENCES userSubscriptionFeature (idUserSubscriptionFeature) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE userSubscriptionPlanFeature ADD CONSTRAINT `FK_A79A097F8C7A167C` FOREIGN KEY (idUserSubscriptionPlan) REFERENCES userSubscriptionPlan (idUserSubscriptionPlan) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE consumptionPlace DROP FOREIGN KEY FK_25096645FE6E88D7');
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
        $this->addSql('DROP TABLE consumptionPlace');
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
        $this->addSql('ALTER TABLE userInfo DROP FOREIGN KEY FK_CDC6E61879962C09');
        $this->addSql('ALTER TABLE userInfo DROP FOREIGN KEY FK_CDC6E61835993BCB');
        $this->addSql('DROP INDEX IDX_CDC6E61879962C09 ON userInfo');
        $this->addSql('DROP INDEX IDX_CDC6E61835993BCB ON userInfo');
        $this->addSql('ALTER TABLE userInfo DROP idLocaleType, DROP idTimezoneType');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
