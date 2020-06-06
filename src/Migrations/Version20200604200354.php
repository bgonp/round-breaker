<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200604200354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competition DROP is_individual, CHANGE held_at held_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE team CHANGE captain_id captain_id INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_player_competition ON registration (player_id, competition_id)');
        $this->addSql('ALTER TABLE player DROP twitch_id, CHANGE twitch_name twitch_name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A6572EE84A6 ON player (twitch_name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A65E7927C74 ON player (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competition ADD is_individual TINYINT(1) NOT NULL, CHANGE held_at held_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_98197A6572EE84A6 ON player');
        $this->addSql('DROP INDEX UNIQ_98197A65E7927C74 ON player');
        $this->addSql('ALTER TABLE player ADD twitch_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE twitch_name twitch_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX unique_player_competition ON registration');
        $this->addSql('ALTER TABLE team CHANGE captain_id captain_id INT NOT NULL');
    }
}
