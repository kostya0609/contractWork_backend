<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLContractWorkRoleUser extends Migration {
    public function up(){
        Schema::create('l_contract_work_role_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
        });
    }

    public function down(){
        Schema::dropIfExists('l_contract_work_role_user');
    }
};
