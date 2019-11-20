<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191114160006 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE commande_produit (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, commande_id INT NOT NULL, quantite INT NOT NULL, prix DOUBLE PRECISION NOT NULL, INDEX IDX_DF1E9E87F347EFB (produit_id), INDEX IDX_DF1E9E8782EA2E54 (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande_produit ADD CONSTRAINT FK_DF1E9E87F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE commande_produit ADD CONSTRAINT FK_DF1E9E8782EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE howard_access_token CHANGE token token VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE howard_refresh_token CHANGE token token VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE howard_auth_code CHANGE token token VARCHAR(180) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE commande_produit');
        $this->addSql('ALTER TABLE howard_access_token CHANGE token token VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE howard_auth_code CHANGE token token VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE howard_refresh_token CHANGE token token VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
