<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250929115041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE turno ADD CONSTRAINT FK_E797676271CAA3E7 FOREIGN KEY (servicio_id) REFERENCES servicio (id)');
        $this->addSql('ALTER TABLE turno ADD CONSTRAINT FK_E79767625C53C0E6 FOREIGN KEY (usuario_temporal_id) REFERENCES usuario_temporal (id)');
        $this->addSql('CREATE INDEX IDX_E797676271CAA3E7 ON turno (servicio_id)');
        $this->addSql('CREATE INDEX IDX_E79767625C53C0E6 ON turno (usuario_temporal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE turno DROP FOREIGN KEY FK_E797676271CAA3E7');
        $this->addSql('ALTER TABLE turno DROP FOREIGN KEY FK_E79767625C53C0E6');
        $this->addSql('DROP INDEX IDX_E797676271CAA3E7 ON turno');
        $this->addSql('DROP INDEX IDX_E79767625C53C0E6 ON turno');
    }
}
