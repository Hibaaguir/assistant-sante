<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compte extends Model
{
    protected $table = 'comptes';

    protected $fillable = [
        'email',
        'email_verified_at',
        'password',
        'remember_token',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'compte_id');
    }
}
