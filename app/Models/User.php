<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'username',
        'password',
        'name',
    ];

    public $timestamps = true;

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'user_id', 'id');
    }
}
