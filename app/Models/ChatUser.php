<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatUser extends Model
{
    use HasFactory;

    protected $table = 'chat_users';

    const STATUS_LEAVE = 'left';
    const STATUS_PARTICIPANT = 'participant';

    const AVAILABLE_STATUSES = [
        self::STATUS_LEAVE,
        self::STATUS_PARTICIPANT
    ];

    protected $guarded = [];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function chat() : BelongsTo
    {
        return $this->belongsTo(
            Chat::class,
            'chat_id',
            'id',
            'chat'
        );
    }

    /**
     * @return BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id',
            'user'
        );
    }

    public function messages(): HasMany
    {
        return $this->hasMany(
            ChatMessage::class,
            'chat_id',
            'chat_id'
        );
    }
}
