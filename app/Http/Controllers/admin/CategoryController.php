<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function view()
    {
        $this->data['categories'] = Category::paginate(10);
        $this->data['categories_all'] = Category::whereNull('parent_id')->get();
        return view('admin.category.view')->with($this->data);
    }

    public function save(Request $request)
    {
        try {
            $isSubCategory = !empty($request->parent_id);
            $rules = [
                'category_name' => [
                    'required',
                    'max:255',
                    'unique:categories,name,' . $request->category_id,
                ],
                'category_status' => 'required|boolean',
                'parent_id' => 'nullable|exists:categories,id',
                'gender' => $isSubCategory ? 'nullable' : 'required|in:male,female,others',
            ];
            if (empty($request['category_id']) && !$request->has('existing_image')) {
                $rules['category_image'] = 'required|image|mimes:jpeg,png,jpg';
            } elseif ($request->hasFile('category_image')) {
                $rules['category_image'] = 'image|mimes:jpeg,png,jpg';
            }
            $request->validate($rules);
            if ($request->category_id) {
                $category = Category::findOrFail($request->category_id);
                $message = 'Category updated successfully';
            } else {
                $category = new Category();
                $message = 'Category saved successfully';
            }
            $category->name = $request->category_name;
            $category->status = $request->category_status;
            $category->parent_id = $request->parent_id ?? null;
            $category->gender = $isSubCategory ? null : $request->gender;
            $category->admin_id = Auth::guard('admin')->id();
            if ($request->hasFile('category_image')) {
                $img_name = time() . '_' . $request->file('category_image')->getClientOriginalName();
                $request->file('category_image')->storeAs('categories', $img_name, 'public');
                $category->image = 'categories/' . $img_name;
            } elseif ($request->has('existing_image')) {
                $category->image = $request->existing_image;
            }
            $category->save();
            return response()->json([
                'success' => true,
                'message' => $message,
                'category' => $category
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        if (!$request->id) {
            return response()->json(['success' => false, 'message' => 'category ID is required'], 400);
        }
        $category = Category::find($request->id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'category not found'], 404);
        }
        $category->delete();
        return response()->json(['success' => true, 'message' => 'category deleted successfully']);
    }
}
