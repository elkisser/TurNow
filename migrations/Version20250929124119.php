<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250929124119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE suscripcion CHANGE fecha_inicio fecha_inicio DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE fecha_fin fecha_fin DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE fecha_creacion fecha_creacion DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE activa activa TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE suscripcion CHANGE fecha_inicio fecha_inicio DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE fecha_fin fecha_fin DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE fecha_creacion fecha_creacion DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE activa activa TINYINT(1) NOT NULL');
    }
}
