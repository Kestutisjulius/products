# Product App (Laravel 12 + Inertia + Vue 3)

Moderni produktų valdymo sistema su Vue 3 (Inertia), Tailwind, Laravel 12, Sanctum (API), eilių (queue) importais ir testais. Paruošta plėtrai ir gamybai.

---

## ✨ Funkcionalumas

* **Dashboard**: bazinė statistika (produktai, vartotojai, sandėlio įrašai, viso likutis).
* **Produktų sąrašas** (Products/All.vue):

  * Lentelė ↔ Tinklelis (perjungiklis)
  * Paieška (SKU, aprašymas)
  * Rūšiavimas (Naujausi / SKU)
  * Puslapiavimas (12 įrašų)
  * **Populiariausios žymos** pagal produktų kiekį + filtravimas pagal žymą.
* **Produkto puslapis** (Products/Show\.vue):

  * Kešuojama produkto informacija (SKU, aprašymas, dydis, žymos)
  * **Real‑time sandėlis** (miestai + kiekiai)
* **Importai**:

  * `products:import` – produktai per jobs (queue)
  * `product:update-stock` – likučiai real‑time
  * Valandinis scheduleris
* **JSON API** (su Sanctum): `/api/products`, `/api/products/{product:sku}/stock`, `/api/auth/token`, `/api/auth/logout`
* **Naršyklės srauto nukreipimas** iš `/api/*` į info puslapį (kai prašoma HTML: „kreipkitės į administratorių…“)
* **Testai**: unit + feature (modelių ryšiai, Show puslapis, cache, komandos, API auth)

---

## 🧱 Technologijos

* Laravel 12 (PHP ≥ 8.2)
* Inertia.js + Vue 3 + Tailwind CSS
* Laravel Sanctum 4 (Bearer token API)
* Eilės: database/redis (+ Horizon rekomenduojama)
* SQLite/MySQL/PostgreSQL

---

## 🚀 Pradžia

### Reikalavimai

* PHP 8.2+
* Composer
* Node 18+
* Duomenų bazė (SQLite/MySQL/PostgreSQL)
* (Pasirenkama) Redis, jei norėsi Horizon

### Diegimas

```bash
# 1) Klonas
git clone <repo-url>
cd products

# 2) Priklausomybės
composer install
npm install

# 3) Aplinka
cp .env.example .env
php artisan key:generate

# 4) DB (pvz., SQLite)
mkdir -p database
[ -f database/database.sqlite ] || touch database/database.sqlite

# 5) Migracijos
php artisan migrate

# 6) (Jei pirmą kartą diegi Sanctum)
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### Vystymo režimas

* **Viskas vienu įsakymu** (žr. `composer.json` → `scripts.dev`):

```bash
composer run dev
```

Tai paleis: `php artisan serve`, `queue:listen`, `pail` logus ir `npm run dev` (Vite) per `npx concurrently`.

* **Rankiniu būdu**:

```bash
php artisan serve
php artisan queue:work --queue=imports,default
npm run dev
```

---

## 🔐 API (Sanctum) ir nukreipimas naršyklei

### User modelis

```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}
```

> Klaida „*cannot use Laravel\Sanctum\Contracts\HasApiTokens*“ reiškia, kad importuotas ne tas tipas — turi būti **`Laravel\Sanctum\HasApiTokens`**.

### Maršrutai (`routes/api.php`)

```php
use App\Http\Controllers\API\AuthTokenController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStockController;
use Illuminate\Support\Facades\Route;

// Informacinis puslapis (naršyklėms)
Route::view('/info', 'api.info')->name('api.info');

// Autentifikacija
Route::post('/auth/token', [AuthTokenController::class, 'store']);
Route::post('/auth/logout', [AuthTokenController::class, 'destroy'])->middleware('auth:sanctum');

// Apsaugotas API
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product:sku}/stock', [ProductStockController::class, 'show']);
});
```

### Tokeno gavimas (Postman/cURL)

```bash
curl -X POST http://localhost/api/auth/token \
  -H "Accept: application/json" \
  -d '{"email":"demo@example.com","password":"secret"}'
```

Atsakyme gauni `token` (Bearer). Tada:

```bash
curl http://localhost/api/products \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Naršyklių nukreipimas iš `/api/*`

**Middleware**: `app/Http/Middleware/RedirectApiBrowser.php`

```php
class RedirectApiBrowser
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $wantsJson = $request->expectsJson()
                || str_contains($request->header('Accept', ''), 'application/json');
            if (!$wantsJson && in_array($request->method(), ['GET','HEAD'])) {
                return redirect()->route('api.info');
            }
        }
        return $next($request);
    }
}
```

**Registracija** (`bootstrap/app.php`):

```php
->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->alias([
        'redirect.api.browser' => \App\Http\Middleware\RedirectApiBrowser::class,
    ]);
    $middleware->prependToGroup('api', \App\Http\Middleware\RedirectApiBrowser::class);
})
```

**Info puslapis**:

```php
// routes/web.php
Route::view('/api/info', 'api.info')->name('api.info');
```

`resources/views/api/info.blade.php` – paprastas informacinis HTML.

> CORS: jei API kvies kitas domenas (SPA), naudok `config/cors.php` ir leisk `Authorization`/`Accept` antraštes.

---

## 🗂️ Front-end (Inertia + Vue 3)

### Dashboard

`resources/js/Pages/Dashboard.vue` – rodo 4 statistikų korteles.

### Produktų sąrašas

`resources/js/Pages/Products/All.vue` – funkcijos:

* perjungiklis (lentelė/tinklelis)
* paieška, rūšiavimas, puslapiavimas (12)
* populiariausių žymų sąrašas + filtravimas
* „Peržiūrėti“ mygtukas → `route('products.show', product.sku)`

Controller (`ui_index`):

```php
$query = Product::with(['stocks','tags']);
if ($search = $request->input('search')) {
    $query->where(fn($q)=>$q->where('sku','like',"%$search%")
                            ->orWhere('description','like',"%$search%"));
}
if ($tag = $request->input('tag')) {
    $query->whereHas('tags', fn($q)=>$q->where('title',$tag));
}
$sort = $request->input('sort','updated_at');
$direction = $request->input('direction','desc');
if (in_array($sort,['sku','updated_at']) && in_array($direction,['asc','desc'])) {
    $query->orderBy($sort,$direction);
}
$products = $query->paginate(12)->withQueryString();
$popularTags = Tag::select('title', DB::raw('COUNT(DISTINCT product_id) as products_count'))
    ->groupBy('title')->orderByDesc('products_count')->limit(12)->get();
return Inertia::render('Products/All', compact('products','popularTags') + ['filters'=>compact('search','sort','direction','tag')]);
```

### Produkto puslapis (kešas + real‑time stock)

`resources/js/Pages/Products/Show.vue` – rodo foto, SKU, aprašymą, dydį, žymas ir **sandėlius real‑time**.

Controller (`show`):

```php
$cached = Cache::remember("product_info_{$product->sku}", now()->addMinutes(30), function() use ($product) {
    $product->load('tags');
    return [
        'id' => $product->id,
        'sku' => $product->sku,
        'description' => $product->description,
        'size' => $product->size,
        'photo' => $product->photo,
        'tags' => $product->tags->map(fn($t)=>['id'=>$t->id,'title'=>$t->title])->all(),
    ];
});
$stocks = $product->stocks()->get(['id','city','stock']);
return Inertia::render('Products/Show', ['product' => $cached + ['stocks'=>$stocks]]);
```

---

## ⛓️ Importai (komandos + jobs)

### Komanda: produktų importas

```bash
php artisan products:import --url=https://example.com/products.json
```

* Parsisiunčia JSON
* Kiekvieną įrašą siunčia į eilę: `ImportProductJob` (numatytai į `imports`)

### Komanda: likučiai

```bash
php artisan product:update-stock --url=https://demo/stocks.json
```

* Atnaujina `stocks` pagal SKU + miestą (real‑time, be kešo)
* Scheduleris (valandinis):

```php
// routes/console.php arba app/Console/Kernel alternatyva
Schedule::command('product:update-stock')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
```

### Jobs: ImportProductJob (kešo valymas)

```php
class ImportProductJob implements ShouldQueue
{
    public $queue = 'imports';
    public $tries = 5;
    public $backoff = [5,30,120];

    public function __construct(private array $productData) {}

    public function handle(): void
    {
        DB::beginTransaction();
        try {
            $product = Product::updateOrCreate(
                ['sku' => $this->productData['sku']],
                [
                    'description' => $this->productData['description'] ?? null,
                    'size'        => $this->productData['size'] ?? null,
                    'photo'       => $this->productData['photo'] ?? null,
                    'updated_at'  => $this->productData['updated_at'] ?? now(),
                ]
            );
            foreach ($this->productData['tags'] ?? [] as $tag) {
                $product->tags()->updateOrCreate(['title' => $tag['title']]);
            }
            DB::commit();
            Cache::forget("product_info_{$product->sku}");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->release(60);
        }
    }
}
```

### Eilių paleidimas

* `.env`:

```
QUEUE_CONNECTION=database
```

* Paruošti DB lenteles job’ams:

```bash
php artisan queue:table && php artisan migrate
```

* Paleisti worker’į (rekomenduojama atskirą eilę `imports`):

```bash
php artisan queue:work --queue=imports,default
```

> Jei nori vykdyti **iškart**, naudok `dispatchSync()` arba `QUEUE_CONNECTION=sync` (tik lokaliai).

### JSON pavyzdžiai

**Produktai** (`products:import`):

```json
[
  {
    "sku": "SKU123",
    "description": "Aprašymas",
    "size": "L",
    "photo": "https://.../img.jpg",
    "updated_at": "2025-01-01T00:00:00Z",
    "tags": [{"title": "Ekologiškas"}, {"title": "Lietuviškas"}]
  }
]
```

**Likučiai** (`product:update-stock`):

```json
[
  {"sku": "SKU123", "city": "Vilnius", "stock": 10},
  {"sku": "SKU123", "city": "Kaunas",  "stock": 5}
]
```

---

## 🧪 Testai

```bash
php artisan test
```

Įtraukti pavyzdžiai:

* **Unit**: ProductRelationsTest – `tags`, `stocks` ryšiai
* **Feature**: ProductShowTest – Show puslapis, cache ir real‑time stocks
* **Feature**: ImportProductsCommandTest, UpdateStocksCommandTest – komandų elgsena
* **Feature**: ImportProductJobTest – kešo invalidacija po importo
* **Feature**: ApiAuthTest – API apsauga (401) ir prieiga su tokenu

> Pastaba: PHPUnit 12 nebeskaitys `/** @test */` metaduomenų – palaipsniui pereikite prie `#[Test]` anotacijų.

---

## 📦 DB indeksai (rekomenduojama)

* `products.sku` → **unique**
* `stocks(product_id, city)` → **unique**
* `tags(product_id, title)` → **unique**

Migracijų idėja:

```php
Schema::table('products', fn($t)=>$t->unique('sku'));
Schema::table('stocks', function($t){ $t->unique(['product_id','city']); $t->index('product_id'); });
Schema::table('tags', function($t){ $t->unique(['product_id','title']); $t->index('product_id'); });
```

---

## 🛠️ Diegimas į gamybą (trumpai)

* `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`
* `php artisan migrate --force`
* Asset’ai: `npm run build`
* Eilės: **Redis + Horizon** (rekomenduojama), paleisti su Supervisor
* Cron:

```
* * * * * /usr/bin/php /path/to/artisan schedule:run >> /dev/null 2>&1
```

---

## 🧩 Dažniausios bėdos

* **Sanctum trait**: `App\Models\User cannot use Laravel\Sanctum\Contracts\HasApiTokens` → importuok **`Laravel\Sanctum\HasApiTokens`**.
* **SQLite testuose**: `no such table: products` → `use RefreshDatabase` + paleisk migracijas; prireikus sukurk testinius įrašus per `factory`.
* **Vue įspėjimas**: „runtime compilation is not supported“ → nenaudoti inline `template` JS objekte; kurti atskirą `.vue` komponentą.
* **`Link` komponentas**: privaloma `import { Link } from '@inertiajs/vue3'`.

---

## 📁 Struktūros santrauka

```
app/
  Http/
    Controllers/
      ProductController.php
      ProductStockController.php
      API/AuthTokenController.php
    Middleware/RedirectApiBrowser.php
  Jobs/ImportProductJob.php
  Models/{Product,Stock,Tag,User}.php
bootstrap/app.php (middleware registracija)
resources/
  js/Pages/Dashboard.vue
  js/Pages/Products/All.vue
  js/Pages/Products/Show.vue
  views/api/info.blade.php
routes/{web.php,api.php,console.php}
```

---

## 📝 Licencija

MIT

---
