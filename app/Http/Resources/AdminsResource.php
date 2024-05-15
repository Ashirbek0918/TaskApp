<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminsResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'tasks_count' => $this->assignee()->where('status', 'active')->count(),
            'images' => $this->getImageUrls(),
        ];        
    }

    protected function getImageUrls()
    {
        return $this->images->map(function ($image) {
            return url("storage/".$image->path . $image->name);
        });
    }
}
