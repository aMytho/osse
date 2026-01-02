#!/bin/bash

# Dev script to run osse-broadcast with the envs. Change these in development ONLY. 
# If you are running this in production (as a user), read the instructions. You shouldn't be here :)

export OSSE_BROADCAST_URL="localhost:9003"
export OSSE_REDIS_HOST="localhost:6379"
export OSSE_ALLOWED_ORIGIN="localhost:4200"

go run .
