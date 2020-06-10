<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200610075618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reset password bundle database';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(
            'CREATE TABLE reset_password_request (
                    id INT AUTO_INCREMENT NOT NULL,
                    user_id INT DEFAULT NULL,
                    selector VARCHAR(20) NOT NULL,
                    hashed_token VARCHAR(100) NOT NULL,
                    requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                    expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                    INDEX IDX_7CE748AA76ED395 (user_id),
                    PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES player (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE reset_password_request');
    }
}
