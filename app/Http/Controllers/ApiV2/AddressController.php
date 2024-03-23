<?php


namespace App\Http\Controllers\ApiV2;

use App\Models\Address;
use App\Models\District;
use App\Models\Product;
use App\Models\Province;
use App\Models\Ward;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAddressRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    protected $guard = 'customer';

    public function __construct()
    {
    }

    public function getProvinces()
    {
        return Province::select(['id as value', 'name as label'])->get();
    }

    public function getDistrictsByProvince($id)
    {
        return District::where('province_id', $id)->select(['id as value', 'name as label'])->get();
    }

    public function getWardsByDistrict($id)
    {
        return Ward::where('district_id', $id)->select(['id as value', 'name as label'])->get();
    }

    public function index(Request $request)
    {
        $customer = Auth::guard($this->guard)->user();
        $query = Address::whereNull('deleted_at')
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc');

        if ($request->get('q')) {
            $query->where(function ($q) use ($request) {
                $search = $request->get('q');
                $q->where('phone', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
            });
        }

        $result = $query->limit($request->perpage + 1)
            ->offset(($request->get('page', 1) - 1) * $request->perpage)
            ->get();

        $hasMore = $result->count() > $request->perpage;

        return [
            'items' => $result->splice(0, $request->perpage),
            'hasMore' => $hasMore
        ];
    }

    public function store(CustomerAddressRequest $request)
    {
        $customer = Auth::guard($this->guard)->user();
        Address::create([
            'customer_id' => $customer->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'province' => $request->province,
            'district' => $request->district,
            'ward' => $request->ward,
            'default' => $request->has('default')
        ]);
        return response()->json([]);
    }

    public function update($id, CustomerAddressRequest $request)
    {
        $customer = Auth::guard($this->guard)->user();
        Address::where('customer_id', $customer->id)
            ->where('id', $id)
            ->first()
            ->update($request->only([
                'name',
                'phone',
                'address',
                'province',
                'district',
                'ward',
                'default'
            ]));
        if (!Address::where('customer_id', $customer->id)
                ->where('id', $id)
                ->where('default', 1)
                ->exists()
        ) {
            Address::where('customer_id', $customer->id)
                ->where('id', $id)
                ->orderBy('id', 'asc')
                ->first()
                ->update([
                    'default' => 1
                ]);
        }

        return response()->json([]);
    }

    public function delete($id)
    {
        $customer = Auth::guard($this->guard)->user();
        Address::where('customer_id', $customer->id)
            ->where('id', $id)
            ->delete();
        if (!Address::where('customer_id', $customer->id)
            ->where('id', $id)
            ->where('default', 1)
            ->exists()
        ) {
            Address::where('customer_id', $customer->id)
                ->where('id', $id)
                ->orderBy('id', 'asc')
                ->first()
                ->update([
                    'default' => 1
                ]);
        }

        return response()->json([]);
    }
}
