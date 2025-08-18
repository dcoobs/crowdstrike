#!/bin/bash

# crowdstrike controller
CTL="${BASEURL}index.php?/module/crowdstrike/"

# Remove legacy crowdstrike.sh if it exists
rm -f "${MUNKIPATH}preflight.d/crowdstrike.sh"

# Get the scripts in the proper directories
"${CURL[@]}" "${CTL}get_script/crowdstrike.py" -o "${MUNKIPATH}preflight.d/crowdstrike.py"

# Check exit status of curl
if [ $? = 0 ]; then
	# Make executable
	chmod a+x "${MUNKIPATH}preflight.d/crowdstrike.py"

	# Set preference to include this file in the preflight check
	setreportpref "crowdstrike" "${CACHEPATH}crowdstrike.plist"

else
	echo "Failed to download all required components!"
	rm -f "${MUNKIPATH}preflight.d/crowdstrike.py"

	# Signal that we had an error
	ERR=1
fi
