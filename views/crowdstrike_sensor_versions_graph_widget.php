<div class="col-md-6">

    <div class="panel panel-default" id="crowdstrike-sensor-versions-graph-widget">

        <div class="panel-heading" data-container="body" data-i18n="[title]crowdstrike.sensor-versions-widget-tooltip">
            <h3 class="panel-title"><i class="fa fa-crosshairs"></i>
            <span data-i18n="crowdstrike.sensor-versions-graph-widget"></span>
            <list-link data-url="/show/listing/crowdstrike/crowdstrike"></list-link>
            </h3>

        </div>

    <div class="panel body">
        <svg style="width:100%"></svg>

    </div>

    </div>

</div>

<script>
$(document).on('appReady', function(e, lang) {

    <?php 
        $graph_margins = ['top' => 20, 'right' => 10, 'bottom' => 20, 'left' => 80];

        if(isset($margin) && is_array($margin)){
            $graph_margins = array_merge($graph_margins, $margin);
        }
        
        if( ! isset($margin) || ! is_string($margin)){
            $margin = json_encode($graph_margins);
        }
    ?>

    var conf = {
        url: appUrl + '/module/crowdstrike/get_crowdstrike_sensor_version_stats',
        widget: 'crowdstrike-sensor-versions-graph-widget',
        margin: <?=$margin?>,
        elementClickCallback: function(e){
            var label = mr.integerToVersion(e.data.label);
            window.location.href = appUrl + '/show/listing/crowdstrike/crowdstrike#' + label;
        },
        labelModifier: function(label){
            return mr.integerToVersion(label)
        }
    };

    mr.addGraph(conf);

});
</script>
