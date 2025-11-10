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
        Schema::create('titulos', function (Blueprint $table) {
                 // Relación principal
             $table->unsignedBigInteger('alumno_id');


            $table->string('grado_titulo', 200);                     // "LICENCIATURA EN ..."

            // Fundamento legal del reconocimiento
            $table->string('acuerdo_numero', 50)->nullable();
            $table->date('acuerdo_fecha')->nullable();

            // Examen profesional (cumplimiento de planes y programas y examen)
            $table->date('examen_fecha')->nullable();

            // Expedición del título (lado izquierdo, parte inferior)
            $table->string('expedido_en', 120)->nullable();          // "Ciudad Altamirano, Guerrero"
            $table->date('expedicion_fecha')->nullable();

            // Departamento de Registro y Certificación (lado derecho, arriba izq)
            $table->string('registro', 50)->nullable();
            $table->string('libro', 50)->nullable();
            $table->string('foja', 50)->nullable();
            $table->string('registro_lugar', 120)->nullable();       // "Chilpancingo de los Bravo, Guerrero"
            $table->date('registro_fecha')->nullable();

            // Escuela (lado derecho, arriba der)
            $table->string('plan_estudios', 100)->nullable();
            $table->year('anio_egreso')->nullable();
            $table->string('acta_examen', 120)->nullable();          // título del acta / tipo
            $table->string('acta_numero', 60)->nullable();
            $table->date('acta_fecha')->nullable();
            $table->string('acta_expedida_en', 120)->nullable();
            $table->string('titulo_numero', 80)->nullable();


            // Recursos y control
            $table->string('folio_cadena', 40)->unique()->nullable();                   // cuadro “FOLIO” al pie derecho
            $table->enum('estatus', ['borrador','emitido','cancelado'])->default('borrador');


            // LLAVES FORANEAS
            $table->foreign('alumno_id')->references('id')->on('inscripciones')->onDelete('cascade');


            // Marcas de tiempo
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titulos');
    }
};
