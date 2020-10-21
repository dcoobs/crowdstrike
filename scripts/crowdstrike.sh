#!/bin/sh

# Written by @dcoobs

# This is the clientside module for crowdstrike_status

CWD=$(dirname $0)
CACHEDIR="$CWD/cache/"
OUTPUT_FILE="${CACHEDIR}crowdstrike.plist"
FALCONCTL="/Applications/Falcon.app/Contents/Resources/falconctl"

# Skip manual check
if [ "$1" = 'manualcheck' ]; then
	echo 'Manual check: skipping'
	exit 0
fi

# Check if CrowdStrike is running before going further
$FALCONCTL stats > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo 'CS Falcon is not running on client. Skipping'
    exit 0
fi

# Create cache dir if it does not exist
mkdir -p "${CACHEDIR}"

# Gather standard CrowdStrike Falcon information and settings
$FALCONCTL stats --plist agent_info > "$OUTPUT_FILE"

if [ -e "/Library/Application Support/CrowdStrike/Falcon/.falconinstallguard.bin" ]; then
    cs_sensor_installguard=1
else
    cs_sensor_installguard=0
fi

# Append uninstall protection data into plist file
defaults write "$OUTPUT_FILE" agent_info -dict-add sensor_installguard "<string>$cs_sensor_installguard</string>"

# Correct file permissions on resulting plist to allow proper upload
chmod 644 "$OUTPUT_FILE"
