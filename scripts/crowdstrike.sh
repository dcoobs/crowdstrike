#!/bin/sh

# Written by @dcoobs

# This is the clientside module for crowdstrike_status

CWD=$(dirname $0)
CACHEDIR="$CWD/cache/"
OUTPUT_FILE="${CACHEDIR}crowdstrike.plist"

# Skip manual check
if [ "$1" = 'manualcheck' ]; then
	echo 'Manual check: skipping'
	exit 0
fi

# Check if CrowdStrike is running before going further
sysctl cs > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo 'CS Falcon is not running on client. Skipping'
    exit 0
fi

# Create cache dir if it does not exist
mkdir -p "${CACHEDIR}"

# Gather standard CrowdStrike Falcon information and settings
cs_sensor_id=$(sysctl cs.sensorid | awk '{print $2}' | sed s/\-//g)
cs_customer_id=$(sysctl -n cs.customerid  | sed s/\-//g)
cs_sensor_version=$(sysctl -n cs.version)
cs_sensor_installguard=$(sysctl -n cs.control.installguard)

# Convert appropriate values to boolean
if [ $cs_sensor_installguard = "Enabled" ]; then
    cs_sensor_installguard=1
else
    cs_sensor_installguard=0
fi

# Output data into file
defaults write "$OUTPUT_FILE" sensor_id "$cs_sensor_id"
defaults write "$OUTPUT_FILE" sensor_version "$cs_sensor_version"
defaults write "$OUTPUT_FILE" customer_id "$cs_customer_id"
defaults write "$OUTPUT_FILE" sensor_installguard "$cs_sensor_installguard"

# Correct file permissions on resulting plist to allow proper upload
chmod 644 "$OUTPUT_FILE"
