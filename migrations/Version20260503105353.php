<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503105353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE access_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, INDEX IDX_B6A2DD68A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, taxonomy_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, start DATE DEFAULT NULL, ends DATE DEFAULT NULL, INDEX IDX_161498D39557E6F6 (taxonomy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_species (card_id INT NOT NULL, species_id INT NOT NULL, INDEX IDX_F2157D644ACC9A20 (card_id), INDEX IDX_F2157D64B2A1D860 (species_id), PRIMARY KEY(card_id, species_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_user (card_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_61A0D4EB4ACC9A20 (card_id), INDEX IDX_61A0D4EBA76ED395 (user_id), PRIMARY KEY(card_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_sightings (card_id INT NOT NULL, sighting_id INT NOT NULL, INDEX IDX_D5B8896C4ACC9A20 (card_id), INDEX IDX_D5B8896C34964DD9 (sighting_id), PRIMARY KEY(card_id, sighting_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_template (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, category_id INT NOT NULL, INDEX IDX_2E51D1007E3C61F9 (owner_id), INDEX IDX_2E51D10012469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_template_species (card_template_id INT NOT NULL, species_id INT NOT NULL, INDEX IDX_65D054A9E20E5022 (card_template_id), INDEX IDX_65D054A9B2A1D860 (species_id), PRIMARY KEY(card_template_id, species_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detection (id INT AUTO_INCREMENT NOT NULL, species_id INT NOT NULL, device_id INT NOT NULL, confidence DOUBLE PRECISION NOT NULL, detected_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A35F1C6B2A1D860 (species_id), INDEX IDX_A35F1C694A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, installed_at DATE NOT NULL, api_key VARCHAR(255) NOT NULL, active TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE family (id INT AUTO_INCREMENT NOT NULL, tax_order_id INT DEFAULT NULL, taxonomy_id INT NOT NULL, scientific_name VARCHAR(255) NOT NULL, vernacular_name VARCHAR(255) DEFAULT NULL, INDEX IDX_A5E6215B6D0BCC86 (tax_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE genus (id INT AUTO_INCREMENT NOT NULL, family_id INT NOT NULL, taxonomy_id INT NOT NULL, scientific_name VARCHAR(255) NOT NULL, vernacular_name VARCHAR(255) DEFAULT NULL, INDEX IDX_38C5106EC35E566A (family_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, point POINT NOT NULL, radius DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, class_id INT DEFAULT NULL, taxonomy_id INT NOT NULL, scientific_name VARCHAR(255) NOT NULL, vernacular_name VARCHAR(255) DEFAULT NULL, INDEX IDX_F5299398EA000B10 (class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sighting (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, species_id INT NOT NULL, location_id INT DEFAULT NULL, place VARCHAR(255) DEFAULT NULL, date_time DATETIME NOT NULL, comment VARCHAR(512) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, coordinates TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_6E9336F4A76ED395 (user_id), INDEX IDX_6E9336F4B2A1D860 (species_id), INDEX IDX_6E9336F464D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE species (id INT AUTO_INCREMENT NOT NULL, genus_id INT DEFAULT NULL, direct_tax_class_id INT DEFAULT NULL, taxonomy_id INT NOT NULL, scientific_name VARCHAR(255) NOT NULL, vernacular_name VARCHAR(255) DEFAULT NULL, swedish_prominence VARCHAR(255) DEFAULT NULL, INDEX IDX_A50FF71285C4074C (genus_id), INDEX IDX_A50FF7124B8F1D68 (direct_tax_class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tax_class (id INT AUTO_INCREMENT NOT NULL, taxonomy_id INT DEFAULT NULL, scientific_name VARCHAR(255) NOT NULL, vernacular_name VARCHAR(255) DEFAULT NULL, icon VARCHAR(128) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', username VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, bildspel_integration TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_species (user_id INT NOT NULL, species_id INT NOT NULL, INDEX IDX_FD02E918A76ED395 (user_id), INDEX IDX_FD02E918B2A1D860 (species_id), PRIMARY KEY(user_id, species_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE access_token ADD CONSTRAINT FK_B6A2DD68A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D39557E6F6 FOREIGN KEY (taxonomy_id) REFERENCES tax_class (id)');
        $this->addSql('ALTER TABLE card_species ADD CONSTRAINT FK_F2157D644ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_species ADD CONSTRAINT FK_F2157D64B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_user ADD CONSTRAINT FK_61A0D4EB4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_user ADD CONSTRAINT FK_61A0D4EBA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_sightings ADD CONSTRAINT FK_D5B8896C4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_sightings ADD CONSTRAINT FK_D5B8896C34964DD9 FOREIGN KEY (sighting_id) REFERENCES sighting (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_template ADD CONSTRAINT FK_2E51D1007E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE card_template ADD CONSTRAINT FK_2E51D10012469DE2 FOREIGN KEY (category_id) REFERENCES tax_class (id)');
        $this->addSql('ALTER TABLE card_template_species ADD CONSTRAINT FK_65D054A9E20E5022 FOREIGN KEY (card_template_id) REFERENCES card_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_template_species ADD CONSTRAINT FK_65D054A9B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE detection ADD CONSTRAINT FK_A35F1C6B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id)');
        $this->addSql('ALTER TABLE detection ADD CONSTRAINT FK_A35F1C694A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id)');
        $this->addSql('ALTER TABLE family ADD CONSTRAINT FK_A5E6215B6D0BCC86 FOREIGN KEY (tax_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE genus ADD CONSTRAINT FK_38C5106EC35E566A FOREIGN KEY (family_id) REFERENCES family (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398EA000B10 FOREIGN KEY (class_id) REFERENCES tax_class (id)');
        $this->addSql('ALTER TABLE sighting ADD CONSTRAINT FK_6E9336F4A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE sighting ADD CONSTRAINT FK_6E9336F4B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id)');
        $this->addSql('ALTER TABLE sighting ADD CONSTRAINT FK_6E9336F464D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE species ADD CONSTRAINT FK_A50FF71285C4074C FOREIGN KEY (genus_id) REFERENCES genus (id)');
        $this->addSql('ALTER TABLE species ADD CONSTRAINT FK_A50FF7124B8F1D68 FOREIGN KEY (direct_tax_class_id) REFERENCES tax_class (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_species ADD CONSTRAINT FK_FD02E918A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_species ADD CONSTRAINT FK_FD02E918B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_token DROP FOREIGN KEY FK_B6A2DD68A76ED395');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D39557E6F6');
        $this->addSql('ALTER TABLE card_species DROP FOREIGN KEY FK_F2157D644ACC9A20');
        $this->addSql('ALTER TABLE card_species DROP FOREIGN KEY FK_F2157D64B2A1D860');
        $this->addSql('ALTER TABLE card_user DROP FOREIGN KEY FK_61A0D4EB4ACC9A20');
        $this->addSql('ALTER TABLE card_user DROP FOREIGN KEY FK_61A0D4EBA76ED395');
        $this->addSql('ALTER TABLE card_sightings DROP FOREIGN KEY FK_D5B8896C4ACC9A20');
        $this->addSql('ALTER TABLE card_sightings DROP FOREIGN KEY FK_D5B8896C34964DD9');
        $this->addSql('ALTER TABLE card_template DROP FOREIGN KEY FK_2E51D1007E3C61F9');
        $this->addSql('ALTER TABLE card_template DROP FOREIGN KEY FK_2E51D10012469DE2');
        $this->addSql('ALTER TABLE card_template_species DROP FOREIGN KEY FK_65D054A9E20E5022');
        $this->addSql('ALTER TABLE card_template_species DROP FOREIGN KEY FK_65D054A9B2A1D860');
        $this->addSql('ALTER TABLE detection DROP FOREIGN KEY FK_A35F1C6B2A1D860');
        $this->addSql('ALTER TABLE detection DROP FOREIGN KEY FK_A35F1C694A4C7D4');
        $this->addSql('ALTER TABLE family DROP FOREIGN KEY FK_A5E6215B6D0BCC86');
        $this->addSql('ALTER TABLE genus DROP FOREIGN KEY FK_38C5106EC35E566A');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398EA000B10');
        $this->addSql('ALTER TABLE sighting DROP FOREIGN KEY FK_6E9336F4A76ED395');
        $this->addSql('ALTER TABLE sighting DROP FOREIGN KEY FK_6E9336F4B2A1D860');
        $this->addSql('ALTER TABLE sighting DROP FOREIGN KEY FK_6E9336F464D218E');
        $this->addSql('ALTER TABLE species DROP FOREIGN KEY FK_A50FF71285C4074C');
        $this->addSql('ALTER TABLE species DROP FOREIGN KEY FK_A50FF7124B8F1D68');
        $this->addSql('ALTER TABLE user_species DROP FOREIGN KEY FK_FD02E918A76ED395');
        $this->addSql('ALTER TABLE user_species DROP FOREIGN KEY FK_FD02E918B2A1D860');
        $this->addSql('DROP TABLE access_token');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE card_species');
        $this->addSql('DROP TABLE card_user');
        $this->addSql('DROP TABLE card_sightings');
        $this->addSql('DROP TABLE card_template');
        $this->addSql('DROP TABLE card_template_species');
        $this->addSql('DROP TABLE detection');
        $this->addSql('DROP TABLE device');
        $this->addSql('DROP TABLE family');
        $this->addSql('DROP TABLE genus');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE sighting');
        $this->addSql('DROP TABLE species');
        $this->addSql('DROP TABLE tax_class');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_species');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
