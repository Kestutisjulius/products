<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->tokenCan('products:read'), 403);

        $products = Product::with(['stocks','tags'])
            ->latest('updated_at')
            ->paginate(12);

        return response()->json($products);
    }
    
        public function ui_index(Request $request)
        {
            $query = Product::with(['stocks', 'tags']);

            // Paieška
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtras pagal žymą
            $tag = $request->input('tag');
            if ($tag) {
                $query->whereHas('tags', fn ($q) => $q->where('title', $tag));
            }

            // Rūšiavimas
            $sort = $request->input('sort', 'updated_at'); // 'updated_at' = "Naujausi"
            $direction = $request->input('direction', 'desc');
            if (in_array($sort, ['sku', 'updated_at']) && in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($sort, $direction);
            }

            $products = $query->paginate(12)->withQueryString();

            // Populiariausios žymos pagal produktų kiekį
            $popularTags = Tag::select('title', DB::raw('COUNT(DISTINCT product_id) AS products_count'))
                ->groupBy('title')
                ->orderByDesc('products_count')
                ->limit(12)
                ->get();

            return Inertia::render('Products/All', [
                'products'     => $products,
                'popularTags'  => $popularTags,
                'filters'      => [
                    'search'    => $search,
                    'sort'      => $sort,
                    'direction' => $direction,
                    'tag'       => $tag,
                ],
            ]);
        }


        public function show(Product $product)
        {
            
            $cachedProduct = Cache::remember("product_info_{$product->sku}", now()->addMinutes(30), function () use ($product) {
                
                $product->load('tags');

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'description' => $product->description,
                    'size' => $product->size,
                    'photo' => $product->photo,
                    'tags' => $product->tags->map(fn($t) => [
                        'id' => $t->id,
                        'title' => $t->title,
                    ]),
                ];

            });

            
            $stocks = $product->stocks()->get(['id', 'city', 'stock']);

            
            $data = array_merge($cachedProduct, [
                'stocks' => $stocks,
            ]);

            return Inertia::render('Products/Show', [
                'product' => $data,
            ]);
        }



}
