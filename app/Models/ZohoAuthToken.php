<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZohoAuthToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'refresh_token',
        'access_token',
    ];
}
