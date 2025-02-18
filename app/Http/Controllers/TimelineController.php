<?php

namespace App\Http\Controllers;

use App\Services\DynamoDbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TimelineController extends Controller
{
    protected $dynamoDbService;
    protected const TABLE_TIMELINE = 'Timeline';

    public function __construct(DynamoDbService $dynamoDbService)
    {
        $this->dynamoDbService = $dynamoDbService;
    }

    public function getTimeline(string $userId): JsonResponse
    {
        try {
            // 🔹 Consultar la tabla Timeline usando el user_id
            $timeline = $this->dynamoDbService->query('Timeline', ['user_id' => $userId], true);

            return response()->json([
                'message' => '✅ Timeline obtenido con éxito',
                'data'    => $timeline
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => '❌ Error al obtener el Timeline',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
