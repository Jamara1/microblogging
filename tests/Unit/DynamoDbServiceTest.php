<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Services\DynamoDbService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use ReflectionClass;

class DynamoDbServiceTest extends TestCase
{
    protected $dynamoDbService;
    protected $mockClient;

    public function setUp(): void
    {
        parent::setUp();

        // Crear un mock del cliente DynamoDbClient
        $this->mockClient = Mockery::mock(DynamoDbClient::class);

        // Crear una instancia real del servicio
        $this->dynamoDbService = new DynamoDbService();

        // Usar Reflection para sobrescribir la propiedad protegida $client con el mock
        $reflection = new ReflectionClass($this->dynamoDbService);
        $clientProp = $reflection->getProperty('client');
        $clientProp->setAccessible(true);
        $clientProp->setValue($this->dynamoDbService, $this->mockClient);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_insert_success()
    {
        $data = [
            'user_id' => 'user_123',
            'tweet_id' => 'tweet_1',
            'content'  => 'Test tweet'
        ];

        // Simular que putItem se ejecuta correctamente
        $this->mockClient->shouldReceive('putItem')
            ->with(Mockery::on(function ($args) use ($data) {
                return $args['TableName'] === 'Tweets'
                    && isset($args['Item']['user_id'])
                    && $args['Item']['user_id']['S'] === $data['user_id'];
            }))
            ->once()
            ->andReturn(null);

        $result = $this->dynamoDbService->insert('Tweets', $data);
        $this->assertEquals($data, $result);
    }

    public function test_insert_failure()
    {
        // Simular error en putItem, pasando un comando dummy como segundo argumento
        $this->mockClient->shouldReceive('putItem')
        ->once()
            ->andThrow(new DynamoDbException('Error al insertar', new \Aws\Command(['TableName' => 'Tweets'])));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('❌ Error al insertar: Error al insertar');

        $this->dynamoDbService->insert('Tweets', ['user_id' => 'user_123', 'tweet_id' => 'tweet_1']);
    }

    public function test_query_success()
    {
        $table = 'Tweets';
        $conditions = ['user_id' => 'user_123'];

        // Items en formato nativo de DynamoDB
        $rawItems = [
            [
                'tweet_id' => ['S' => 'tweet_123'],
                'user_id'  => ['S' => 'user_123'],
                'content'  => ['S' => 'Test tweet']
            ]
        ];

        // Resultado esperado luego de unmarshal
        $expectedItems = [
            [
                'tweet_id' => 'tweet_123',
                'user_id'  => 'user_123',
                'content'  => 'Test tweet'
            ]
        ];

        $expectedParams = [
            'TableName' => $table,
            'KeyConditionExpression' => '#user_id = :user_id',
            'ExpressionAttributeValues' => [':user_id' => ['S' => 'user_123']],
            'ExpressionAttributeNames'  => ['#user_id' => 'user_id'],
            'ScanIndexForward'          => true
        ];

        $this->mockClient->shouldReceive('query')
            ->with(Mockery::on(function ($args) use ($expectedParams) {
                return $args['TableName'] === $expectedParams['TableName']
                    && $args['KeyConditionExpression'] === $expectedParams['KeyConditionExpression']
                    && $args['ExpressionAttributeValues'] == $expectedParams['ExpressionAttributeValues']
                    && $args['ExpressionAttributeNames'] == $expectedParams['ExpressionAttributeNames']
                    && $args['ScanIndexForward'] === $expectedParams['ScanIndexForward'];
            }))
            ->once()
            ->andReturn(['Items' => $rawItems]);

        $result = $this->dynamoDbService->query($table, $conditions);
        $this->assertEquals($expectedItems, $result);
    }

    public function test_query_failure()
    {
        $conditions = ['user_id' => 'user_123'];

        $this->mockClient->shouldReceive('query')
            ->with(Mockery::on(function ($args) use ($conditions) {
                return $args['TableName'] === 'Tweets'
                    && $args['KeyConditionExpression'] === '#user_id = :user_id';
            }))
            ->once()
            ->andThrow(new DynamoDbException("Query failed", new \Aws\Command(['TableName' => 'Tweets'])));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('❌ Error en DynamoDB: Query failed');
        $this->dynamoDbService->query('Tweets', $conditions);
    }
}
