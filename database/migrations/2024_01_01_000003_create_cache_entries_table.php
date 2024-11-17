<?php

declare(strict_types=1);

namespace Database\Migrations;

use SdFramework\Database\Migration;
use SdFramework\Database\Schema\Table;

class CreateCacheEntriesTable extends Migration
{
    public function up(): void
    {
        $this->createTable('cache_entries', function (Table $table) {
            $table->id();
            $table->string('key')->unique();
            $table->mediumText('value');
            $table->json('tags')->nullable();
            $table->integer('expiration');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            
            // Index for expiration cleanup
            $table->index('expiration');
            // Index for tag-based operations
            $table->index(['key', 'expiration']);
        });
    }
    
    public function down(): void
    {
        $this->dropTable('cache_entries');
    }
}
