<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            
            $table->increments('id');
            $table->unsignedInteger('store_id')->unsigned();
            $table->string('uid')->unique();
            $table->string('name');
            $table->text('desc')->nullable();
            $table->string('code')->unique();
            $table->decimal('disc',8,2)->default(0.00);
            $table->decimal('discpctg',8,2)->default(0.00);
            $table->date('startdate')->nullable();
            $table->date('enddate')->nullable();
            $table->decimal('minpurchase',8,2)->default(0.00);
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('vouchers');
    }
}