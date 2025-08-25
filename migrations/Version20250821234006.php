<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821234006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE administrador (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, fecha_creacion DATETIME NOT NULL, rol JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE servicio (id INT AUTO_INCREMENT NOT NULL, administrador_id INT NOT NULL, nombre VARCHAR(255) NOT NULL, descripcion LONGTEXT NOT NULL, banner_url VARCHAR(255) DEFAULT NULL, dias_trabajo JSON NOT NULL, horarios_disponibles JSON NOT NULL, INDEX IDX_CB86F22A48DFEBB7 (administrador_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE turno (id INT AUTO_INCREMENT NOT NULL, servicio_id INT NOT NULL, usuario_temporal_id INT DEFAULT NULL, fecha DATE NOT NULL, hora TIME NOT NULL, estado VARCHAR(20) NOT NULL, usuario_email VARCHAR(255) DEFAULT NULL, INDEX IDX_E797676271CAA3E7 (servicio_id), INDEX IDX_E79767625C53C0E6 (usuario_temporal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_temporal (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE servicio ADD CONSTRAINT FK_CB86F22A48DFEBB7 FOREIGN KEY (administrador_id) REFERENCES administrador (id)');
        $this->addSql('ALTER TABLE turno ADD CONSTRAINT FK_E797676271CAA3E7 FOREIGN KEY (servicio_id) REFERENCES servicio (id)');
        $this->addSql('ALTER TABLE turno ADD CONSTRAINT FK_E79767625C53C0E6 FOREIGN KEY (usuario_temporal_id) REFERENCES usuario_temporal (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE servicio DROP FOREIGN KEY FK_CB86F22A48DFEBB7');
        $this->addSql('ALTER TABLE turno DROP FOREIGN KEY FK_E797676271CAA3E7');
        $this->addSql('ALTER TABLE turno DROP FOREIGN KEY FK_E79767625C53C0E6');
        $this->addSql('DROP TABLE administrador');
        $this->addSql('DROP TABLE servicio');
        $this->addSql('DROP TABLE turno');
        $this->addSql('DROP TABLE usuario_temporal');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
