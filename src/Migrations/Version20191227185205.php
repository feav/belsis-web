<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191227185205 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sortie_caisse DROP FOREIGN KEY FK_B5579974B1E7706E');
        $this->addSql('ALTER TABLE sortie_caisse ADD CONSTRAINT FK_B5579974B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sortie_caisse DROP FOREIGN KEY FK_B5579974B1E7706E');
        $this->addSql('ALTER TABLE sortie_caisse ADD CONSTRAINT FK_B5579974B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
    }
}
