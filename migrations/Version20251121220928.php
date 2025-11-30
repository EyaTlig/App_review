<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121220928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE business (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, phone VARCHAR(50) NOT NULL, website VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, owner_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_8D36E387E3C61F9 (owner_id), INDEX IDX_8D36E3812469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE business_photo (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, business_id INT NOT NULL, INDEX IDX_6A5663A7A89DB457 (business_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE favorite_business (id INT AUTO_INCREMENT NOT NULL, added_at DATETIME NOT NULL, user_id INT DEFAULT NULL, business_id INT DEFAULT NULL, INDEX IDX_A28476C9A76ED395 (user_id), INDEX IDX_A28476C9A89DB457 (business_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE like_review (id INT AUTO_INCREMENT NOT NULL, liked_at DATETIME NOT NULL, user_id INT DEFAULT NULL, review_id INT DEFAULT NULL, INDEX IDX_479A9234A76ED395 (user_id), INDEX IDX_479A92343E2E969B (review_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(200) NOT NULL, message LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE report_review (id INT AUTO_INCREMENT NOT NULL, reason LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, review_id INT DEFAULT NULL, INDEX IDX_50B05459A76ED395 (user_id), INDEX IDX_50B054593E2E969B (review_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, rating INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, is_edited TINYINT(1) NOT NULL, user_id INT DEFAULT NULL, business_id INT DEFAULT NULL, INDEX IDX_794381C6A76ED395 (user_id), INDEX IDX_794381C6A89DB457 (business_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE review_photo (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, review_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_739A8033E2E969B (review_id), INDEX IDX_739A803A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE business ADD CONSTRAINT FK_8D36E387E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE business ADD CONSTRAINT FK_8D36E3812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE business_photo ADD CONSTRAINT FK_6A5663A7A89DB457 FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE favorite_business ADD CONSTRAINT FK_A28476C9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE favorite_business ADD CONSTRAINT FK_A28476C9A89DB457 FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE like_review ADD CONSTRAINT FK_479A9234A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE like_review ADD CONSTRAINT FK_479A92343E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE report_review ADD CONSTRAINT FK_50B05459A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE report_review ADD CONSTRAINT FK_50B054593E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A89DB457 FOREIGN KEY (business_id) REFERENCES business (id)');
        $this->addSql('ALTER TABLE review_photo ADD CONSTRAINT FK_739A8033E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE review_photo ADD CONSTRAINT FK_739A803A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE business DROP FOREIGN KEY FK_8D36E387E3C61F9');
        $this->addSql('ALTER TABLE business DROP FOREIGN KEY FK_8D36E3812469DE2');
        $this->addSql('ALTER TABLE business_photo DROP FOREIGN KEY FK_6A5663A7A89DB457');
        $this->addSql('ALTER TABLE favorite_business DROP FOREIGN KEY FK_A28476C9A76ED395');
        $this->addSql('ALTER TABLE favorite_business DROP FOREIGN KEY FK_A28476C9A89DB457');
        $this->addSql('ALTER TABLE like_review DROP FOREIGN KEY FK_479A9234A76ED395');
        $this->addSql('ALTER TABLE like_review DROP FOREIGN KEY FK_479A92343E2E969B');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE report_review DROP FOREIGN KEY FK_50B05459A76ED395');
        $this->addSql('ALTER TABLE report_review DROP FOREIGN KEY FK_50B054593E2E969B');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A89DB457');
        $this->addSql('ALTER TABLE review_photo DROP FOREIGN KEY FK_739A8033E2E969B');
        $this->addSql('ALTER TABLE review_photo DROP FOREIGN KEY FK_739A803A76ED395');
        $this->addSql('DROP TABLE business');
        $this->addSql('DROP TABLE business_photo');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE favorite_business');
        $this->addSql('DROP TABLE like_review');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE report_review');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE review_photo');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
