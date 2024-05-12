<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    use HasFactory;

    protected $guarded= ['id'];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y h:i:s', 
        'updated_at' => 'datetime:d/m/Y h:i:s',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
