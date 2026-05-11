<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id('id'); // PK
            $table->unsignedBigInteger('sale_id'); // FK
            $table->unsignedBigInteger('product_id'); // FK
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};