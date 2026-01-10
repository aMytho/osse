FROM golang:1.24-alpine

WORKDIR /broadcast

# pre-copy/cache go.mod for pre-downloading dependencies and only redownloading them in subsequent builds if they change
COPY go.mod go.sum ./
RUN go mod download

COPY . .
RUN go build -o /usr/local/bin/osse-broadcast

ENTRYPOINT ["osse-broadcast"]
