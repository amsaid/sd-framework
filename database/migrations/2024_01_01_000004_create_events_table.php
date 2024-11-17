<?php

declare(strict_types=1);

namespace Database\Migrations;

use SdFramework\Database\Migration;
use SdFramework\Database\Schema\Table;

return new class extends  Migration
{
    public function up(): void
    {
        $this->createTable('events', function (Table $table) {
            $table->id();
            $table->string('event_name');
            $table->string('module');
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->integer('attempts')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            
            // Indexes for event processing
            $table->index(['event_name', 'status']);
            $table->index(['module', 'status']);
            $table->index('scheduled_at');
        });
    }
    
    public function down(): void
    {
        $this->dropTable('events');
    }
};
