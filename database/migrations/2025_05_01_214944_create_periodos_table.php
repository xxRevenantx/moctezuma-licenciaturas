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
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->string('ciclo_escolar');
            $table->unsignedBigInteger('cuatrimestre_id')->nullable();
            $table->unsignedBigInteger('generacion_id')->nullable();
            $table->unsignedBigInteger('mes_id')->nullable();

            $table->date('inicio_periodo')->nullable();
            $table->date('termino_periodo')->nullable();
            $table->integer('order');



            $table->foreign('cuatrimestre_id')->references('id')->on('cuatrimestres')->onDelete('set null');
            $table->foreign('generacion_id')->references('id')->on('generaciones')->onDelete('set null');
            $table->foreign('mes_id')->references('id')->on('meses')->onDelete('set null');



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};
