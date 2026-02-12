<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_id')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('handle');
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('status')->default('active');
            $table->json('tags')->nullable();
            $table->json('variants')->nullable();
            $table->json('images')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('handle');
            $table->index('status');
            $table->index('vendor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
