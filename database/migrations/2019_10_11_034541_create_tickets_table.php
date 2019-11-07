<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->unsignedInteger('store_id')->unsigned();
            $table->string('uid')->unique();
            $table->string('name');
            $table->string('sku');
            $table->string('code');
            $table->string('desc')->nullable();
            $table->decimal('price',8,2)->default(0.00);
            $table->decimal('disc',8,2)->default(0.00);
            $table->decimal('discpctg',8,2)->default(0.00);
            $table->decimal('promoprice',8,2)->default(0.00);
            $table->dateTime('promostartdate')->nullable();
            $table->dateTime('promoenddate')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('salesqty')->default(0);
            $table->dateTime('enddate');
            $table->integer('stockthreshold')->default(0);
            $table->boolean('backorder')->default(0);
            $table->boolean('status')->default(1);
            $table->string('lastedit_by')->nullable();
            $table->timestamps();

            $table->foreign('store_id')
            ->references('id')
            ->on('stores')
            ->onUpdate('cascade')
            ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
