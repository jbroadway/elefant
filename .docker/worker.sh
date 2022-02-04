#!/bin/sh

# Start all workers
./elefant start-workers

# Don't exit so Docker doesn't shut the container down
sleep infinity
