<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $softDelete = true;
    protected $fillable = [
        'user_id','domain', 'access_token', 
    ];
}
