<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'message' => $this->message,
            'sender' => UserResource::make($this->sender),
            'read_at' => $this->read_at,
            'type' => $this->type,
            'time' => $this->created_at->toTimeString(),
        ];
    }
}
