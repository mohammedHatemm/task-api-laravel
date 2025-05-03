<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */

    use HasFactory , HasApiTokens;
   protected $fillable = [
        'name',
        'email',
        'password',
    ];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
