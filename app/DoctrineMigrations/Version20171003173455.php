<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171003173455 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE groups (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, link VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, is_comment TINYINT(1) NOT NULL, subscribers INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, gain VARCHAR(255) DEFAULT NULL, expense VARCHAR(255) DEFAULT NULL, is_verify TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, code VARCHAR(255) NOT NULL, social CHAR(2) NOT NULL, visitors INT UNSIGNED DEFAULT 0 NOT NULL, group_avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F06D3970989D9B62 (slug), INDEX IDX_F06D3970A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D3970A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE groups');
    }
}
