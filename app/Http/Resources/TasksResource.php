<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TasksResource extends JsonResource
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
            'buyer' => [
                'id' => $this->buyer->id,
                'name' => $this->buyer->name
            ],
            'assignee' =>[
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ],
            'status' => $this->status,
            'deadline' =>$this->deadline,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
