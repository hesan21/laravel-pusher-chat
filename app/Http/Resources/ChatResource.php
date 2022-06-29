<?php

namespace App\Http\Resources;

use App\Models\Chat;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'user_id' => $this->user_id,
            'users' => UserResource::collection($this->whenLoaded('activeUsers')),
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
                    $message = $this->messages()->latest('created_at')->first();

                    if($message) {
                        $message->message = Str::limit($message->message,15);
                    }

                    return [
                        'last_message' => ChatMessageResource::make($message),
                        'unread_message_count' => $this->unreadmessages()->count()
                    ];
                }
            )
        ];
    }
}
