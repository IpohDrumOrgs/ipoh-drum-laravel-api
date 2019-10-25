<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_sale', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_id')->unsigned();
            $table->unsignedInteger('sale_id')->unsigned();
            $table->decimal('amt',8,2)->default(0.00);
            $table->decimal('discount',8,2)->default(0.00);
            $table->string('type')->default('display');
            $table->string('status')->default('close');
            $table->timestamps();

            $table->foreign('payment_id')
            ->references('id')
            ->on('payments')
            ->onUpdate('cascade')
            ->onDelete('restrict');

            $table->foreign('sale_id')
            ->references('id')
            ->on('sales')
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
        Schema::dropIfExists('payment_sale');
    }
}
