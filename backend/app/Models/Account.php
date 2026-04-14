<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $table = 'accounts';

    protected $fillable = [
        'email',
        'password',
        'account_status',
    ];

    protected $hidden = ['password'];

    public function user()
    {
        return $this->hasOne(User::class, 'account_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'account_id');
    }
}
