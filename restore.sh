#!/usr/bin/env bash
docker exec -i $1 pg_restore -C --clean --no-acl - -no-owner -U $2 -d $3 < $4