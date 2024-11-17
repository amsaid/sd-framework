<?php

declare(strict_types=1);

use SdFramework\Database\Migration;
use SdFramework\Database\Schema\Blueprint;
use SdFramework\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_messages', function (Blueprint $table) {
            $table->id();
            $table->string('locale')->default('en');
            $table->string('rule');
            $table->string('message');
            $table->timestamps();
            
            // Composite unique key for locale and rule
            $table->unique(['locale', 'rule']);
        });

        // Insert default English messages
        $this->insertDefaultMessages();
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_messages');
    }

    private function insertDefaultMessages(): void
    {
        $messages = [
            'required' => 'The :field field is required.',
            'email' => 'The :field must be a valid email address.',
            'min' => 'The :field must be at least :param characters.',
            'max' => 'The :field must not exceed :param characters.',
            'numeric' => 'The :field must be a number.',
            'alpha' => 'The :field must only contain letters.',
            'alphanumeric' => 'The :field must only contain letters and numbers.',
            'url' => 'The :field must be a valid URL.',
            'date' => 'The :field must be a valid date.',
            'matches' => 'The :field must match :param.',
        ];

        foreach ($messages as $rule => $message) {
            DB::table('validation_messages')->insert([
                'locale' => 'en',
                'rule' => $rule,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
};
