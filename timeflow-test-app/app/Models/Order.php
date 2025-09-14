<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Product;
use App\Models\OrderDetail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'customer_id',
        'total_amount',
        'status',
        'order_date',
    ];

    // protected $casts = [
    //     'order_date' => 'datetime:Y-m-d',
    // ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity', 'price');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public static $sortable = [
        'id', 
        'status', 
        'order_date', 
        'total_amount', 
        'created_at', 
        'orders.id', 
        'orders.status', 
        'orders.order_date', 
        'orders.total_amount', 
        'orders.created_at',
        'customers.name',
        'customers.email',
        'customers.phone',
        'customers.address'
    ];

    public static $sortFieldMap = [
        'id' => 'orders.id',
        'status' => 'orders.status',
        'order_date' => 'orders.order_date',
        'total_amount' => 'orders.total_amount',
        'created_at' => 'orders.created_at'
    ];
}