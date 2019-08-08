<?php $this->view('partials/head'); ?>

<?php //Initialize models needed for the table
new Machine_model;
new Reportdata_model;
new crowdstrike_model;
?>

<div class="container">

  <div class="row">

  	<div class="col-lg-12">

		  <h3><span data-i18n="crowdstrike.listing.title"></span> <span id="total-count" class='label label-primary'>…</span></h3>
		  <table class="table table-striped table-condensed table-bordered">
		    <thead>
		      <tr>
		      	<th data-i18n="listing.computername" data-colname='machine.computer_name'></th>
		        <th data-i18n="serial" data-colname='reportdata.serial_number'></th>
		        <th data-i18n="crowdstrike.sensor_id" data-colname='crowdstrike.sensor_id'></th>
                        <th data-i18n="crowdstrike.sensor_version" data-colname='crowdstrike.sensor_version'></th>
                        <th data-i18n="crowdstrike.customer_id" data-colname='crowdstrike.customer_id'></th>
                        <th data-i18n="crowdstrike.sensor_installguard" data-colname='crowdstrike.sensor_installguard'></th>
		      </tr>
		    </thead>
		    <tbody>
		    	<tr>
					<td data-i18n="listing.loading" colspan="3" class="dataTables_empty"></td>
				</tr>
		    </tbody>
		  </table>
    </div> <!-- /span 12 -->
  </div> <!-- /row -->
</div>  <!-- /container -->

<script type="text/javascript">

	$(document).on('appUpdate', function(e){

		var oTable = $('.table').DataTable();
		oTable.ajax.reload();
		return;

	});

	$(document).on('appReady', function(e, lang) {
		// Get column names from data attribute
		var columnDefs = [],
            col = 0; // Column counter
		$('.table th').map(function(){
              columnDefs.push({name: $(this).data('colname'), targets: col});
              col++;
		});
	    oTable = $('.table').dataTable( {
	        columnDefs: columnDefs,
	        ajax: {
                url: appUrl + '/datatables/data',
                type: "POST"
            },
            dom: mr.dt.buttonDom,
            buttons: mr.dt.buttons,
	        createdRow: function( nRow, aData, iDataIndex ) {
	        	// Update name in first column to link
	        	var name=$('td:eq(0)', nRow).html();
	        	if(name == ''){name = "No Name"};
	        	var sn=$('td:eq(1)', nRow).html();
	        	var link = mr.getClientDetailLink(name, sn);
	        	$('td:eq(0)', nRow).html(link);


                        // Format uninstall protection status
                	var sp = $('td:eq(5)', nRow).html();
                	$('td:eq(5)', nRow).html(function(){
                    	    if( sp == '1'){
                                return '<span class="label label-success">'+i18n.t('Enabled')+'</span>';
                    	    } else if (sp == '0') {
                                return '<span class="label label-danger">'+i18n.t('Disabled')+'</span>';
                            }
                        });

                        // Format uninstall protection status
	        	//var status=$('td:eq(5)', nRow).html();
	        	//var cls = status == '0' ? 'danger' : (status == '1' ? 'success' : 'warning');
	        	//$('td:eq(5)', nRow).html('<span class="label label-'+cls+'">'+status+'</span>');
	        }
	    });
	});
</script> <?php $this->view('partials/foot'); ?>
