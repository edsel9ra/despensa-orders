<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_item', 20)->unique();
            $table->string('descripcion');
            $table->decimal('precio_unidad', 12, 2);
            $table->string('presentacion');
            $table->decimal('precio_presentacion', 12, 2);
            $table->foreignId('categoria_id')->constrained('categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
