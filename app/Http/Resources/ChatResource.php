<?php

namespace App\Http\Resources;

use App\Models\Chat;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'messages' => ChatMessageResource::collection($this->whenLoaded('messages')),
            'type' => $this->type,
            $this->mergeWhen(
                $this->type == Chat::TYPE_GROUP,
                function () use ($request) {
                    return [
                        'member_status' => $this->users()->where('user_id', $request->user()->id)->first()->pivot->status
                    ];
                }
            ),
            $this->mergeWhen(
                $this->type == Chat::TYPE_CHAT,
                function () use ($request) {
                    return [
                        'other_user' => $this->users()->whereNot('user_id', $request->user()->id)->first()
                    ];
                }
            ),
            $this->mergeWhen(
                !$this->relationLoaded('messages'),
                function () {
                    return [
                        'last_message' => ChatMessageResource::make($this->messages()->latest('created_at')->first()),
                        'unread_message_count' => $this->unreadmessages()->count()
                    ];
                }
            )
        ];
    }
}
