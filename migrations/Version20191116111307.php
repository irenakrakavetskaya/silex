<?php

namespace App\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20191116111307 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $orders = $schema->createTable('orders');
        $orders->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $orders->addColumn('status', 'smallint', [
            'values' => [0, 1, 2],
            'default' => '0',
            'notnull' => true])
            ->setNotnull(true);
        $orders->addColumn('datetime', 'datetime')->setNotnull(true);
        $orders->addColumn('timezone', 'string')->setNotnull(true);
        $orders->addColumn('phone', 'text')->setNotnull(true);
        $orders->addColumn('address', 'text')->setNotnull(true);
        $orders->setPrimaryKey(['id']);
        $orders->addIndex(['status'])->addIndex(['datetime'])->addIndex(['timezone']);

        $books_orders = $schema->createTable('books_orders');
        $books_orders->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $books_orders->addColumn('book_id', 'integer', ['unsigned' => true])->setNotnull(true);
        $books_orders->addColumn('order_id', 'integer', ['unsigned' => true])->setNotnull(false);
        $books_orders->setPrimaryKey(['id']);
        $books_orders->addIndex(['book_id'])->addIndex(['order_id'])->addUniqueIndex(['book_id', 'order_id']);
        $books_orders->addForeignKeyConstraint('books', ['book_id'], ['id'], ['onUpdate' => 'CASCADE', 'onDelete' => 'RESTRICT']);
        $books_orders->addForeignKeyConstraint('orders', ['order_id'], ['id'], ['onUpdate' => 'CASCADE', 'onDelete' => 'SET NULL']);

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('orders');
        $schema->dropTable('books_orders');
    }
}
