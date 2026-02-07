<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204210722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userPasswordReset DROP FOREIGN KEY `FK_37C5B247FE6E88D7`');
        $this->addSql('DROP INDEX IDX_37C5B247FE6E88D7 ON userPasswordReset');
        $this->addSql('ALTER TABLE userPasswordReset ADD email VARCHAR(50) NOT NULL, DROP idUser');
        $this->addSql('ALTER TABLE userVerifyEmail DROP FOREIGN KEY `FK_3DA58E53FE6E88D7`');
        $this->addSql('DROP INDEX IDX_3DA58E53FE6E88D7 ON userVerifyEmail');
        $this->addSql('ALTER TABLE userVerifyEmail ADD email VARCHAR(50) NOT NULL, DROP idUser');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE userPasswordReset ADD idUser VARCHAR(26) NOT NULL, DROP email');
        $this->addSql('ALTER TABLE userPasswordReset ADD CONSTRAINT `FK_37C5B247FE6E88D7` FOREIGN KEY (idUser) REFERENCES user (idUser) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_37C5B247FE6E88D7 ON userPasswordReset (idUser)');
        $this->addSql('ALTER TABLE userVerifyEmail ADD idUser VARCHAR(26) NOT NULL, DROP email');
        $this->addSql('ALTER TABLE userVerifyEmail ADD CONSTRAINT `FK_3DA58E53FE6E88D7` FOREIGN KEY (idUser) REFERENCES user (idUser) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3DA58E53FE6E88D7 ON userVerifyEmail (idUser)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
