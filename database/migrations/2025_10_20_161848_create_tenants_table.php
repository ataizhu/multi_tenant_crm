<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('database')->unique();
            $table->jsonb('settings')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('tenant_trash', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('deleted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('deleted_at');
            $table->text('deletion_reason')->nullable();

            $table->index(['tenant_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('tenant_trash');
        Schema::dropIfExists('tenants');
    }
};
