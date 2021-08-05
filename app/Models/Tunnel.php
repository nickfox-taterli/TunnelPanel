<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tunnel extends Model
{
    use HasFactory;

    protected $fillable = [
        'remark',
        'uuid',
        'server_ipv4',
        'client_ipv4',
        'server_ipv6',
        'client_ipv6',
        'routed_ipv6',
        'bind',
    ];
}
