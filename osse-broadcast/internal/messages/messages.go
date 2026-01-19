package messages

import (
	"encoding/json"
	"fmt"
	"log"
)

const (
	SCANSTARTED    string = "ScanStarted"
	SCANCOMPLETE   string = "ScanCompleted"
	SCANPROGRESSED string = "ScanProgressed"
	SCANERROR      string = "ScanError"  // Problem, scan not stopped.
	SCANFAILED     string = "ScanFailed" // Scan stopped.
	SCANCANCELLED  string = "ScanCancelled"
)

var AllTopics = []string{
	SCANSTARTED,
	SCANCOMPLETE,
	SCANPROGRESSED,
	SCANERROR,
	SCANFAILED,
	SCANCANCELLED,
}

// The raw message sent from redis.
type BaseEvent struct {
	Event string          `json:"event"`
	Data  json.RawMessage `json:"data"`
}

type OsseEvent interface {
	GetType() string
}

// The individual message types from Osse

type ScanStarted struct {
	Directories []ScanDirectory `json:"directories"`
}

func (s ScanStarted) GetType() string {
	return SCANSTARTED
}

type ScanDirectory struct {
	ID           uint    `json:"id"`
	ScanJobID    uint    `json:"scanJobID"`
	Path         string  `json:"path"`
	Status       string  `json:"status"`
	FilesScanned uint    `json:"filesScanned"`
	FilesSkipped uint    `json:"filesSkipped"`
	StartedAt    *string `json:"startedAt"`
	FinishedAt   *string `json:"finishedAt"`
}

type ScanProgressed struct {
	DirectoryID   uint   `json:"directoryID"`
	DirectoryName string `json:"directoryName"`
	FilesScanned  int    `json:"filesScanned"`
	FilesSkipped  int    `json:"filesSkipped"`
	Status        string `json:"status"`
}

func (s ScanProgressed) GetType() string {
	return SCANPROGRESSED
}

type ScanCompleted struct {
	DirectoryCount int `json:"directoryCount"`
}

func (s ScanCompleted) GetType() string {
	return SCANCOMPLETE
}

type ScanError struct {
	Message string `json:"message"`
}

func (s ScanError) GetType() string {
	return SCANERROR
}

type ScanFailed struct {
	Reason string `json:"message"`
}

func (s ScanFailed) GetType() string {
	return SCANFAILED
}

type ScanCancelled struct {
	DirectoriesScannedBeforeCancellation int `json:"directoriesScannedBeforeCancellation"`
}

func (s ScanCancelled) GetType() string {
	return SCANCANCELLED
}

func GetEventFromMessage(message string) (OsseEvent, error) {
	var base BaseEvent
	err := json.Unmarshal([]byte(message), &base)
	if err != nil {
		log.Println("Error parsing event: ", err)
		return nil, err
	}

	// Now that its valid json, we determine the event type
	switch base.Event {
	case "App\\Events\\ScanStarted":
		var data ScanStarted
		err = json.Unmarshal(base.Data, &data)
		if err == nil {
			return data, nil
		}

	case "App\\Events\\ScanProgressed":
		var data ScanProgressed
		err = json.Unmarshal(base.Data, &data)
		if err == nil {
			return data, nil
		}

	case "App\\Events\\ScanCompleted":
		var data ScanCompleted
		err = json.Unmarshal(base.Data, &data)
		if err == nil {
			return data, nil
		}

	case "App\\Events\\ScanError":
		var data ScanError
		err = json.Unmarshal(base.Data, &data)
		if err == nil {
			return data, nil
		}

	case "App\\Events\\ScanFailed":
		var data ScanFailed
		err = json.Unmarshal(base.Data, &data)
		if err == nil {
			return data, nil
		}

	case "App\\Events\\ScanCancelled":
		var data ScanCancelled
		err = json.Unmarshal(base.Data, &data)
		if err == nil {
			return data, nil
		}
	}

	return nil, fmt.Errorf("Unknown event: %s", base.Event)
}

func GetJsonOfEvent(event OsseEvent) (string, error) {
	jsonData, err := json.Marshal(event)
	if err != nil {
		log.Println("Error with converting osse event to json.")
		return "", err
	}

	return string(jsonData), nil
}
