# Osse

Osse is a free and open source music player and server. This repository is the **server**.

## Features

> Osse is in **early development**. There will be bugs and unexpected behavior. Some features are not yet complete. It is safe to use on your library, but it will need some time before it can be your main music player.

- Supports most music formats (MP3, Ogg/Opus, Flac, WAV).
- Support reading tags for library generation.
- Album & Playlist support.
- No Tracking/Telemetry/Data collection.
- Simplicity. Install it and it **just works**.
- Support for Linux/Mac/Windows (Mac/Windows need Docker or other medium). Any device (including Android and IOS) can use the web frontend.

## Installation 

> Interested in helping us test? Use the below instructions for an installation.

Both the server (this repo) and the web frontend must be installed.

> When v1 releases, we will provide a standalone installer/executable to simplify this process. We will also provide docker images. Currently, you must manually install the projects and their dependencies.

You will need the following tools installed:

- Git https://git-scm.com/downloads
- PHP 8.4 with the PCNTL extension `/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.4)"`
- NodeJS v22 https://nodejs.org/en
- PNPM (optional, preferred over NPM) https://pnpm.io/installation

> You may be able to run osse with older versions of the above software. However, the above configuration has been tested and proven to work.

Clone this repository and the web client.

```
git clone https://github.com/amytho/osse
git clone https://github.com/amytho/osse-web
```

Start the server and the web frontend.

```
cd osse
composer install
cp .env.example .env
composer run dev
```

In another terminal window:
```
cd osse-web
pnpm install
pnpm start
```

Open the web frontend and login. http://localhost:4200

The default username is `osse` and the default password is `cassidor`.

## Configuration

Configuration is stored in a .env file in the osse server repo. This file should have been created for you if you followed the above instructions.

You shouldn't need to change anything if you are accessing this project from your current device. However, you should confirm that the client URL variables are set. Do not add a ending slash to the host. The below URL variables are the default that the web frontend will use.

```
clientUseHttps="false" # or true
clientHostAndPort="localhost:4200" # no ending slash. Include port.
```

> If you want to access the project from another device, adjust the cookie, session, and sanctum fields. We will provide examples before release.

### Adding Music

You need to tell Osse where your music library is. You can provide a comma separated list of directories in the .env file for the `directories` entry. See below example.

`directories="/mnt/laptop-music/my-folder,/mnt/laptop-music/my-other-folder,/home/myuser/Music"`

**You must provide an absolute path for each directory**. Osse will scan subdirectories recursively. If you store your music in a top level music folder, simply point Osse to it and it will scan all of the files.

Click on the settings page on the web frontend. Press the scan button. This will scan your files. When the scan is complete a message will appear verifying that you can leave the page. Your files should be accessible now, happy listening!

## Providing Feedback

Osse is in an early stage. We need your feedback to help shape the future of the project. If you have a bug or feature request, please make an issue!

## Contributing and Support

The best way to support the project is to use it and provide feedback. Starring the project is also appreciated. 

Code contributions are welcome. We request that you open an issue before starting work on a feature.
