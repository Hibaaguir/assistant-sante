<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAnalysisController extends Controller
{
    public function analyze(Request $request): JsonResponse
    {
        $userId     = (int) $request->user()->id;
        $scriptPath = base_path('../ai-module/api.py');

        $pythonPath = env('PYTHON_PATH', 'python');
        $command    = escapeshellarg($pythonPath) . ' ' . escapeshellarg($scriptPath) . ' ' . $userId . ' 2>&1';
        $output  = shell_exec($command);

        if (!$output) {
            return response()->json(['error' => 'L\'analyse IA n\'a retourné aucun résultat.'], 500);
        }

        $result = json_decode($output, true);

        if (!$result) {
            return response()->json(['error' => 'Réponse IA invalide.'], 500);
        }

        return response()->json($result);
    }
}
