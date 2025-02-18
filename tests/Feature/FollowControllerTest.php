<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Services\DynamoDbService;

class FollowControllerTest extends TestCase
{
    protected $dynamoDbService;

    public function setUp(): void
    {
        parent::setUp();

        // Crear un mock del servicio DynamoDbService
        $this->dynamoDbService = Mockery::mock(DynamoDbService::class);
        $this->app->instance(DynamoDbService::class, $this->dynamoDbService);
    }

    public function test_follow_user_successfully()
    {
        $followData = [
            'follower_id' => 'user_123',
            'followed_id' => 'user_456',
        ];

        $tweets = [
            [
                'user_id' => 'user_456',
                'tweet_id' => 'tweet_1',
                'content' => 'Tweet de prueba',
                'created_at' => time() - 3600
            ]
        ];

        // Simular la inserción en Follow
        $this->dynamoDbService
            ->shouldReceive('insert')
            ->once()
            ->with('Follow', Mockery::on(function ($data) use ($followData) {
                return $data['follower_id'] === $followData['follower_id'] && $data['followed_id'] === $followData['followed_id'];
            }))
            ->andReturn(['success' => true]);

        // Simular la consulta de tweets del usuario seguido
        $this->dynamoDbService
            ->shouldReceive('query')
            ->once()
            ->with('Tweets', ['user_id' => 'user_456'], true)
            ->andReturn($tweets);

        // Simular la inserción en Timeline
        $this->dynamoDbService
            ->shouldReceive('insert')
            ->once()
            ->with('Timeline', Mockery::on(function ($data) {
                return $data['user_id'] === 'user_123' &&
                    $data['author_id'] === 'user_456' &&
                    $data['tweet_id'] === 'tweet_1' &&
                    $data['content'] === 'Tweet de prueba';
            }))
            ->andReturn(['success' => true]);

        // Enviar petición HTTP
        $response = $this->postJson('/api/follow', $followData);

        // Verificar respuesta
        $response->assertStatus(201)
            ->assertJson(['message' => '✅ Usuario seguido con éxito']);
    }

    public function test_follow_user_fails_due_to_exception()
    {
        $this->dynamoDbService
            ->shouldReceive('insert')
            ->once()
            ->andThrow(new \Exception('Error en la base de datos'));

        $response = $this->postJson('/api/follow', [
            'follower_id' => 'user_123',
            'followed_id' => 'user_456',
        ]);

        $response->assertStatus(500)
            ->assertJson(['error' => '❌ No se pudo seguir al usuario']);
    }
}
