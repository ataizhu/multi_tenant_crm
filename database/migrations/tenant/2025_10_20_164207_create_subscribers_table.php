<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->string('apartment_number')->nullable();
            $table->string('building_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->date('registration_date')->default(now());
            $table->jsonb('additional_info')->nullable();
            $table->timestamps();

            // Индексы для оптимизации
            $table->index('status');
            $table->index('registration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('subscribers');
    }
};
