<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->unsignedInteger('user_id')->unsigned()->nullable();
            $table->unsignedInteger('store_id')->unsigned()->nullable();
            $table->string('uid')->unique();
            $table->string('sono')->nullable();
            $table->integer('totalqty')->default(0);
            $table->decimal('discpctg',9,6)->default(0.00);
            $table->decimal('totalcost',8,2)->default(0.00);
            $table->decimal('linetotal',8,2)->default(0.00);
            $table->decimal('charge',8,2)->default(0.00);
            $table->decimal('totaldisc',8,2)->default(0.00);
            $table->decimal('grandtotal',8,2)->default(0.00);
            $table->decimal('payment',8,2)->default(0.00);
            $table->decimal('outstanding',8,2)->default(0.00);
            $table->string('status')->default('open');
            $table->text('remark')->nullable();
            $table->dateTime('docdate')->nullable();
            $table->boolean('pos')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onUpdate('cascade')
            ->onDelete('restrict');

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
        Schema::dropIfExists('sales');
    }
}
