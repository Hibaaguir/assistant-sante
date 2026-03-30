<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Compte extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'comptes';

    protected $fillable = [
        'email',
        'password',
        'statut',
    ];

    protected $hidden = [
        'password',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'compte_id');
    }
}
