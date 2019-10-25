<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->unsignedInteger('inventory_id')->unsigned()->nullable();
            $table->string('uid')->unique();
            $table->integer('stock')->default(0);
            $table->unsignedInteger('warrantyperiod')->default(0);
            $table->decimal('price',8,2)->default(0.00);
            $table->decimal('cost',8,2)->default(0.00);
            $table->boolean('status')->default(1);
            $table->boolean('curbatch')->default(0);
            $table->boolean('backorder')->default(0);
            $table->unsignedInteger('batchno')->unsigned();
            $table->unsignedInteger('salesqty')->unsigned()->default(0);
            $table->string('lastedit_by')->nullable();
            $table->timestamps();


            $table->foreign('inventory_id')
            ->references('id')
            ->on('inventories')
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
        Schema::dropIfExists('batches');
    }
}
