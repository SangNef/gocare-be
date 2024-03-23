<?php

namespace App\Http\Controllers\ApiV2;

use App\Models\Customer;
use App\Models\Draw;
use App\Models\ProductSeri;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Config;
use App\Services\Upload;

class ConfigController extends Controller
{
    protected $uploadSv;

    public function __construct(Upload $uploadSv)
    {
        $this->uploadSv = $uploadSv;
    }

    public function homePageSlider()
    {
        $sliders = array_map(function ($item) {
            return $this->uploadSv->getImagePath($item);
        }, Config::getAZProSliderConfig());

        return response()->json([
            'messages' => 'Success',
            'images' => $sliders
        ]);
    }

    public function homePage()
    {
        $data = [];
        $section1 = Config::getHomeSection1();
        $section2 = Config::getHomeSection2();
        $section3 = Config::getHomeSection3();
        $home_section4 = Config::getHomeSection4();
        $home_section5 = Config::getHomeSection5();
        $section1->popup_img = $this->uploadSv->getImagePath($section1->popup_img);
        $section1->img1 = $this->uploadSv->getImagePath($section1->img1);
        $section1->img2 = $this->uploadSv->getImagePath($section1->img2);
        $section1->img3 = $this->uploadSv->getImagePath($section1->img3);
        $section2->desktop = $this->uploadSv->getImagePath($section2->desktop);
        $section2->mobile = $this->uploadSv->getImagePath($section2->mobile);
        $section3->img1 = $this->uploadSv->getImagePath($section3->img1);
        $section3->img2 = $this->uploadSv->getImagePath($section3->img2);
        $section3->img3 = $this->uploadSv->getImagePath($section3->img3);
        $section3->img4 = $this->uploadSv->getImagePath($section3->img4);
        $section3->img5 = $this->uploadSv->getImagePath($section3->img5);
        $section3->img6 = $this->uploadSv->getImagePath($section3->img6);
        $section3->img7 = $this->uploadSv->getImagePath($section3->img7);
        $section3->img8 = $this->uploadSv->getImagePath($section3->img8);
        $title = explode('-', $section1->title);
        if (count($title) == 3) {
            $section1->title = $title[0] . '<span>' . $title[1] . '</span>' . $title[2];
        }
        $data['section1'] = $section1;
        $data['section2'] = $section2;
        $data['section3'] = $section3;
        $section4 = [];
        if (count($home_section4) > 0) {
            foreach ($home_section4->name as $k => $v) {
                if ($k > 0) {
                    $section4[$k]['name'] = $v;
                    $section4[$k]['job'] = $home_section4->job[$k];
                    $section4[$k]['text'] = $home_section4->text[$k];
                }
            }
        }
        $section5 = [];
        if (count($home_section5) > 0) {
            foreach ($home_section5->link as $k => $v) {
                if ($k > 0) {
                    $section5[$k]['link'] = $v;
                }
            }
        }
        $data['section4'] = $section4;
        $data['section5'] = $section5;
        return response()->json([
            'messages' => 'Success',
            'data' => $data
        ]);
    }

    public function getBanks(Bank $bank)
    {
        return response()->json($bank->getBankByAccName());
    }

    public function getDraws()
    {
        if (!auth()->check() || auth()->user()->id != config('app.luckydraw')) {
            return [];
        }

        return Draw::whereNull('deleted_at')
            ->orderBy('index', 'asc')
            ->get()
            ->map(function (Draw $draw) {
                if ($draw->prize_img) {
                    /** @var Upload $sv */
                    $sv = app(Upload::class);
                    $draw->prize_img = $sv->getImagePath($draw->prize_img);
                }
                if ($draw->lists) {
                    $seriIds = explode(',', $draw->lists);
                    $draw->lists = ProductSeri::whereIn('id', $seriIds)
                        ->get()
                        ->map(function (ProductSeri $seri) {
                            return [
                                'id' => $seri->id,
                                'name' => $seri->seri_number . ', ' . $seri->name . ', ' . substr($seri->phone, 0, -3) . '***',
                            ];
                        });
                }
                return $draw;
            });
    }

    public function getDrawWinner($id)
    {
        if (!auth()->check() || auth()->user()->id != config('app.luckydraw')) {
            return [];
        }

        $draw = Draw::find($id);

        if ($draw->winner) {
            $customer = ProductSeri::find($draw->winner);
        } else {
            $customerIds = explode(',', $draw->lists);
            $customer = ProductSeri::whereIn('id', $customerIds)->get()->random();
        }

        return [
            'id' => $customer->id,
            'name' => $customer->seri_number,
            'address' => $customer->name . ', ' . substr($customer->phone, 0, -3) . '***' . ', ' . $customer->getWarrantyFullAddressAttribute(),
        ];
    }

    public function getAvailablePaymentMethod()
    {
        return [
            [
                'code' => 'Vnpay',
                'name' => 'Thanh toán bằng VNPAY',
                'logo' => 'https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png'
            ],
            [
                'code' => 'Viettel',
                'name' => 'Thanh toán bằng Viettel Money',
                'logo' => 'https://gocare.onespace.click/files/9qyrlrnjdwf4ndvcsxp8/unnamed.png'
            ],
        ];
    }


}
