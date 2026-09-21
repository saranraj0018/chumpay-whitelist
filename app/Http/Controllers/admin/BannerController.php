<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function view()
    {
        $banners = Banner::paginate(10);
        return view('admin.banner.view', compact('banners'));
    }

    public function save(Request $request)
    {
        try {
            $rules = [];

            if (empty($request->banner_id) && !$request->has('existing_image')) {
                $rules['banner_image'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            } elseif ($request->hasFile('banner_image')) {
                $rules['banner_image'] = 'image|mimes:jpeg,png,jpg|max:2048';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 409,
                    'message' => $validator->errors()->first(),
                ], 409);
            }

            $banner = $request->banner_id ? Banner::find($request->banner_id) : new Banner();
            $banner->admin_id = Auth::guard('admin')->id();

            if ($request->hasFile('banner_image')) {
                $img_name = time() . '_' . $request->file('banner_image')->getClientOriginalName();
                $request->banner_image->storeAs('banners', $img_name, 'public');
                $banner->image = 'banners/' . $img_name;
            } elseif ($request->has('existing_image')) {
                $banner->image = $request->existing_image;
            }

            $banner->save();

            return response()->json([
                'success' => true,
                'message' => $request->banner_id ? 'Banner updated successfully' : 'Banner saved successfully',
                'banner' => $banner
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }


    public function destroy(Request $request)
    {
        if (!$request->id) {
            return response()->json(['success' => false, 'message' => 'Banner ID is required'], 400);
        }

        $banner = Banner::find($request->id);
        if (!$banner) {
            return response()->json(['success' => false, 'message' => 'Banner not found'], 404);
        }

        $banner->delete();
        return response()->json(['success' => true, 'message' => 'Banner deleted successfully']);
    }
}
