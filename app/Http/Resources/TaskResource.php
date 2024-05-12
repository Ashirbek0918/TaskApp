<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'buyer' => [
                'id' => $this->buyer->id,
                'name' => $this->buyer->name,
            ],
            'assignee' => [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ],
            'images' => $this->getImageUrls(),
            'deadline' => $this->deadline
        ];
    }

    protected function getImageUrls()
    {
        return $this->images->map(function ($image) {
            return url("storage/".$image->path . $image->name); // URL-ni yaratish
        });
    }
}
