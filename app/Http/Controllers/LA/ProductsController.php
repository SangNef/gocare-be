<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Helper\StringHelper;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\DOrder;
use App\Models\Group;
use App\Models\Bank;
use App\Models\Config;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductCombo;
use App\Models\ProductGroupAttributeMedia;
use App\Models\ProductRelated;
use App\Models\ProductSeri;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Repositories\AttributeValueRepository;
use App\Repositories\ProductRepository;
use App\Services\Discount;
use App\Services\RelatedProduct;
use App\Http\Requests\ProductRequest;
use App\Services\Upload;
use App\Models\Upload as UploadModel;
use Dompdf\Exception;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Cache;
use App\Datatable\Datatables;
use App\Models\Customer;
use Collective\Html\FormFacade as Form;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFields;
use PDF;
use App\Models\ProductsProductCategory;
use App\Repositories\ProductsProductCategoryRepository;
use App\Models\Product;
use App\Repositories\ProductSeriesRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use App\Models\StoreProductGroupAttributeExtra;
use Maatwebsite\Excel\Facades\Excel;

class ProductsController extends Controller
{
    protected $ppcRepository;

    public $show_action = true;
    public $view_col = 'sku';
    public $listing_cols = ['id', 'category_ids', 'featured_image', 'sku', 'name', 'price', 'retail_price', 'price_in_ndt', 'n_quantity', 'w_quantity', 'quantity', 'status', 'type'];

    public function __construct(ProductsProductCategoryRepository $ppcRepository)
    {
        // Field Access of Listing Columns
        if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
            $this->middleware(function ($request, $next) {
                $this->listing_cols = ModuleFields::listingColumnAccessScan('Products', $this->listing_cols);
                return $next($request);
            });
        } else {
            $this->listing_cols = ModuleFields::listingColumnAccessScan('Products', $this->listing_cols);
        }
        $this->ppcRepository = $ppcRepository;
    }

    /**
     * Display a listing of the Products.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $module = Module::get('Products');
        if (Module::hasAccess($module->id)) {
            return View('la.products.index', [
                'show_actions' => $this->show_action,
                'listing_cols' => $this->listing_cols,
                'module' => $module
            ]);
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Show the form for creating a new product.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created product in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request, Discount $discount, RelatedProduct $relatedProduct)
    {
        if (Module::hasAccess("Products", "create")) {
            try {
                DB::beginTransaction();
                if (
                    isset($request->relation_product)
                    && $request->type == Product::TYPE_GROUP_PRODUCT
                    && $request->use_child_product == 1
                    && $request->n_quantity > 0
                ) {
                    $children = $request->relation_product;
                    $childrenProduct = Product::whereIn('id', array_keys($children))->get();
                    foreach ($childrenProduct as $child) {
                        if ($child->n_quantity < $children[$child->id] * $request->n_quantity) {
                            return redirect()->back()->withErrors([
                                'product' => trans('messages.product_quantity_not_enough')
                            ])->withInput();
                        }
                    }
                }

                $insert_id = Module::insert("Products", $request);
                $product = Product::find($insert_id);
                $product->min_stock = $request->min_stock;
                $product->unit = $request->unit;
                $product->warranty_period = $request->warranty_period;
                $product->short_desc = $request->short_desc;
                $product->weight = $request->weight;
                $product->height = $request->height;
                $product->width = $request->width;
                $product->length = $request->length;
                $product->status_text = trim($request->status_text);
                $product->save();

                $this->ppcRepository->create($product->id, $request->get('category_ids', []));
                $discountArr = $request->group_discount;
                if (!empty($discountArr)) {
                    $discount->setDiscountForProduct($product, $discountArr);
                }
                if (isset($request->relation_product) && $request->type == Product::TYPE_GROUP_PRODUCT) {
                    $relatedProduct->makeGroupProduct($product, $request->relation_product, $request->use_child_product == 1);
                }
                $stores = Store::all();
                foreach ($stores as $store) {
                    StoreProduct::create([
                        'store_id' => $store->id,
                        'product_id' => $product->id,
                        'n_quantity' => 0,
                        'w_quantity' => 0,
                        'min' => 0,
                    ]);
                }
                DB::commit();

                return redirect()->route(config('laraadmin.adminRoute') . '.products.index');
            } catch (\Exception $exception) {
                DB::rollback();
                \Log::error($exception->getMessage());
                \Log::error($exception->getTraceAsString());
                return redirect()->back()->withErrors($exception->getMessage());
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Display the specified product.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Discount $discount)
    {
        if (Module::hasAccess("Products", "view")) {

            $product = Product::find($id);
            if (isset($product->id)) {
                $module = Module::get('Products');
                $module->row = $product;
                $discountArr = $discount->getGroupDiscountByCurrencyForProduct($product->id);
                $groups = Group::all();
                $series = $product->series()
                    ->where(function ($q) {
                        if (request()->has('qr_code')) {
                            $q->where('qr_code_status', request('qr_code'));
                        }
                        if (request()->has('stock_status')) {
                            $q->where('stock_status', request('stock_status'));
                        }
                    })->paginate()->appends(request()->all());
                return view('la.products.show', [
                    'module' => $module,
                    'view_col' => $this->view_col,
                    'no_header' => true,
                    'no_padding' => "no-padding",
                    'discount' => $discountArr,
                    'groups' => $groups,
                    'series' => $series,
                ])->with('product', $product);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst("product"),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Discount $discount)
    {
        if (Module::hasAccess("Products", "edit")) {
            $product = Product::find($id);
            if (isset($product->id)) {
                $module = Module::get('Products');

                $module->row = $product;
                $discountArr = $discount->getGroupDiscountByCurrencyForProduct($product->id);
                $discountNdtArr = $discount->getGroupDiscountByCurrencyForProduct($product->id, Bank::CURRENCY_NDT);
                $discountPercentArr = $discount->getGroupDiscountPercentForProduct($product->id);
                $groups = Group::all();
                $pSeriesPaginatorLength = ProductSeri::defaultPaginatorLength();
                $attrs = ProductGroupAttributeMedia::where('product_id', $id)->get();
                return view('la.products.edit', [
                    'module' => $module,
                    'view_col' => $this->view_col,
                    'discount' => $discountArr,
                    'discountNdt' => $discountNdtArr,
                    'discountPercent' => $discountPercentArr,
                    'groups' => $groups,
                    'pSeriesPaginatorLength' => $pSeriesPaginatorLength,
                    'attrs' => $attrs
                ])->with('product', $product);
            } else {
                return view('errors.404', [
                    'record_id' => $id,
                    'record_name' => ucfirst("product"),
                ]);
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Update the specified product in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id, Discount $discount)
    {
        if (Module::hasAccess("Products", "edit")) {
            try {
                DB::beginTransaction();
                $insert_id = Module::updateRow("Products", $request, $id);
                $product = Product::find($insert_id);
                $product->min_stock = $request->min_stock;
                $product->unit = $request->unit;
                $product->warranty_period = $request->warranty_period;
                $product->short_desc = $request->short_desc;
                $product->weight = $request->weight;
                $product->height = $request->height;
                $product->width = $request->width;
                $product->length = $request->length;
                $product->status_text = trim($request->status_text);
                //$product->categories()->sync($request->get('category_ids', []));
                $product->save();

                $discountArr = $request->group_discount;
                if (!empty($discountArr)) {
                    $discount->setDiscountForProduct($product, $discountArr);
                }

                $this->ppcRepository->update($product->id, $request->get('category_ids', []));
                DB::commit();

                return redirect()->route(config('laraadmin.adminRoute') . '.products.index');
            } catch (\Exception $exception) {
                DB::rollback();
                \Log::error($exception->getMessage());
                \Log::error($exception->getTraceAsString());
                return redirect()->back()->withErrors($exception->getMessage());
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Module::hasAccess("Products", "delete")) {
            try {
                DB::beginTransaction();
                $product = Product::findOrFail($id);
                $this->ppcRepository->delete($product->id, []);
                $product->delete();
                DB::commit();

                return redirect()->route(config('laraadmin.adminRoute') . '.products.index');
            } catch (\Exception $exception) {
                DB::rollback();
                \Log::error($exception->getMessage());
                \Log::error($exception->getTraceAsString());
                return redirect()->back()->withErrors($exception->getMessage());
            }
        } else {
            return redirect(config('laraadmin.adminRoute') . "/");
        }
    }

    /**
     * Datatable Ajax fetch
     *
     * @return
     */
    public function dtajax(Request $request, Discount $discount, \App\Services\Upload $uploadSv)
    {
        if ($request->listing_cols) {
            $this->listing_cols = explode(',', $request->listing_cols);
        }
        $cols = array_map(function ($col) {
            return 'products.' . $col;
        }, $this->listing_cols);
        $values = Product::query()
            ->select($cols)
            ->whereNull('deleted_at');

        // Temporary query because datatables filterColumn doesn't support join table
        // to get right position of producs
        $catId = $request->columns[1]['search']['value'];
        if ($catId) {
            $values->join('products_product_category', function ($join) use ($catId) {
                $join->on('products.id', '=', 'products_product_category.product_id')
                    ->where('products_product_category.product_category_id', '=', $catId);
            })
                ->orderBy('products_product_category.position');
        } else {
            $values->orderBy('id', 'DESC');
        }

        if ($request->store_id) {
            $values->whereExists(function ($q) use ($request) {
                $q->select(\DB::raw(1))
                    ->from('store_products')
                    ->whereRaw('store_products.product_id = products.id')
                    ->where('store_id', $request->store_id);
            });
        }
        if ($request->exclude) {
            $values->whereNotIn('id', explode(',', $request->exclude));
        }

        if (isset($request->seri) && ($request->seri != '')) {
            $seri = \App\Models\ProductSeri::where('seri_number', $request->seri)->first();
            $values->where('id', $seri ? $seri->product_id : 0);
        }
        $datatable = Datatables::of($values);
        $out = $datatable->make();
        $data = $out->getData();

        $fields_popup = ModuleFields::getModuleFields('Products');
        $groups = Group::all()->pluck('id')->toArray();
        $quantities = [];
        if ($request->store_id) {
            $productIds = array_map(function ($item) {
                return $item['0'];
            }, $data->data);
            $quantities = StoreProduct::whereIn('product_id', $productIds)
                ->where('store_id', $request->store_id)
                ->get()
                ->map(function ($item) {
                    $item->quantity = $item->n_quantity + $item->w_quantity;
                    return $item;
                })
                ->keyBy('product_id')
                ->toArray();
        }
        for ($i = 0; $i < count($data->data); $i++) {
            $id = $data->data[$i][0];
            $product = Product::find($id);
            for ($j = 0; $j < count($this->listing_cols); $j++) {
                $col = $this->listing_cols[$j];
                if ($col == "id") {
                    $data->data[$i][$j] = $id;
                    if ($catId) {
                        $productCate = ProductsProductCategory::where('product_id', $id)
                            ->where('product_category_id', $catId)
                            ->first();
                        $data->data[$i][$j] .= '<div class="reorder-group" data-position=' . $productCate->position . '>';
                        if ($i != 0) {
                            $data->data[$i][$j] .= '<button value="top" class="btn btn-warning btn-sm reorder fa fa-angle-double-up"></button>';
                            $data->data[$i][$j] .= '<button value="up" class="btn btn-primary btn-sm reorder fa fa-angle-up"></button>';
                        }
                        if ($i + 1 != count($data->data)) {
                            $data->data[$i][$j] .= '<button value="down" class="btn btn-primary btn-sm reorder fa fa-angle-down"></button>';
                            $data->data[$i][$j] .= '<button value="bottom" class="btn btn-warning btn-sm reorder fa fa-angle-double-down"></button>';
                        }
                        $data->data[$i][$j] .= '</div>';
                    }
                    if ($request->selectable) {
                        $data->data[$i][$j] = '<input type="checkbox" value="' . $id . '" data-sku="' . $product->sku . '" data-name="' . $product->name . '"/>' . $id;
                    }
                }
                if ($col == "category_ids") {
                    $data->data[$i][$j] = ProductCategory::whereIn('id', json_decode($data->data[$i][$j]))->get()->implode('name', ',');
                } else if ($fields_popup[$col] != null && starts_with($fields_popup[$col]->popup_vals, "@")) {
                    $data->data[$i][$j] = ModuleFields::getFieldValue($fields_popup[$col], $data->data[$i][$j]);
                }
                if ($col == $this->view_col) {
                    $data->data[$i][$j] = '<a href="' . url(config('laraadmin.adminRoute') . '/products/' . $id) . '">' . $data->data[$i][$j] . '</a>';
                } else if ($col == "status") {
                    $data->data[$i][$j] = $data->data[$i][$j]
                        ? '<span class="label label-success">Đang kinh doanh</span>'
                        : '<span class="label label-danger">Ngừng kinh doanh</span>';
                } else if (in_array($col, ['n_quantity', 'w_quantity'])) {
                    if (isset($quantities[$data->data[$i][0]])) {
                        $data->data[$i][$j] = $quantities[$data->data[$i][0]][$col];
                    }
                    $data->data[$i][$j] = number_format($data->data[$i][$j]);
                } else if (in_array($col, ['price', 'price_in_ndt', 'retail_price'])) {
                    $data->data[$i][$j] = number_format($data->data[$i][$j], $col == 'price_in_ndt' ? 2 : 0) . ' đ';
                } else if (in_array($col, ['featured_image'])) {
                    $data->data[$i][$j] = $uploadSv->getThumbnail($data->data[$i][$j]);
                } else if ($col == "type") {
                    $data->data[$i][$j] = trans('product.type_' . $data->data[$i][$j]);
                } else if ($col == "quantity") {
                    if (isset($quantities[$data->data[$i][0]])) {
                        $data->data[$i][$j] = $quantities[$data->data[$i][0]][$col];
                    }
                    $data->data[$i][$j] = $data->data[$i][$j] > 0
                        ? number_format($data->data[$i][$j])
                        : '<span class="label label-danger">Hết hàng</span>';
                } else if ($col == "discount") {
                    $discountArr = $discount->getGroupDiscountByCurrencyForProduct($data->data[$i][0]);
                    $data->data[$i][$j] = '<table class="table"><tr>';
                    foreach ($groups as $groupId) {
                        $data->data[$i][$j] .= '<td>' . (isset($discountArr[$groupId]) ? $discountArr[$groupId] : '0') . '%</td>';
                    }
                    $data->data[$i][$j] .= '</tr></table>';
                }
            }

            if ($this->show_action) {
                $output = '';
                if (Module::hasAccess("Products", "edit")) {
                    $output .= '<a href="' . url(config('laraadmin.adminRoute') . '/products/' . $id . '/edit') . '" class="btn btn-warning btn-xs" style="display:inline;padding:2px 5px 3px 5px;"><i class="fa fa-edit"></i></a>';
                }

                if (Module::hasAccess("Products", "delete")) {
                    $output .= Form::open(['route' => [config('laraadmin.adminRoute') . '.products.destroy', $id], 'method' => 'delete', 'style' => 'display:inline']);
                    $output .= ' <button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-times"></i></button>';
                    $output .= Form::close();
                }
                $data->data[$i][] = (string)$output;
            }
        }
        $out->setData($data);
        return $out;
    }

    public function get(Request $request)
    {
        $products = Product::search($request->all())
            ->where('status', 1)
            ->where(function ($q) {
                if (request('type')) {
                    $q->where('type', request('type'));
                }
            });
        if (isset($request->sub_type) && $request->sub_type == 2) {
            $productIds = DB::table('orders as or')
                ->where('or.customer_id', $request->customer_id)
                ->where('or.sub_type', 2)
                ->join('orderproducts as op', 'or.id', '=', 'op.order_id')
                ->select('op.product_id')
                ->groupBy('op.product_id')
                ->get();
            $soldProductIds = array_map(function ($value) {
                return $value->product_id;
            }, $productIds);
            $products = $products->whereIn('id', $soldProductIds);
        }
        $validCombo = [];
        $customer = null;
        if ($request->get('order_customer')) {
            $customer = Customer::find($request->get('order_customer'));
            if ($customer) {
                $groupId = $customer->group_id;
                $validCombo = collect(DB::table('product_combo_groups')
                    ->where('group_id', $groupId)
                    ->groupBy('combo_id')
                    ->get())->pluck('combo_id');
            }
        }
        if ($request->get('combo')) {

            $productIds = DB::table('productcombos')
                ->where('status', 1)
                ->groupBy('product_id');
            $productIds->whereIn('id', $validCombo);
            $productIds = collect($productIds->get())->pluck('product_id');
            $products = $products->whereIn('id', $productIds);
        }
        $products = $products->paginate();
        $storeId = $request->store_id ?: ($customer ? $customer->store_id : 0);
        if ($storeId) {
            $items = array_map(function ($item) {
                return $item->id;
            }, $products->items());
            $storeQuantity = StoreProduct::where('store_id', $storeId)
                ->whereIn('product_id', $items)
                ->get()
                ->keyBy('product_id')
                ->map(function ($product) {
                    return [
                        'n_quantity' => $product->n_quantity,
                        'w_quantity' => $product->w_quantity,
                    ];
                })
                ->toArray();
            $pendingQuantity = collect(\Illuminate\Support\Facades\DB::table('d_orders')
                ->join('d_orderproducts', 'd_orderproducts.order_id', '=', 'd_orders.id')
                ->whereNull('d_orders.deleted_at')
                ->whereIn('d_orderproducts.product_id', $items)
                ->select(\Illuminate\Support\Facades\DB::raw('d_orderproducts.product_id, SUM(d_orderproducts.quantity) as quantity'))
                ->groupBy('d_orderproducts.product_id')
                ->get())
                ->pluck('quantity', 'product_id');
            $items = $products->items();
            foreach ($items as $key => $item) {
                $items[$key]->n_quantity = @$storeQuantity[$item->id] ? $storeQuantity[$item->id]['n_quantity'] : 0;
                $items[$key]->w_quantity = @$storeQuantity[$item->id] ? $storeQuantity[$item->id]['w_quantity'] : 0;
                $items[$key]->validCombos = collect();
                $items[$key]->pending_quantity = (int)@$pendingQuantity[$item->id];
                if (!empty($validCombo)) {
                    $items[$key]->validCombos = $item->combos()
                        ->whereIn('id', $validCombo)
                        ->get()
                        ->map(function (ProductCombo $combo) use ($customer) {
                            $related = json_decode($combo->related, true);
                            $relatedProducts = [];
                            foreach ($related as $product) {
                                $quantity = $product[1];
                                $result = Product::find($product[0]);
                                if ($result) {
                                    $result->p_id = $result->id;
                                    $result->required_quantity = $quantity;
                                    $relatedProducts[] = $result;
                                }
                            }

                            $combo->related = $relatedProducts;
                            if ($customer) {
                                $discount = \Illuminate\Support\Facades\DB::table('product_combo_groups')
                                    ->where('combo_id', $combo->id)
                                    ->where('group_id', $customer->group_id)
                                    ->first();
                                $combo->discount = $discount ? $discount->discount : 0;
                            }

                            return $combo;
                        });
                }
            }

            $products->setCollection(collect($items));
        }

        return View('la.products.list', [
            'products' => $products,
        ])->render();
    }

    public function getProduct(Request $request, AttributeValueRepository $repository)
    {
        if ($request->product_id) {
            $selectedIds = explode(',', $request->product_id);
            $result = $products = Product::whereIn('id', $selectedIds)->get();
            if ($products->count() != count($selectedIds)) {
                $result = collect();
                foreach ($selectedIds as $id) {
                    $product = $products->filter(function ($p) use ($id) {
                        return $p->id == $id;
                    });

                    $result->push($product->first());
                }
            }
            $selectedAttrs = explode('|', $request->get('attr_ids', ''));
            $selectedAttrs = array_map(function ($item) {
                return explode(',', $item);
            }, $selectedAttrs);
            $attrs = [];
            foreach ($selectedAttrs as $item) {
                $attrs[] = $repository->getAttrs($item);
            }
            $index = explode('|', $request->get('existed_index', ''));

            $combos = explode(',', $request->get('combos', ''));
            if (!empty($combos) && $customer = $request->get('customer_id')) {
                $customer = Customer::find($customer);
                $combos = ProductCombo::whereIn('id', $combos)
                    ->get()
                    ->map(function (ProductCombo $combo) use ($customer) {
                        $related = json_decode($combo->related, true);
                        $relatedProducts = [];
                        foreach ($related as $product) {
                            $quantity = $product[1];
                            $result = Product::find($product[0]);
                            if ($result) {
                                $result->p_id = $result->id;
                                $result->required_quantity = $quantity;
                                $relatedProducts[] = $result;
                            }
                        }

                        $combo->related = $relatedProducts;
                        $discount = \Illuminate\Support\Facades\DB::table('product_combo_groups')
                            ->where('combo_id', $combo->id)
                            ->where('group_id', $customer->group_id)
                            ->first();

                        $combo->discount = $discount ? $discount->discount : 0;

                        return $combo;
                    })->filter(function (ProductCombo $combo) use ($customer) {
                        return $combo->discount > 0
                            && count($combo->related) > 0
                            && $customer
                            && $combo->isApplyForGroup($customer->group_id);
                    });
                $result1 = collect();
                foreach ($result as $product) {
                    $customerGroupDiscount = app(\App\Services\Discount::class)->getGroupDiscountPercentForProduct($product->id, $customer->group_id);
                    $product->discount_percent = $customerGroupDiscount ? $customerGroupDiscount->discount_percent : 0;

                    $validCombos = $combos->filter(function (ProductCombo $combo) use ($product) {
                        return $combo->product_id == $product->id;
                    });
                    if ($validCombos->count() > 0) {
                        foreach ($validCombos as $combo) {
                            $product->has_combo = 1;
                            $product->combo = $combo;
                            $result1->push($product);
                            foreach ($combo->related as $p) {
                                $p->parent_id = $product->id;
                                $p->combo = $combo;
                                $result1->push($p);
                            }
                        }
                    } else {
                        $result1->push($product);
                    }
                }
                $result = $result1;
            }

            return View($request->view ?: 'la.products_selecting.product_selected_product', [
                'products' => $result,
                'attrs' => $attrs,
                'selectedAttrs' => $selectedAttrs,
                'existedIndex' => $index
            ])->render();
        }
    }

    public function deleteSavedPrice(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id'
        ]);
        $discount = app(Discount::class)->getOnlyDiscountForCustomer($request->customer_id, $request->product_id);
        if ($discount) $discount->delete();

        return response()->json('OK');
    }

    public function deleteRelatedProduct($productId, Request $request)
    {
        ProductRelated::where('product_id', $productId)
            ->whereIn('id', explode(',', $request->ids))
            ->delete();

        return back()->with('success', trans('messages.updated_successful'));
    }

    public function addRelatedProduct($productId, Request $request, RelatedProduct $rp)
    {
        $this->validate($request, [
            'relation_product.*' => 'required|integer|min:1'
        ], [
            'relation_product.*' => 'Số lượng sản phẩm con không hợp lệ'
        ]);

        $product = Product::find($productId);

        $rp->makeGroupProduct($product, $request->relation_product, false);

        return back()->with('success', trans('messages.updated_successful'));
    }

    public function loadSeries($productId, Request $request)
    {
        $perPage = @$request->per_page ?: 50;
        $series = ProductSeri::query()
            ->with(['groupAttribute'])
            ->where('product_id', $productId)
            ->where(function ($query) use ($request) {
                if ($request->has('qr_code_status')) {
                    $query->where('qr_code_status', $request->qr_code_status);
                }
                if ($request->has('stock_status')) {
                    $query->where('stock_status', $request->stock_status);
                }
                if ($request->has('import_status') && $request->get('import_status') !== '') {
                    $query->where('status', $request->import_status);
                }
                if ($request->has('attr_id')) {
                    $query->where('group_attribute_id', $request->attr_id);
                }
                if ($request->has('seri_number')) {
                    $query->where('seri_number', 'LIKE', '%' . $request->seri_number . '%');
                }
                if ($request->has('created_at')) {
                    $from = Carbon::parse($request->created_at)->format('Y-m-d 00:00:00');
                    $to = Carbon::parse($request->created_at)->format('Y-m-d 23:59:59');
                    $query->whereBetween('created_at', [$from, $to]);
                }
            })
            ->paginate($perPage);

        return View::make('la.products.series.list', compact('series'))->render();
    }

    public function printProductSeries($productId, Request $request)
    {
        $codes = ProductSeri::where('product_id', $productId)
            ->where(function ($q) use ($request) {
                if ($request->has('ids')) {
                    $q->whereIn('id', explode(',', $request->ids));
                }
            })
            ->select(['seri_number', 'activation_code', 'id'])
            ->get();

        return view('la.products.series.print', compact('codes'));
    }

    public function updateStatusProductSeries($productId, Request $request)
    {
        \App\Models\ProductSeri::where('product_id', $productId)
            ->whereIn('id', explode(',', $request->ids))
            ->orderBy('id', 'desc')
            ->each(function (ProductSeri $seri) {
                $seri->update(['qr_code_status' => 1]);
            });
        return back();
    }

    public function extraSeries(Request $request, ProductSeriesRepository $productSeriesRp)
    {
        try {
            DB::beginTransaction();
            $product = Product::findOrFail($request->product_id);
            $newSeries = $productSeriesRp->createSeries($product->id, $request->extra_series, [
                'qr_code_status' => 2,
                'group_attribute_id' => $request->avm_id
            ]);
            DB::commit();
            return response()->json($newSeries);
        } catch (\Exception $exception) {
            DB::rollback();
            \Log::info($exception->getTraceAsString());
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function deleteSeries($productId, Request $request)
    {
        \App\Models\ProductSeri::where('product_id', $productId)
            ->whereIn('id', explode(',', $request->ids))
            ->delete();

        return back();
    }

    public function export(Bank $bank, Request $request, ProductRepository $productRp)
    {
        $customer = Customer::findOrFail($request->customer_id);
        $store = $customer->store;
        if (!$store) {
            return redirect()->back()->withErrors([
                'store_id' => "Không tìm thấy kho"
            ]);
        }
        $request->merge([
            'pc_ids' => array_filter(explode(',', $request->pc_ids))
        ]);

        $products = $productRp->getProductsByStore($store->id, $request->all())->get()->sortBy('position');
        $products = $productRp->getProductQuantityByProducts($products, $store->id);
        $products = $products->map(function ($product, $key) use ($customer) {
            $upload = UploadModel::find($product->featured_image);
            $path = $upload ? $upload->path : public_path('la-assets/img/default.png');
            $data['stt'] = ++$key;
            $data['name'] = $product->name;
            $data['price'] = $product->getLastestPriceForCustomer($customer->id) ?: $product->price;
            $data['status'] = $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng';
            $data['featured_image'] = base64_encode(@file_get_contents($path));
            return $data;
        });
        $configs = Config::all()->pluck('value', 'key');
        $banks = $bank->getBankByAccName([], $store->id);

        return view('la.products.export', [
            'store' => $store,
            'products' => $products,
            'configs' => $configs,
            'banks' => $banks,
            'customer' => $customer
        ]);
    }

    public function reorderPosition(Request $request)
    {
        $this->validate($request, [
            'category_id' => 'required|exists:productcategories,id',
            'product_id' => 'required|exists:products,id',
            'old' => 'required',
            'new' => 'required'
        ]);

        // Update current product position
        $currentRecord = ProductsProductCategory::where('product_id', $request->product_id)
            ->where('product_category_id', $request->category_id)
            ->first();
        $currentRecord->update(['position' => $request->new]);

        $range = [$request->old, $request->new];
        if ($request->old > $request->new) {
            $range = [$request->new, $request->old];
        }
        $lowPriorityRecords = ProductsProductCategory::where('product_category_id', $request->category_id)
            ->where('id', '<>', $currentRecord->id)
            ->whereBetween('position', $range)
            ->get();
        foreach ($lowPriorityRecords as $record) {
            if ($request->old < $request->new) {
                $record->position--;
            } else {
                $record->position++;
            }
            $record->save();
        }

        return response()->json("OK");
    }

    public function attributes($id)
    {
        if (Module::hasAccess("Products", "edit")) {
            $product = Product::find($id);
            return view('la.products.attribute', compact('product'))->render();
        }
    }

    public function saveAttributes($id, Request $request, ProductRepository $repository)
    {
        if (Module::hasAccess("Products", "edit")) {
            $product = Product::find($id);
            $attrbitues = $request->get('attribute_ids', []);

            if (is_array($attrbitues)) {
                foreach ($attrbitues as $attrbitue) {
                    $productAttribute = ProductAttribute::where('product_id', $id)
                        ->where('attribute_id', $attrbitue)
                        ->first();
                    if (!$productAttribute) {
                        ProductAttribute::create([
                            'product_id' => $id,
                            'attribute_id' => $attrbitue,
                            'attribute_value_id' => ''
                        ]);
                    }
                }
            }

            ProductAttribute::where('product_id', $id)
                ->whereNotIn('attribute_id', $attrbitues)
                ->delete();
            $product = $product->fresh();
            $repository->createProductGroupAttribute($product);

            return view('la.products.attribute', compact('product'))->render();
        }
    }

    public function saveAttributeValues($id, Request $request, ProductRepository $repository)
    {
        if (Module::hasAccess("Products", "edit")) {
            $product = Product::find($id);
            $attrbitue = $request->get('attribute_id', '');
            $values = $request->get('values', []);
            $values = $values ?: [];

            ProductAttribute::where('product_id', $id)
                ->where('attribute_id', $attrbitue)
                ->update([
                    'attribute_value_id' => implode(',', $values)
                ]);
            $product = $product->fresh();
            $repository->createProductGroupAttribute($product);
            $product = $product->fresh();

            return view('la.products.attribute', compact('product'))->render();
        }
    }

    public function getAttributeValues($id, Request $request, ProductRepository $repository, Upload $uploadSv)
    {
        if (Module::hasAccess("Products", "edit")) {
            $product = Product::find($id);
            $groupAttribute = ProductGroupAttributeMedia::find($request->avm_id);
            $selectedMedia = $groupAttribute ? explode(',', $groupAttribute->media_ids) : [];

            $gallery = json_decode($product->product_gallery);

            $gallery = array_map(function ($uploadId) use ($uploadSv) {
                return [
                    'id' => $uploadId,
                    'path' => $uploadSv->getImagePath($uploadId)
                ];
            }, $gallery);
            $avmId = $request->avm_id;

            return view('la.products.group_attr_media', compact('gallery', 'selectedMedia', 'avmId'))->render();
        }
    }

    public function saveGroupAttributeValues($id, Request $request)
    {
        if (Module::hasAccess("Products", "edit")) {
            $avm_id = $request->avm_id;
            $mediaIds = $request->media_id;

            ProductGroupAttributeMedia::where('product_id', $id)
                ->where('id', $avm_id)
                ->update([
                    'media_ids' => implode(',', $mediaIds)
                ]);
            $product = Product::find($id);

            return view('la.products.attribute', compact('product'))->render();
        }
    }

    public function saveSeriForGroupAttributeValues($id, Request $request)
    {
        if (Module::hasAccess("Products", "edit")) {
            $avm_id = $request->avm_id;
            $seris = explode(',', $request->ids);

            ProductSeri::whereIn('id', $seris)
                ->where('product_id', $id)
                ->update([
                    'group_attribute_id' => $avm_id
                ]);

            return response()->json('OK');
        }
    }

    public function getSeriInfo(Request $request)
    {
        $seri = ProductSeri::where('seri_number', $request->seri)
            ->first();
        if ($seri) {
            $groupAttribute = ProductGroupAttributeMedia::find($seri->group_attribute_id);
            return response()->json([
                'seri_id' => $seri->id,
                'product_id' => $seri->product_id,
                'attr_ids' => $groupAttribute ? $groupAttribute->attribute_value_ids : '',
            ]);
        }

        return response()->json('');
    }

    public function importSeri(Request $request)
    {
        $this->validate($request, [
            'file' => 'required'
        ]);
        $extensions = array("xlsx");
        $type = array($request->file('file')->getClientOriginalExtension());
        $qr_status = ($request->get('qr_code_status')) ? (int)$request->get('qr_code_status') : 0;
        $result = '';
        if (in_array($type[0], $extensions)) {
            $file = $request->file('file')->getRealPath();
            Excel::filter('chunk')->selectSheetsByIndex(0)->ignoreEmpty(true)->noHeading(true)->load($file)->chunk(500, function ($reader) use (&$result, $qr_status) {
                $data = $reader->toArray();
                if ($qr_status !== 2) {
                    $result = $this->importServiceSeri($data, $qr_status);
                } else {
                    $result = $this->importDeviceSeri($data);
                }
            }, $result);
        } else {
            $result = ['valid_date' => ['Định dạng file không hợp lệ']];
        }
        return is_array($result) ? response()->json($result, 422) : response()->json([]);
    }

    protected function importDeviceSeri($data)
    {
        $startCol = 0;
        $seri = [];
        $attributes = Attribute::orderBy('id', 'asc')
            ->get()
            ->map(function ($attribute) {
                $attribute->name = strtolower(StringHelper::convertUTF8ToASCII($attribute->name));
                return $attribute;
            })
            ->pluck('id', 'name')
            ->toArray();
        $attibuteValues = AttributeValue::orderBy('id', 'asc')
            ->get()
            ->map(function ($attributeValue) {
                $attributeValue->value = strtolower(StringHelper::convertUTF8ToASCII($attributeValue->value)) . '_' . $attributeValue->attribute_id;
                return $attributeValue;
            })
            ->pluck('id', 'value')
            ->toArray();
        $storeId = Store::first()->id;
        foreach ($data as $k => $item) {
            $sku = $item[$startCol + 1] ?? '';
            $seriNumber = rtrim($item[$startCol + 2] ?? '', '.0');
            if (!$sku || $item[$startCol] == 'STT') continue;
            $attrs = array_filter(array_map(function ($v) {
                return trim($v);
            }, explode(',', @$item[$startCol + 3] ?: '')));
            $validAttr = [];
            $spgae = null;
            if (!empty($attrs)) {
                $invalidAttr = [];
                foreach ($attrs as $v) {
                    $attr = array_filter(array_map(function ($v1) {
                        return trim($v1);
                    }, explode(':', $v)));
                    if (count($attr) === 2) {
                        $attrText = strtolower(StringHelper::convertUTF8ToASCII($attr[0]));
                        $attrValueText = strtolower(StringHelper::convertUTF8ToASCII($attr[1]));
                        $attrId = @$attributes[$attrText];
                        if ($attrId) {
                            $attrValueId = @$attibuteValues[$attrValueText . '_' . $attrId];
                            $validAttr[$attrId] = $attrValueId;
                            continue;
                        }
                    }
                    $invalidAttr[] = $v;
                }
                if (!empty($invalidAttr)) {
                    return ['sku' => ['Thuộc tính sản phẩm '. implode(',', $v) .' không hợp lệ']];
                }
                ksort($validAttr);
                $spgae = StoreProductGroupAttributeExtra::where('attribute_value_ids', implode(',', array_values($validAttr)))
                    ->where('store_id', $storeId)
                    ->first();
                $validSeri = ProductSeri::where('seri_number', rtrim($item[$startCol + 2], '.0'))
                    ->first();
                if (!$spgae || ($validSeri && $validSeri->group_attribute_id != @$spgae->id)) {
                    return ['sku' => ['Serinumber ' . rtrim($item[$startCol + 2], '.0') . ' với thuộc tính ' . implode(',', $attrs) . ' Không hợp lệ']];
                }
            }
            if (!$sku || !$seriNumber) {
                return ['sku' => ['Serinumber, SKU không được để trống']];
            }
            $seri[] = [
                'sku' => $item[$startCol + 1],
                'seri_number' => rtrim($item[$startCol + 2], '.0'),
                'group_attribute_id' => isset($spgae) ? $spgae->id : null,
                'status' => 0,
            ];
        }
        $sku = array_unique(array_map(function ($item) {
            return $item['sku'];
        }, $seri));
        $existedSku = Product::whereIn('sku', $sku)->pluck('sku', 'id')->toArray();
        $nonExisted = array_diff($sku, $existedSku);
        if (count($nonExisted) > 0) {
            return ['sku' => ['Sản phẩm ' . implode(',', $nonExisted) . ' không tồn tại']];
        }
        foreach ($seri as $item) {
            $productId = array_search($item['sku'], $existedSku);
            $existedSeri = ProductSeri::where('product_id', $productId)
                ->where('seri_number', $item['seri_number'])
                ->first();

            $item['store_id'] = Store::first()->id;
            if ($existedSeri) {
                $existedSeri->update(array_except($item, [
                    'product_id',
                    'seri_number',
                    'sku'
                ]));
            } else {
                $item['product_id'] = $productId;
                ProductSeri::create(array_except($item, [
                    'sku'
                ]));
                if (@$item['group_attribute_id']) {
                    \Illuminate\Support\Facades\DB::table('store_product_attributes_value_extra')
                        ->where('id', $item['group_attribute_id'])
                        ->update([
                            'n_quantity' => \Illuminate\Support\Facades\DB::raw('n_quantity + 1')
                        ]);
                }
            }
        }

    }

    protected function importServiceSeri($data, $qr_status)
    {
        $startCol = 3;
        $seri = [];
        foreach ($data as $k => $item) {
            $sku = $item[$startCol + 4] ?? '';
            $seriNumber = rtrim($item[$startCol + 6] ?? '', '.0');
            $code = $item[$startCol + 7] ?? '';
            if (!$sku || $item[$startCol] == 'STT') continue;
            if (!$sku || !$seriNumber || !$code) {
                return ['sku' => ['Serinumber, Subcription hoặc mã kích hoạt không được để trống']];
            }
            try {
                $validDate = Carbon::createFromFormat('H:i d/m/Y', $item[$startCol + 8]);
                $expireDate = Carbon::createFromFormat('H:i d/m/Y', $item[$startCol + 9]);
            } catch (Exception $exception) {
                return ['valid_date' => ['Định dạng ngày tháng không hợp lệ']];
            }
            $seri[] = [
                'package_id' => $item[$startCol + 1],
                'package_name' => $item[$startCol + 2],
                'package_type' => $item[$startCol + 3],
                'sku' => $item[$startCol + 4],
                'seri_number' => rtrim($item[$startCol + 6], '.0'),
                'activation_code' => $item[$startCol + 7],
                'valid_date' => $validDate,
                'expire_date' => $expireDate,
                'status' => $item[$startCol + 10],
                'qr_code_status' => $qr_status,
            ];
        }
        $sku = array_unique(array_map(function ($item) {
            return $item['sku'];
        }, $seri));
        $existedSku = Product::whereIn('sku', $sku)->pluck('sku', 'id')->toArray();
        $nonExisted = array_diff($sku, $existedSku);
        if (count($nonExisted) > 0) {
            return ['sku' => ['Sản phẩm ' . implode(',', $nonExisted) . ' không tồn tại']];
        }
        $products = [];
        foreach ($seri as $item) {
            $productId = array_search($item['sku'], $existedSku);
            $existedSeri = ProductSeri::where('product_id', $productId)
                ->where('seri_number', $item['seri_number'])
                ->first();
            $item['store_id'] = Store::first()->id;
            if ($existedSeri) {
                $existedSeri->update(array_except($item, [
                    'product_id',
                    'seri_number',
                    'sku'
                ]));
            } else {
                $item['product_id'] = $productId;
                ProductSeri::create(array_except($item, [
                    'sku'
                ]));
                $products[$productId] = @$products[$productId] ? $products[$productId] + 1 : 1;
            }
        }

        foreach ($products as $productId => $quantity) {
            $sp = StoreProduct::where('product_id', $productId)
                ->where('store_id', Store::first()->id)
                ->first();
            if ($sp) {
                $sp->n_quantity += $quantity;
                $sp->save();
            } else {
                StoreProduct::create([
                    'product_id' => $productId,
                    'n_quantity' => $quantity,
                    'store_id' => Store::first()->id,
                ]);
            }
        }

        return;
    }
}
