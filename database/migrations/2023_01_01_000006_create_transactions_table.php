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
        Schema::create("transactions", function (Blueprint $table) {
            $table->increments("id");
            $table->integer("order_no");
            $table->timestamp("date_ordered")->useCurrent();
            $table->timestamp("date_needed")->nullable();
            $table->timestamp("date_approved")->nullable();
            $table->timestamp("date_served")->nullable();
            $table->string("order_type")->nullable();

            $table->integer("hri_id")->nullable();
            $table->string("hri_code")->nullable();
            $table->string("hri_name")->nullable();

            $table->integer("keyword_id");
            $table->string("keyword_code");
            $table->string("keyword_desc");

            $table->integer("company_id");
            $table->string("company_code");
            $table->string("company_name");

            $table->integer("department_id");
            $table->string("department_code");
            $table->string("department_name");

            $table->integer("location_id");
            $table->string("location_code");
            $table->string("location_name");

            $table->integer("customer_id");
            $table->string("customer_code");
            $table->string("customer_name");

            $table->unsignedBigInteger("charge_company_id")->index();
            $table
                ->foreign("charge_company_id")
                ->references("sync_id")
                ->on("company");

            $table->string("charge_company_code");
            $table->string("charge_company_name");

            $table->unsignedBigInteger("charge_department_id")->index();
            $table
                ->foreign("charge_department_id")
                ->references("sync_id")
                ->on("department");

            $table->string("charge_department_code");
            $table->string("charge_department_name");

            $table->unsignedBigInteger("charge_location_id")->index();
            $table
                ->foreign("charge_location_id")
                ->references("sync_id")
                ->on("location");

            $table->string("charge_location_code");
            $table->string("charge_location_name");

            $table->string("rush")->nullable();
            $table->string("reason")->nullable();

            $table->unsignedInteger("requestor_id")->index();
            $table
                ->foreign("requestor_id")
                ->references("id")
                ->on("users");
            $table->string("requestor_name");

            $table
                ->unsignedInteger("approver_id")
                ->index()
                ->nullable();
            $table
                ->foreign("approver_id")
                ->references("id")
                ->on("users");
            $table->string("approver_name")->nullable();

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
        Schema::dropIfExists("transactions");
    }
};
