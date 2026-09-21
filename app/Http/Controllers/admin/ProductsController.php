<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BulkProduct;
use App\Models\BulkProductVariant;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGalleryImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\VariantAttribute;
use App\Models\VariantAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && $request->get_sub_category) {
            $sub_category = Category::where('parent_id', $request->category_id)->where('status', 1)->get();
            return response()->json([
                'success' => true,
                'sub_category' => $sub_category
            ]);
        }

        $this->data['category'] = Category::whereNull('parent_id')->where('status', 1)->get();
        $this->data['variants'] = VariantAttribute::with('get_variant_value')->get();
        $this->data['product_lists'] = Product::with(
            'product_variant',
            'product_variant.variantValues',
            'product_variant.gallery_images',
            'product_gallery_image',
            'bulk_product',
            'bulk_product.bulk_product_variants'
        )->paginate(10);

        return view('admin.product.view_product')->with($this->data);
    }

    public function getVariantValues($id)
    {
        $values = VariantAttributeValue::where('attribute_id', $id)->get();
        return response()->json($values);
    }

    public function getSecondaryValues($id)
    {
        $values = VariantAttributeValue::where('attribute_id', '!=', $id)->get();
        return response()->json($values);
    }

    public function saveProduct(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'product_name' => 'required|string|max:255',
                'category_id'  => 'required|exists:categories,id',
                'sub_category_id' => 'nullable|exists:categories,id',
                'product_type' => 'required|in:single,variant,bulk',
                'product_code'   =>  ['required', Rule::unique('products', 'product_code')->ignore($request->product_id)],
            ];

            if ($request->product_type === 'single') {
                $rules += [
                    'single_regular_price' => 'required|numeric|min:0',
                    'single_sale_price'    => 'required|numeric|min:0|lte:single_regular_price',
                    'single_stock'         => 'required|integer|min:0',
                ];
            }

            /* -------------------- VARIANT RULES (grouped structure) -------------------- */
            if ($request->product_type === 'variant') {
                $rules['primary_variant'] = 'required|exists:variant_attributes,id';
                $rules['variants'] = 'required|array|min:1';

                foreach ($request->input('variants', []) as $gIdx => $group) {
                    $rules["variants.$gIdx.primary_value"] = 'required|exists:variant_attribute_values,id';

                    $hasExistingImage = !empty($group['existing_image']);
                    $rules["variants.$gIdx.image"] = $hasExistingImage
                        ? 'nullable|image|max:2048'
                        : 'required|image|max:2048';

                    $rules["variants.$gIdx.gallery_images"]   = 'nullable|array';
                    $rules["variants.$gIdx.gallery_images.*"] = 'nullable|image|max:2048';
                    $rules["variants.$gIdx.existing_gallery"]   = 'nullable|array';
                    $rules["variants.$gIdx.existing_gallery.*"] = 'nullable|integer|exists:product_gallery_images,id';

                    $rules["variants.$gIdx.prices"] = 'required|array|min:1';

                    foreach (($group['prices'] ?? []) as $pIdx => $price) {
                        $rules["variants.$gIdx.prices.$pIdx.secondary_value"] = 'nullable|exists:variant_attribute_values,id';
                        $rules["variants.$gIdx.prices.$pIdx.regular_price"]   = 'required|numeric|min:0';
                        $rules["variants.$gIdx.prices.$pIdx.sale_price"]      = 'nullable|numeric|min:0|lte:variants.' . $gIdx . '.prices.' . $pIdx . '.regular_price';
                        $rules["variants.$gIdx.prices.$pIdx.stock"]          = 'required|integer|min:0';
                    }
                }
            }

            if ($request->product_type === 'bulk') {
                $rules['per_piece_price'] = 'required|numeric|min:0';
                $rules['bulk'] = 'required|array|min:1';
                $rules['bulk.*'] = Rule::forEach(function ($value, $attribute) {
                    return [
                        'minimum'       => 'required|integer|min:1',
                        'maximum'       => 'required|integer|gte:' . $attribute . '.minimum',
                        'regular_price' => 'required|numeric|min:0',
                        'sale_price'    => 'nullable|numeric|min:0|lte:' . $attribute . '.regular_price',
                    ];
                });
                $rules['bulk_attributes'] = 'required|array|min:1';
                $rules['bulk_attributes.*'] = 'array|min:1';
            }

            if (empty($request->product_id)) {
                $rules['main_image'] = 'required|image|max:2048';
            } else {
                $rules['main_image'] = 'nullable|image|max:2048';
            }

            $rules['gallery_images']   = 'nullable|array';
            $rules['gallery_images.*'] = 'nullable|image|max:2048';

            $messages = [
                'product_name.required'    => 'Product name is required.',
                'category_id.required'     => 'Please select a category.',
                'sub_category_id.exists'   => 'Selected sub category is invalid.',
                'product_type.required'    => 'Please select a product type.',
                'product_code.required'    => 'Product code is required.',
                'product_code.unique'      => 'This product code is already taken.',
                'main_image.required'      => 'Main image is required.',
                'main_image.image'         => 'Main image must be a valid image file.',

                'single_regular_price.required' => 'Regular price is required.',
                'single_sale_price.required'    => 'Sale price is required.',
                'single_sale_price.lte'         => 'Sale price cannot be greater than regular price.',
                'single_stock.required'         => 'Stock is required.',

                'primary_variant.required' => 'Please select a primary variant attribute.',
                'variants.required'        => 'Please add at least one variant.',

                'per_piece_price.required' => 'Per piece price is required.',
                'bulk.required'            => 'Please add at least one bulk price range.',
                'bulk.*.minimum.required'      => 'Minimum quantity is required.',
                'bulk.*.maximum.required'      => 'Maximum quantity is required.',
                'bulk.*.maximum.gte'           => 'Maximum quantity must be greater than or equal to minimum quantity.',
                'bulk.*.regular_price.required' => 'Regular price is required for every bulk range.',
                'bulk.*.sale_price.lte'         => 'Bulk sale price cannot be greater than its regular price.',
                'bulk_attributes.required' => 'Please select at least one attribute value.',
                'bulk_attributes.min'      => 'Please select at least one attribute value.',
                'bulk_attributes.*.min'    => 'Please select at least one value for each attribute.',
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);
            $validator->validate();
            /* -------------------- SAVE PRODUCT -------------------- */
            $product = empty($request->product_id)
                ? new Product()
                : Product::findOrFail($request->product_id);
            $product->category_id     = $request->category_id;
            $product->sub_category_id = $request->sub_category_id ?: null;
            $product->name            = $request->product_name;
            $product->product_type    = $request->product_type;
            $product->product_code    = $request->product_code;
            $product->description     = $request->description ?? '';
            $product->per_piece_price = $request->product_type === 'bulk'
                ? $request->per_piece_price
                : null;

            if ($request->product_type == 'single') {
                $product->regular_price = $request->single_regular_price;
                $product->sale_price    = $request->single_sale_price ?? 0;
                $product->stock         = $request->single_stock;
            } else {
                $product->regular_price = 0;
                $product->sale_price    = 0;
                $product->stock         = 0;
            }

            /* -------------------- MAIN IMAGE -------------------- */
            if ($request->hasFile('main_image')) {
                $img_name = time() . '_' . $request->file('main_image')->getClientOriginalName();
                $request->file('main_image')->storeAs('main_image/', $img_name, 'public');
                $product->main_image = 'main_image/' . $img_name;
            } elseif ($request->existing_main) {
                $product->main_image = $request->existing_main;
            }

            $product->save();

            /* -------------------- TYPE SWITCH CLEANUP -------------------- */
            if (!empty($request->product_id)) {

                $variantIds = ProductVariant::where('product_id', $product->id)->pluck('id');
                $bulkIds    = BulkProduct::where('product_id', $product->id)->pluck('id');

                if ($request->product_type != 'variant' && $variantIds->isNotEmpty()) {
                    $variantGalleryImages = ProductGalleryImage::whereIn('variant_id', $variantIds)->get();
                    foreach ($variantGalleryImages as $vg) {
                        if (Storage::disk('public')->exists($vg->image_path)) {
                            Storage::disk('public')->delete($vg->image_path);
                        }
                    }
                    ProductGalleryImage::whereIn('variant_id', $variantIds)->delete();

                    ProductVariantValue::whereIn('variant_id', $variantIds)->delete();
                    ProductVariant::whereIn('id', $variantIds)->delete();
                }

                if ($bulkIds->isNotEmpty() && $request->product_type != 'bulk') {
                    BulkProductVariant::whereIn('bulk_product_id', $bulkIds)->delete();
                    BulkProduct::whereIn('id', $bulkIds)->delete();
                }

                if ($request->product_type != 'single') {
                    $product->update([
                        'regular_price' => 0,
                        'sale_price'    => 0,
                        'stock'         => 0
                    ]);
                }
            }

            /* -------------------- PRODUCT-LEVEL GALLERY DELETE (single/bulk) -------------------- */
            $oldImages = ProductGalleryImage::where('product_id', $product->id)
                ->whereNull('variant_id')
                ->pluck('image_path')
                ->toArray();

            $keepImages = array_filter($request->existing_gallery ?? []);
            $deleteImages = array_diff($oldImages, $keepImages);

            if (!empty($deleteImages)) {
                ProductGalleryImage::where('product_id', $product->id)
                    ->whereNull('variant_id')
                    ->whereIn('image_path', $deleteImages)
                    ->delete();

                foreach ($deleteImages as $img) {
                    if (Storage::disk('public')->exists($img)) {
                        Storage::disk('public')->delete($img);
                    }
                }
            }

            /* -------------------- PRODUCT-LEVEL GALLERY ADD -------------------- */
            if ($request->hasFile('gallery_images')) {

                $existingImages = ProductGalleryImage::where('product_id', $product->id)
                    ->whereNull('variant_id')
                    ->pluck('image_path');
                $existingHashes = [];

                foreach ($existingImages as $imgPath) {
                    $fullPath = storage_path('app/public/' . $imgPath);
                    if (file_exists($fullPath)) {
                        $existingHashes[] = md5_file($fullPath);
                    }
                }

                foreach ($request->file('gallery_images') as $image) {
                    $newHash = md5_file($image->getRealPath());
                    if (in_array($newHash, $existingHashes)) {
                        continue;
                    }

                    $gimg_name = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('gallery_images/', $gimg_name, 'public');

                    $save_product_gallery = new ProductGalleryImage();
                    $save_product_gallery->product_id = $product->id;
                    $save_product_gallery->variant_id = null;
                    $save_product_gallery->image_path = 'gallery_images/' . $gimg_name;
                    $save_product_gallery->save();

                    $existingHashes[] = $newHash;
                }
            }

            /* -------------------- VARIANT SAVE (GROUPED) -------------------- */
            if ($request->product_type == 'variant' && !empty($request->variants)) {

                $submittedVariantIds = [];

                foreach ($request->variants as $gIdx => $group) {

                    /* ---- resolve group-level cover image ---- */
                    $variantImage = $group['existing_image'] ?? null;

                    if ($request->hasFile("variants.$gIdx.image")) {
                        $file = $request->file("variants.$gIdx.image");
                        $img_name = time() . '_' . $file->getClientOriginalName();
                        $file->storeAs('variants/', $img_name, 'public');
                        $variantImage = 'variants/' . $img_name;
                    }

                    $primaryValueId = $group['primary_value'] ?? null;

                    /* ---- figure out this group's OLD variant ids (for gallery diffing) ---- */
                    $oldGroupVariantIds = collect($group['prices'] ?? [])
                        ->pluck('variant_id')
                        ->filter()
                        ->values();

                    $canonicalVariantId = null; // first price-row's ProductVariant id in this group

                    foreach (($group['prices'] ?? []) as $pIdx => $price) {

                        $priceVariantId = $price['variant_id'] ?? null;

                        $productVariant = $priceVariantId
                            ? ProductVariant::find($priceVariantId)
                            : new ProductVariant();

                        if (!$productVariant) {
                            $productVariant = new ProductVariant();
                        }

                        $productVariant->product_id = $product->id;
                        $productVariant->regular_price = $price['regular_price'];
                        $productVariant->sale_price = $price['sale_price'] ?? 0;
                        $productVariant->pri_attribute_id = $request->primary_variant;
                        $productVariant->stock = $price['stock'];
                        $productVariant->cover_image = $variantImage; // shared across the group
                        $productVariant->save();

                        $submittedVariantIds[] = $productVariant->id;

                        if ($canonicalVariantId === null) {
                            $canonicalVariantId = $productVariant->id;
                        }

                        /* ---- reset + resave attribute values for this price row ---- */
                        ProductVariantValue::where('product_variant_id', $productVariant->id)->delete();

                        if (!empty($primaryValueId)) {
                            $pro_variant_value = new ProductVariantValue();
                            $pro_variant_value->product_variant_id = $productVariant->id;
                            $pro_variant_value->attribute_id = $request->primary_variant;
                            $pro_variant_value->attribute_value_id = $primaryValueId;
                            $pro_variant_value->save();
                        }

                        if (!empty($price['secondary_value'])) {
                            $secondary = VariantAttributeValue::find($price['secondary_value']);
                            if ($secondary) {
                                $pro_variant_value = new ProductVariantValue();
                                $pro_variant_value->product_variant_id = $productVariant->id;
                                $pro_variant_value->attribute_id = $secondary->attribute_id;
                                $pro_variant_value->attribute_value_id = $price['secondary_value'];
                                $pro_variant_value->save();
                            }
                        }
                    }

                    /* ---- GROUP GALLERY: delete removed existing ones (across old group variant ids) ---- */
                    $keepGalleryIds = array_filter($group['existing_gallery'] ?? []);

                    if ($oldGroupVariantIds->isNotEmpty()) {
                        $oldGroupGallery = ProductGalleryImage::whereIn('variant_id', $oldGroupVariantIds)->get();

                        foreach ($oldGroupGallery as $og) {
                            if (!in_array($og->id, $keepGalleryIds)) {
                                if (Storage::disk('public')->exists($og->image_path)) {
                                    Storage::disk('public')->delete($og->image_path);
                                }
                                $og->delete();
                            } elseif ($og->variant_id != $canonicalVariantId) {
                                // re-point kept images onto the (possibly new) canonical row
                                $og->variant_id = $canonicalVariantId;
                                $og->save();
                            }
                        }
                    }

                    /* ---- GROUP GALLERY: add new uploads, tied to the canonical row ---- */
                    if ($request->hasFile("variants.$gIdx.gallery_images")) {
                        foreach ($request->file("variants.$gIdx.gallery_images") as $file) {
                            $vg_name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('variant_gallery/', $vg_name, 'public');

                            ProductGalleryImage::create([
                                'product_id' => $product->id,
                                'variant_id' => $canonicalVariantId,
                                'image_path' => 'variant_gallery/' . $vg_name,
                            ]);
                        }
                    }
                }

                /* ---- remove variants that existed before but weren't submitted this time ---- */
                $staleVariants = ProductVariant::where('product_id', $product->id)
                    ->whereNotIn('id', $submittedVariantIds)
                    ->get();

                foreach ($staleVariants as $stale) {
                    $staleGallery = ProductGalleryImage::where('variant_id', $stale->id)->get();
                    foreach ($staleGallery as $sg) {
                        if (Storage::disk('public')->exists($sg->image_path)) {
                            Storage::disk('public')->delete($sg->image_path);
                        }
                    }
                    ProductGalleryImage::where('variant_id', $stale->id)->delete();
                    ProductVariantValue::where('product_variant_id', $stale->id)->delete();
                    $stale->delete();
                }
            }

            /* -------------------- BULK SAVE -------------------- */
            if ($request->product_type == 'bulk' && !empty($request->bulk)) {

                foreach ($request->bulk as $bulk) {
                    $bulkProduct = BulkProduct::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'minimum'    => $bulk['minimum'],
                            'maximum'    => $bulk['maximum'],
                        ],
                        [
                            'regular_price' => $bulk['regular_price'],
                            'sale_price'    => $bulk['sale_price'],
                        ]
                    );

                    if (!empty($request->bulk_attributes)) {
                        foreach ($request->bulk_attributes as $attributeId => $values) {
                            foreach ($values as $value) {
                                BulkProductVariant::firstOrCreate([
                                    'bulk_product_id'    => $bulkProduct->id,
                                    'attribute_id'       => $attributeId,
                                    'attribute_value_id' => $value,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product saved successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteProduct(Request $request)
    {
        if (!$request->id) {
            return response()->json([
                'success' => false,
                'message' => 'Product ID is required'
            ], 400);
        }

        $product = Product::find($request->id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $variantIds = $product->product_variant()->pluck('id');

        $variantGallery = ProductGalleryImage::whereIn('variant_id', $variantIds)->get();
        foreach ($variantGallery as $vg) {
            if (Storage::disk('public')->exists($vg->image_path)) {
                Storage::disk('public')->delete($vg->image_path);
            }
        }
        ProductGalleryImage::whereIn('variant_id', $variantIds)->delete();

        DB::table('product_variant_values')->whereIn('product_variant_id', $variantIds)->delete();
        $product->product_variant()->delete();

        $bulkIds = $product->bulk_product()->pluck('id');
        DB::table('bulk_product_variants')->whereIn('bulk_product_id', $bulkIds)->delete();
        $product->bulk_product()->delete();

        $productGallery = $product->product_gallery_image()->get();
        foreach ($productGallery as $pg) {
            if (Storage::disk('public')->exists($pg->image_path)) {
                Storage::disk('public')->delete($pg->image_path);
            }
        }
        $product->product_gallery_image()->delete();

        $product->wishlists()->delete();
        DB::table('order_details')->where('product_id', $product->id)->update(['product_id' => null]);
        $product->ratings()->delete();
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
