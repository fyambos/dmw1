<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221205141524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket ADD reporter_id INT DEFAULT NULL, ADD assignee_id INT DEFAULT NULL, ADD assignee VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA359EC7D60 FOREIGN KEY (assignee_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3E1CFE6F5 ON ticket (reporter_id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA359EC7D60 ON ticket (assignee_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3E1CFE6F5');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA359EC7D60');
        $this->addSql('DROP INDEX IDX_97A0ADA3E1CFE6F5 ON ticket');
        $this->addSql('DROP INDEX IDX_97A0ADA359EC7D60 ON ticket');
        $this->addSql('ALTER TABLE ticket DROP reporter_id, DROP assignee_id, DROP assignee');
    }
}
