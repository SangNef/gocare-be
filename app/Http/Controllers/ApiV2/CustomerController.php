<?php


namespace App\Http\Controllers\ApiV2;

use App\Models\AZPoint;
use App\Models\Commission;
use App\Models\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Bank;
use App\Models\CustomerRevenue;
use App\Models\Group;
use App\Repositories\SocialAccountRepository;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Smssent;
use Illuminate\Http\Request;
use App\Models\Address;

class CustomerController extends Controller
{
    protected $guard = 'customer';

    public function login(LoginRequest $request)
    {
        $loginType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $customer = Customer::where($loginType, $request->username)
            ->where('customer_currency', Bank::CURRENCY_VND)
            ->first();
        if ($customer && Hash::check($request->password, $customer->password)) {
            $token = Auth::guard($this->guard)->attempt([$loginType => $request->username, 'password' => $request->password]);
            return response()->json([
                'user' => $customer->getDataForFE(),
                'token' => $token
            ]);
        }
        return response()->json([
            'error' => 'Thông tin đăng nhập không chính xác'
        ], 400);
    }

    public function getCurrentCustomer()
    {
        $token = substr(request()->header('Authorization'), 7);
        $customer = Auth::guard($this->guard)->user();
        return response()->json(['user' => $customer->getDataForFE(), 'token' => $token]);
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
        }
        return response()->json("OK");
    }

    public function update(CustomerRequest $request)
    {
        $otp = $request->otp;
        $phone = request()->get('phone');
        $customer = Auth::guard($this->guard)->user();
        
        if ($customer->phone != $phone && !SmsSent::where('username', $customer->id)
            ->where('message', 'AZpro - Ma so OTP cua quy khach tai https://azpro.net.vn/ la: '. $otp . '. Vui long khong chia se OTP cho bat ky ai.')
            ->where('phone', $phone)
            ->where('mod', 'Xác thực SĐT')
            ->where('created_at', '>=', \Carbon\Carbon::now()->subMinutes(30)->format('Y-m-d H:i:s'))
            ->exists()) {
                return response()->json([
                    'otp' => [
                        'Mã OTP không hợp lệ'
                    ]
                    ], 422);
            }
        SmsSent::where('username', $customer->id)
            ->where('message', 'AZpro - Ma so OTP cua quy khach tai https://azpro.net.vn/ la: '. $otp . '. Vui long khong chia se OTP cho bat ky ai.')
            ->where('phone', $phone)
            ->where('mod', 'Xác thực SĐT')
            ->update([
                'deleted_at' => \Carbon\Carbon::now()
            ]);
        Auth::guard($this->guard)->user()->update($request->all());
        if ($customer->addresses()->count() == 0) {
            Address::create([
                'customer_id' => $customer->id,
                'address' => $request->address,
                'province' => $request->province,
                'district' => $request->district,
                'ward' => $request->ward,
                'phone' => $request->phone,
                'name' => $request->username,
                'default' => 1
            ]);
        }

        return response()->json("");
    }

    // Socical login
    public function redirectToProvider($provider)
    {
        return response()->json([
            'url' => Socialite::driver($provider)->stateless()->redirect()->getTargetUrl()
        ]);
    }

    public function providerCallback($provider, SocialAccountRepository $socialAccountRp)
    {
        $customer = $socialAccountRp->createOrGetUser(Socialite::driver($provider)->stateless());
        $token = JWTAuth::fromUser($customer);
        return response()->json([
            'user' => $customer->getDataForFE(),
            'token' => $token
        ]);
    }

    public function getCommissions()
    {
        $customer = Auth::guard($this->guard)->user();
        $items = Commission::where('customer_id', $customer->id)
            ->paginate();

        return response()->json($items);
    }

    public function generateOTP(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required|regex:/^0/|digits_between:10,10',
        ]);
        $customer = Auth::guard($this->guard)->user();
        $phone = $request->phone;
        $otp = $this->generateNumericOTP(6);
        Smssent::create([
            'mod' => 'Xác thực SĐT',
            'username' => $customer->id,
            'phone' => $phone,
            'message' => 'AZpro - Ma so OTP cua quy khach tai https://azpro.net.vn/ la: '. $otp . '. Vui long khong chia se OTP cho bat ky ai.',
            'status' => 'Chờ gửi'
        ]);
        
        $cmd = "ulimit -n 10000;php  " . base_path() . '/artisan sms:send ';
        shell_exec($cmd . ' > /dev/null 2>&1 &');

        return response()->json('');

    }

    protected function generateNumericOTP($n) 
    {  
        $generator = "1357902468";
        $result = "";
      
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }
    
        return $result;
    }

    public function createSubCustomer(CustomerRequest $request)
    {
        $this->validate($request, [
            'phone' => 'unique:customers|unique:customers,username',
            'password' => 'required',
        ], [
            'phone.unique' => 'Số điện thoại đã được sử dụng'
        ]);
        $auth = Auth::guard($this->guard)->user();
        $group = Group::where('name', 'ctv')->first();
        $customer = Customer::create(array_merge($request->all(), [
            'customer_parent_id' => Auth::guard($this->guard)->user()->id,
            'email' => $request->phone . '_sub_customer@gocare.vn',
            'username' => $request->phone,
            'store_id' => $auth->store_id,
            'group_id' => $group->id,
            'can_create_sub' => 0,
        ]));
        $customer->password = $request->password;
        $customer->save();
        Address::create([
            'customer_id' => $customer->id,
            'address' => $request->address,
            'province' => $request->province,
            'district' => $request->district,
            'ward' => $request->ward,
            'phone' => $request->phone,
            'name' => $request->name,
            'default' => 1,
        ]);

        return response()->json('OK');
    }
    
    public function getSubCustomer(Request $request, CustomerRepository $rp)
    {
        $customer = Auth::guard($this->guard)->user();
        $customers = $rp->getSubCustomers($customer->id, $request->all())->paginate();
        $items = $customers->getCollection()->map(function ($customer) use($request, $rp) {
            $analysic = [];
            if ($request->analysic) {
                $analysic = $rp->analysisForCustomer(
                    $customer, 
                    \Carbon\Carbon::createFromFormat('Y-m-d', $request->revenue_from), 
                    \Carbon\Carbon::createFromFormat('Y-m-d', $request->revenue_to)
                );
            }
            $azpoint = AZPoint::where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->first();
            return array_merge([
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->getFullAddress(),
                'created_at' => $customer->created_at->format('d/m/Y'),
                'code' => $customer->code,
                'az_point' => $azpoint ? $azpoint->balance : 0,
                'cccd' => $customer->cccd,
                'bank_name' => $customer->bank_name,
                'bank_acc' => $customer->bank_acc,
                'bank_acc_name' => $customer->bank_acc_name,
            ], $analysic);
        });
        $analysic = [];
        if ($request->analysic) {
            $analysic = $rp->analysisForCustomer(
                $customer, 
                \Carbon\Carbon::createFromFormat('Y-m-d', $request->revenue_from), 
                \Carbon\Carbon::createFromFormat('Y-m-d', $request->revenue_to)
            );
        }
        return response()->json([
            'last_page' => $customers->lastPage(),
            'total' => $customers->total(),
            'has_more' => $customers->hasMorePages(),
            'items' => $items,
            'current' => $analysic,
        ]);
    }

    public function getCustomerReport($report_id, Request $request)
    {
        $customer = Auth::guard($this->guard)->user();
        $report = CustomerRevenue::where('customer_id', $customer->id);
        $report->where('id', $report_id);
        $report = $report->first();

        return response()->json($report);
    }

    public function getCustomerAvailableReports(Request $request)
    {
        $customer = Auth::guard($this->guard)->user();
        $reports = CustomerRevenue::select(['id', 'month'])
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reports);
    }

    public function getCustomerReportDetail($report_id, $area, Request $request)
    {
        $customer = Auth::guard($this->guard)->user();
        $report = CustomerRevenue::where('customer_id', $customer->id);
        $report->where('id', $report_id);
        $report = $report->first();
        if ($report) {
            switch ($area) {
                case 1:
                    $id = $report->getOrders()->get()->pluck('id')->toArray();
                    $request->merge([
                        'include' => $id
                    ]);
                    return app(OrderController::class)->index($request);
                case 2:
                    $id = $report->getOnlineOrders()->get()->pluck('id')->toArray();
                    $request->merge([
                        'include' => $id
                    ]);
                    return app(OrderController::class)->index($request);
                case 3:
                    $id = $report->getAffiliateOrders()->get()->pluck('id')->toArray();
                    $request->merge([
                        'include' => $id
                    ]);
                    return app(OrderController::class)->index($request);
                case 4:
                    $id = $report->getActivations()->get()->pluck('seri_number')->toArray();
                    $request->merge([
                        'seri_numbers' => count($id) > 0 ? implode(',', $id) : '-1'
                    ]);
                    return app(ProductSeriController::class)->index($request);
                case 5:
                    $id = $report->getAffiliateActivations()->get()->pluck('seri_number')->toArray();
                    $request->merge([
                        'seri_numbers' => count($id) > 0 ? implode(',', $id) : '-1'
                    ]);
                    return app(ProductSeriController::class)->index($request);
            }
        }

        return response()->json($report);
    }
}
