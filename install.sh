#!/bin/bash

if [[ "$OSTYPE" == "linux-gnu" ]]; then
        chmod +x ./scripts/ubuntu.sh
        ./scripts/ubuntu.sh
elif [[ "$OSTYPE" == "darwin"* ]]; then
        chmod +x ./scripts/macosx.sh
        ./scripts/macosx.sh
else
        echo "Unhandled OS type"
fi
