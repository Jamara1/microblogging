<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Services\DynamoDbService;

class TweetControllerTest extends TestCase
{
    protected $dynamoDbService;

    public function setUp(): void
    {
        parent::setUp();

        // Crear un mock del servicio DynamoDbService
        $this->dynamoDbService = Mockery::mock(DynamoDbService::class);
        $this->app->instance(DynamoDbService::class, $this->dynamoDbService);
    }

    public function test_store_tweet_successfully()
    {
        $tweetData = [
            'user_id' => 'user_123',
            'content' => 'Este es un tweet de prueba',
        ];

        // Simular la inserción del tweet en DynamoDB
        $this->dynamoDbService
            ->shouldReceive('insert')
            ->once()
            ->with('Tweets', Mockery::on(function ($data) use ($tweetData) {
                return $data['user_id'] === $tweetData['user_id'] && $data['content'] === $tweetData['content'];
            }))
            ->andReturn([
                'tweet_id' => 'fake-uuid',
                'user_id' => 'user_123',
                'content' => 'Este es un tweet de prueba',
                'created_at' => time(),
            ]);

        // Simular la consulta de seguidores
        $this->dynamoDbService
            ->shouldReceive('query')
            ->once()
            ->with('Follow', ['followed_id' => 'user_123'])
            ->andReturn([
                ['follower_id' => 'user_456'],
            ]);

        // Simular la inserción en la Timeline para los seguidores
        $this->dynamoDbService
            ->shouldReceive('insert')
            ->once()
            ->with('Timeline', Mockery::on(function ($data) {
                return $data['user_id'] === 'user_456' &&
                    $data['author_id'] === 'user_123' &&
                    $data['tweet_id'] === 'fake-uuid' &&
                    $data['content'] === 'Este es un tweet de prueba';
            }))
            ->andReturn(['success' => true]);

        // Enviar la solicitud POST para crear un tweet
        $response = $this->postJson('/api/tweet', $tweetData);

        // Verificar que la respuesta sea la esperada
        $response->assertStatus(201)
            ->assertJson([
                'message' => '✅ Tweet publicado correctamente',
                'data' => [
                    'tweet_id' => 'fake-uuid',
                    'user_id' => 'user_123',
                    'content' => 'Este es un tweet de prueba',
                    'created_at' => time(),
                ],
            ]);
    }

    public function test_store_tweet_fails_due_to_exception()
    {
        // Simular que ocurre una excepción durante la inserción
        $this->dynamoDbService
            ->shouldReceive('insert')
            ->once()
            ->andThrow(new \Exception('Error al insertar el tweet'));

        // Enviar la solicitud POST para crear un tweet
        $response = $this->postJson('/api/tweet', [
            'user_id' => 'user_123',
            'content' => 'Este es un tweet de prueba',
        ]);

        // Verificar que la respuesta sea la esperada
        $response->assertStatus(500)
            ->assertJson([
                'error' => '❌ No se pudo crear el tweet',
                'details' => 'Error al insertar el tweet',
            ]);
    }
}
