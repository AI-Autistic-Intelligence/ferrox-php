#!/bin/bash
set -e

echo "🐘 Ferrox PHP: Running E2E Test Suite via Docker Compose..."

# Boot the environment in detached mode
docker-compose -f ops/docker/docker-compose.yml up -d --build

# Wait for the API Gateway to be ready
echo "Waiting for RoadRunner/Swoole to boot..."
sleep 5

# Run the tests inside the container or externally
# Setting the env variable so our E2E tests know the container is up
export E2E_DOCKER_RUNNING=1

echo "Executing PHPUnit..."
./vendor/bin/phpunit --testsuite E2E

echo "Tests passed! Tearing down..."
docker-compose -f ops/docker/docker-compose.yml down
