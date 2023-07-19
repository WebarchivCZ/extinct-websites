#!/bin/bash

group="whitelist"

source="https://webarchiv.cz/seeder/source/dump"
api="http://10.3.0.24/api/v2/action/addUrl/?db="


data=$(curl -k $source)

urls=$(echo $data | tr " " "\n")
for url in $urls
do
    curl -X POST -H "Content-Type: application/json" -d \
    '{"group":"'"$group"'", "url":"'"$url"'"}' "$api"; \
done









