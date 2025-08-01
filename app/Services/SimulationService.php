<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SimulationService
{
    protected $pythonServiceUrl;

    public function __construct()
    {
        $this->pythonServiceUrl = env('PYTHON_SERVICE_URL', 'http://localhost:5000');
    }

    public function calculerMensualite(
        $clientId,
        $dureeAnnees,
        $prixBien,
        $tauxInteret,
        $tauxAssurance,
        $apport,
        $moisDebut,
        $anneeDebut,
        $fraisAgence,
        $fraisNotaire,
        $travaux,
        $revalorisationBien
    ) {
        try {
            Log::channel('simulation')->info('Appel du service Python pour calcul de mensualité');

            // Préparer les données pour l'API Python
            $requestData = [
                'duree_annees' => $dureeAnnees,
                'prix_bien' => $prixBien,
                'taux_interet' => $tauxInteret,
                'taux_assurance' => $tauxAssurance,
                'apport' => $apport,
                'mois_debut' => $moisDebut,
                'annee_debut' => $anneeDebut,
                'frais_agence' => $fraisAgence,
                'frais_notaire' => $fraisNotaire,
                'travaux' => $travaux,
                'revalorisation_bien' => $revalorisationBien
            ];

            Log::channel('simulation')->info('Données envoyées au service Python', $requestData);

            // Appel HTTP vers le service Python
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->pythonServiceUrl . '/api/calculer-mensualite', $requestData);

            if (!$response->successful()) {
                throw new Exception('Erreur du service Python: ' . $response->body());
            }

            $pythonResult = $response->json();
            
            Log::channel('simulation')->info('Réponse reçue du service Python', ['status' => $response->status()]);

            // Transformer la réponse Python au format attendu par Laravel
            return [
                'client_id' => $clientId,
                'duree_annees' => $dureeAnnees,
                'prix_bien' => $prixBien,
                'taux_interet' => $tauxInteret,
                'taux_assurance' => $tauxAssurance,
                'apport' => $apport,
                'mois_debut' => $moisDebut,
                'annee_debut' => $anneeDebut,
                'travaux' => $travaux,
                'revalorisation_bien' => $revalorisationBien,
                
                // Résultats calculés par Python
                'mensualite' => $pythonResult['mensualite'],
                'interets_total' => $pythonResult['interets_total'],
                'assurance_total' => $pythonResult['assurance_total'],
                'frais_notaire_calcules' => $pythonResult['frais_notaire'],
                'garantie_bancaire' => $pythonResult['garantie_bancaire'],
                'salaire_minimum' => $pythonResult['salaire_minimum'],
                'frais_agence_calcules' => $pythonResult['frais_agence'],
                'total_financer' => $pythonResult['total_financer'],
                'frais_agence' => $pythonResult['frais_agence'],
                'frais_notaire' => $pythonResult['frais_notaire'],
                
                // Données en JSON
                'tableau_amortissement' => json_encode($pythonResult['tableau_amortissement']),
                'resume_financement' => json_encode($pythonResult['resume_financement']),
                'details_pret' => json_encode($pythonResult['details_pret']),
                'dates_financement' => json_encode($pythonResult['dates_financement']),
                'evolution_bien' => json_encode($pythonResult['evolution_bien']),

                'date_acquisition' => \Carbon\Carbon::createFromFormat('d/m/Y', $pythonResult['date_acquisition'])->format('Y-m-d'),
                'date_financement' => \Carbon\Carbon::createFromFormat('d/m/Y', $pythonResult['date_acquisition'])->format('Y-m-d'),
                
                'created_at' => now(),
                'updated_at' => now(),
            ];

        } catch (Exception $e) {
            Log::channel('simulation')->error('Erreur lors de l\'appel au service Python', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new Exception('Erreur lors du calcul de la simulation: ' . $e->getMessage());
        }
    }
}