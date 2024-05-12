<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'messagereads' => MessageReadsResource::collection($this->messageread),
            'images' => $this->getImageUrls(),
            'created_at' => $this->created_at
        ];
    }

    protected function getImageUrls()
    {
        return $this->images->map(function ($image) {
            return url("storage/".$image->path . $image->name);
        });
    }
}
