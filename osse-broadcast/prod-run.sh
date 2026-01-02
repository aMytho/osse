#!/bin/bash

# Runs osse-broadcast in prod. Expects a valkey instance with a domain name of valkey.
# Running docker-compose in osse will do this.

# Wait for redis (valkey) to go online
until nc -z valkey 6379; do
  echo "Waiting for Valkey..."
  sleep 1
done

exec osse-broadcast
