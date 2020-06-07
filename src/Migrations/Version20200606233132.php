<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200606233132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA345DFCD4B8');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA345DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA345DFCD4B8');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA345DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
