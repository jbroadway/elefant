default: build

# Run `make build` to build a production container
build:
	cp .docker/Dockerfile-PROD Dockerfile
	docker build -t elefant .
	rm Dockerfile

# Run `make dev` to build a development container
dev:
	cp .docker/Dockerfile-DEV Dockerfile
	docker build -t elefant-dev .
	rm Dockerfile

# Run `make worker` to build a worker container
worker:
	cp .docker/Dockerfile-WORKER Dockerfile
	docker build -t elefant-worker .
	rm Dockerfile

# Run `make run` to spin up a development environment
run:
	docker-compose up -d

# Run `make down` to spin down a development environment
down:
	docker-compose down
# Run `make local-cert DOMAIN=www.elefant.lo` to generate an SSL certificate
# for your development environment. Note: Requires mkcert
DOMAIN=www.elefant.lo
export DOMAIN
local-cert:
	mkcert -cert-file .docker/certs/$(DOMAIN).crt \
		-key-file .docker/certs/$(DOMAIN).key \
		$(DOMAIN)
