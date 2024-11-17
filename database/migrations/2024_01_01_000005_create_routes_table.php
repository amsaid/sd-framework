<?php

declare(strict_types=1);

namespace Database\Migrations;

use SdFramework\Database\Migration;
use SdFramework\Database\Schema\Table;

class CreateRoutesTable extends Migration
{
    public function up(): void
    {
        $this->createTable('routes', function (Table $table) {
            $table->id();
            $table->string('path');
            $table->string('handler');
            $table->string('method')->default('GET');
            $table->string('name')->nullable();
            $table->json('middleware')->nullable();
            $table->string('module')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            
            // Indexes for route matching
            $table->index(['path', 'method', 'is_active']);
            $table->index(['module', 'is_active']);
            $table->unique(['name', 'module']);
        });
    }
    
    public function down(): void
    {
        $this->dropTable('routes');
    }
}
