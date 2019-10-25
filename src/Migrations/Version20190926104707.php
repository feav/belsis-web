<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190926104707 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE howard_access_token (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(180) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_A2B81E8C5F37A13B (token), INDEX IDX_A2B81E8C19EB6921 (client_id), INDEX IDX_A2B81E8CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE howard_client (id INT AUTO_INCREMENT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), INDEX IDX_8D93D649B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE howard_refresh_token (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(180) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_603C1D375F37A13B (token), INDEX IDX_603C1D3719EB6921 (client_id), INDEX IDX_603C1D37A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appareil (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, marque VARCHAR(255) NOT NULL, imei VARCHAR(15) NOT NULL, type VARCHAR(255) NOT NULL, num_serie VARCHAR(255) DEFAULT NULL, INDEX IDX_456A601AB1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE howard_auth_code (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(180) NOT NULL, redirect_uri LONGTEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_33C1262B5F37A13B (token), INDEX IDX_33C1262B19EB6921 (client_id), INDEX IDX_33C1262BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, body VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_post_user (blog_post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_E1B8590DA77FBEAF (blog_post_id), INDEX IDX_E1B8590DA76ED395 (user_id), PRIMARY KEY(blog_post_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_497DD634B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, modepaiement_id INT DEFAULT NULL, restaurant_id INT DEFAULT NULL, table_id INT DEFAULT NULL, user_id INT DEFAULT NULL, code VARCHAR(50) DEFAULT NULL, date DATETIME NOT NULL, etat VARCHAR(50) NOT NULL, INDEX IDX_6EEAA67D8CDA5193 (modepaiement_id), INDEX IDX_6EEAA67DB1E7706E (restaurant_id), INDEX IDX_6EEAA67DECFF285C (table_id), INDEX IDX_6EEAA67DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande_produit (commande_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_DF1E9E8782EA2E54 (commande_id), INDEX IDX_DF1E9E87F347EFB (produit_id), PRIMARY KEY(commande_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mode_paiement (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, nom VARCHAR(255) NOT NULL, code VARCHAR(20) NOT NULL, INDEX IDX_B2BB0E85B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT DEFAULT NULL, categorie_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prix INT NOT NULL, INDEX IDX_29A5EC27B1E7706E (restaurant_id), INDEX IDX_29A5EC27BCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit_stock (produit_id INT NOT NULL, stock_id INT NOT NULL, INDEX IDX_7BAA31F4F347EFB (produit_id), INDEX IDX_7BAA31F4DCD6110 (stock_id), PRIMARY KEY(produit_id, stock_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, devise VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sortie_caisse (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, date DATETIME NOT NULL, description VARCHAR(255) NOT NULL, montant INT NOT NULL, INDEX IDX_B5579974B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, nom VARCHAR(255) NOT NULL, quantite DOUBLE PRECISION NOT NULL, INDEX IDX_4B365660B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `table` (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, coord_x DOUBLE PRECISION NOT NULL, coord_y DOUBLE PRECISION NOT NULL, INDEX IDX_F6298F46B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user00 (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE howard_access_token ADD CONSTRAINT FK_A2B81E8C19EB6921 FOREIGN KEY (client_id) REFERENCES howard_client (id)');
        $this->addSql('ALTER TABLE howard_access_token ADD CONSTRAINT FK_A2B81E8CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE howard_refresh_token ADD CONSTRAINT FK_603C1D3719EB6921 FOREIGN KEY (client_id) REFERENCES howard_client (id)');
        $this->addSql('ALTER TABLE howard_refresh_token ADD CONSTRAINT FK_603C1D37A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE appareil ADD CONSTRAINT FK_456A601AB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE howard_auth_code ADD CONSTRAINT FK_33C1262B19EB6921 FOREIGN KEY (client_id) REFERENCES howard_client (id)');
        $this->addSql('ALTER TABLE howard_auth_code ADD CONSTRAINT FK_33C1262BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE blog_post_user ADD CONSTRAINT FK_E1B8590DA77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_post_user ADD CONSTRAINT FK_E1B8590DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE categorie ADD CONSTRAINT FK_497DD634B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D8CDA5193 FOREIGN KEY (modepaiement_id) REFERENCES mode_paiement (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DECFF285C FOREIGN KEY (table_id) REFERENCES `table` (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commande_produit ADD CONSTRAINT FK_DF1E9E8782EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande_produit ADD CONSTRAINT FK_DF1E9E87F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mode_paiement ADD CONSTRAINT FK_B2BB0E85B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE produit_stock ADD CONSTRAINT FK_7BAA31F4F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit_stock ADD CONSTRAINT FK_7BAA31F4DCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie_caisse ADD CONSTRAINT FK_B5579974B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE `table` ADD CONSTRAINT FK_F6298F46B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE howard_access_token DROP FOREIGN KEY FK_A2B81E8C19EB6921');
        $this->addSql('ALTER TABLE howard_refresh_token DROP FOREIGN KEY FK_603C1D3719EB6921');
        $this->addSql('ALTER TABLE howard_auth_code DROP FOREIGN KEY FK_33C1262B19EB6921');
        $this->addSql('ALTER TABLE howard_access_token DROP FOREIGN KEY FK_A2B81E8CA76ED395');
        $this->addSql('ALTER TABLE howard_refresh_token DROP FOREIGN KEY FK_603C1D37A76ED395');
        $this->addSql('ALTER TABLE howard_auth_code DROP FOREIGN KEY FK_33C1262BA76ED395');
        $this->addSql('ALTER TABLE blog_post_user DROP FOREIGN KEY FK_E1B8590DA76ED395');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('ALTER TABLE blog_post_user DROP FOREIGN KEY FK_E1B8590DA77FBEAF');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('ALTER TABLE commande_produit DROP FOREIGN KEY FK_DF1E9E8782EA2E54');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D8CDA5193');
        $this->addSql('ALTER TABLE commande_produit DROP FOREIGN KEY FK_DF1E9E87F347EFB');
        $this->addSql('ALTER TABLE produit_stock DROP FOREIGN KEY FK_7BAA31F4F347EFB');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B1E7706E');
        $this->addSql('ALTER TABLE appareil DROP FOREIGN KEY FK_456A601AB1E7706E');
        $this->addSql('ALTER TABLE categorie DROP FOREIGN KEY FK_497DD634B1E7706E');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DB1E7706E');
        $this->addSql('ALTER TABLE mode_paiement DROP FOREIGN KEY FK_B2BB0E85B1E7706E');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27B1E7706E');
        $this->addSql('ALTER TABLE sortie_caisse DROP FOREIGN KEY FK_B5579974B1E7706E');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660B1E7706E');
        $this->addSql('ALTER TABLE `table` DROP FOREIGN KEY FK_F6298F46B1E7706E');
        $this->addSql('ALTER TABLE produit_stock DROP FOREIGN KEY FK_7BAA31F4DCD6110');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DECFF285C');
        $this->addSql('DROP TABLE howard_access_token');
        $this->addSql('DROP TABLE howard_client');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE howard_refresh_token');
        $this->addSql('DROP TABLE appareil');
        $this->addSql('DROP TABLE howard_auth_code');
        $this->addSql('DROP TABLE blog_post');
        $this->addSql('DROP TABLE blog_post_user');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE commande_produit');
        $this->addSql('DROP TABLE mode_paiement');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE produit_stock');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP TABLE sortie_caisse');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE `table`');
        $this->addSql('DROP TABLE user00');
    }
}
