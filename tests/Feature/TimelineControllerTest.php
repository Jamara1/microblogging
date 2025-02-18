<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Services\DynamoDbService;

class TimelineControllerTest extends TestCase
{
    protected $dynamoDbService;

    public function setUp(): void
    {
        parent::setUp();

        // Crear un mock del servicio DynamoDbService
        $this->dynamoDbService = Mockery::mock(DynamoDbService::class);
        $this->app->instance(DynamoDbService::class, $this->dynamoDbService);
    }

    public function test_get_timeline_successfully()
    {
        $userId = 'user_123';

        $timelineData = [
            [
                'user_id' => 'user_123',
                'tweet_id' => 'tweet_1',
                'author_id' => 'user_456',
                'content' => 'Tweet de prueba',
                'tweet_timestamp' => time() - 3600,
            ],
            [
                'user_id' => 'user_123',
                'tweet_id' => 'tweet_2',
                'author_id' => 'user_456',
                'content' => 'Otro tweet de prueba',
                'tweet_timestamp' => time() - 1800,
            ]
        ];

        // Simular la consulta a la tabla Timeline
        $this->dynamoDbService
            ->shouldReceive('query')
            ->once()
            ->with('Timeline', ['user_id' => $userId], true)
            ->andReturn($timelineData);

        // Enviar la solicitud GET para obtener el timeline
        $response = $this->getJson('/api/timeline/' . $userId);

        // Verificar que la respuesta sea la esperada
        $response->assertStatus(200)
            ->assertJson([
                'message' => '✅ Timeline obtenido con éxito',
                'data' => $timelineData,
            ]);
    }

    public function test_get_timeline_fails_due_to_exception()
    {
        $userId = 'user_123';

        // Simular que ocurre una excepción al consultar la tabla Timeline
        $this->dynamoDbService
            ->shouldReceive('query')
            ->once()
            ->with('Timeline', ['user_id' => $userId], true)
            ->andThrow(new \Exception('Error al consultar la base de datos'));

        // Enviar la solicitud GET para obtener el timeline
        $response = $this->getJson('/api/timeline/' . $userId);

        // Verificar que la respuesta sea la esperada en caso de error
        $response->assertStatus(500)
            ->assertJson([
                'error' => '❌ Error al obtener el Timeline',
                'details' => 'Error al consultar la base de datos',
            ]);
    }
}
