<?php

/**
 * Auto generated by Foot Cli
 */

namespace App\Migration;

use Footup\Database\Schema\Schema;
use Footup\Database\Schema\Table;
use Footup\Database\Migration;


class {class_name} extends Migration
{
    /**
     * @param  $schema
     * @return bool|string|Schema
     */
    protected function up(Schema $schema)
    {
        /**
         * @var Table $table
         */
        $table = $schema->table("{table}");
        
        return $schema->create("{table}"); // or just return $schema
    }

    /**
     * @param Schema $schema
     * @return bool|string|Schema
     */
    protected function empty(Schema $schema)
    {
        return $schema->empty("{table}"); // or just return $schema
    }

    /**
     * @param Schema $schema
     * @return bool|string|Schema
     */
    protected function down(Schema $schema)
    {
        return $schema->drop("{table}"); // or just return $schema
    }
}