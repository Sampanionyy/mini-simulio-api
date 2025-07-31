<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::channel('auth')->info('Tentative d\'inscription reçue', [
            'ip' => $request->ip(),
            'email' => $request->email,
            'name' => $request->name,
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('auth')->warning('Échec de validation lors de l\'inscription', [
                'erreurs' => $e->errors(),
                'email' => $request->email,
            ]);
            throw $e;
        }

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            Log::channel('auth')->info('Nouvel utilisateur inscrit', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::channel('auth')->info('Token généré pour l\'utilisateur', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Inscription réussie',
                'token_type' => 'Bearer',
            ], 201);
        } catch (\Exception $e) {
            Log::channel('auth')->error('Erreur lors de l\'enregistrement de l\'utilisateur', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Une erreur est survenue.'], 500);
        }
    }

    public function login(Request $request)
    {
        Log::channel('auth')->info('Tentative de connexion.', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'time' => now()->toDateTimeString(),
        ]);

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::channel('auth')->warning('Échec de connexion : utilisateur non trouvé.', [
                'email' => $request->email,
            ]);

            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            Log::channel('auth')->warning('Échec de connexion : mot de passe incorrect.', [
                'email' => $request->email,
            ]);

            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::channel('auth')->info('Connexion réussie.', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Connexion réussie',
            'token_type' => 'Bearer',
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}