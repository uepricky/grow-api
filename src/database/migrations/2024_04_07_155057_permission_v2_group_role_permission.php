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
        Schema::create('permission_v2_group_role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_v2_permission_id')->constrained('permission_v2_permissions')->cascadeOnDelete()->comment('パーミッションID');
            $table->foreignId('permission_v2_group_roles_id')->constrained('permission_v2_group_roles')->cascadeOnDelete()->comment('グループロールID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_v2_group_role_permission');
    }
};
