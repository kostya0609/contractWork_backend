<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLContractWorkCompanyTypes extends Migration {
    public function up(){
        Schema::create('l_contract_work_company_types', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('l_contract_work_company_types');
    }
};
