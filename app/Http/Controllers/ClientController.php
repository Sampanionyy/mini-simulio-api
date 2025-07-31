<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    /**
     * Affiche la liste de tous les clients
     */
    public function index(): JsonResponse
    {
        try {
            $clients = Client::all();
            
            Log::channel('client')->info('Liste des clients récupérée avec succès', [
                'count' => $clients->count()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Liste des clients récupérée avec succès',
                'data' => $clients
            ], 200);
            
        } catch (\Exception $e) {
            Log::channel('client')->error('Erreur lors de la récupération des clients', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des clients'
            ], 500);
        }
    }

    /**
     * Affiche un client spécifique
     */
    public function show($id): JsonResponse
    {
        try {
            $client = Client::find($id);
            
            if (!$client) {
                Log::channel('client')->warning('Client non trouvé', ['id' => $id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Client non trouvé'
                ], 404);
            }
            
            Log::channel('client')->info('Client récupéré avec succès', [
                'client_id' => $client->id,
                'client_name' => $client->name
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Client récupéré avec succès',
                'data' => $client
            ], 200);
            
        } catch (\Exception $e) {
            Log::channel('client')->error('Erreur lors de la récupération du client', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du client'
            ], 500);
        }
    }

    /**
     * Crée un nouveau client
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|min:2',
                'phone' => 'required|string|max:20|unique:clients,phone|regex:/^[0-9+\-\s\(\)]+$/',
                'email' => 'required|email|max:255|unique:clients,email',
                'address' => 'required|string|max:500|min:5'
            ], [
                'name.required' => 'Le nom est obligatoire',
                'name.min' => 'Le nom doit contenir au moins 2 caractères',
                'name.max' => 'Le nom ne peut pas dépasser 255 caractères',
                'phone.required' => 'Le téléphone est obligatoire',
                'phone.unique' => 'Ce numéro de téléphone est déjà utilisé',
                'phone.regex' => 'Le format du numéro de téléphone est invalide',
                'email.required' => 'L\'email est obligatoire',
                'email.email' => 'Le format de l\'email est invalide',
                'email.unique' => 'Cette adresse email est déjà utilisée',
                'address.required' => 'L\'adresse est obligatoire',
                'address.min' => 'L\'adresse doit contenir au moins 5 caractères',
                'address.max' => 'L\'adresse ne peut pas dépasser 500 caractères'
            ]);

            if ($validator->fails()) {
                Log::channel('client')->warning('Validation échouée lors de la création du client', [
                    'errors' => $validator->errors()->toArray(),
                    'data' => $request->all()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Création du client
            $client = Client::create([
                'name' => trim($request->name),
                'phone' => trim($request->phone),
                'email' => strtolower(trim($request->email)),
                'address' => trim($request->address)
            ]);

            Log::channel('client')->info('Client créé avec succès', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_email' => $client->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client créé avec succès',
                'data' => $client
            ], 201);

        } catch (\Exception $e) {
            Log::channel('client')->error('Erreur lors de la création du client', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du client'
            ], 500);
        }
    }

    /**
     * Met à jour un client existant
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $client = Client::find($id);
            
            if (!$client) {
                Log::channel('client')->warning('Tentative de mise à jour d\'un client inexistant', ['id' => $id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Client non trouvé'
                ], 404);
            }

            // Validation des données
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|min:2',
                'phone' => 'sometimes|required|string|max:20|regex:/^[0-9+\-\s\(\)]+$/|unique:clients,phone,' . $id,
                'email' => 'sometimes|required|email|max:255|unique:clients,email,' . $id,
                'address' => 'sometimes|required|string|max:500|min:5'
            ], [
                'name.required' => 'Le nom est obligatoire',
                'name.min' => 'Le nom doit contenir au moins 2 caractères',
                'name.max' => 'Le nom ne peut pas dépasser 255 caractères',
                'phone.required' => 'Le téléphone est obligatoire',
                'phone.unique' => 'Ce numéro de téléphone est déjà utilisé',
                'phone.regex' => 'Le format du numéro de téléphone est invalide',
                'email.required' => 'L\'email est obligatoire',
                'email.email' => 'Le format de l\'email est invalide',
                'email.unique' => 'Cette adresse email est déjà utilisée',
                'address.required' => 'L\'adresse est obligatoire',
                'address.min' => 'L\'adresse doit contenir au moins 5 caractères',
                'address.max' => 'L\'adresse ne peut pas dépasser 500 caractères'
            ]);

            if ($validator->fails()) {
                Log::channel('client')->warning('Validation échouée lors de la mise à jour du client', [
                    'client_id' => $id,
                    'errors' => $validator->errors()->toArray(),
                    'data' => $request->all()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Préparation des données à mettre à jour
            $updateData = [];
            if ($request->has('name')) {
                $updateData['name'] = trim($request->name);
            }
            if ($request->has('phone')) {
                $updateData['phone'] = trim($request->phone);
            }
            if ($request->has('email')) {
                $updateData['email'] = strtolower(trim($request->email));
            }
            if ($request->has('address')) {
                $updateData['address'] = trim($request->address);
            }

            // Mise à jour du client
            $client->update($updateData);

            Log::channel('client')->info('Client mis à jour avec succès', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'updated_fields' => array_keys($updateData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client mis à jour avec succès',
                'data' => $client->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::channel('client')->error('Erreur lors de la mise à jour du client', [
                'id' => $id,
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du client'
            ], 500);
        }
    }

    /**
     * Supprime un client
     */
    public function destroy($id): JsonResponse
    {
        try {
            $client = Client::find($id);
            
            if (!$client) {
                Log::channel('client')->warning('Tentative de suppression d\'un client inexistant', ['id' => $id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Client non trouvé'
                ], 404);
            }

            $clientName = $client->name;
            $client->delete();

            Log::channel('client')->info('Client supprimé avec succès', [
                'client_id' => $id,
                'client_name' => $clientName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            Log::channel('client')->error('Erreur lors de la suppression du client', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du client'
            ], 500);
        }
    }
}