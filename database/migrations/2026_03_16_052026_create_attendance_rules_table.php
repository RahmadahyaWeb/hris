<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->integer('late_tolerance_minutes')->default(10);
            $table->integer('early_checkout_tolerance')->default(10);
            $table->integer('overtime_after_minutes')->default(30);
            $table->boolean('allow_early_checkin')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_rules');
    }
};
