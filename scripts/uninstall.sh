#!/bin/bash

# Remove crowdstrike script
rm -f "${MUNKIPATH}preflight.d/crowdstrike.py"

# Remove legacy crowdstrike.sh if it exists
rm -f "${MUNKIPATH}preflight.d/crowdstrike.sh"

# Remove crowdstrike.plist file
rm -f "${CACHEPATH}crowdstrike.plist"
