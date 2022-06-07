<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Models\Chat;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name')->nullable(); // For Groups Name
            $table->enum('type', Chat::AVAILABLE_TYPES)
                ->default(Chat::TYPE_CHAT);

            $table->foreignId('user_id') // For keeping track who started the chat
                ->nullable()
                ->constrained('users', 'id')
                ->onDelete('set null'); //In case User is deleted chat should not be deleted from other user(s)

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
        Schema::dropIfExists('chats');
    }
};
