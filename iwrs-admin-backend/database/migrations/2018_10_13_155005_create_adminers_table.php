<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adminers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username',200);
            $table->char('realname',20);
            $table->char('email',45);
            $table->char('phone',20);
            $table->char('password',64);
            $table->tinyInteger('status');
            $table->timestamps('created_at');
            $table->timestamps('updated_at');
            $table->timestamps('login_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adminers');
    }
}
