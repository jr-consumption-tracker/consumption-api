<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260217230222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userPasswordReset DROP usedAt');
        $this->addSql('CREATE INDEX IDX_B952B2ED2B8C7D2F ON userToken (expiresAt)');
        $this->addSql('ALTER TABLE userVerifyEmail DROP usedAt');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userPasswordReset ADD usedAt DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_B952B2ED2B8C7D2F ON userToken');
        $this->addSql('ALTER TABLE userVerifyEmail ADD usedAt DATETIME DEFAULT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
