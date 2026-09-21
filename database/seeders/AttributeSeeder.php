<?php

namespace Database\Seeders;

use App\Models\VariantAttribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'id' => 1,
                'name' => 'color',
            ],
            [
                'id' => 2,
                'name' => 'size',
            ]
        ];

        foreach ($attributes as $attribute) {
            $exists = VariantAttribute::where('name', $attribute['name'])->exists();
            if (! $exists) {
                $att =new VariantAttribute();
                $att->name = $attribute['name'];
                $att->save();
            }
        }
    }
}
