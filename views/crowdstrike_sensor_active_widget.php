<div class="col-lg-4 col-md-6">

    <div id="sensor-active-widget" class="panel panel-default">

        <div class="panel-heading" data-container="body" data-i18n="[title]crowdstrike.sensor-active-widget-tooltip">

            <h3 class="panel-title"><i class="fa fa-shield"></i>
                <span data-i18n="crowdstrike.sensor-active-widget"></span>
                <list-link data-url="/show/listing/crowdstrike/crowdstrike"></list-link>
            </h3>

        </div>

        <div class="panel-body text-center">


            <a id="sa-disabled" class="btn btn-danger disabled">
                <span class="sa-count bigger-150"></span><br>
                <span data-i18n="no"></span>
            </a>
            <a id="sa-enabled" class="btn btn-success disabled">
                <span class="sa-count bigger-150"></span><br>
                <span data-i18n="yes"></span>
            </a>

            <span id="sa-nodata" data-i18n=""></span>

        </div>

    </div><!-- /panel -->

</div><!-- /col -->

<script>
$(document).on('appUpdate', function(e, lang) {

    $.getJSON( appUrl + '/module/crowdstrike/get_crowdstrike_sensor_active_stats', function( data ) {

        if(data.error){
            //alert(data.error);
            return;
        }

        var url = appUrl + '/show/listing/crowdstrike/crowdstrike#'
        var url_active = appUrl + '/show/listing/crowdstrike/crowdstrike#active'
        var url_inactive = appUrl + '/show/listing/crowdstrike/crowdstrike#inactive'

        // Set urls
        //$('#sa-disabled').attr('href', url + encodeURIComponent('sensor_sensor-active = 0'));
        //$('#sa-enabled').attr('href', url + encodeURIComponent('sensor_sensor-active = 1'));
        $('#sa-disabled').attr('href', url_inactive);
        $('#sa-enabled').attr('href', url_active);

        // Show no clients span
        $('#sa-nodata').removeClass('disabled');

        $.each(data.sensor_stats, function(prop, val){
            if(val > 0)
            {
                $('#sa-' + prop).removeClass('disabled');
                $('#sa-' + prop + '>span.sa-count').text(val);

                // Hide no clients span
                $('#sa-nodata').addClass('disabled');
            }
            else
            {
                $('#sa-' + prop).addClass('disabled');
            }
        });
    });
});

</script>
