#!/usr/bin/env bash
scp root@boulderdb.de:~/dumps/_latest.dump .
cat _latest.dump | docker-compose exec -T postgres psql -U blocbeta
rm _latest.dump