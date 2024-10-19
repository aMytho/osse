# Osse Server

This is the server for the Osse music player. This is a work in progress so no download is provided at this time.

## Features
- Free as in (cost) and free as in (freedom) under the AGPL license
- Supports all major music formats (mp3/ogg/WAV/FLAC)
- Uses your local music library with no reliance on online third-party services
- Privacy respecting with no telemetry or tracking whatsoever
- Simple and minimal. Low resource usage
- Cross platform (Windows, Mac, and Linux)
- Built with Rust

## Installation (Development)

Clone the project into a directory of your choosing.

```
git clone https://github.com/amytho/osse
```

Create a `config.json` file at the root of the project, copying the format from the example json config file.

Install dependencies and build project.

```
cargo install
cargo build
```

Run the project. This will automatically apply any pending CLI migrations.

```
cargo run
```

