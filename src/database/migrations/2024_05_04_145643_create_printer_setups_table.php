<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('printer_setups', function (Blueprint $table) {
            $table->comment('プリンタ設定');

            $table->id();
            $table->foreignId('store_id')->constrained('stores')->comment('店舗ID');
            $table->string('name')->nullable()->comment('プリンタ名');
            $table->string('port')->nullable()->comment('プリンタ名');
            $table->string('ip_address')->nullable()->comment('プリンタ名');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_setups');
    }
};
