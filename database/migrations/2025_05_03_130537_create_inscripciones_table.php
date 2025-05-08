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
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->unique();
            $table->string('matricula', 8);
            $table->string('folio', 10)->nullable();

            $table->string('CURP', 18)->unique();
            $table->string('nombre', 50);
            $table->string('apellido_paterno', 50);
            $table->string('apellido_materno', 50);
            $table->date('fecha_nacimiento');
            $table->integer('edad');
            $table->enum('sexo', ['H', 'M']);
            $table->string('pais', 100)->nullable();
            $table->unsignedBigInteger('estado_nacimiento_id')->nullable();
            $table->unsignedBigInteger('ciudad_nacimiento_id')->nullable();

            $table->string('calle')->nullable();
            $table->string('numero_exterior', 10)->nullable();
            $table->string('numero_interior', 10)->nullable();
            $table->string('colonia')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('municipio')->nullable();
            $table->unsignedBigInteger('ciudad_id')->nullable();
            $table->unsignedBigInteger('estado_id')->nullable();
            $table->string('telefono', 10)->nullable();
            $table->string('celular', 10)->nullable();
            $table->string('tutor', 255)->nullable();

            $table->string('bachillerato_procedente', 255)->nullable();
            $table->unsignedBigInteger('licenciatura_id');
            $table->unsignedBigInteger('generacion_id');
            $table->unsignedBigInteger('cuatrimestre_id');
            $table->unsignedBigInteger('modalidad_id');

            $table->enum('certificado', ["true","false"])->nullable()->default("true");
            $table->enum('acta_nacimiento', ["true", "false"])->nullable()->default("true");
            $table->enum('certificado_medico', ["true", "false"])->nullable()->default("true");
            $table->enum('fotos_infantiles', ["true", "false"])->nullable()->default("true");
            $table->string('foto')->nullable();

            $table->string('otros')->nullable();

            $table->enum('foraneo', ['true', 'false'])->nullable()->default('false');

            $table->enum('status', ['true', 'false'])->default('true');

            $table->foreign('estado_nacimiento_id')->references('id')->on('estados')->onDelete('cascade');
            $table->foreign('ciudad_nacimiento_id')->references('id')->on('ciudades')->onDelete('cascade');


            $table->foreign('ciudad_id')->references('id')->on('ciudades')->onDelete('cascade');
            $table->foreign('estado_id')->references('id')->on('estados')->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('licenciatura_id')->references('id')->on('licenciaturas')->onDelete('cascade');
            $table->foreign('generacion_id')->references('id')->on('generaciones')->onDelete('cascade');
            $table->foreign('cuatrimestre_id')->references('id')->on('cuatrimestres')->onDelete('cascade');
            $table->foreign('modalidad_id')->references('id')->on('modalidades')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
