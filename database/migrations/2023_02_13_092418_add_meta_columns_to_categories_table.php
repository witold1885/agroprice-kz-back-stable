<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaColumnsToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->text('description')
                    ->after('url')
                    ->nullable();
            $table->string('meta_heading')
                    ->after('description')
                    ->nullable();
            $table->string('meta_title')
                    ->after('meta_heading')
                    ->nullable();
            $table->text('meta_description')
                    ->after('meta_title')
                    ->nullable();
            $table->text('meta_keywords')
                    ->after('meta_description')
                    ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            //
        });
    }
}
