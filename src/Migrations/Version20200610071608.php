<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200610071608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create main app schema';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Create table player
        $this->addSql(
            'CREATE TABLE player (
                    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    username VARCHAR(180) NOT NULL,
                    roles JSON NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    twitch_name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    UNIQUE INDEX UNIQ_username (username),
                    UNIQUE INDEX UNIQ_twitch_name (twitch_name),
                    UNIQUE INDEX UNIQ_email (email)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        // Create table game
        $this->addSql(
            'CREATE TABLE game (
                id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        // Create table competition
        $this->addSql(
            'CREATE TABLE competition (
                    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    game_id INT DEFAULT NULL,
                    streamer_id INT DEFAULT NULL,
                    is_open TINYINT(1) NOT NULL,
                    is_finished TINYINT(1) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    description VARCHAR(255) DEFAULT NULL,
                    lobby_name VARCHAR(255) DEFAULT NULL,
                    lobby_password VARCHAR(255) DEFAULT NULL,
                    held_at DATETIME NOT NULL,
                    players_per_team INT NOT NULL,
                    max_players INT NOT NULL,
                    twitch_bot_name VARCHAR(255) DEFAULT NULL,
                    twitch_bot_token VARCHAR(255) DEFAULT NULL,
                    twitch_channel VARCHAR(255) DEFAULT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    CONSTRAINT FK_competition_game_id FOREIGN KEY (game_id) REFERENCES game (id),
                    CONSTRAINT FK_competition_streamer_id FOREIGN KEY (streamer_id) REFERENCES player (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        // Create table registration
        $this->addSql(
            'CREATE TABLE registration (
                    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY ,
                    player_id INT NOT NULL,
                    competition_id INT NOT NULL,
                    is_confirmed TINYINT(1) NOT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    CONSTRAINT FK_registration_player FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE,
                    CONSTRAINT FK_registration_competition FOREIGN KEY (competition_id) REFERENCES competition (id) ON DELETE CASCADE,
                    UNIQUE INDEX UNIQ_player_competition (player_id, competition_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        // Create team table
        $this->addSql(
            'CREATE TABLE team (
                    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    competition_id INT NOT NULL,
                    captain_id INT DEFAULT NULL,
                    ranking INT DEFAULT NULL,
                    name VARCHAR(255) DEFAULT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    CONSTRAINT FK_team_competition_id FOREIGN KEY (competition_id) REFERENCES competition (id) ON DELETE CASCADE,
                    CONSTRAINT FK_team_captain_id FOREIGN KEY (captain_id) REFERENCES player (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        // Create table round
        $this->addSql(
            'CREATE TABLE round (
                    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    competition_id INT NOT NULL,
                    winner_id INT DEFAULT NULL,
                    bracket_level INT NOT NULL,
                    bracket_order INT NOT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    CONSTRAINT FK_round_competition_id FOREIGN KEY (competition_id) REFERENCES competition (id) ON DELETE CASCADE,
                    CONSTRAINT FK_round_winner_id FOREIGN KEY (winner_id) REFERENCES team (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        // Create round_team relation table
        $this->addSql(
            'CREATE TABLE round_team (
                    round_id INT NOT NULL,
                    team_id INT NOT NULL,
                    PRIMARY KEY(round_id, team_id),
                    CONSTRAINT FK_round_team_round FOREIGN KEY (round_id) REFERENCES round (id) ON DELETE CASCADE,
                    CONSTRAINT FK_round_team_team FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        // Create team_player relation table
        $this->addSql(
            'CREATE TABLE team_player (
                    team_id INT NOT NULL,
                    player_id INT NOT NULL,
                    PRIMARY KEY(team_id, player_id),
                    CONSTRAINT FK_team_player_team FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE,
                    CONSTRAINT FK_team_player_player FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Drop all tables
        $this->addSql('DROP TABLE team_player');
        $this->addSql('DROP TABLE round_team');
        $this->addSql('DROP TABLE round');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE registration');
        $this->addSql('DROP TABLE competition');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE player');
    }
}
