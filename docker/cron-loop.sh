#!/bin/bash

echo "Updating dynamic content every 1 minute..."

while :
do
	php artisan dynamic:schedule
	sleep 60
done
