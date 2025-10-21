<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['utility', 'maintenance', 'additional', 'penalty']);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('unit')->default('month'); // месяц, кв.м, кВт*ч, м³
            $table->boolean('is_active')->default(true);
            $table->boolean('is_metered')->default(false); // зависит ли от показаний счетчиков
            $table->jsonb('calculation_rules')->nullable(); // правила расчета
            $table->jsonb('additional_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('services');
    }
};
