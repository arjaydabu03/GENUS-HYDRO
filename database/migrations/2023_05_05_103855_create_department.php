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
        Schema::create("department", function (Blueprint $table) {
            $table->increments("id");
            $table->unsignedBigInteger("sync_id")->unique();
            $table->string("code");
            $table->string("name");

            $table->unsignedBigInteger("company_id")->index();
            $table
                ->foreign("company_id")
                ->references("sync_id")
                ->on("company");

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("department");
    }
};
