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
       Schema::create('projects', function (Blueprint $t) {
  $t->id();
  $t->string('name');
  $t->text('description')->nullable();
  $t->date('start_date')->nullable();
  $t->date('end_date')->nullable();
  $t->enum('status', ['Activo','Pausado','Terminado'])->default('Activo');
  $t->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
