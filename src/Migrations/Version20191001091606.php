<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191001091606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categorie ADD updated_at DATETIME NOT NULL, ADD image_original_name VARCHAR(255) DEFAULT NULL, ADD image_mime_type VARCHAR(255) DEFAULT NULL, ADD image_size INT DEFAULT NULL, ADD image_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE image image_name VARCHAR(255) DEFAULT NULL');
        //$this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DECFF285C FOREIGN KEY (table_id) REFERENCES _table (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categorie ADD image VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP updated_at, DROP image_name, DROP image_original_name, DROP image_mime_type, DROP image_size, DROP image_dimensions');
        //$this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DECFF285C');
    }
}
