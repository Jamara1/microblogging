<?php

namespace App\Services;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class DynamoDbService
{
    protected $client;
    protected $marshaler;

    public function __construct()
    {
        // 🔹 Crear cliente de la conexión DynamoDB
        $this->client = new DynamoDbClient([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID', 'local'),
                'secret' => env('AWS_SECRET_ACCESS_KEY', 'local'),
            ],
            'endpoint' => env('AWS_DYNAMODB_ENDPOINT', 'http://localhost:4566'),
        ]);

        $this->marshaler = new Marshaler();
    }

    public function insert($table, $data)
    {
        try {
            $item = $this->marshaler->marshalItem($data);
            $this->client->putItem([
                'TableName' => $table,
                'Item'      => $item
            ]);

            // 🔹 Retornar el dato insertado
            return $data;
        } catch (DynamoDbException $e) {
            throw new \Exception('❌ Error al insertar: ' . $e->getMessage(), 500);
        }
    }

    public function query(string $table, array $conditions, bool $descending = false)
    {
        try {
            // 🔹 Construcción dinámica de KeyConditionExpression
            $keyConditionParts = [];
            $expressionAttributeValues = [];
            $expressionAttributeNames = [];

            foreach ($conditions as $key => $value) {
                $attributeKey = "#$key";
                $valueKey = ":$key";

                $keyConditionParts[] = "$attributeKey = $valueKey";
                $expressionAttributeValues[$valueKey] = ['S' => $value];
                $expressionAttributeNames[$attributeKey] = $key;
            }

            $keyConditionExpression = implode(" AND ", $keyConditionParts);

            // 🔹 Realizar la consulta a DynamoDB
            $result = $this->client->query([
                'TableName'                 => $table,
                'KeyConditionExpression'    => $keyConditionExpression,
                'ExpressionAttributeValues' => $expressionAttributeValues,
                'ExpressionAttributeNames'  => $expressionAttributeNames,
                'ScanIndexForward'          => !$descending // 🔹 false = DESC, true = ASC
            ]);

            return array_map([$this->marshaler, 'unmarshalItem'], $result['Items']);
        } catch (\Aws\DynamoDb\Exception\DynamoDbException $e) {
            throw new \Exception('❌ Error en DynamoDB: ' . $e->getMessage(), 500);
        }
    }
}
