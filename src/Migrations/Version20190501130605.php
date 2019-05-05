<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190501130605 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE alarm_time (id INT AUTO_INCREMENT NOT NULL, todo_id INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_40B71768EA1EBC33 (todo_id), INDEX date_idx (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE todo (local_id INT AUTO_INCREMENT NOT NULL, global_id INT DEFAULT NULL, date DATETIME NOT NULL, display_interval VARCHAR(255) NOT NULL COMMENT \'(DC2Type:dateinterval)\', alarm_times JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, text TEXT NOT NULL, INDEX date_idx (date), INDEX display_interval_idx (display_interval), PRIMARY KEY(local_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE alarm_time ADD CONSTRAINT FK_40B71768EA1EBC33 FOREIGN KEY (todo_id) REFERENCES todo (local_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE alarm_time DROP FOREIGN KEY FK_40B71768EA1EBC33');
        $this->addSql('DROP TABLE alarm_time');
        $this->addSql('DROP TABLE todo');
    }
}
