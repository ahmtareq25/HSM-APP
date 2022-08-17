<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')
                ->default(0);
            $table->string('name');
            $table->string('usergroup_ids', 100)->nullable();
            $table->string('email')
                ->unique();
            $table->timestamp('email_verified_at')
                ->nullable();
            $table->string('password');
            $table->string('language', 10)
                ->default('en');
            $table->integer('permission_version')
                ->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
