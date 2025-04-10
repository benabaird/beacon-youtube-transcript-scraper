<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220215627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE video (id INT AUTO_INCREMENT NOT NULL, video_id VARCHAR(11) NOT NULL, title VARCHAR(255) NOT NULL, published DATE NOT NULL, transcript LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_set (video_id INT NOT NULL, set_id INT NOT NULL, INDEX IDX_1C8DE0D829C1004E (video_id), INDEX IDX_1C8DE0D810FB0D18 (set_id), PRIMARY KEY(video_id, set_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE video_set ADD CONSTRAINT FK_1C8DE0D829C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_set ADD CONSTRAINT FK_1C8DE0D810FB0D18 FOREIGN KEY (set_id) REFERENCES `set` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video_set DROP FOREIGN KEY FK_1C8DE0D829C1004E');
        $this->addSql('ALTER TABLE video_set DROP FOREIGN KEY FK_1C8DE0D810FB0D18');
        $this->addSql('DROP TABLE video');
        $this->addSql('DROP TABLE video_set');
    }
}
