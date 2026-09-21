<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\VariantAttribute;
use App\Models\VariantAttributeValue;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{

    public function view(Request $request)
    {
        $this->data['attributes'] = VariantAttribute::with('get_variant_value')->paginate(10);
        $this->data['attribute_name'] = VariantAttribute::get();
        return view('admin.attribute.view')->with($this->data);
    }

    public function save(Request $request)
    {
        $request->validate([
            'attribute' => 'required|exists:variant_attributes,id',
            'attribute_value' => 'required|string'
        ]);

        try {
            $attributeId = $request->attribute;
            $values = explode(',', $request->attribute_value);
            $values = array_filter(array_map(function ($value) {
                return trim($value);
            }, $values));
            foreach ($values as $value) {
                $exists = VariantAttributeValue::where(['attribute_id' => $attributeId, 'value' => $value])->exists();
                if (!$exists) {
                    $attribute_value = new VariantAttributeValue();
                    $attribute_value->attribute_id = $attributeId;
                    $attribute_value->value = $value;
                    $attribute_value->save();
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Attribute values saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
