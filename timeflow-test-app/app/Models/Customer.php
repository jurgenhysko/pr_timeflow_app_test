<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
class Customer extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query) 
    {
        return $query->where('is_active', true);
    }
    
    public static $sortable = ['name', 'email', 'address', 'is_active', 'created_at', 'updated_at'];

    public function getProducts()
    {
        $productIds = OrderDetail::whereIn('order_id', $this->orders->pluck('id'))->pluck('product_id');
        return Product::whereIn('id', $productIds)->get();
    }

    public function favoriteProduct()
    {
       return OrderDetail::leftJoin('orders', 'order_product.order_id', '=', 'orders.id')
        ->where('orders.customer_id', $this->id)
        ->groupBy('product_id')
        ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))  
        ->orderBy('total_quantity', 'desc')
        ->first();
    }

}
