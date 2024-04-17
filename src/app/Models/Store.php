<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Subscription;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_id',
        'name',
        'image_path',
        'address',
        'postal_code',
        'tel_number',
        'opening_time',
        'closing_time',
        'working_time_unit_id',
        'subscription_id',
    ];

    public function storeDetails()
    {
        return $this->hasMany(StoreDetail::class);
    }

    public function groups()
    {
        return $this->belongsTo(Group::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
