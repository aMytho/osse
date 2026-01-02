package server

import (
	"osse-broadcast/internal/redis"
)

func validateUserToken(userID string, token string) bool {
	// Check that the user is permitted to access osse-broadcast
	userToken, err := redis.GetValue("osse_database_sse_access:" + userID)
	if err != nil {
		return false
	}

	return userToken == token
}

