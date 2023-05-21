<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230521193502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Transaction (id INT AUTO_INCREMENT NOT NULL, giver_id INT DEFAULT NULL, receiver_id INT DEFAULT NULL, amount INT NOT NULL, reason VARCHAR(255) NOT NULL, wereLastMriqs TINYINT(1) NOT NULL, reaction VARCHAR(255) DEFAULT NULL, mriqChannelMessageTs VARCHAR(255) DEFAULT NULL, mriqSlackbotMessageTs VARCHAR(255) DEFAULT NULL, INDEX IDX_F4AB8A0675BD1D29 (giver_id), INDEX IDX_F4AB8A06CD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE User (id INT AUTO_INCREMENT NOT NULL, slackId VARCHAR(10) NOT NULL, slackName VARCHAR(255) NOT NULL, slackRealName VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, totalGiven INT NOT NULL, totalEarned INT NOT NULL, toGive INT NOT NULL, UNIQUE INDEX UNIQ_2DA179779DFDA935 (slackId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Transaction ADD CONSTRAINT FK_F4AB8A0675BD1D29 FOREIGN KEY (giver_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Transaction ADD CONSTRAINT FK_F4AB8A06CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES User (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Transaction DROP FOREIGN KEY FK_F4AB8A0675BD1D29');
        $this->addSql('ALTER TABLE Transaction DROP FOREIGN KEY FK_F4AB8A06CD53EDB6');
        $this->addSql('DROP TABLE Transaction');
        $this->addSql('DROP TABLE User');
    }
}
