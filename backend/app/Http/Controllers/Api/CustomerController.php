<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Http\Resources\CustomerAddressResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponse;

    public function addresses(Request $request)
    {
        $addresses = $request->user()->addresses;
        return $this->success(CustomerAddressResource::collection($addresses));
    }

    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string',
            'address_line1' => 'required|string',
            'address_line2' => 'nullable|string',
            'city'          => 'required|string',
            'state'         => 'required|string',
            'postal_code'   => 'required|string',
            'country'       => 'required|string',
            'is_default'    => 'boolean',
        ]);

        $user = $request->user();

        if ($validated['is_default'] ?? false) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validated);

        return $this->success(new CustomerAddressResource($address), 'Address created', 201);
    }
}
