<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200529000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competition DROP FOREIGN KEY FK_B50A2CB161220EA6');
        $this->addSql('DROP INDEX IDX_B50A2CB161220EA6 ON competition');
        $this->addSql('ALTER TABLE competition ADD streamer_id INT DEFAULT NULL, DROP creator_id, CHANGE game_id game_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL, CHANGE held_at held_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE competition ADD CONSTRAINT FK_B50A2CB125F432AD FOREIGN KEY (streamer_id) REFERENCES player (id)');
        $this->addSql('CREATE INDEX IDX_B50A2CB125F432AD ON competition (streamer_id)');
        $this->addSql('ALTER TABLE game CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE player CHANGE roles roles JSON NOT NULL, CHANGE twitch_name twitch_name VARCHAR(255) DEFAULT NULL, CHANGE twitch_id twitch_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE registration CHANGE elo elo INT DEFAULT NULL, CHANGE is_sub is_sub TINYINT(1) DEFAULT NULL, CHANGE priority priority INT DEFAULT NULL, CHANGE is_confirmed is_confirmed TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE round CHANGE winner_id winner_id INT DEFAULT NULL, CHANGE lobby_name lobby_name VARCHAR(255) DEFAULT NULL, CHANGE lobby_password lobby_password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD captain_id INT NOT NULL, CHANGE lobby_name lobby_name VARCHAR(255) DEFAULT NULL, CHANGE lobby_password lobby_password VARCHAR(255) DEFAULT NULL, CHANGE ranking ranking INT DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F3346729B FOREIGN KEY (captain_id) REFERENCES player (id)');
        $this->addSql('CREATE INDEX IDX_C4E0A61F3346729B ON team (captain_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competition DROP FOREIGN KEY FK_B50A2CB125F432AD');
        $this->addSql('DROP INDEX IDX_B50A2CB125F432AD ON competition');
        $this->addSql('ALTER TABLE competition ADD creator_id INT DEFAULT NULL, DROP streamer_id, CHANGE game_id game_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE url url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE held_at held_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE competition ADD CONSTRAINT FK_B50A2CB161220EA6 FOREIGN KEY (creator_id) REFERENCES player (id)');
        $this->addSql('CREATE INDEX IDX_B50A2CB161220EA6 ON competition (creator_id)');
        $this->addSql('ALTER TABLE game CHANGE description description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE url url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE image image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE player CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE twitch_name twitch_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE twitch_id twitch_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE registration CHANGE elo elo INT DEFAULT NULL, CHANGE is_sub is_sub TINYINT(1) DEFAULT \'NULL\', CHANGE priority priority INT DEFAULT NULL, CHANGE is_confirmed is_confirmed TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE round CHANGE winner_id winner_id INT DEFAULT NULL, CHANGE lobby_name lobby_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lobby_password lobby_password VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F3346729B');
        $this->addSql('DROP INDEX IDX_C4E0A61F3346729B ON team');
        $this->addSql('ALTER TABLE team DROP captain_id, CHANGE lobby_name lobby_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lobby_password lobby_password VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE ranking ranking INT DEFAULT NULL, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
