#!/bin/sh
echo "Inicializando tablas de DynamoDB en LocalStack..."

# Crear tabla Tweets
awslocal dynamodb create-table \
  --table-name Tweets \
  --attribute-definitions \
      AttributeName=user_id,AttributeType=S \
      AttributeName=tweet_id,AttributeType=S \
  --key-schema \
      AttributeName=user_id,KeyType=HASH \
      AttributeName=tweet_id,KeyType=RANGE \
  --billing-mode PAY_PER_REQUEST

# Crear tabla Follow
awslocal dynamodb create-table \
  --table-name Follow \
  --attribute-definitions \
      AttributeName=followed_id,AttributeType=S \
      AttributeName=follower_id,AttributeType=S \
  --key-schema \
      AttributeName=followed_id,KeyType=HASH \
      AttributeName=follower_id,KeyType=RANGE \
  --billing-mode PAY_PER_REQUEST

# Crear tabla Timeline
awslocal dynamodb create-table \
  --table-name Timeline \
  --attribute-definitions \
      AttributeName=user_id,AttributeType=S \
      AttributeName=tweet_timestamp,AttributeType=N \
  --key-schema \
      AttributeName=user_id,KeyType=HASH \
      AttributeName=tweet_timestamp,KeyType=RANGE \
  --billing-mode PAY_PER_REQUEST

echo "¡Tablas de DynamoDB creadas correctamente!"
