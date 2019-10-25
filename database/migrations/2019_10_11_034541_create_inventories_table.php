<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->unsignedInteger('company_id')->unsigned()->nullable();
            $table->string('uid')->unique();
            $table->string('code');
            $table->string('sku');
            $table->string('name');
            $table->string('desc')->nullable();
            $table->decimal('cost',8,2)->default(0.00);
            $table->decimal('price',8,2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->integer('salesqty')->default(0);
            $table->integer('warrantyperiod')->default(0);
            $table->integer('stockthreshold')->default(0);
            $table->boolean('backorder')->default(0);
            $table->boolean('status')->default(1);
            $table->string('lastedit_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')
            ->references('id')
            ->on('companies')
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
        Schema::dropIfExists('inventories');
    }
}
