<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Simulation;
use App\Services\SimulationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SimulationController extends Controller
{
    protected $simulationService;

    public function __construct(SimulationService $simulationService)
    {
        $this->simulationService = $simulationService;
    }

    public function store(Request $request)
    {
        Log::channel('simulation')->info('Début de la création de simulation', ['request' => $request->all()]);

        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'prix_bien' => 'required|numeric',
            'taux_interet' => 'required|numeric',
            'taux_assurance' => 'required|numeric',
            'apport' => 'required|numeric',
            'mois_debut' => 'required|integer|min:1|max:12',
            'annee_debut' => 'required|integer',
            'frais_agence' => 'required|numeric',
            'frais_notaire' => 'required|numeric',
            'travaux' => 'required|numeric',
            'revalorisation_bien' => 'required|numeric',
            'duree_annees' => 'required|integer|min:1',
        ]);

        try {
            $simulation = $this->simulationService->calculerMensualite(
                $data['client_id'],
                $data['duree_annees'],
                $data['prix_bien'],
                $data['taux_interet'],
                $data['taux_assurance'],
                $data['apport'],
                $data['mois_debut'],
                $data['annee_debut'],
                $data['frais_agence'],
                $data['frais_notaire'],
                $data['travaux'],
                $data['revalorisation_bien']
            );

            Log::channel('simulation')->info('Résultat du calcul de mensualité', ['simulation' => $simulation]);

            $saved = Simulation::create($simulation);

            Log::channel('simulation')->info('Simulation sauvegardée avec succès', ['id' => $saved->id]);

            return response()->json($saved, 201);
        } catch (\Exception $e) {
            Log::channel('simulation')->error('Erreur lors de la création de simulation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erreur lors de la création de la simulation'], 500);
        }
    }

    public function getByClientId($clientId)
    {
        Log::channel('simulation')->info('Récupération des simulations pour le client', ['client_id' => $clientId]);

        $simulations = Simulation::where('client_id', $clientId)->get();

        if ($simulations->isEmpty()) {
            return response()->json(['message' => 'Aucune simulation trouvée pour ce client'], 404);
        }

        Log::channel('simulation')->info('Simulations récupérées avec succès', ['count' => $simulations->count()]);

        return response()->json($simulations, 200);
    }
}
