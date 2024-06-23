# Osse Server

This is the server for the Osse music player. This is a work in progress so no download is provided at this time.

This project is free and open source under the AGPL.

Regenerate entities:
```
sea-orm-cli generate entity -o "src/entities" --with-serde "both"
```

Reset DB (deletes all data)
```
sea-orm-cli migrate refresh
```