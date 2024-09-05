<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_roles', function ($table) {
            $table->increments('id');
            $table->string('name', 20)->unique();
            $table->string('description', 50)->nullable();
            $table->softDeletes();
        });
        Schema::create('auth_user_roles', function ($table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('role_id');
            $table->unique(['user_id', 'role_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('role_id')->references('id')->on('auth_roles')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::create('auth_modules', function ($table) {
            $table->increments('id');
            $table->string('name', 50)->unique();
            $table->string('friendly_name', 100);
            $table->boolean('show')->unsigned()->default(true);
            $table->softDeletes();
        });
        Schema::create('auth_permissions', function ($table) {
            $table->increments('id');
            $table->string('name', 20)->unique();
            $table->string('friendly_name', 20);
            $table->boolean('show')->unsigned()->default(true);
            $table->softDeletes();
        });
        Schema::create('auth_module_permissions', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('module_id');
            $table->unsignedInteger('permission_id');
            $table->unique(['module_id', 'permission_id']);

            $table->foreign('module_id')->references('id')->on('auth_modules')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('permission_id')->references('id')->on('auth_permissions')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::create('auth_role_module_permissions', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('module_permission_id');
            $table->unique(['role_id', 'module_permission_id']);

            $table->foreign('role_id')->references('id')->on('auth_roles')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('module_permission_id')->references('id')->on('auth_module_permissions')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::create('auth_menu', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('module_permission_id')->nullable();
            $table->string('name', 30);
            $table->unsignedInteger('order')->nullable();
            $table->string('icon', 30)->nullable();

            $table->foreign('parent_id')->references('id')->on('auth_menu')->onUpdate('cascade');
            $table->foreign('module_permission_id')->references('id')->on('auth_module_permissions')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //AUTH
        Schema::dropIfExists('auth_menu');
        Schema::dropIfExists('auth_role_module_permissions');
        Schema::dropIfExists('auth_module_permissions');
        Schema::dropIfExists('auth_permissions');
        Schema::dropIfExists('auth_modules');
        Schema::dropIfExists('auth_user_roles');
        Schema::dropIfExists('auth_roles');
    }
};
