<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicates = DB::table('orders')
            ->select('remision', 'sede', DB::raw('COUNT(*) as total'))
            ->groupBy('remision', 'sede')
            ->havingRaw('COUNT(*) > 1')
            ->limit(5)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $examples = $duplicates
                ->map(fn ($order) => "{$order->remision} / {$order->sede} ({$order->total})")
                ->implode(', ');

            throw new RuntimeException("No se puede crear el índice único orders_remision_sede_unique porque existen órdenes duplicadas por remisión y sede: {$examples}. Corrige esos duplicados antes de ejecutar la migración.");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->unique(['remision', 'sede'], 'orders_remision_sede_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_remision_sede_unique');
        });
    }
};
