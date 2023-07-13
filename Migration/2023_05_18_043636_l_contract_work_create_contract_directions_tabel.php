<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLContractWorkContractDirections extends Migration {
    public function up(){
        Schema::create('l_contract_work_contract_directions', function (Blueprint $table) {
            $table->id();
            $table->string('direction');
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('l_contract_work_contract_directions
        ');
    }
};
