<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Models\ChatMessage;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ChatMessage::AVAILABLE_TYPES);
            $table->longText('message')->nullable();
            $table->dateTime('read_at')->nullable();

            $table->foreignId('sender_id')
                ->constrained('users', 'id')
                ->onDelete('cascade');

            $table->uuid('chat_id');
            $table->foreign('chat_id')
                ->references('id')
                ->on('chats')
                ->onDelete('restrict')
                ->onUpdate('cascade');

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
        Schema::dropIfExists('chat_messages');
    }
};
