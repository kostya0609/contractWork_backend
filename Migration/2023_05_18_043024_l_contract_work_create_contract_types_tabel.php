<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLContractWorkContractTypes extends Migration {
    public function up(){
        Schema::create('l_contract_work_contract_types', function (Blueprint $table) {
            $table->id();
            $table->text('type');
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('l_contract_work_contract_types');
    }
};
