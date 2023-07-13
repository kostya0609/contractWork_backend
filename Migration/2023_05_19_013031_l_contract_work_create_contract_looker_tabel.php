<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLContractWorkContractLooker extends Migration {
    public function up(){
        Schema::create('l_contract_work_contract_looker', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('looker_id');
        });
    }

    public function down(){
        Schema::dropIfExists('l_contract_work_contract_looker');
    }
};
