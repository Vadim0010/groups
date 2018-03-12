<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171006132224 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE groups CHANGE link link VARCHAR(100) NOT NULL, CHANGE slug slug VARCHAR(150) NOT NULL, CHANGE gain gain NUMERIC(10, 2) DEFAULT NULL, CHANGE expense expense NUMERIC(10, 2) DEFAULT NULL, CHANGE code code VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE groups CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE groups CHANGE link link VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE slug slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE gain gain VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE expense expense VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE code code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
