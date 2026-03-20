<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('role_level'); // 1=admin, 2=supervisor/staff, 3=worker
            $table->string('position');
            $table->enum('salary_type', ['weekly', 'monthly']);
            $table->decimal('base_salary', 12, 2);
            $table->decimal('overtime_rate_per_minute', 10, 2)->default(0);
            $table->decimal('late_penalty_per_minute', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
