<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('email')->nullable()->after('domain');
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('contact_person')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['email', 'phone', 'address', 'contact_person']);
        });
    }
};