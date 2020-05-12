<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200511210451 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competition CHANGE game_id game_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL, CHANGE held_at held_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE player CHANGE roles roles JSON NOT NULL, CHANGE twitch_name twitch_name VARCHAR(255) DEFAULT NULL, CHANGE twitch_id twitch_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE registration CHANGE elo elo INT DEFAULT NULL, CHANGE is_sub is_sub TINYINT(1) DEFAULT NULL, CHANGE priority priority INT DEFAULT NULL, CHANGE is_confirmed is_confirmed TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE round CHANGE winner_id winner_id INT DEFAULT NULL, CHANGE lobby_name lobby_name VARCHAR(255) DEFAULT NULL, CHANGE lobby_password lobby_password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE team CHANGE lobby_name lobby_name VARCHAR(255) DEFAULT NULL, CHANGE lobby_password lobby_password VARCHAR(255) DEFAULT NULL, CHANGE rank rank INT DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE competition CHANGE game_id game_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE url url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE held_at held_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE game CHANGE description description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE url url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE player CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE twitch_name twitch_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE twitch_id twitch_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE registration CHANGE elo elo INT DEFAULT NULL, CHANGE is_sub is_sub TINYINT(1) DEFAULT \'NULL\', CHANGE priority priority INT DEFAULT NULL, CHANGE is_confirmed is_confirmed TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE round CHANGE winner_id winner_id INT DEFAULT NULL, CHANGE lobby_name lobby_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lobby_password lobby_password VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE team CHANGE lobby_name lobby_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE lobby_password lobby_password VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE rank rank INT DEFAULT NULL, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
