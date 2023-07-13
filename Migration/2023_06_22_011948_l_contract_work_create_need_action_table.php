<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLContractWorkNeedAction extends Migration {
    public function up(){
        Schema::create('l_contract_work_need_action', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('contract_id');
        });
    }

    public function down(){
        Schema::dropIfExists('l_contract_work_need_action');
    }
};
