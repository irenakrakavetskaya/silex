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
        $books_authors->addColumn('book_id', 'integer', ['unsigned' => true])->setNotnull(false);
        $books_authors->addColumn('author_id', 'integer', ['unsigned' => true])->setNotnull(true);
        $books_authors->setPrimaryKey(['id']);
        $books_authors->addIndex(['book_id'])->addIndex(['author_id'])->addUniqueIndex(['book_id','author_id']);;
        $books_authors->addForeignKeyConstraint('books', ['book_id'], ['id'], ['onUpdate' => 'CASCADE', 'onDelete' => 'SET NULL']);
        $books_authors->addForeignKeyConstraint('authors', ['author_id'], ['id'], ['onUpdate' => 'CASCADE', 'onDelete' => 'RESTRICT']);
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
    }
}
