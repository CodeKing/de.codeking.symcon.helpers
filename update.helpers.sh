#!/bin/bash

DIR=$(cd `dirname $0` && pwd)

# colors
RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# loop folders
echo ""
find ../ -type d -iname "de.codeking.symcon.*" -print0 | while IFS= read -r -d $'\0' folder; do
    echo -e -n "${CYAN}updating${NC} ${BOLD}$(echo $folder | cut -d'/' -f 2)${NC}... "

    cd $folder
    git submodule update --remote --force --quiet; changes=$?

    if [ $changes -eq 1 ]; then
        echo -e "${GREEN}done!${NC}"
    else
        echo -e "${RED}up to date!${NC}"
    fi

    cd $DIR
done