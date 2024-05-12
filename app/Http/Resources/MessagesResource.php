<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessagesResource extends JsonResource
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
            'message' => $this->message,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name
            ],
            'created_at' => $this->created_at,
            'reads_count' =>count($this->messageread)
        ];
    }
}
