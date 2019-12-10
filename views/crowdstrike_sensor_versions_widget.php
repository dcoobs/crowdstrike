<div class="col-lg-4 col-md-6">
        <div class="panel panel-default" id="crowdstrike-sensor-versions-widget">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-crosshairs"></i>
                    <span data-i18n="crowdstrike.sensor-versions-widget"></span>
                    <list-link data-url="/show/listing/crowdstrike/crowdstrike"></list-link>
                </h3>
            </div>
        <div class="list-group scroll-box"></div>
        </div> <!--panel-->
</div><!--col-->

<script>
$(document).on('appUpdate', function(e, lang) {
	$.getJSON( appUrl + '/module/crowdstrike/get_crowdstrike_sensor_version_stats', function( data ) {
        var box = $('#crowdstrike-sensor-versions-widget div.scroll-box').empty();
		if(data.length){
			$.each(data, function(i,d){
				var badge = '<span class="badge pull-right">'+d.count+'</span>',
                    url = appUrl+'/show/listing/crowdstrike/crowdstrike/#'+d.label,
					display_name = d.label;
				box.append('<a href="'+url+'" class="list-group-item">'+display_name+badge+'</a>');
			});
		}
		else{
			box.append('<span class="list-group-item">'+i18n.t('no_clients')+'</span>');
		}
	});
});
</script>
