<?php

namespace App\Services;

use Carbon\Carbon;

class SimulationService
{
    public function calculerMensualite(
        ?int $clientId,
        int $dureeAnnees,
        float $prixBien,
        float $tauxInteret,
        float $tauxAssurance,
        float $apport,
        int $mois,
        int $annee,
        float $fraisAgencePourcent,
        float $fraisNotairePourcent,
        float $travaux,
        float $revalorisationBien
    ): array {
        $capitalDepart = $prixBien;
        $fraisAgence = ($fraisAgencePourcent / 100) * $prixBien;
        $fraisNotaire = ($fraisNotairePourcent / 100) * $prixBien;
        $capital = $prixBien - $apport;

        $garantieBancaire = max(0, 0.015 * $capital);
        $capital += $fraisNotaire + $garantieBancaire + $fraisAgence + $travaux;

        $dureeMois = $dureeAnnees * 12;
        $tauxMensuel = $tauxInteret / 12 / 100;

        $mensualite = ($capital * $tauxMensuel) / (1 - pow(1 + $tauxMensuel, -$dureeMois));
        $mensualite += ($capital * $tauxAssurance / 100) / 12;

        $assuranceTotale = $capital * ($tauxAssurance / 100) * $dureeAnnees;
        $interets = $mensualite * $dureeMois - $capital;
        $salaireMinimum = (int)(($mensualite * 100) / 35);

        $dateAcquisition = \Carbon\Carbon::createFromDate($annee, $mois, 1);
        $dateFinancement = $dateAcquisition->copy()->addYears($dureeAnnees);

        return [
            'client_id' => $clientId,
            'prix_bien' => $capitalDepart,
            'frais_notaire' => $fraisNotaire,
            'garantie_bancaire' => $garantieBancaire,
            'frais_agence' => $fraisAgence,
            'apport' => $apport,
            'total_financer' => $capital,
            'taux_interet' => $tauxInteret,
            'taux_assurance' => $tauxAssurance,
            'mensualite' => round($mensualite, 2),
            'assurance_total' => round($assuranceTotale, 2),
            'interets_total' => round($interets, 2),
            'salaire_minimum' => $salaireMinimum,
            'duree_annees' => $dureeAnnees,
            'mois_debut' => $mois,
            'annee_debut' => $annee,
            'date_acquisition' => $dateAcquisition->format('Y-m-d'),
            'date_financement' => $dateFinancement->format('Y-m-d'),
            'revalorisation_bien' => $revalorisationBien,
            'travaux' => $travaux,
        ];
    }
}
