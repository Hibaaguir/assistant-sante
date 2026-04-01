<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
{
    protected $table = 'comptes';

    protected $fillable = [
        'email',
        'email_verified_at',
        'motdepasse',
        'remember_token',
    ];

    protected $hidden = ['motdepasse', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->hasOne(Utilisateur::class, 'compte_id');
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(Utilisateur::class, 'compte_id');
    }
}
