<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171122153458 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_contacts (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, skype VARCHAR(255) DEFAULT NULL, viber VARCHAR(255) DEFAULT NULL, telegram VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_D3CDF173A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_contacts ADD CONSTRAINT FK_D3CDF173A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE up_history ADD subscribers_before BIGINT UNSIGNED NOT NULL, DROP subscrivers_before, CHANGE subscribers_after subscribers_after BIGINT UNSIGNED NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_contacts');
        $this->addSql('ALTER TABLE up_history ADD subscrivers_before BIGINT UNSIGNED DEFAULT NULL, DROP subscribers_before, CHANGE subscribers_after subscribers_after BIGINT UNSIGNED DEFAULT NULL');
    }
}
