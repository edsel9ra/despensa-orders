<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'stats' => [
            'categories_count' => \App\Models\Category::count(),
            'items_count' => \App\Models\Item::count(),
            'orders_count' => \App\Models\Order::count(),
            'recent_orders' => \App\Models\Order::withCount('orderItems')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'remision' => $o->remision,
                    'sede' => $o->sede,
                    'fecha' => $o->fecha->format('d/m/Y'),
                    'total' => $o->total,
                    'items_count' => $o->order_items_count,
                ]),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', \App\Http\Controllers\CategoryController::class);

    Route::get('items/import', [\App\Http\Controllers\ItemController::class, 'importForm'])->name('items.import.form');
    Route::post('items/import', [\App\Http\Controllers\ItemController::class, 'import'])->name('items.import');
    Route::resource('items', \App\Http\Controllers\ItemController::class);

    Route::get('orders/create', [\App\Http\Controllers\OrderController::class, 'create'])->name('orders.create');
    Route::post('orders/preview', [\App\Http\Controllers\OrderController::class, 'preview'])->name('orders.preview');
    Route::post('orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/xlsx', [\App\Http\Controllers\OrderController::class, 'exportXlsx'])->name('orders.export-xlsx');
    Route::get('orders/{order}/pdf', [\App\Http\Controllers\OrderController::class, 'exportPdf'])->name('orders.export-pdf');
});

require __DIR__.'/auth.php';
