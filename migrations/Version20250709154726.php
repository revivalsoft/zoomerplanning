<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250704125053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE calendar (id INT AUTO_INCREMENT NOT NULL, region VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(30) NOT NULL, visible TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE dependency (id INT AUTO_INCREMENT NOT NULL, from_gtask_id INT NOT NULL, to_gtask_id INT NOT NULL, INDEX IDX_2F585505E93948A9 (from_gtask_id), INDEX IDX_2F585505628F369D (to_gtask_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gestion (id INT AUTO_INCREMENT NOT NULL, ressource_id INT DEFAULT NULL, plage_id INT DEFAULT NULL, line SMALLINT NOT NULL, date DATE NOT NULL, note VARCHAR(100) DEFAULT NULL, INDEX IDX_DE0255B0FC6CD52A (ressource_id), INDEX IDX_DE0255B0F82604D9 (plage_id), UNIQUE INDEX unique_cell (ressource_id, line, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE groupe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(30) NOT NULL, visible TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gtask (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status VARCHAR(20) NOT NULL, INDEX IDX_9E0A5879166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gtask_dependencies (gtask_id INT NOT NULL, depends_on_gtask_id INT NOT NULL, INDEX IDX_8A8FA864AFD6A833 (gtask_id), INDEX IDX_8A8FA86464814EB7 (depends_on_gtask_id), PRIMARY KEY(gtask_id, depends_on_gtask_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gtask_raci (id INT AUTO_INCREMENT NOT NULL, gtask_id INT NOT NULL, ressource_id INT NOT NULL, role VARCHAR(1) NOT NULL, INDEX IDX_96D2002EAFD6A833 (gtask_id), INDEX IDX_96D2002EFC6CD52A (ressource_id), UNIQUE INDEX unique_assignment (gtask_id, ressource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE gtask_resource (id INT AUTO_INCREMENT NOT NULL, gtask_id INT NOT NULL, ressource_id INT NOT NULL, INDEX IDX_CB8E04F6AFD6A833 (gtask_id), INDEX IDX_CB8E04F6FC6CD52A (ressource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE hierarchic (id INT AUTO_INCREMENT NOT NULL, groupe_id INT NOT NULL, position LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE journal (id INT AUTO_INCREMENT NOT NULL, administrateur_id INT DEFAULT NULL, action_type SMALLINT NOT NULL, action_date DATETIME NOT NULL, id_res INT NOT NULL, id_sigle INT NOT NULL, note VARCHAR(255) DEFAULT NULL, ligne SMALLINT NOT NULL, date_sigle DATE NOT NULL, mail TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_C1A7E74D7EE5403C (administrateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE key_result (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, objective_id INT NOT NULL, title VARCHAR(255) NOT NULL, initial_value DOUBLE PRECISION NOT NULL, target_value DOUBLE PRECISION NOT NULL, current_value DOUBLE PRECISION NOT NULL, last_update DATETIME NOT NULL, is_achieved TINYINT(1) NOT NULL, INDEX IDX_1853204FA76ED395 (user_id), INDEX IDX_1853204F73484933 (objective_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE mailer_event (id INT AUTO_INCREMENT NOT NULL, event VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, admin VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification_destinataire (id INT AUTO_INCREMENT NOT NULL, notification_id INT NOT NULL, ressource_id INT NOT NULL, vue TINYINT(1) NOT NULL, date_vue DATETIME DEFAULT NULL, INDEX IDX_FFA715A7EF1A9D84 (notification_id), INDEX IDX_FFA715A7FC6CD52A (ressource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification_message (id INT AUTO_INCREMENT NOT NULL, auteur_id INT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, access_token VARCHAR(32) NOT NULL, UNIQUE INDEX UNIQ_A3A3BAC8B6A2DD68 (access_token), INDEX IDX_A3A3BAC860BB6FE6 (auteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE objective (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, is_closed TINYINT(1) NOT NULL, is_public TINYINT(1) NOT NULL, INDEX IDX_B996F101A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE param (id INT AUTO_INCREMENT NOT NULL, calendar SMALLINT DEFAULT NULL, public SMALLINT NOT NULL, admin SMALLINT NOT NULL, dates TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE plage (id INT AUTO_INCREMENT NOT NULL, sigle VARCHAR(4) NOT NULL, legende VARCHAR(30) NOT NULL, absence TINYINT(1) NOT NULL, heure INT DEFAULT NULL, minute INT DEFAULT NULL, couleurtexte VARCHAR(7) NOT NULL, couleurfond VARCHAR(7) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE plage_categorie (plage_id INT NOT NULL, categorie_id INT NOT NULL, INDEX IDX_3EE06436F82604D9 (plage_id), INDEX IDX_3EE06436BCF5E72D (categorie_id), PRIMARY KEY(plage_id, categorie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_public TINYINT(1) NOT NULL, INDEX IDX_2FB3D0EEA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE push_subscriptions (id INT AUTO_INCREMENT NOT NULL, ressource_id INT NOT NULL, endpoint VARCHAR(255) NOT NULL, public_key VARCHAR(255) NOT NULL, auth_token VARCHAR(255) NOT NULL, content_encoding VARCHAR(20) NOT NULL, p256dh VARCHAR(255) NOT NULL, auth VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_3FEC449DFC6CD52A (ressource_id), UNIQUE INDEX endpoint_unique (endpoint), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ressource (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(15) NOT NULL, fonction VARCHAR(30) NOT NULL, email VARCHAR(100) NOT NULL, matricule VARCHAR(100) DEFAULT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ressource_groupe (ressource_id INT NOT NULL, groupe_id INT NOT NULL, INDEX IDX_EEF85F9CFC6CD52A (ressource_id), INDEX IDX_EEF85F9C7A45358C (groupe_id), PRIMARY KEY(ressource_id, groupe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE admin_groupe (ressource_id INT NOT NULL, groupe_id INT NOT NULL, INDEX IDX_5971BDD8FC6CD52A (ressource_id), INDEX IDX_5971BDD87A45358C (groupe_id), PRIMARY KEY(ressource_id, groupe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ressource_token (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, ressource_id INT DEFAULT NULL, admin_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_CBA325BF5F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, task_column VARCHAR(20) NOT NULL, position INT NOT NULL, INDEX IDX_527EDB25A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dependency ADD CONSTRAINT FK_2F585505E93948A9 FOREIGN KEY (from_gtask_id) REFERENCES gtask (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dependency ADD CONSTRAINT FK_2F585505628F369D FOREIGN KEY (to_gtask_id) REFERENCES gtask (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gestion ADD CONSTRAINT FK_DE0255B0FC6CD52A FOREIGN KEY (ressource_id) REFERENCES ressource (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gestion ADD CONSTRAINT FK_DE0255B0F82604D9 FOREIGN KEY (plage_id) REFERENCES plage (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask ADD CONSTRAINT FK_9E0A5879166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_dependencies ADD CONSTRAINT FK_8A8FA864AFD6A833 FOREIGN KEY (gtask_id) REFERENCES gtask (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_dependencies ADD CONSTRAINT FK_8A8FA86464814EB7 FOREIGN KEY (depends_on_gtask_id) REFERENCES gtask (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_raci ADD CONSTRAINT FK_96D2002EAFD6A833 FOREIGN KEY (gtask_id) REFERENCES gtask (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_raci ADD CONSTRAINT FK_96D2002EFC6CD52A FOREIGN KEY (ressource_id) REFERENCES ressource (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_resource ADD CONSTRAINT FK_CB8E04F6AFD6A833 FOREIGN KEY (gtask_id) REFERENCES gtask (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_resource ADD CONSTRAINT FK_CB8E04F6FC6CD52A FOREIGN KEY (ressource_id) REFERENCES ressource (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE journal ADD CONSTRAINT FK_C1A7E74D7EE5403C FOREIGN KEY (administrateur_id) REFERENCES ressource (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE key_result ADD CONSTRAINT FK_1853204FA76ED395 FOREIGN KEY (user_id) REFERENCES ressource (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE key_result ADD CONSTRAINT FK_1853204F73484933 FOREIGN KEY (objective_id) REFERENCES objective (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_destinataire ADD CONSTRAINT FK_FFA715A7EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification_message (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_destinataire ADD CONSTRAINT FK_FFA715A7FC6CD52A FOREIGN KEY (ressource_id) REFERENCES ressource (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_message ADD CONSTRAINT FK_A3A3BAC860BB6FE6 FOREIGN KEY (auteur_id) REFERENCES ressource (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objective ADD CONSTRAINT FK_B996F101A76ED395 FOREIGN KEY (user_id) REFERENCES ressource (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plage_categorie ADD CONSTRAINT FK_3EE06436F82604D9 FOREIGN KEY (plage_id) REFERENCES plage (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plage_categorie ADD CONSTRAINT FK_3EE06436BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEA76ED395 FOREIGN KEY (user_id) REFERENCES ressource (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE push_subscriptions ADD CONSTRAINT FK_3FEC449DFC6CD52A FOREIGN KEY (ressource_id) REFERENCES ressource (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ressource_groupe ADD CONSTRAINT FK_EEF85F9CFC6CD52A FOREIGN KEY (ressource_id) REFERENCES ressource (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ressource_groupe ADD CONSTRAINT FK_EEF85F9C7A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_groupe ADD CONSTRAINT FK_5971BDD8FC6CD52A FOREIGN KEY (ressource_id) REFERENCES ressource (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_groupe ADD CONSTRAINT FK_5971BDD87A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task ADD CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id) REFERENCES ressource (id)
        SQL);

        $this->addSql("
        INSERT INTO calendar (id, region) VALUES
        (1, 'Alsace-Moselle'),
        (2, 'Guadeloupe'),
        (3, 'Guyane'),
        (4, 'Martinique'),
        (5, 'Mayotte'),
        (6, 'Nouvelle-Calédonie'),
        (7, 'La Réunion'),
        (8, 'Polynésie Française'),
        (9, 'Saint-Barthélémy'),
        (10, 'Saint-Martin'),
        (11, 'Wallis-et-Futuna'),
        (12, 'Saint-Pierre-et-Miquelon')
    ");

        $this->addSql("
        INSERT INTO `param` (`id`, `calendar`, `public`, `admin`, `dates`) VALUES
        (1, 0, 3, 3, 1)");

        $this->addSql("
        INSERT INTO ressource (id, nom, fonction, email, matricule, password, roles) VALUES
        (
        1,
        'Admin',
        'Développeur',
        'admin@gmail.com',
        NULL,
        '\$2y\$13\$Pa9YFpL6nmQYsG4maUnnfer8nhzL0x0oru9cyFdJp59WyloISITtS',
        '[\"ROLE_SUPER_ADMIN\",\"ROLE_ADMIN\"]'
        )
    ");
    }

    public function down(Schema $schema): void
    {

        $this->addSql("DELETE FROM param WHERE id = 1");
        $this->addSql("DELETE FROM ressource WHERE id = 1");
        $this->addSql("DELETE FROM calendar WHERE id BETWEEN 1 AND 12");
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE dependency DROP FOREIGN KEY FK_2F585505E93948A9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dependency DROP FOREIGN KEY FK_2F585505628F369D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gestion DROP FOREIGN KEY FK_DE0255B0FC6CD52A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gestion DROP FOREIGN KEY FK_DE0255B0F82604D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask DROP FOREIGN KEY FK_9E0A5879166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_dependencies DROP FOREIGN KEY FK_8A8FA864AFD6A833
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_dependencies DROP FOREIGN KEY FK_8A8FA86464814EB7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_raci DROP FOREIGN KEY FK_96D2002EAFD6A833
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_raci DROP FOREIGN KEY FK_96D2002EFC6CD52A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_resource DROP FOREIGN KEY FK_CB8E04F6AFD6A833
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE gtask_resource DROP FOREIGN KEY FK_CB8E04F6FC6CD52A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE journal DROP FOREIGN KEY FK_C1A7E74D7EE5403C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE key_result DROP FOREIGN KEY FK_1853204FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE key_result DROP FOREIGN KEY FK_1853204F73484933
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_destinataire DROP FOREIGN KEY FK_FFA715A7EF1A9D84
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_destinataire DROP FOREIGN KEY FK_FFA715A7FC6CD52A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_message DROP FOREIGN KEY FK_A3A3BAC860BB6FE6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objective DROP FOREIGN KEY FK_B996F101A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plage_categorie DROP FOREIGN KEY FK_3EE06436F82604D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plage_categorie DROP FOREIGN KEY FK_3EE06436BCF5E72D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE push_subscriptions DROP FOREIGN KEY FK_3FEC449DFC6CD52A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ressource_groupe DROP FOREIGN KEY FK_EEF85F9CFC6CD52A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ressource_groupe DROP FOREIGN KEY FK_EEF85F9C7A45358C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_groupe DROP FOREIGN KEY FK_5971BDD8FC6CD52A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_groupe DROP FOREIGN KEY FK_5971BDD87A45358C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task DROP FOREIGN KEY FK_527EDB25A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE calendar
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categorie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE dependency
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gestion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE groupe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gtask
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gtask_dependencies
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gtask_raci
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE gtask_resource
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE hierarchic
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE journal
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE key_result
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mailer_event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification_destinataire
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification_message
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE objective
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE param
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE plage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE plage_categorie
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE project
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE push_subscriptions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ressource
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ressource_groupe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE admin_groupe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ressource_token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE task
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
