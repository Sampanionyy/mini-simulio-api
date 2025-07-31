<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
     protected $fillable = [
        'client_id',
        'prix_bien',
        'frais_notaire',
        'garantie_bancaire',
        'frais_agence',
        'apport',
        'total_financer',
        'taux_interet',
        'taux_assurance',
        'mensualite',
        'interets_total',
        'assurance_total',
        'salaire_minimum',
        'duree_annees',
        'mois_debut',
        'annee_debut',
        'date_acquisition',
        'date_financement',
        'revalorisation_bien',
        'travaux',
    ];
}
