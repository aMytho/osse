<?php

use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 25);
            $table->string('description', 50);
        });

        Permission::query()->insert([
            'name' => 'Password Manager',
            'description' => 'Allows changing the password of a user.',
        ]);

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('permission_id');
            $table->dateTime('created_at');
        });

        // Give root user the password manager permission
        DB::table('user_permissions')->insert(['user_id' => 1, 'permission_id' => 1, 'created_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('permissions');
        Schema::drop('user_permissions');
    }
};
