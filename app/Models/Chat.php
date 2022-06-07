<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use App\Traits\GenerateUUIDTrait;

class Chat extends Model
{
    use HasFactory;
    use GenerateUUIDTrait;

    const TYPE_GROUP = 'group';
    const TYPE_CHAT = 'chat';

    const AVAILABLE_TYPES = [
        self::TYPE_CHAT,
        self::TYPE_GROUP
    ];

    /** @var string */
    protected $table = "chats";

    /**
     * Tell Model that the Primary key in non-incrementing
     * @var bool
     */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'uuid';

    /** @var array */
    protected $fillable = [
        'name',
        'user_id',
        'type'
    ];

    /**
     * Creator of the Chat
     * @return BelongsTo
     */
    public function chatCreator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id',
            'chatCreator'
        );
    }

    /**
     * Chat Messages
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(
            ChatMessage::class,
            'chat_id',
            'id'
        )->orderBy('created_at', 'DESC');
    }

    /**
     * Get the unread messages of a chat.
     */
    public function unreadMessages() : HasMany
    {
        return $this->hasMany(
            ChatMessage::class,
            'chat_id',
            'id'
        )->whereNull('read_at');
    }
    /**
     * All Chat Users with their last Active Time
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'chat_users',
            'chat_id',
            'user_id',
            'id',
            'id',
            'users'
        )
            ->withPivot(['last_active_at']);
    }
}
