// Filters

var sensor_state = function(colNumber, d){
    // Look for 'Enabled' keyword
    if(d.search.value.match(/^inactive$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = '!= 1';
        // Clear global search
        d.search.value = '';
    }

    // Look for 'Disabled' keyword
    if(d.search.value.match(/^active$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = '= 1';
        // Clear global search
        d.search.value = '';
    }
}

var protect_state = function(colNumber, d){
    // Look for 'Enabled' keyword
    if(d.search.value.match(/^notprotected$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = '!= 1';
        // Clear global search
        d.search.value = '';
    }

    // Look for 'Disabled' keyword
    if(d.search.value.match(/^protected$/))
    {
        // Add column specific search
        d.columns[colNumber].search.value = '= 1';
        // Clear global search
        d.search.value = '';
    }
}