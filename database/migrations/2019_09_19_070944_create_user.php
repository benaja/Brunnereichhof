<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usertype', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 200);
        });

        Schema::create('role', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 200);
            $table->string('name_de', 200)->nullable();
            $table->timestamps();
        });

        Schema::create('authorizationrule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 200);
            $table->string('name_de', 200);
            $table->timestamps();
        });

        Schema::create('role_authorizationrule', function (Blueprint $table) {
            $table->primary(['role_id', 'authorizationrule_id']);
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('authorizationrule_id');
            $table->foreign('role_id')->references('id')->on('role');
            $table->foreign('authorizationrule_id')->references('id')->on('authorizationrule');
        });

        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 200)->nullable();
            $table->string('username', 200)->nullable();
            $table->string('firstname', 200);
            $table->string('lastname', 200);
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->boolean('isPasswordChanged')->default(false);
            $table->boolean('isDeleted')->default(false);
            $table->boolean('ismealdefault')->default(false);
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('usertype');
            $table->foreign('role_id')->references('id')->on('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
        Schema::dropIfExists('role_authorizationrule');
        Schema::dropIfExists('authorizationrule');
        Schema::dropIfExists('role');
        Schema::dropIfExists('usertype');
    }
}
