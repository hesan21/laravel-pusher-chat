<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Models\ChatUser;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_users', function (Blueprint $table) {
            $table->id();
//            $table->primary(['user_id', 'chat_id']);
            $table->foreignId('user_id')
                ->constrained('users', 'id')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->uuid('chat_id');
            $table->foreign('chat_id')
                ->references('id')
                ->on('chats')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // To See which users are in chat and who has left ( specifically for Groups)
            $table->enum('status', ChatUser::AVAILABLE_STATUSES)
                ->default(ChatUser::STATUS_PARTICIPANT);

            $table->timestamp('last_active_at')->nullable(); // A way to track when user was last online
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_users');
    }
};
