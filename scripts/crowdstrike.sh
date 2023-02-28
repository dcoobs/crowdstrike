#!/bin/sh

# Written by @dcoobs

# This is the clientside module for crowdstrike_status

CWD=$(dirname $0)
CACHEDIR="$CWD/cache/"
OUTPUT_FILE="${CACHEDIR}crowdstrike.plist"
FALCONCTL="/Applications/Falcon.app/Contents/Resources/falconctl"

# Skip manual check
if [ "$1" = 'manualcheck' ]; then
	/bin/echo 'Manual check: skipping'
	exit 0
fi

# Create cache dir if it does not exist
/bin/mkdir -p "${CACHEDIR}"

if [ ! -f $FALCONCTL ]; then
    echo "CS Falcon not found. Skipping"
    defaults delete "$OUTPUT_FILE" > /dev/null 2>&1
    exit 0
fi

# Check if CrowdStrike is running before going further
$FALCONCTL stats agent_info > /dev/null 2>&1
if [ $? -ne 0 ]; then
    /bin/echo 'CS Falcon installed but not running on client'
    defaults delete "$OUTPUT_FILE" > /dev/null 2>&1
    defaults write "$OUTPUT_FILE" agent_info -dict-add sensor_operational "<string>0</string>"
    exit 0
fi

# Gather standard CrowdStrike Falcon information and settings
$FALCONCTL stats --plist agent_info > "$OUTPUT_FILE"

if $FALCONCTL stats | grep installGuard | awk '{print $2}' | grep "Enabled" > /dev/null 2>&1; then
    cs_sensor_installguard=1
else
    cs_sensor_installguard=0
fi

if $FALCONCTL stats agent_info | grep "Sensor operational: true" > /dev/null 2>&1; then
    sensor_operational=1
else
    sensor_operational=0
fi

# Append uninstall protection data into plist file
defaults write "$OUTPUT_FILE" agent_info -dict-add sensor_installguard "<string>$cs_sensor_installguard</string>"
# Append sensor active data into plist file
defaults write "$OUTPUT_FILE" agent_info -dict-add sensor_operational "<string>$sensor_operational</string>"

# Correct file permissions on resulting plist to allow proper upload
/bin/chmod 644 "$OUTPUT_FILE"
