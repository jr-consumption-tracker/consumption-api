# Docker Commands

This document contains comprehensive Docker commands for managing the containers, networks, and volumes in this project.

## Lifecycle Commands

### Start containers

Starts existing containers without recreating them.

```bash
sudo docker compose start [service_name]
```

**Parameters:**

- `[service_name]`: Optional. Start only specific service (e.g., `app`, `db`, `nginx`).

### Stop containers

Stops running containers without removing them.

```bash
sudo docker compose stop [service_name]
```

**Parameters:**

- `[service_name]`: Optional. Stop only specific service.
- `-t, --timeout`: Specify a shutdown timeout in seconds (default: 10).

### Restart containers

Restarts running containers.

```bash
sudo docker compose restart [service_name]
```

**Options:**

- `-t, --timeout`: Specify a shutdown timeout in seconds (default: 10).

### Up (Create and start)

Builds, (re)creates, starts, and attaches to containers for a service.

```bash
sudo docker compose up
```

**Common Parameters:**

- `-d, --detach`: Detached mode: Run containers in the background.
- `--build`: Build images before starting containers.
- `--force-recreate`: Recreate containers even if their configuration and image haven't changed.
- `--no-recreate`: If containers already exist, don't recreate them.
- `--always-recreate-deps`: Recreate dependent containers.
- `--remove-orphans`: Remove containers for services not defined in the Compose file.

### Down (Stop and remove)

Stops containers and removes containers, networks, volumes, and images created by `up`.

```bash
sudo docker compose down
```

**Crucial Parameters:**

- `-v, --volumes`: Remove named volumes declared in the `volumes` section of the Compose file and anonymous volumes attached to containers. **(Smaže všechna data v databázi!)**
- `--rmi type`: Remove images. Type can be `all` (all images used by any service) or `local` (only images that don't have a custom tag).
- `--remove-orphans`: Remove containers for services not defined in the Compose file.

---

## Maintenance & Cleanup

### Remove Containers

Removes stopped service containers.

```bash
sudo docker compose rm [service_name]
```

**Parameters:**

- `-f, --force`: Don't ask to confirm removal.
- `-s, --stop`: Stop the containers, if required, before removing.
- `-v`: Remove any anonymous volumes attached to containers.

### Rebuild Images

Builds or rebuilds services.

```bash
sudo docker compose build [service_name]
```

**Parameters:**

- `--no-cache`: Do not use cache when building the image.
- `--pull`: Always attempt to pull a newer version of the image.

### Logs

Displays log output from services.

```bash
sudo docker compose logs [service_name]
```

**Parameters:**

- `-f, --follow`: Follow log output.
- `-t, --timestamps`: Show timestamps.
- `--tail`: Number of lines to show from the end of the logs (e.g., `--tail=100`).

### Interactive Shell

Execute a command in a running container.

```bash
sudo docker compose exec app bash
```

### System Cleanup

Remove unused data (containers, networks, images, and optionally volumes).

```bash
sudo docker system prune
```

**Parameters:**

- `-a, --all`: Remove all unused images, not just dangling ones.
- `--volumes`: Remove all unused volumes.

---

## Monitoring

### Process List

List containers.

```bash
sudo docker compose ps
```

### Top

Display the running processes.

```bash
sudo docker compose top
```
