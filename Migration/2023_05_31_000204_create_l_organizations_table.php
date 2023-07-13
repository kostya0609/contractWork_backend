<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLOrganization extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('l_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guid');
            $table->string('direction')->nullable();
            $table->string('inn');
            $table->string('kpp');
            $table->string('ur_address')->nullable();
            $table->string('fact_address')->nullable();
            $table->string('mail_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('l_organizations');
    }
};
