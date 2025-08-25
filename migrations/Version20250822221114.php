<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250822221114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE servicio DROP FOREIGN KEY FK_CB86F22A48DFEBB7');
        $this->addSql('ALTER TABLE servicio ADD CONSTRAINT FK_CB86F22A48DFEBB7 FOREIGN KEY (administrador_id) REFERENCES `usuario` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE servicio DROP FOREIGN KEY FK_CB86F22A48DFEBB7');
        $this->addSql('ALTER TABLE servicio ADD CONSTRAINT FK_CB86F22A48DFEBB7 FOREIGN KEY (administrador_id) REFERENCES administrador (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
