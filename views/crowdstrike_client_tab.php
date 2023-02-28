    <h2 data-i18n="crowdstrike.client_tab"></h2>

    <div id="crowdstrike-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

    <div id="crowdstrike-view" class="row hide">
        <div class="col-md-6">
            <table class="table table-striped">
                <tr>
                    <th data-i18n="crowdstrike.sensor_id"></th>
                    <td id="crowdstrike-sensor_id"></td>
                </tr>
                <tr>
                    <th data-i18n="crowdstrike.sensor_version"></th>
                    <td id="crowdstrike-sensor_version"></td>
                </tr>
                <tr>
                    <th data-i18n="crowdstrike.customer_id"></th>
                    <td id="crowdstrike-customer_id"></td>
                </tr>
<tr>
                    <th data-i18n="crowdstrike.sensor_active"></th>
                    <td id="crowdstrike-sensor_active"></td>
                </tr>
                <tr>
                    <th data-i18n="crowdstrike.sensor_installguard"></th>
                    <td id="crowdstrike-sensor_installguard"></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
        </div>
    </div>

<script>
$(document).on('appReady', function(e, lang) {

    // Get crowdstrike data
    $.getJSON( appUrl + '/module/crowdstrike/get_data/' + serialNumber, function( data ) {
            // Hide
            $('#crowdstrike-msg').text('');
            $('#crowdstrike-view').removeClass('hide');

            // Add strings
            $('#crowdstrike-sensor_id').text(data.sensor_id);
            $('#crowdstrike-sensor_version').text(data.sensor_version);
            $('#crowdstrike-customer_id').text(data.customer_id);
            $('#crowdstrike-sensor_active').text(data.sensor_active);

            if(data.sensor_active === "0" ) {
                $('#crowdstrike-sensor_active').text("No");
            } else if(data.sensor_active === "1" ) {
                $('#crowdstrike-sensor_active').text("Yes");
            } else{
                 $('#crowdstrike-sensor_active').text(data.sensor_active);
            } 

            $('#crowdstrike-sensor_installguard').text(data.sensor_installguard);

            if(data.sensor_installguard === "0" ) {
                $('#crowdstrike-sensor_installguard').text("Disabled");
            } else if(data.sensor_installguard === "1" ) {
                $('#crowdstrike-sensor_installguard').text("Enabled");
            } else{
                 $('#crowdstrike-sensor_installguard').text(data.sensor_installguard);
            } 
        });
});

</script>
