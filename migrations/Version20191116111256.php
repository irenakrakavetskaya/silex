<?php

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20191116111256 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $users = $schema->createTable('users');
        $users->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $users->addColumn('username', 'string', ['length' => 255])->setNotnull(true);
        $users->addColumn('password', 'string', ['length' => 255])->setNotnull(false);
        $users->addColumn('token', 'text')->setNotnull(false);
        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['username']);
        $users->addIndex(['password']);
    }

    public function postUp(Schema $schema)
    {
        $user = 'test';
        $pass = password_hash('123456', PASSWORD_DEFAULT);
        $data = ['username' => $user, 'password' => $pass];
        $this->connection->insert('users', $data);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('users');
    }
}
