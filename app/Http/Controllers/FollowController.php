<?php

namespace App\Http\Controllers;

use App\Services\DynamoDbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FollowController extends Controller
{
    protected $dynamoDbService;
    protected const TABLE_FOLLOW = 'Follow';
    protected const TABLE_TWEETS = 'Tweets';
    protected const TABLE_TIMELINE = 'Timeline';

    public function __construct(DynamoDbService $dynamoDbService)
    {
        $this->dynamoDbService = $dynamoDbService;
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'followed_id' => 'required|string',
                'follower_id' => 'required|string|different:followed_id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => '❌ Error de validación',
                'details' => $e->errors()
            ], 422);
        }

        $data['created_at'] = time();
        try {

            $follow = $this->dynamoDbService->insert(self::TABLE_FOLLOW, $data);

            // 🔹 Obtener los tweets previos del usuario seguido
            $tweets = $this->dynamoDbService->query(self::TABLE_TWEETS, ['user_id' => $data['followed_id']], true);

            // 🔹 Insertar tweets previos en la tabla Timeline del seguidor
            foreach ($tweets as $tweet) {
                $timelineData = [
                    'user_id'    => $data['follower_id'],
                    'tweet_timestamp'   => $tweet['created_at'],
                    'author_id'  => $tweet['user_id'],
                    'tweet_id'  => $tweet['tweet_id'],
                    'content'    => $tweet['content'],
                ];
                $this->dynamoDbService->insert(self::TABLE_TIMELINE, $timelineData);
            }

            return response()->json(['message' => '✅ Usuario seguido con éxito', 'data' => $follow], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => '❌ No se pudo seguir al usuario',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
