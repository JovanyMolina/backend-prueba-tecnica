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
        Schema::create('tasks', function (Blueprint $t) {
  $t->id();
  $t->foreignId('project_id')->constrained()->cascadeOnDelete();
  $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
  $t->string('title');
  $t->text('description')->nullable();
  $t->enum('priority', ['Alta','Media','Baja'])->default('Media');
  $t->date('due_date')->nullable();
  $t->enum('state', ['Pendiente','En progreso','Hecha'])->default('Pendiente');
  $t->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
