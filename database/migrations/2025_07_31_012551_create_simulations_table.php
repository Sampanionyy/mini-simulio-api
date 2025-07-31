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
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->decimal('prix_bien', 15, 2);
            $table->decimal('frais_notaire', 15, 2);
            $table->decimal('garantie_bancaire', 15, 2);
            $table->decimal('frais_agence', 15, 2);
            $table->decimal('apport', 15, 2);
            $table->decimal('total_financer', 15, 2);
            $table->decimal('taux_interet', 5, 2);
            $table->decimal('taux_assurance', 5, 2);
            $table->decimal('mensualite', 15, 2);
            $table->decimal('interets_total', 15, 2);
            $table->decimal('assurance_total', 15, 2);
            $table->integer('salaire_minimum');
            $table->integer('duree_annees');
            $table->integer('mois_debut');
            $table->integer('annee_debut');
            $table->date('date_acquisition');
            $table->date('date_financement');
            $table->decimal('revalorisation_bien', 5, 2);
            $table->decimal('travaux', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulations');
    }
};
