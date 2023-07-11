<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStringToHtmlFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('converted_string_to_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->string('model_attribute');
            $table->morphs('model');
            $table->timestamps();            
            $table->unique(['model_attribute', 'model_id', 'model_type'], 'model_attribute_model_type_and_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('converted_string_to_files');
    }
}
