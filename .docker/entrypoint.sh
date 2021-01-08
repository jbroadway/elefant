#!/bin/sh

echo "Creating Elefant schema via ./elefant install..."
./elefant install --no-installed-error

/usr/local/bin/apache2-foreground
