<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AllTableTenants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenants')->create('companies', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('tax_identification')->unique();
            $table->string('contact');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->boolean('active')->default(true);
            $table->nullableTimestamps();
        });

        Schema::connection('tenants')->create('tenants', function ($table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constraint()->on('empresas');
            $table->string('domain')->unique();
            $table->string('description')->nullable();
            $table->string('database_name')->unique();
            $table->string('database_host');
            $table->string('database_user');
            $table->string('database_password');
            $table->boolean('active')->default(true);
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenants')->dropIfExists('tenants');
        Schema::connection('tenants')->dropIfExists('companies');
    }
}
