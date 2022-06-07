<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'gender',
        'DOB',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * User Chats
     * @return BelongsToMany
     */
    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(
            Chat::class,
            'chat_users',
            'user_id',
            'chat_id',
            'id',
            'id',
            'chats'
        )
            ->withPivot(['last_active_at']);
    }

    /**
     * User Active Chats ( Chats user hasn't left yet)
     * @return BelongsToMany
     */
    public function activeChats(): BelongsToMany
    {
        return $this->belongsToMany(
            Chat::class,
            'chat_users',
            'user_id',
            'chat_id',
            'id',
            'id',
            'chats'
        )
            ->using(ChatUser::class)
            ->as('chatUser')
            ->withPivot(['last_active_at'])
            ->wherePivot('status', ChatUser::STATUS_PARTICIPANT);
    }

    /**
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this
            ->hasMany(
                ChatMessage::class,
                'sender_id',
                'id'
            );
    }
}
