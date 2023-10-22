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
        Schema::create('resources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('scheme');
            $table->string('host');
            $table->string('path')->nullable()->default(null);
            $table->string('query')->nullable()->default(null);
            $table->string('fragment')->nullable()->default(null);
            $table->integer('status_code')->nullable()->default(null);
            $table->timestamp('visited_at')->nullable()->default(null);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
