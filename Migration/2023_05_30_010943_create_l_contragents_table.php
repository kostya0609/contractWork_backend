<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLContrAgents extends Migration {

    public function up(){
        Schema::create('l_contragents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('full_name');
            $table->string('guid');
            $table->string('email')->nullable();
            $table->string('account_number');
            $table->string('parent_name')->nullable();
            $table->string('parent_guid')->nullable();
            $table->string('fact_address');
            $table->string('ur_address');
            $table->string('inn');
            $table->string('kpp');
            $table->string('okopf')->nullable();
            $table->string('okpo')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('trade_area')->nullable();
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('l_contragents');
    }
};
