<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241028123409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mouvement_caisse DROP FOREIGN KEY FK_C8E3DDFE3E0CACFE');
        $this->addSql('DROP INDEX IDX_C8E3DDFE3E0CACFE ON mouvement_caisse');
        $this->addSql('ALTER TABLE mouvement_caisse DROP sortie_stock_id');
        $this->addSql('ALTER TABLE mouvement_collaborateur DROP FOREIGN KEY FK_C47441A23E0CACFE');
        $this->addSql('DROP INDEX IDX_C47441A23E0CACFE ON mouvement_collaborateur');
        $this->addSql('ALTER TABLE mouvement_collaborateur DROP sortie_stock_id');
        $this->addSql('ALTER TABLE sortie_stock DROP origine');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mouvement_caisse ADD sortie_stock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mouvement_caisse ADD CONSTRAINT FK_C8E3DDFE3E0CACFE FOREIGN KEY (sortie_stock_id) REFERENCES sortie_stock (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_C8E3DDFE3E0CACFE ON mouvement_caisse (sortie_stock_id)');
        $this->addSql('ALTER TABLE mouvement_collaborateur ADD sortie_stock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mouvement_collaborateur ADD CONSTRAINT FK_C47441A23E0CACFE FOREIGN KEY (sortie_stock_id) REFERENCES sortie_stock (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_C47441A23E0CACFE ON mouvement_collaborateur (sortie_stock_id)');
        $this->addSql('ALTER TABLE sortie_stock ADD origine VARCHAR(50) DEFAULT NULL');
    }
}
