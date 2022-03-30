#!/bin/bash

# rename files

for file in *.log; do
    mv "$file" "${file%.log}.log-`date +"%d-%m-%Y"`"
done

