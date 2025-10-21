<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained()->onDelete('cascade');
            $table->string('number')->unique();
            $table->enum('type', ['water', 'electricity', 'gas', 'heating']);
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->decimal('last_reading', 10, 2)->default(0);
            $table->date('last_reading_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'broken', 'replaced'])->default('active');
            $table->date('installation_date')->nullable();
            $table->date('verification_date')->nullable();
            $table->date('next_verification_date')->nullable();
            $table->jsonb('additional_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('meters');
    }
};
