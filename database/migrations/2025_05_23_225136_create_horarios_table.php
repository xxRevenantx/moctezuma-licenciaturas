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
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();


            $table->string('hora')->nullable();

            $table->unsignedBigInteger('dia_id')->nullable();
            $table->unsignedBigInteger('licenciatura_id')->nullable();
            $table->unsignedBigInteger('cuatrimestre_id')->nullable();
            $table->unsignedBigInteger('modalidad_id')->nullable();
            $table->unsignedBigInteger('generacion_id')->nullable();
            $table->unsignedBigInteger('asignacion_materia_id')->nullable();



            $table->foreign('dia_id')->references('id')->on('dias')->onDelete('cascade');
            $table->foreign('licenciatura_id')->references('id')->on('licenciaturas')->onDelete('cascade');
            $table->foreign('cuatrimestre_id')->references('id')->on('cuatrimestres')->onDelete('cascade');
            $table->foreign('modalidad_id')->references('id')->on('modalidades')->onDelete('cascade');
            $table->foreign('generacion_id')->references('id')->on('generaciones')->onDelete('cascade');
            $table->foreign('asignacion_materia_id')->references('id')->on('asignacion_materias')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
