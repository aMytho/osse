package main

import (
	"osse-broadcast/internal/config"
	"osse-broadcast/internal/redis"
	"osse-broadcast/internal/server"
)

func main() {
	// Get config
	config := config.GetOsseConfig()

	// Connect to Redis/Valkey
	redis.Connect(config.RedisHost, server.Clients)

	// Start HTTP server with SSE route
	server.Start(config.HttpHost, config.OsseClientOrigin)
}
