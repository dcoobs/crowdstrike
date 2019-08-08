<div class="col-lg-4 col-md-6">

    <div id="installguard-widget" class="panel panel-default">

        <div class="panel-heading">

            <h3 class="panel-title"><i class="fa fa-shield"></i>
                <span data-i18n="crowdstrike.installguard-widget"></span>
                <list-link data-url="/show/listing/crowdstrike/crowdstrike"></list-link>
            </h3>

        </div>

        <div class="panel-body text-center">


            <a id="sp-disabled" class="btn btn-danger disabled">
                <span class="sp-count bigger-150"></span><br>
                <span data-i18n="crowdstrike.disabled"></span>
            </a>
            <a id="sp-enabled" class="btn btn-success disabled">
                <span class="sp-count bigger-150"></span><br>
                <span data-i18n="crowdstrike.enabled"></span>
            </a>

            <span id="sp-nodata" data-i18n=""></span>

        </div>

    </div><!-- /panel -->

</div><!-- /col -->

<script>
$(document).on('appUpdate', function(e, lang) {

    $.getJSON( appUrl + '/module/crowdstrike/get_crowdstrike_installguard_stats', function( data ) {

        if(data.error){
            //alert(data.error);
            return;
        }

        var url = appUrl + '/show/listing/crowdstrike/crowdstrike#'

        // Set urls
        $('#sp-disabled').attr('href', url + encodeURIComponent('sensor_installguard = 0'));
        $('#sp-enabled').attr('href', url + encodeURIComponent('sensor_installguard = 1'));

        // Show no clients span
        $('#sp-nodata').removeClass('disabled');

        $.each(data.stats, function(prop, val){
            if(val > 0)
            {
                $('#sp-' + prop).removeClass('disabled');
                $('#sp-' + prop + '>span.sp-count').text(val);

                // Hide no clients span
                $('#sp-nodata').addClass('disabled');
            }
            else
            {
                $('#sp-' + prop).addClass('disabled');
            }
        });
    });
});

</script>
