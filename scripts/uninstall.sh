#!/bin/bash

MODULE_NAME="crowdstrike"
MODULESCRIPT="crowdstrike.sh"
MODULE_CACHE_FILE="crowdstrike.plist"

# Remove preflight script
rm -f "${MUNKIPATH}preflight.d/${MODULESCRIPT}"

# Remove cache file
rm -f "${CACHEPATH}${MODULE_CACHE_FILE}"
