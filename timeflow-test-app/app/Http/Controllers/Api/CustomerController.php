<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerProfileResource;
class CustomerController extends BaseController
{

    public function index(Request $request): JsonResponse
    {
        try {
        $query = Customer::query();

        if(!$request->has('with_inactive')) {
            $query = $query->active();
        }
        if($request->has('qs')) {
            $qs = $request->get('qs', '');
            $query = $query->where(function($q) use ($qs) {
                $q->where('name', 'like', "%$qs%")
                ->orWhere('email', 'like', "%$qs%")
                ->orWhere('phone', 'like', "%$qs%")
                ->orWhere('address', 'like', "%$qs%");
            });
        }
        
        $query = $this->prepareSorting($query, Customer::$sortable);
        
        $customers = $this->paginate($query);

            return $this->success(CustomerResource::collection($customers));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function store(StoreCustomerRequest $request): JsonResponse
    {
        try {
            $customer = Customer::create($request->validated());
            return $this->success(new CustomerResource($customer), 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    public function show(string $id): JsonResponse
    {
        try {
            $customer = Customer::with('orders')->findOrFail($id);
            return $this->success(new CustomerResource($customer));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

 
    public function update(UpdateCustomerRequest $request, string $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->update($request->validated());
            return $this->success(new CustomerResource($customer));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->delete();
            return $this->success(new CustomerResource($customer));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function customerProfile(string $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            return $this->success(new CustomerProfileResource($customer));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
}
