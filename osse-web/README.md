# Osse-Web

Osse is a free and open source music player and server. This repository is the web frontend.

## Features

> Osse is in early development. There will be bugs and unexpected behavior. Some features are not yet complete. It is safe to use on your library, but it will need some time before it can be your main music player.

- Supports most music formats (MP3, Ogg/Opus, Flac, WAV).
- Support reading tags for library generation.
- Album & Playlist support.
- No Tracking/Telemetry/Data collection.
- Simplicity. Install it and it will just work.
- Support for Linux/Mac/Windows (Mac/Windows need Docker or other medium). Any OS (including Android/IOS) can use the web frontend.

## Installation

Both the server and the web frontend (this project) must be installed.

> When v1 releases, we will provide a standalone installer/executable to simplify this process. We will also provide docker images. Currently, you must manually install the projects and their dependencies.

You will need the following tools installed:

- Git https://git-scm.com/downloads
- PHP 8.4 `/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"`
- NodeJS v22 https://nodejs.org/en
- PNPM (optional, preferred over NPM) https://pnpm.io/installation

Clone this repository and the server.

```
git clone https://github.com/amytho/osse
cd
git clone https://github.com/amytho/osse-web
```

Start the web frontend and the php backend.

```
cd osse
composer run dev
```

In another terminal window:
```
cd osse-web
pnpm start
```

Open the web frontend and login. http://localhost:4200

The default username is `osse` and the default password is `cassidor`.

You should edit the .env file in the server and add your music directory to it. This is located at the bottom of the file.
