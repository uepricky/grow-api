<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrinterSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'store_id',
        'name',
        'port',
        'ip_address',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
