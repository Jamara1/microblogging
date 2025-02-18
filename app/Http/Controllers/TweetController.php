<?php

namespace App\Http\Controllers;

use App\Services\DynamoDbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class TweetController extends Controller
{
    protected $dynamoDbService;
    protected const TABLE_TWEETS = 'Tweets';

    public function __construct(DynamoDbService $dynamoDbService)
    {
        $this->dynamoDbService = $dynamoDbService;
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|string',
                'content' => 'required|string|max:280',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => '❌ Error de validación',
                'details' => $e->errors()
            ], 422);
        }

        // 🔹 Generar UUID y asignarlo como tweet_id
        $data['tweet_id'] = (string) Str::uuid();

        // 🔹 Generar timestamp con formato unix'
        $data['created_at'] = time();

        try {
            // 🔹 Guardar Tweet en DynamoDB
            $tweet = $this->dynamoDbService->insert(self::TABLE_TWEETS, $data);

            // 🔹 Insertar en la Timeline de los seguidores
            $this->addToTimeline($tweet);

            // 🔹 Retornar solo el Tweet insertado
            return response()->json([
                'message' => "✅ Tweet publicado correctamente",
                'data'   => $tweet,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => '❌ No se pudo crear el tweet',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    private function addToTimeline(array $tweet)
    {
        try {
            // 🔹 Obtener los seguidores del usuario
            $followers = $this->dynamoDbService->query('Follow', ['followed_id' => $tweet['user_id']]);

            // Si no tiene seguidores, no hay nada que hacer
            if (empty($followers)) {
                return;
            }

            // 🔹 Insertar en la tabla Timeline para cada seguidor
            foreach ($followers as $follower) {
                $timelineItem = [
                    'user_id'    => $follower['follower_id'],
                    'tweet_timestamp'   => $tweet['created_at'],
                    'author_id'  => $tweet['user_id'],
                    'tweet_id'  => $tweet['tweet_id'],
                    'content'    => $tweet['content'],
                ];
                $this->dynamoDbService->insert('Timeline', $timelineItem);
            }
        } catch (\Exception $e) {
            throw new \Exception("❌ Error al actualizar Timeline: " . $e->getMessage(), 500);
        }
    }
}
