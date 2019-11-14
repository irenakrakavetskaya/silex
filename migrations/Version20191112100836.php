<?php

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20191112100836 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $books = $schema->createTable('books');
        $books->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $books->addColumn('title', 'string', ['length' => 255])->setNotnull(true);
        $books->addColumn('description', 'text')->setNotnull(false);
        $books->setPrimaryKey(['id']);
        $books->addIndex(['title']);

        $authors = $schema->createTable('authors');
        $authors->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $authors->addColumn('name', 'text')->setNotnull(false);
        $authors->addColumn('surname', 'text')->setNotnull(false);
        $authors->setPrimaryKey(['id']);

        $books_authors = $schema->createTable('books_authors');
        $books_authors->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $books_authors->addColumn('book_id', 'integer', ['unsigned' => true])->setNotnull(true);
        $books_authors->addColumn('author_id', 'integer', ['unsigned' => true])->setNotnull(true);
        $books_authors->setPrimaryKey(['id']);
        $books_authors->addIndex(['book_id'])->addIndex(['author_id']);
        $books_authors->addForeignKeyConstraint('books', ['book_id'], ['id']);
        $books_authors->addForeignKeyConstraint('authors', ['author_id'], ['id']);

        $users = $schema->createTable('users');
        $users->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $users->addColumn('username', 'string', ['length' => 255]);//)->setNotnull(true);
        $users->addColumn('password', 'string', ['length' => 255]);//->setNotnull(true);
        $users->addColumn('token', 'string', ['length' => 255]);//->setNotnull(true);
        $users->addColumn('phone', 'text')->setNotnull(true);
        $users->addColumn('address', 'text')->setNotnull(true);
        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['username']);
        $users->addIndex(['password'])->addIndex(['token']);

        $orders = $schema->createTable('orders');
        $orders->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $orders->addColumn('user_id', 'integer', ['unsigned' => true])->setNotnull(true);
        $orders->addColumn('status', 'smallint', [
            'values' => [0, 1, 2],
            'default' => '0',
            'notnull' => true])
            ->setNotnull(true);
        $orders->addColumn('datetime', 'datetimetz')->setNotnull(false);
        $orders->setPrimaryKey(['id']);
        $orders->addIndex(['user_id'])->addIndex(['status'])->addIndex(['datetime']);
        $orders->addForeignKeyConstraint('users', ['user_id'], ['id']);

        $books_orders = $schema->createTable('books_orders');
        $books_orders->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $books_orders->addColumn('book_id', 'integer', ['unsigned' => true])->setNotnull(true);
        $books_orders->addColumn('order_id', 'integer', ['unsigned' => true])->setNotnull(true);
        $books_orders->setPrimaryKey(['id']);
        $books_orders->addIndex(['book_id'])->addIndex(['order_id']);
        $books_orders->addForeignKeyConstraint('books', ['book_id'], ['id']);
        $books_orders->addForeignKeyConstraint('orders', ['order_id'], ['id']);


    }

    /**
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('books');
        $schema->dropTable('authors');
        $schema->dropTable('books_authors');

        $schema->dropTable('users');
        $schema->dropTable('orders');
        $schema->dropTable('books_orders');

    }
}
