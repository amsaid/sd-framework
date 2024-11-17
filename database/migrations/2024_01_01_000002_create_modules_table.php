<?php

declare(strict_types=1);

namespace Database\Migrations;

use SdFramework\Database\Migration;
use SdFramework\Database\Schema\Table;

return new class extends  Migration
{
    public function up(): void
    {
        $this->createTable('modules', function (Table $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('version');
            $table->text('description')->nullable();
            $table->json('dependencies')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('installed_at');
            $table->timestamp('updated_at');
            
            $table->index('is_active');
            $table->index('sort_order');
        });
    }
    
    public function down(): void
    {
        $this->dropTable('modules');
    }
};
