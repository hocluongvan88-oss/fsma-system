<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Location;
use App\Models\Partner;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    /**
     * Get product details by ID
     */
    public function getProduct($id)
    {
        $product = Product::forOrganization(auth()->user()->organization_id)
            ->findOrFail($id);
            
        return response()->json([
            'id' => $product->id,
            'sku' => $product->sku,
            'product_name' => $product->product_name,
            'description' => $product->description,
            'category' => $product->category,
            'unit_of_measure' => $product->unit_of_measure,
            'is_ftl' => $product->is_ftl,
        ]);
    }

    /**
     * Get location details by ID
     */
    public function getLocation($id)
    {
        $location = Location::forOrganization(auth()->user()->organization_id)
            ->findOrFail($id);
            
        return response()->json([
            'id' => $location->id,
            'location_name' => $location->location_name,
            'gln' => $location->gln,
            'ffrn' => $location->ffrn,
            'address' => $location->address,
            'city' => $location->city,
            'state' => $location->state,
            'zip_code' => $location->zip_code,
            'country' => $location->country,
            'location_type' => $location->location_type,
            'full_address' => $location->getFullAddress(),
        ]);
    }

    /**
     * Get partner details by ID
     */
    public function getPartner($id)
    {
        $currentUser = auth()->user();
        $partner = Partner::forOrganization($currentUser->organization_id)
            ->findOrFail($id);
        
        $data = [
            'id' => $partner->id,
            'partner_name' => $partner->partner_name,
            'partner_type' => $partner->partner_type,
            'gln' => $partner->gln,
        ];
        
        // Only managers and admins can see contact details (principle of least privilege)
        if ($currentUser->hasRole('manager') || $currentUser->hasRole('admin')) {
            $data['contact_person'] = $partner->contact_person;
            $data['email'] = $partner->email;
            $data['phone'] = $partner->phone;
            $data['address'] = $partner->address;
        }
        
        return response()->json($data);
    }

    /**
     * Get all FTL products for dropdown
     */
    public function getFTLProducts()
    {
        $products = Product::ftl()
            ->forOrganization(auth()->user()->organization_id)
            ->select('id', 'sku', 'product_name', 'unit_of_measure')
            ->orderBy('product_name')
            ->get();
            
        return response()->json($products);
    }

    /**
     * Get all locations for dropdown
     */
    public function getLocations()
    {
        $locations = Location::forOrganization(auth()->user()->organization_id)
            ->select('id', 'location_name', 'gln', 'location_type')
            ->orderBy('location_name')
            ->get();
            
        return response()->json($locations);
    }

    /**
     * Get all suppliers for dropdown
     */
    public function getSuppliers()
    {
        $suppliers = Partner::suppliers()
            ->forOrganization(auth()->user()->organization_id)
            ->select('id', 'partner_name', 'gln', 'partner_type')
            ->orderBy('partner_name')
            ->get();
            
        return response()->json($suppliers);
    }

    /**
     * Get all customers for dropdown
     */
    public function getCustomers()
    {
        $customers = Partner::customers()
            ->forOrganization(auth()->user()->organization_id)
            ->select('id', 'partner_name', 'gln', 'partner_type')
            ->orderBy('partner_name')
            ->get();
            
        return response()->json($customers);
    }
}
