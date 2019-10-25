<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190926142906 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DECFF285C');
        $this->addSql('CREATE TABLE _table (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, coord_x DOUBLE PRECISION NOT NULL, coord_y DOUBLE PRECISION NOT NULL, INDEX IDX_7C1163DAB1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE _table ADD CONSTRAINT FK_7C1163DAB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('DROP TABLE `table`');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DECFF285C');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DECFF285C FOREIGN KEY (table_id) REFERENCES _table (id)');
        $this->addSql('ALTER TABLE howard_access_token CHANGE token token VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE howard_refresh_token CHANGE token token VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE howard_auth_code CHANGE token token VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DECFF285C');
        $this->addSql('CREATE TABLE `table` (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, nom VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, description VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, coord_x DOUBLE PRECISION NOT NULL, coord_y DOUBLE PRECISION NOT NULL, INDEX IDX_F6298F46B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE `table` ADD CONSTRAINT FK_F6298F46B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('DROP TABLE _table');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DECFF285C');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DECFF285C FOREIGN KEY (table_id) REFERENCES `table` (id)');
        $this->addSql('ALTER TABLE howard_access_token CHANGE token token VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE howard_auth_code CHANGE token token VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE howard_refresh_token CHANGE token token VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
