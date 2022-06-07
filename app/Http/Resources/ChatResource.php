<?php

namespace App\Http\Resources;

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
            'users' => UserResource::collection($this->whenLoaded('users')),
            'messages' => ChatMessageResource::collection($this->whenLoaded('messages')),
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
