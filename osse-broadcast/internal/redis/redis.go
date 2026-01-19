package redis

import (
	"context"
	"log"
	"osse-broadcast/internal/messages"
	"time"

	"github.com/redis/go-redis/v9"
)

var rdb *redis.Client

func Connect(host string, channel chan messages.OsseEvent) {
	rdb = redis.NewClient(&redis.Options{
		Addr: host,
	})

	log.Println("Connected to redis on " + host)

	// Start Redis pub/sub listener
	go listenRedis(channel)
}

func listenRedis(channel chan messages.OsseEvent) {
	ctx := context.Background()
	pubsub := rdb.Subscribe(ctx, "osse_database_scan")

	for msg := range pubsub.Channel() {
		log.Println("Received message:", msg.Payload)

		// Parse the message into a Message type
		event, err := messages.GetEventFromMessage(msg.Payload)
		if err != nil {
			log.Println("Received message from Osse that osse-broadcast cannot parse...")
			continue
		}

		// Broadcast to connected SSE clients
		channel <- event
	}
}

// Gets a redis value from a key. Returns the message and an error
func GetValue(key string) (string, error) {
	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()

	return rdb.Get(ctx, key).Result()
}
