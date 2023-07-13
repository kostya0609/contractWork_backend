<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLContractWorkProtocols extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('l_contract_work_protocols', function (Blueprint $table) {

            $table->id();
            $table->integer('contract_id');
            $table->integer('version');
            $table->integer('user_id');
            $table->dateTime('date');
            $table->string('comment')->nullable();
            $table->timestamps();

            /**
            $table->unsignedBigInteger('request_id'); //Для отношений или беззнаковое число
            $table->string('string'); // строка
            $table->text('text')->nullable(); // текст с указанием что может быть null
            $table->enum('enum', ['one','two', 'three']);//Перечесляемый список
            $table->date('date'); // дата
             */
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('l_contract_work_protocols');
    }
};
