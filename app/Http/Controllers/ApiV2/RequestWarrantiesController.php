<?php

namespace App\Http\Controllers\ApiV2;

use Illuminate\Http\Request;

use App\Http\Requests\RequestWarranty as RW;
use App\Http\Controllers\Controller;
use App\Models\RequestWarranty as Model;
use App\Models\RequestWarranty;
use App\Repositories\ProductSeriesRepository;
use App\Services\Upload;
use Illuminate\Support\Facades\Storage;

class RequestWarrantiesController extends Controller
{
    protected $requestWarranty;
    protected $productSeriesRp;

    public function __construct(
        Model $requestWarranty,
        ProductSeriesRepository $productSeriesRp
    ) {
        $this->middleware('api-v2', ['except' => [
            'store'
        ]]);
        $this->requestWarranty = $requestWarranty;
        $this->productSeriesRp = $productSeriesRp;
    }

    public function index(Request $request)
    {
        $results = $this->requestWarranty
            ->where('seri_number', $request->warranty_param)
            ->orWhere('phone', $request->warranty_param)
            ->select(['id', 'seri_number', 'product_name', 'created_at', 'status'])
            ->paginate($request->get('perpage', 15));

        return response()->json([
            'last_page' => $results->lastPage(),
            'total' => $results->total(),
            'has_more' => $results->hasMorePages(),
            'items' => $results->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'seri_number' => $item->seri_number,
                    'product_name' => $item->product_name,
                    'created_at' => \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i:s'),
                    'status' => Model::getListStatus()[$item->status]
                ];
            })
        ]);
    }

    public function show($id)
    {
        $rw = $this->requestWarranty->find($id);
        return $rw
            ? response()->json([
                'seri_number' => $rw->seri_number,
                'product_name' => $rw->product_name,
                'created_at' => \Carbon\Carbon::parse($rw->created_at)->format('d-m-Y H:i:s'),
                'status' => Model::getListStatus()[$rw->status],
                'content' => $rw->content,
                'name' => $rw->name,
                'phone' => $rw->phone,
                'address' => $rw->getFullAddress()
            ])
            : response()->json([], 404);
    }

    public function store(RW $request, Upload $uploadSv)
    {
        $pSeri = $this->productSeriesRp->getBySeri($request->seri);
        if ($pSeri) {
            $attachmentIds = [];
            if ($request->has('images') && !empty($request->images)) {
                foreach ($request->images as $key => $image) {
                    $folder = storage_path('uploads');
                    $fileName = $image->getClientOriginalName();
                    $storeFile = $image->move($folder . '/warranties/' . $request->seri, $fileName);
                    if ($storeFile) {
                        $path = $folder . '/warranties/' . $request->seri . '/' . $fileName;
                        $upload = $uploadSv->storeFile($fileName, $path);
                        $attachmentIds[] = $upload->id;
                    }
                }
            }
            $this->requestWarranty->create([
                'seri_number' => $request->seri,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'province' => $request->province,
                'district' => $request->district,
                'ward' => $request->ward,
                'product_name' => $pSeri->product->name,
                'content' => $request->content,
                'attachments' => $attachmentIds,
                'from' => RequestWarranty::FROM_FE
            ]);
            return response()->json("Tạo yêu cầu thành công");
        }
        return response()->json("Seri không tồn tại hoặc đã kích hoạt", 422);
    }
}
