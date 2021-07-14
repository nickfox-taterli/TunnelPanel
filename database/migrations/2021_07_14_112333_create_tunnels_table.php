<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTunnelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tunnels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('用户标记,也用于对用户的IP进行更新.'); // 默认不能用户注册,管理员通过特定链接注册后给用户.
            $table->string('server_ipv4')->comment('服务器公网IPv4.');
            $table->string('client_ipv4')->unique()->comment('客户端公网IPv4.');
            $table->string('server_ipv6')->unique()->comment('服务器IPv6.');
            $table->string('client_ipv6')->unique()->comment('客户端IPv6.'); 
            $table->bigInteger('bind')->comment('关联的管理员ID'); // 和数据库的ID绑定,每个用户创5条隧道.
            $table->timestamps(); // 用户创建以及最后汇报时间都在这里.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tunnels');
    }
}
