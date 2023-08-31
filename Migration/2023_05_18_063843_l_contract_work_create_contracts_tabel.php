<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLContractWorkContracts extends Migration {
    public function up(){
        Schema::create('l_contract_work_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->integer('contract_type_id');
            $table->integer('organization_id');
            $table->integer('department_id');
            $table->integer('contragent_id');
            $table->integer('company_type_id');
            $table->integer('responsible_id');
            $table->integer('recording_id');
            $table->integer('signatory_id');
            $table->text('content');
            $table->boolean('waiting_edit');
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('l_contract_work_contracts');
    }
};
