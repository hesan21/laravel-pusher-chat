<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    const TYPE_MESSAGE = 'message';
    const TYPE_FILE = 'file';

    const AVAILABLE_TYPES = [
        self::TYPE_MESSAGE,
        self::TYPE_FILE
    ];

    /** @var array */
    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array */
    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'read_at',
        'type'
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
    public function sender() : BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'sender_id',
            'id',
            'sender'
        );
    }
}
