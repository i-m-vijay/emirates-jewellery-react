<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Controller;
use App\Models\MetalPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoldPriceController extends Controller
{
    /**
     * >>> CHANGE THIS if your metal type (Gold/Diamond) lives in a
     * different column, e.g. 'metal_type'. Weight stays as given.
     */
    private const METAL_TYPE_COLUMN = 'meta_metal';

    // Columns tried in order; first one that yields a positive number wins.
    private const WEIGHT_COLUMNS = [
        // 'meta_gold_weight_grams',
        // 'meta_weight_in_gram',
        'meta_gold_weight',
        // 'attribute_2_value',
    ];

    public function index()
    {
        // Latest entered price per metal type, for display
        $latestPrices = MetalPrice::orderByDesc('created_at')
            ->get()
            ->unique('metal_type')
            ->values();

        return view('admin.gold_price.index', compact('latestPrices'));
    }

    public function update(Request $request)
{
    $validated = $request->validate([
        'metal_type'     => 'required|string|in:Gold,Diamond',
        'per_gram_price' => 'required|numeric|min:0',
    ]);

    $metalType    = strtolower($validated['metal_type']);
    $perGramPrice = (float) $validated['per_gram_price'];

    // Guard: price of 0 must NOT zero-out products.
    // Leave every regular_price at its previous value and stop here.
    if ($perGramPrice <= 0) {
        return redirect()
            ->route('admin.gold-price.index')
            ->with('success', "{$metalType} price was 0 — no changes made. Existing prices kept as-is.");
    }

    $updatedCount = 0;

    DB::transaction(function () use ($metalType, $perGramPrice, &$updatedCount) {

        DB::table('product_detail')
            
            ->whereNotNull('meta_gold_weight')
            ->where('meta_gold_weight', '!=', '')
            ->whereNotNull('regular_price')
            ->where('regular_price', '!=', '')
            ->chunkById(200, function ($products) use ($perGramPrice, &$updatedCount) {
                foreach ($products as $product) {
                    $weight = $this->resolveWeight($product);

                    if ($weight <= 0) {
                        continue; // no usable weight → keep previous price
                    }

                    $newPrice = round($perGramPrice * $weight, 2);

                    // Extra safety: never write a 0 price; keep the previous one.
                    if ($newPrice <= 0) {
                        continue;
                    }

                    DB::table('product_detail')
                        ->where('record_id', $product->record_id)
                        ->update(['regular_price' => $newPrice]);

                    $updatedCount++;
                }
            }, 'record_id');

        MetalPrice::create([
            'metal_type'       => $metalType,
            'per_gram_price'   => $perGramPrice,
            'products_updated' => $updatedCount,
        ]);
    });

    return redirect()
        ->route('admin.gold-price.index')
        ->with('success', "{$metalType} price updated at \${$perGramPrice}/gram. {$updatedCount} product(s) recalculated.");
}

    

    /**
     * Try each weight column in priority order; return the first positive value found.
     */
    private function resolveWeight(object $product): float
    {
        foreach (self::WEIGHT_COLUMNS as $col) {
            $weight = $this->parseWeight($product->{$col} ?? null);
            if ($weight > 0) {
                return $weight;
            }
        }

        return 0.0;
    }

    /**
     * Pulls the numeric weight out of values like "5.5", "5.5 gms", "5.5 g".
     */
    private function parseWeight($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        preg_match('/\d+(\.\d+)?/', (string) $value, $m);

        return isset($m[0]) ? (float) $m[0] : 0.0;
    }
}