<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'name',
        'amount'
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
