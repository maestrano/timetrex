# Developers notes

## Setup a local development environment
To perform development tasks on this application, the easiest way is to start a container and map the application source code onto local volume

```bash
# Ignore files permissions changes
git config core.fileMode false

# Pull the latest version of the container
docker pull maestrano/timetrex:latest

# Start the container with local configuration
docker run -it \
  -e "MNO_SSO_ENABLED=true" \
  -e "MNO_CONNEC_ENABLED=true" \
  -e "MNO_MAESTRANO_ENVIRONMENT=local" \
  -e "MNO_SERVER_HOSTNAME=timetrex.app.dev.maestrano.io" \
  -e "MNO_API_KEY=3cb17e9e3c4df9fc02a673bc1bfe747ed877ec9818240ee92d8e5d911fb652a7" \
  -e "MNO_API_SECRET=844480c1-ac98-463e-a079-165564d1f51e" \
  -e "MNO_APPLICATION_VERSION=mno-develop" \
  -e "MNO_POWER_UNITS=2" \
  --add-host application.maestrano.io:172.17.0.1 \
  --add-host connec.maestrano.io:172.17.0.1 \
  --name timetrex-dev \
  -v ~/Workspace/timetrex:/var/lib/timetrex/webapp \
  maestrano/timetrex:latest
```

Add your container to the host entries `/etc/hosts`
```
172.17.0.2 timetrex.app.dev.maestrano.io
```

Source code modification in `~/Workspace/timetrex` are then applied to the running container
Verify that the application is accessible at `http://timetrex.app.dev.maestrano.io`