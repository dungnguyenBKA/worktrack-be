# Work track: Timesheet management system 

## Before make call to API

1. Add to .env file

```
CLIENT_ID=UeIB9mxDGDm3n2TLWr6G
CLIENT_SECRET=TWvQATu0vF9J58An3pDT
```


2. Add key to headers

```
client-id UeIB9mxDGDm3n2TLWr6G
client-secret TWvQATu0vF9J58An3pDT
device-token 6NciMdglEWeTJtm2lRDX
```

3. Run in docker:

<strong>Note:</strong> If your Host OS is MacOS, un-comment 

```
# platform: linux/x86_64 
```

in [docker-compose.yml](docker-compose.yml) file

Run docker compose:

```shell
docker-compose build
docker-compose up -d
```

4. Seeding and Migration

Run in cms-app container to migrate and seeding all inital data
```
php artisan migrate:refresh --seed
```
