<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->unsignedInteger('sale_id')->unsigned()->nullable();
            $table->unsignedInteger('inventory_id')->unsigned()->nullable();
            $table->string('uid')->unique();
            $table->string('name');
            $table->integer('qty')->default(0);
            $table->string('desc')->nullable();
            $table->decimal('price',8,2)->default(0.00);
            $table->decimal('totaldisc',8,2)->default(0.00);
            $table->decimal('linetotal',8,2)->default(0.00);
            $table->decimal('totalcost',8,2)->default(0.00);
            $table->decimal('payment',8,2)->default(0.00);
            $table->decimal('outstanding',8,2)->default(0.00);
            $table->string('status')->default('open');
            $table->string('lastedit_by')->nullable();
            $table->date('docdate')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('sale_id')
            ->references('id')
            ->on('sales')
            ->onUpdate('cascade')
            ->onDelete('restrict');

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
        Schema::dropIfExists('sale_items');
    }
}
