<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200825184747 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create transaction table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, destination_id INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, commission_percent DOUBLE PRECISION NOT NULL, commission_amount DOUBLE PRECISION NOT NULL, currency VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX IDX_723705D1953C1C61 (source_id), INDEX IDX_723705D1816C6140 (destination_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1953C1C61 FOREIGN KEY (source_id) REFERENCES wallet (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1816C6140 FOREIGN KEY (destination_id) REFERENCES wallet (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE transaction');
    }
}
