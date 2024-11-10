#!/bin/bash

mkdir -p IntegrationTests/log/Integration
chmod a+w IntegrationTests/log/Integration

# Start Docker Compose
docker compose -f docker-compose-integration.yml up --build --abort-on-container-exit

CONTAINER_ID=$(docker ps -a -q --filter name=tests-php)
if [ -z "$CONTAINER_ID" ]; then
    echo "Error: tests-php container not found."
    docker compose -f docker-compose-integration.yml down
    exit 1
fi

# Capture the exit code of the tests-php container
EXIT_CODE=$(docker inspect -f '{{.State.ExitCode}}' "$CONTAINER_ID")
STATUS_CODE=$(docker inspect -f '{{.State.Status}}' "$CONTAINER_ID")

# Tear down the containers and log the tests output
docker compose -f docker-compose-integration.yml logs tests-php
docker compose -f docker-compose-integration.yml down

# Check if EXIT_CODE or STATUS_CODE are empty
if [ -z "$EXIT_CODE" ] && [ -z "$STATUS_CODE" ]; then
    echo "Error: Unable to retrieve exit code OR status code."
    exit 1
fi

# Check the exit code and report the result
if [ "$EXIT_CODE" -eq 0 ] && [ "$STATUS_CODE" = "exited" ]; then
    echo "Tests passed!"
else
    echo "Tests failed!"
fi

exit "$EXIT_CODE"