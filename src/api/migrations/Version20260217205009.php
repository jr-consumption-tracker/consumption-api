<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260217205009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userToken ADD expiresAt DATETIME NOT NULL');
        $this->addSql('CREATE INDEX IDX_USER_TOKEN_EXPIRES_AT ON userToken (expiresAt)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_USER_TOKEN_EXPIRES_AT ON userToken');
        $this->addSql('ALTER TABLE userToken DROP expiresAt');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
