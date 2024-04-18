<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("materials", function (Blueprint $table) {
            $table->increments("id");
            $table->string("code");
            $table->string("name");

            $table->unsignedInteger("category_id")->index();
            $table
                ->foreign("category_id")
                ->references("id")
                ->on("categories");

            $table->unsignedInteger("uom_id")->index();
            $table
                ->foreign("uom_id")
                ->references("id")
                ->on("uom");

            $table->unsignedInteger("warehouse_id")->index();
            $table
                ->foreign("warehouse_id")
                ->references("id")
                ->on("warehouse");

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("materials");
    }
};
