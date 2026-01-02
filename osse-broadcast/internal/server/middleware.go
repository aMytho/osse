package server

import (
	"net/http"
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

func cors(h http.Handler, allowedOrigin string) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		// If the origin matches the OSSE_HOST env var, we can allow the request.
		origin := r.Header.Get("origin")
		if origin == "http://"+allowedOrigin || origin == "https://"+allowedOrigin {
			w.Header().Set("Access-Control-Allow-Origin", origin)
			h.ServeHTTP(w, r)
		} else {
			w.WriteHeader(http.StatusForbidden)
			w.Write([]byte("CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource."))
		}
	})
}
