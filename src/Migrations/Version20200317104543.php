<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200317104543 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE competition (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, is_open TINYINT(1) NOT NULL, is_finished TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, held_at DATETIME DEFAULT NULL, INDEX IDX_B50A2CB1E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, competition_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, lobby_name VARCHAR(255) DEFAULT NULL, lobby_password VARCHAR(255) DEFAULT NULL, rank INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_C4E0A61F7B39D312 (competition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_player (team_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_EE023DBC296CD8AE (team_id), INDEX IDX_EE023DBC99E6F5DF (player_id), PRIMARY KEY(team_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, twitch_name VARCHAR(255) DEFAULT NULL, twitch_id VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_98197A65F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, competition_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, elo INT DEFAULT NULL, is_sub TINYINT(1) NOT NULL, priority INT NOT NULL, is_confirmed TINYINT(1) NOT NULL, INDEX IDX_62A8A7A799E6F5DF (player_id), INDEX IDX_62A8A7A77B39D312 (competition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE round (id INT AUTO_INCREMENT NOT NULL, competition_id INT NOT NULL, winner_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, bracket_level INT NOT NULL, bracket_order INT NOT NULL, best_of INT NOT NULL, lobby_name VARCHAR(255) DEFAULT NULL, lobby_password VARCHAR(255) DEFAULT NULL, INDEX IDX_C5EEEA347B39D312 (competition_id), INDEX IDX_C5EEEA345DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE round_team (round_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_6157B7BFA6005CA0 (round_id), INDEX IDX_6157B7BF296CD8AE (team_id), PRIMARY KEY(round_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE competition ADD CONSTRAINT FK_B50A2CB1E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F7B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id)');
        $this->addSql('ALTER TABLE team_player ADD CONSTRAINT FK_EE023DBC296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_player ADD CONSTRAINT FK_EE023DBC99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A799E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A77B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA347B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id)');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA345DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE round_team ADD CONSTRAINT FK_6157B7BFA6005CA0 FOREIGN KEY (round_id) REFERENCES round (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE round_team ADD CONSTRAINT FK_6157B7BF296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F7B39D312');
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A77B39D312');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA347B39D312');
        $this->addSql('ALTER TABLE team_player DROP FOREIGN KEY FK_EE023DBC296CD8AE');
        $this->addSql('ALTER TABLE round DROP FOREIGN KEY FK_C5EEEA345DFCD4B8');
        $this->addSql('ALTER TABLE round_team DROP FOREIGN KEY FK_6157B7BF296CD8AE');
        $this->addSql('ALTER TABLE competition DROP FOREIGN KEY FK_B50A2CB1E48FD905');
        $this->addSql('ALTER TABLE team_player DROP FOREIGN KEY FK_EE023DBC99E6F5DF');
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A799E6F5DF');
        $this->addSql('ALTER TABLE round_team DROP FOREIGN KEY FK_6157B7BFA6005CA0');
        $this->addSql('DROP TABLE competition');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_player');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE registration');
        $this->addSql('DROP TABLE round');
        $this->addSql('DROP TABLE round_team');
    }
}
