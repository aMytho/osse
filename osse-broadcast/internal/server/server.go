package server

import (
	"context"
	"errors"
	"fmt"
	"log"
	"net/http"
	"os"
	"osse-broadcast/internal/messages"
	"osse-broadcast/internal/redis"
	"time"

	"github.com/tmaxmax/go-sse"
)

// SSE connections
var Clients = make(chan messages.OsseEvent)

func Start(host string, allowOrigin string) {
	sseHandler := createSseSetup()

	mux := http.NewServeMux()
	// /sse is the only cors route.
	mux.Handle("/sse", sseHandler)
	mux.HandleFunc("/stream", createFilestreamSetup)

	httpServer := &http.Server{
		Addr:              host,
		Handler:           cors(mux, allowOrigin),
		ReadHeaderTimeout: time.Second * 10,
	}

	httpServer.RegisterOnShutdown(func() {
		e := &sse.Message{Type: sse.Type("close")}
		// Adding data is necessary because spec-compliant clients
		// do not dispatch events without data.
		e.AppendData("bye")
		// Broadcast a close message so clients can gracefully disconnect.
		_ = sseHandler.Publish(e)

		ctx, cancel := context.WithTimeout(context.Background(), time.Second*5)
		defer cancel()

		// We use a context with a timeout so the program doesn't wait indefinitely
		// for connections to terminate. There may be misbehaving connections
		// which may hang for an unknown timespan, so we just stop waiting on Shutdown
		// after a certain duration.
		_ = sseHandler.Shutdown(ctx)
	})

	// Listen for redis messages
	go func() {
		for event := range Clients {
			eventJson, err := messages.GetJsonOfEvent(event)
			if err != nil {
				continue
			}

			message := &sse.Message{}
			message.AppendData(eventJson)

			// Set the event name (client listens for this)
			eventName, err := sse.NewType(event.GetType())
			if err != nil {
				continue
			}
			message.Type = eventName

			sseHandler.Publish(message, messages.AllTopics...)
			log.Println("Sent Message to client")
		}
	}()

	log.Println("Osse Broadcast running on " + host)
	runServer(httpServer)
}

func createSseSetup() *sse.Server {
	return &sse.Server{
		Provider: &sse.Joe{},
		OnSession: func(s *sse.Session) (sse.Subscription, bool) {
			// Get the user ID and token
			userID := s.Req.URL.Query().Get("id")
			token := s.Req.URL.Query().Get("token")

			log.Println("User attempted to connect.")

			// Validate the userID and token
			if !validateUserToken(userID, token) {
				return sse.Subscription{}, false
			}

			return sse.Subscription{
				Client: s,
				Topics: messages.AllTopics,
			}, true
		},
	}
}

func createFilestreamSetup(w http.ResponseWriter, r *http.Request) {
	// Read the token and user id
	token := r.URL.Query().Get("token")
	trackID := r.URL.Query().Get("trackID")
	userID := r.URL.Query().Get("id")

	if userID == "" {
		http.Error(w, "bad request", http.StatusBadRequest)
		return
	}

	filePath, err := redis.GetValue("osse_database_file_access:" + userID + ":" + trackID + ":" + token)
	if err != nil {
		http.Error(w, "bad request", http.StatusBadRequest)
	}

	// Make sure the file path is absolute (don't serve relatie files, although that should be impossible with how we do this.)
	fmt.Println(filePath)
	if filePath == "" {
		http.Error(w, "invalid file path", http.StatusBadRequest)
		return
	}

	// Open the file
	f, err := os.Open(filePath)
	if err != nil {
		http.Error(w, "file not found", http.StatusNotFound)
		return
	}
	defer f.Close()

	// Get file info
	stat, err := f.Stat()
	if err != nil || stat.IsDir() {
		http.Error(w, "invalid file", http.StatusBadRequest)
		return
	}

	// Go literally handles everything I wrote in manual PHP related to HTTP range requests.
	// woooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo - amytho, 7:31 PM
	http.ServeContent(w, r, stat.Name(), stat.ModTime(), f)
}

func runServer(s *http.Server) error {
	shutdownError := make(chan error)

	if err := s.ListenAndServe(); err != nil && !errors.Is(err, http.ErrServerClosed) {
		return err
	}

	return <-shutdownError
}
