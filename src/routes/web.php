<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
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
            'categories_count' => Category::count(),
            'items_count' => Item::count(),
            'orders_count' => Order::count(),
            'recent_orders' => Order::with('user:id,name')
                ->withCount('orderItems')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'remision' => $o->remision,
                    'sede' => $o->sede,
                    'user_name' => $o->user?->name ?? 'Sin registrar',
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

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/xlsx', [ReportController::class, 'exportXlsx'])->name('reports.export-xlsx');
    Route::get('reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');

    Route::resource('categories', CategoryController::class);

    Route::get('items/import', [ItemController::class, 'importForm'])->name('items.import.form');
    Route::post('items/import', [ItemController::class, 'import'])->name('items.import');
    Route::resource('items', ItemController::class);

    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders/preview', [OrderController::class, 'preview'])->name('orders.preview');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/xlsx', [OrderController::class, 'exportXlsx'])->name('orders.export-xlsx');
    Route::get('orders/{order}/pdf', [OrderController::class, 'exportPdf'])->name('orders.export-pdf');
});

require __DIR__.'/auth.php';
