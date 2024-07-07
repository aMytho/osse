# Osse Server

This is the server for the Osse music player. This is a work in progress so no download is provided at this time.

This project is free and open source under the AGPL.

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

Setup an environment variable to point to the location for the database. Make a blank `.db` file at this location.

.env
```
DATABASE_URL="/path/to/my/file.db"
```

(Be sure to create this file!)

Run migrations with the diesel CLI.

```
cargo install diesel_cli
diesel migration run
```

Run the project

```
cargo run
```