<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20120406191145 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("CREATE TABLE Email (id INT AUTO_INCREMENT NOT NULL, member_email VARCHAR(100) DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_2653537085B3E987 (member_email), PRIMARY KEY(id)) ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Email ADD CONSTRAINT FK_2653537085B3E987 FOREIGN KEY (member_email) REFERENCES Member(email)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("DROP TABLE Email");
    }
}
