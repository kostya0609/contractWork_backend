<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLBusinessTripFilesTable extends Migration{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('l_contract_work_files', function (Blueprint $table) {
            $table->increments('id');
            //$table->integer('contract_id');
            $table->string('dir');
            $table->string('type');
            $table->string('original_name');
            $table->string('translated_name');
            $table->string('hash_name');
            $table->string('type_file');
            $table->integer('contract_id');

            $table->integer('fileable_id');
            $table->string('fileable_type');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('l_contract_work_files');
    }
};

