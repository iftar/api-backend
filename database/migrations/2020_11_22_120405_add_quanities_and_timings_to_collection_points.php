<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuanitiesAndTimingsToCollectionPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collection_points', function (Blueprint $table) {
            $table->integer('quantity_of_meals')->default(1);
            $table->dateTime('pick_up_time');
            $table->dateTime("cut_off_point");
            $table->integer("set_quantity_per_person")->default(1);
            $table->string("logo")->nullable();
            $table->string("unique_url")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collection_points', function (Blueprint $table) {
            $table->dropColumn(['quantity_of_meals',  'pick_up_time', "cut_off_point", "set_quantity_per_person", "logo", "unique_url"]);
        });
    }
}