<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Services\Upload;
use Illuminate\Http\Request;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use App\Models\Config;
use App\Models\Group;

class ConfigsController extends Controller
{
    public $show_action = true;
    public $view_col = 'key';
    public $listing_cols = ['id', 'key', 'value', 'desc'];

    public function __construct()
    {
        // Field Access of Listing Columns
        if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
            $this->middleware(function ($request, $next) {
                $this->listing_cols = ModuleFields::listingColumnAccessScan('Configs', $this->listing_cols);
                return $next($request);
            });
        } else {
            $this->listing_cols = ModuleFields::listingColumnAccessScan('Configs', $this->listing_cols);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Upload $uploadSv)
    {
        $module = Module::get('Configs');

        if (Module::hasAccess($module->id)) {
//            $contacts = Config::getContactConfigs();
//            $azConfigs = Config::getAzConfigs();
//            $vtpConfig = Config::getVTPConfigs();
//            $ghnConfig = Config::getGHNConfigs();
//            $ghtkConfig = Config::getGHTKConfigs();
//            $ghtkDefaultAccessToken = Config::getGHTKDefaultAccessToken();
//            $warrantyUnits = Group::getWarrantyUnitsGroup();
//            $feDiscountGroups = Group::getFEDiscountGroups();
//
//            return View('la.configs.index', [
//                'contacts' => $contacts,
//                'azConfigs' => $azConfigs,
//                'vtpConfig' => $vtpConfig,
//                'ghnConfig' => $ghnConfig,
//                'ghtkConfig' => $ghtkConfig,
//                'warrantyUnits' => $warrantyUnits,
//                'feDiscountGroups' => $feDiscountGroups,
//                'ghtkDefaultAccessToken' => $ghtkDefaultAccessToken
//            ]);
            $home_section1 = Config::getHomeSection1();
            $home_section2 = Config::getHomeSection2();
            $home_section3 = Config::getHomeSection3();
            $home_section4 = Config::getHomeSection4();
            $home_section5 = Config::getHomeSection5();
            $home_section1->imagepopup = isset($home_section1->popup_img) ? $uploadSv->getImagePath($home_section1->popup_img) . '?s=300' : '';
            $home_section1->image1 = isset($home_section1->img1) ? $uploadSv->getImagePath($home_section1->img1) . '?s=190' : '';
            $home_section1->image2 = isset($home_section1->img2) ? $uploadSv->getImagePath($home_section1->img2) . '?s=190' : '';
            $home_section1->image3 = isset($home_section1->img3) ? $uploadSv->getImagePath($home_section1->img3) . '?s=190' : '';
            $home_section2->image1 = isset($home_section2->desktop) ? $uploadSv->getImagePath($home_section2->desktop) . '?s=190' : '';
            $home_section2->image2 = isset($home_section2->mobile) ? $uploadSv->getImagePath($home_section2->mobile) . '?s=190' : '';
            $home_section3->image1 = isset($home_section3->img1) ? $uploadSv->getImagePath($home_section3->img1) . '?s=190' : '';
            $home_section3->image2 = isset($home_section3->img2) ? $uploadSv->getImagePath($home_section3->img2) . '?s=190' : '';
            $home_section3->image3 = isset($home_section3->img3) ? $uploadSv->getImagePath($home_section3->img3) . '?s=190' : '';
            $home_section3->image4 = isset($home_section3->img4) ? $uploadSv->getImagePath($home_section3->img4) . '?s=190' : '';
            $home_section3->image5 = isset($home_section3->img5) ? $uploadSv->getImagePath($home_section3->img5) . '?s=190' : '';
            $home_section3->image6 = isset($home_section3->img6) ? $uploadSv->getImagePath($home_section3->img6) . '?s=190' : '';
            $home_section3->image7 = isset($home_section3->img7) ? $uploadSv->getImagePath($home_section3->img7) . '?s=190' : '';
            $home_section3->image8 = isset($home_section3->img8) ? $uploadSv->getImagePath($home_section3->img8) . '?s=190' : '';
            $sliders = [];
            foreach (Config::getAZProSliderConfig() as $value) {
                $sliders[$value] = $uploadSv->getImagePath($value) . '?s=90';
            }
            return View('la.configs.home', [
                'home_section1' => $home_section1,
                'home_section2' => $home_section2,
                'home_section3' => $home_section3,
                'home_section4' => $home_section4,
                'home_section5' => $home_section5,
                'sliders' => $sliders
            ]);

        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $module = Module::get('Configs');

        if (Module::hasAccess($module->id)) {
            $datas = $request->all();
            if ($request->get('desktop') && $request->get('mobile')) {
                $datas['home_section_2']['desktop'] = $request->get('desktop');
                $datas['home_section_2']['mobile'] = $request->get('mobile');
            }
            foreach ($datas as $key => $value) {
                if ($key == 'home_section_1') {
                    $value['img1'] = $request->home_section_1_img1;
                    $value['img2'] = $request->home_section_1_img2;
                    $value['img3'] = $request->home_section_1_img3;
                    $value['popup_img'] = $request->home_section_1_popup_img;
                }
                if ($key == 'home_section_3') {
                    $value['img1'] = $request->home_section3_img1;
                    $value['img2'] = $request->home_section3_img2;
                    $value['img3'] = $request->home_section3_img3;
                    $value['img4'] = $request->home_section3_img4;
                    $value['img5'] = $request->home_section3_img5;
                    $value['img6'] = $request->home_section3_img6;
                    $value['img7'] = $request->home_section3_img7;
                    $value['img8'] = $request->home_section3_img8;
                }
                if (in_array($key, ['home_section_1', 'home_section_2', 'home_section_3', 'home_section_4', 'home_section_5'])) {
                    $value = json_encode($value);
                }
                Config::where('key', $key)->update(['value' => $value]);
            }
        }

        return redirect(config('laraadmin.adminRoute') . "/configs");
    }

    public function azproConfig(Upload $uploadSv)
    {
        $module = Module::get('Configs');
        if (Module::hasAccess($module->id)) {
            $sliders = [];
            foreach (Config::getAZProSliderConfig() as $value) {
                $sliders[$value] = $uploadSv->getImagePath($value) . '?s=90';
            }
            return View('la.configs.slider', [
                'sliders' => $sliders
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }
}
