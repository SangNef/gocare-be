<?php
/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers;

use App\Helper\CustomLAHelper;
use App\Http\Requests;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentHistory;
use App\Models\ProductSeri;
use App\Models\Smssent;
use App\Models\Transaction;
use App\Repositories\OrderRepository;
use App\Repositories\OrderTransactionRepository;
use App\Services\EsmsService;
use App\Services\Payments\ViettelMoneyService;
use App\Services\Payments\VnpayService;
use App\Services\ViettelActivationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        $roleCount = \App\Role::count();
        if ($roleCount != 0) {
            if ($roleCount != 0) {
                return view('home');
            }
        } else {
            return view('errors.error', [
                'title' => 'Migration not completed',
                'message' => 'Please run command <code>php artisan db:seed</code> to generate required table data.',
            ]);
        }
    }

    public function sendEmail()
    {
        $data = []; // Empty array

        $a = Mail::send('welcome', $data, function ($message) {
            $message->to('hopvq89@gmail.com', 'Gocare')->subject('test email');
        });
        dd($a);
    }

    public function eSMSCallback(Request $request)
    {
        /** @var EsmsService $esms */
        $esms = app(EsmsService::class);
        $requestId = $request->RequestId;
        $sms = Smssent::find($requestId);
        if ($sms) {
            $sms->status = $esms->getStatusLabel($request->SendStatus);
            if ($request->SendStatus == 5) {
                $sms->result = json_encode($request->all());
            }
            $sms->save();
        }
    }

    public function confirmPaymentFromViettel(Request $request)
    {
        \Log::error($request->all());
        $payment = PaymentHistory::find($request->get('order_id'));
        if ($payment) {
            $service = app(ViettelMoneyService::class);
            $requestedData = json_decode($payment->request, true);
            $total = @$requestedData['total'];
            $data = [
                'order_id' => $payment->id,
                'trans_amount' => $total,
            ];

            $result = $service->verifyChecksum($request->check_sum, $data);
            if ($result) {
                $response = [
                    'error_code' => '00',
                    'billcode' => $payment->id,
                    'order_id' => $payment->id,
                    'trans_amount' => $total,
                    'check_sum' => $service->generateChecksum([
                        'billcode' => $payment->id,
                        'error_code' => '00',
                        'merchant_code' => $request->merchant_code,
                        'order_id' => $payment->id,
                        'trans_amount' => $total,
                    ])
                ];
                \Log::error($response);
                return response()->json($response);
            } else {
                return response()->json([
                    'error_code' => '01',
                ]);
            }
        }
        return response()->json([
            'error_code' => '02',
        ]);
    }

    public function vnpayIpn(Request $request, OrderTransactionRepository $otp)
    {
        \Log::error($request->all());
        $paymentHistory = PaymentHistory::find($request->get('vnp_TxnRef'));

        $Status = 1; // Là trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán.
        try {
            if ($paymentHistory) {
                $payment = app(VnpayService::class);
                $paymentStatus = $payment->verifyPayment($request->all());
                $requestedData = json_decode($paymentHistory->request, true);
                $total = @$requestedData['total'];
                //Check Orderid
                //Kiểm tra checksum của dữ liệu
                if ($paymentStatus['status'] == 1) {
                    $vnpTranId = $paymentStatus['trans_id']; //Mã giao dịch tại VNPAY
                    $vnp_Amount = $paymentStatus['amount']; // Số tiền thanh toán VNPAY phản hồi
                    //Lấy thông tin đơn hàng lưu trong Database và kiểm tra trạng thái của đơn hàng, mã đơn hàng là: $orderId
                    //Việc kiểm tra trạng thái của đơn hàng giúp hệ thống không xử lý trùng lặp, xử lý nhiều lần một giao dịch
                    //Giả sử: $order = mysqli_fetch_assoc($result);
                    if ($paymentHistory) {
                        if ($total == $vnp_Amount) //Kiểm tra số tiền thanh toán của giao dịch: giả sử số tiền kiểm tra là đúng. //$order["Amount"] == $vnp_Amount
                        {
                            if ($paymentHistory->status != 3) {
                                if ($request->vnp_ResponseCode == '00' && $request->vnp_TransactionStatus == '00') {
                                    $Status = 3; // Trạng thái thanh toán thành công
                                } else {
                                    $Status = 4; // Trạng thái thanh toán thất bại / lỗi
                                }
                                //Cài đặt Code cập nhật kết quả thanh toán, tình trạng đơn hàng vào DB
                                $paymentHistory->response_2 = json_encode($request->all());
                                $paymentHistory->status = $Status;
                                $paymentHistory->trans_id = $vnpTranId;
                                $paymentHistory->save();
                                if ($paymentHistory->status == 3) {
                                    if ($paymentHistory->order_id) {
                                        $order = Order::find($paymentHistory->order_id);
                                        $order->paid = $paymentStatus['amount'];
                                        $order->unpaid = $order->total - $order->paid;
                                        $order->save();
                                        if ($order) {
                                            $customer = Customer::where('id', $order->customer_id)->first();
                                            if ($customer->email) {
                                                $data = [];
                                                $data['name'] = $customer->name;
                                                $data['key'] = $order->access_key;
                                                $data['address'] = $customer->address . ', ' . $customer->ward . ',' . $customer->district . ', ' . $customer->province;
                                                $html = view('emails.order', compact('data'))->render();
                                                CustomLAHelper::sendEmail($html, $customer->email, 'Xác nhận đơn đặt hàng tại Gocare.vn!', $customer->name);
                                            }
                                        }
                                    }
                                    $activationService = app(ViettelActivationService::class);
                                    $requested = json_decode($paymentHistory->request, true);
                                    if (@$requested['seri_numbers']) {
                                        $codes = ProductSeri::whereIn('seri_number', explode(',', $requested['seri_numbers']))
                                            ->whereNotNull('activation_code')
                                            ->pluck('activation_code')
                                            ->toArray();
                                        try {
                                            $result = $activationService->activate($codes);
                                            foreach ($result as $item) {
                                                if (@$item['status'] == 'ok') {
                                                    ProductSeri::where('activation_code', $item['code'])
                                                        ->update([
                                                            'status' => 1,
                                                            'purchased_date' => Carbon::now(),
                                                        ]);
                                                }
                                            }
                                        } catch (\Exception $exception) {
                                            \Log::error($exception->getMessage());
                                            \Log::error($exception->getTraceAsString());
                                        }
                                    }
                                }
                                //Trả kết quả về cho VNPAY: Website/APP TMĐT ghi nhận yêu cầu thành công
                                $returnData['RspCode'] = '00';
                                $returnData['Message'] = 'Confirm Success';
                            } else {
                                $returnData['RspCode'] = '02';
                                $returnData['Message'] = 'Order already confirmed';
                            }
                        } else {
                            $returnData['RspCode'] = '04';
                            $returnData['Message'] = 'invalid amount';
                        }
                    }
                } else {
                    $returnData['RspCode'] = '97';
                    $returnData['Message'] = 'Invalid signature';
                }
            } else {
                $returnData['RspCode'] = '01';
                $returnData['Message'] = 'Order not found';
            }
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            \Log::error($exception->getTraceAsString());
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknow error';
        }
        return response()->json($returnData);
    }

    public function pingbackPaymentFromViettel(Request $request)
    {
        \Log::error($request->all());
        $paymentHistory = PaymentHistory::find($request->order_id);
        if ($paymentHistory) {
            $paymentHistory->response_2 = json_encode($request->all());
            $paymentHistory->save();
            $service = app(ViettelMoneyService::class);
            $data = [
                'cust_msisdn' => $request->cust_msisdn,
                'error_code' => $request->error_code,
                'payment_status' => $request->payment_status,
                'trans_amount' => $request->trans_amount,
                'vt_transaction_id' => $request->vt_transaction_id,
                'order_id' => $paymentHistory->id
            ];

            $result = $service->verifyChecksum($request->check_sum, $data);

            if ($result && $request->error_code === '00') {
                $paymentHistory->trans_id = $request->vt_transaction_id;
                $paymentHistory->status = $request->payment_status == 1 ? 3 : 4;
            } else {
                $paymentHistory->message = 'Checksum không hợp lệ hoặc giao dịch không thành công';
            }
            $paymentHistory->save();

            if ($paymentHistory->status == 3) {
                if ($paymentHistory->order_id) {
                    $order = Order::find($paymentHistory->order_id);
                    $order->paid = $request->trans_amount;
                    $order->unpaid = $order->total - $order->paid;
                    $order->save();
                    if ($order) {
                        $customer = Customer::where('id', $order->customer_id)->first();
                        if ($customer->email) {
                            $data = [];
                            $data['name'] = $customer->name;
                            $data['key'] = $order->access_key;
                            $data['address'] = $customer->address . ', ' . $customer->ward . ',' . $customer->district . ', ' . $customer->province;
                            $html = view('emails.order', compact('data'))->render();
                            CustomLAHelper::sendEmail($html, $customer->email, 'Xác nhận đơn đặt hàng tại Gocare.vn!', $customer->name);
                        }
                    }
                }
                $activationService = app(ViettelActivationService::class);
                $requested = json_decode($paymentHistory->request, true);
                if (@$requested['seri_numbers']) {
                    $codes = ProductSeri::whereIn('seri_number', explode(',', $requested['seri_numbers']))
                        ->whereNotNull('activation_code')
                        ->pluck('activation_code')
                        ->toArray();
                    try {
                        $result = $activationService->activate($codes);
                        foreach ($result as $item) {
                            if (@$item['status'] == 'ok') {
                                ProductSeri::where('activation_code', $item['code'])
                                    ->update([
                                        'status' => 1,
                                        'purchased_date' => Carbon::now(),
                                    ]);
                            }
                        }
                    } catch (\Exception $exception) {
                        \Log::error($exception->getMessage());
                        \Log::error($exception->getTraceAsString());
                    }
                }
            }
        }

        return response()->json([
            'merchant_code' => $request->merchant_code,
            'order_id' => $request->order_id,
            'error_code' => '00'
        ]);
    }

    public function activatedPingback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apiKey' => 'required|in:' . config('app.activated_api_key'),
            'code' => 'required',
            'codeStatus' => 'required',
            'userId' => 'required',
            'activatedDate' => 'required|date_format:Y-m-d H:i:s'
        ]);

        if ($validator->fails()) {
            return response([
                'error_code' => '2',
                'stauts' => 'error',
                'message' => $validator->messages(),
            ], 200);
        } else {
            $code = ProductSeri::where('activation_code', $request->code)->first();
            if ($code) {
                $code->status = (int)$request->codeStatus;
                $code->user_Id = $request->userId;
                $code->activated_date = $request->activatedDate;
                $code->save();
                return [
                    'error_code' => '1',
                    'status' => 'ok',
                ];
            } else {
                return response([
                    'error_code' => '3',
                    'stauts' => 'error',
                    'message' => ['code' => 'Mã kích hoạt không tồn tại'],
                ], 200);
            }
        }

    }
}
