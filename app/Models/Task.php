<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;



    protected $guarded= ['id'];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y h:i:s', 
        'updated_at' => 'datetime:d/m/Y h:i:s',
    ];


    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
