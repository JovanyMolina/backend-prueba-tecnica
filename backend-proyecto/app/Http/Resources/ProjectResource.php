<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'start_date'  => optional($this->start_date)->toDateString(),
            'end_date'    => optional($this->end_date)->toDateString(),
            'status'      => $this->status,

            'collaborators_count' => isset($this->collaborators_count)
                ? $this->collaborators_count
                : ($this->relationLoaded('collaborators') ? $this->collaborators->count() : 0),

            'collaborators' => $this->whenLoaded('collaborators', function () {
                return $this->collaborators->map(fn($u) => [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'email' => $u->email,
                    'role'  => $u->role,
                ]);
            }),
        ];
    }
}
