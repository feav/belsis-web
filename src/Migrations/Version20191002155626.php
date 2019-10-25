<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191002155626 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE produit ADD categorie_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)');
        $this->addSql('ALTER TABLE howard_access_token CHANGE token token VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE howard_refresh_token CHANGE token token VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE howard_auth_code CHANGE token token VARCHAR(180) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE howard_access_token CHANGE token token VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE howard_auth_code CHANGE token token VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE howard_refresh_token CHANGE token token VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('DROP INDEX IDX_29A5EC27BCF5E72D ON produit');
        $this->addSql('ALTER TABLE produit DROP categorie_id');
    }
}
