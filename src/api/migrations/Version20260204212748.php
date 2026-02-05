<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204212748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_37C5B247E7927C74 ON userPasswordReset (email)');
        $this->addSql('CREATE INDEX IDX_37C5B2475F37A13B ON userPasswordReset (token)');
        $this->addSql('CREATE INDEX IDX_37C5B2472B8C7D2F ON userPasswordReset (expiresAt)');
        $this->addSql('CREATE INDEX IDX_3DA58E53E7927C74 ON userVerifyEmail (email)');
        $this->addSql('CREATE INDEX IDX_3DA58E535F37A13B ON userVerifyEmail (token)');
        $this->addSql('CREATE INDEX IDX_3DA58E532B8C7D2F ON userVerifyEmail (expiresAt)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_37C5B247E7927C74 ON userPasswordReset');
        $this->addSql('DROP INDEX IDX_37C5B2475F37A13B ON userPasswordReset');
        $this->addSql('DROP INDEX IDX_37C5B2472B8C7D2F ON userPasswordReset');
        $this->addSql('DROP INDEX IDX_3DA58E53E7927C74 ON userVerifyEmail');
        $this->addSql('DROP INDEX IDX_3DA58E535F37A13B ON userVerifyEmail');
        $this->addSql('DROP INDEX IDX_3DA58E532B8C7D2F ON userVerifyEmail');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
