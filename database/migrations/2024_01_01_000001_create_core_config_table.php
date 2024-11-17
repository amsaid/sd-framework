<?php

declare(strict_types=1);

namespace Database\Migrations;

use SdFramework\Database\Migration;
use SdFramework\Database\Schema\Table;

return new class extends  Migration
{
    public function up(): void
    {
        $this->createTable('core_config', function (Table $table) {
            $table->id();
            $table->string('path')->unique();
            $table->text('value');
            $table->string('scope')->default('default');
            $table->string('scope_id')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            
            // Composite index for efficient config lookups
            $table->index(['path', 'scope', 'scope_id']);
        });
    }
    
    public function down(): void
    {
        $this->dropTable('core_config');
    }
};
