<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181121143527 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activite ADD site_id INT DEFAULT NULL, DROP site');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_B8755515F6BD1646 ON activite (site_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515F6BD1646');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP INDEX IDX_B8755515F6BD1646 ON activite');
        $this->addSql('ALTER TABLE activite ADD site VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, DROP site_id');
    }
}
