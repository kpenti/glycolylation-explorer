<? $mutabilities = file_get_contents('http://3fx.us/bio/gly/ajax/get_mutabilities.php?from=1968&to=2011');	?>
<script type="text/javascript">
	var mutabilities = jQuery.parseJSON('<?= $mutabilities ?>');
	var gly_area = null;
	$(function() {
		$('.check_all').bind('click', function() {
			$('.neighbour_residue').attr('checked', true); 
			return false;
		});			
		$('.uncheck_all').bind('click', function() {
			$('.neighbour_residue').attr('checked', false); 
			return false;
		});				
		$('#gly_form').bind('submit', function() {
			//$('#centroid').val($('#gly_site').val());
			select_nearby_residues();			
			return false;
		});			
		
		$('#centroid_form').bind('submit', function() {
			select_nearby_residues();			
			return false;
		});				
		
		$('#gly_site').bind('change', function() {
			$('#centroid').val($('#gly_site').val());
			return false;
		});		
		
		function select_nearby_residues() {
			var gly_site = $('#gly_site').val();
			var radius = $('#radius').val();
			var centroid = $('#centroid').val();
			
			jmolScript('center '+centroid+':a');
			jmolScript('isosurface s1 center ('+centroid+':a.ca) sphere '+radius+' mesh nofill')
			jmolScript('zoom 800');
			
			get_nearby_residues(centroid, radius);
			get_glycosylation_sites(gly_site);
		}

		
		function get_nearby_residues(residue, radius) {
			var loadUrl = "ajax/get_nearby_residues.php";
			$.ajax({
				url: loadUrl,
				data: {residue : residue, radius: radius},
				dataType: "json",
				success: function(response) {
					//var responseText = response.variability;
					var responseText = response.distances;
					generate_dropdown_fixations(responseText);
					$('#nearby_residues').html('');
					$.each(response.distances, function(index, value) {
						$('#nearby_residues').append($('#nearby_residue_tpl').jqote(value));
					});
					setTimeout("highlight_residues()",1500);
					$('.check_residue').unbind('mouseenter mouseleave hover');
					$('.check_residue').bind('hover', function() {
						$('.check_residue').removeClass('hover');
						$(this).addClass('hover');
						highlight_residue($(this).data('residue'));
						return false;
					});
					$('.check_residue').tooltip({placement:'right'});
					
					//hoverAll();
					//$("#busy").hide();
				}
			});
			get_fixations(residue);
		}
	
		$.fn.scrollView = function () {
			return this.each(function () {
				$('html, body').animate({
					scrollTop: $(this).offset().top
				}, 1000);
			});
		}

		function generate_dropdown_fixations(residues) {
			$('#fixations_dropdown').html('');
			$.each(residues, function(index, value) {
				$('#fixations_dropdown').append($('#fixations_dropdown_tpl').jqote(value));
			});
			
			$('#fixations_dropdown a').bind('click', function() {
				get_fixations($(this).data('residue'));
				get_correlation($(this).data('residue'));
				return false;
			});
			
			var gly_site = $('#gly_site').val();			
			$('#mini_graphs').html('');
			$.each(residues, function(index, value) {
				value.gly_site = gly_site;
				$('#mini_graphs').append($('#mini_graphs_tpl').jqote(value));
			});
			
			$('#mini_graphs a').bind('click', function() {
				get_fixations($(this).data('residue'));
				get_correlation($(this).data('residue'));
				$('#bio_graph').scrollView();

				return false;
			});
			$('.dropdown-toggle').dropdown();
			
		}		
		
		$('#select_nearby_residues').bind('click', function() {
			highlight_residues();
			return false;
		});
		
		$('#centroid').val($('#gly_site').val());
		select_nearby_residues();
		$('#radius').tooltip({placement:'bottom'});		
	});
	
	function jmolPickCallback(a,mess) {
		var msg;
		var s = "" + mess; // convert the 2nd parameter to a JavaScript string
		var start_pos = mess.indexOf(']') + 1;
		var end_pos = mess.indexOf(':',start_pos);
		var residue = parseInt(mess.substring(start_pos,end_pos));
		var radius = parseFloat($('#radius').val());
		jmolScript('isosurface s1 center ('+residue+':a.ca) sphere '+radius+' mesh nofill')
		$('#centroid').val(residue);		
	}
	
	function jmolHoverCallback(a,mess) {
		var msg;
		var s = "" + mess; // convert the 2nd parameter to a JavaScript string
		if (s.charAt(0) == 'C') {
			msg = "yes, that is a carbon.";
		} else {
			msg = "no, that is not a carbon. The carbons are grey.";
		}
		console.log(mess);
	}
	
	function get_glycosylation_sites(residue) {
		var loadUrl = "ajax/get_glycosylation_sites.php";
		$.ajax({
			url: loadUrl,
			data: {residue : residue},
			dataType: "json",
			success: function(responseText) {
				gly_area = responseText;
				}
		});
	}		

	function get_correlation(residue) {
		var gly_site = $('#gly_site').val();
		var loadUrl = "ajax/calculate_correlation.php";
		$.ajax({
			url: loadUrl,
			data: {residue : residue, gly_site : gly_site},
			dataType: "json",
			success: function(responseText) {
				console.log(responseText);
				$('#corr_'+residue).html(responseText.correlation.toFixed(2));
			}
		});
	}		
	
	function get_fixations(residue) {
		var loadUrl = "ajax/get_fixations.php";
		$.ajax({
			url: loadUrl,
			data: {residue : residue},
			dataType: "json",
			success: function(responseText) {
				plot_fixations(residue, responseText);
			}
		});
	}		
	
	function highlight_residues() {
		jmolScript('select *; color white;');
		//get fixations
		var gly_site = $('#gly_site').val();
		var fixations = $('.neighbour_residue:checked').serializeArray();
		$.each(fixations, function(index, value) {
			if($.inArray(value.value, mutabilities.fixations) != -1) {
				jmolScript('select '+value.value+':A; color blue;');
			} else if($.inArray(value.value, mutabilities.polymorphisms) != -1) {
				jmolScript('select '+value.value+':A; color red;');
			} else if($.inArray(value.value, mutabilities.intermediates) != -1) {
				jmolScript('select '+value.value+':A; color pink;');
			} else {
				jmolScript('select '+value.value+':A; color grey;');
			}

			//if() {
				//jmolScript('select '+value.value+':A; color crimson;');
			//}
			jmolScript('select '+gly_site+':A; color yellow;');
		});
		
		var gly_site = $('#gly_site').val();
		get_fixations(gly_site);
	}
	
	function highlight_residue(residue) {
		jmolScript('select '+residue+':A; set display selected;');
	}
	
	var chart;
	function plot_fixations(residue, fixations) {
		console.log(fixations);
		<?
		$yys = array();
		for($yy = 1968; $yy < 2012; $yy++ ) {
			$yys[] = $yy;
		}
		?>
		/**
 * Grid theme for Highcharts JS
 * @author Torstein Hønsi
 */

Highcharts.theme = {
   colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
   chart: {
      /*backgroundColor: {
         linearGradient: [0, 0, 500, 500],
         stops: [
            [0, 'rgb(255, 255, 255)'],
            [1, 'rgb(240, 240, 255)']
         ]
      },
      borderWidth: 0,
      /*plotBackgroundColor: 'rgba(255, 255, 255, .9)',
      plotShadow: true,*/
      plotBorderWidth: 1
   },
   title: {
      style: {
         color: '#000',
         font: 'bold 16px "Trebuchet MS", Verdana, sans-serif'
      }
   },
   subtitle: {
      style: {
         color: '#666666',
         font: 'bold 12px "Trebuchet MS", Verdana, sans-serif'
      }
   },
   xAxis: {
      /*gridLineWidth: 1,
      lineColor: '#000',
      tickColor: '#000',*/
      labels: {
         style: {
            color: '#000',
            font: '11px Trebuchet MS, Verdana, sans-serif'
         }
      },
      title: {
         style: {
            color: '#333',
            fontWeight: 'bold',
            fontSize: '12px',
            fontFamily: 'Trebuchet MS, Verdana, sans-serif'

         }
      }
   },
   yAxis: {
      /*minorTickInterval: 'auto',
      lineColor: '#000',
      lineWidth: 1,
      tickWidth: 1,
      tickColor: '#000',*/
      labels: {
         style: {
            color: '#000',
            font: '11px Trebuchet MS, Verdana, sans-serif'
         }
      },
      title: {
         style: {
            color: '#333',
            fontWeight: 'bold',
            fontSize: '12px',
            fontFamily: 'Trebuchet MS, Verdana, sans-serif'
         }
      }
   },
   legend: {
      itemStyle: {
         font: '9pt Trebuchet MS, Verdana, sans-serif',
         color: 'black'

      },
      itemHoverStyle: {
         color: '#039'
      },
      itemHiddenStyle: {
         color: 'gray'
      }
   },
   labels: {
      style: {
         color: '#99b'
      }
   }
};

// Apply the theme
var highchartsOptions = Highcharts.setOptions(Highcharts.theme);
		var categories = <?= json_encode($yys) ?>;
		var options = {
			chart: {
				renderTo: 'bio_graph',
				defaultSeriesType: 'line'
			},
			title: {
				text: 'Fixations for residue '+residue
			},
			xAxis: {
				categories: [<?= implode(',', $yys) ?>],
				tickInterval: 2
			},
			yAxis: {
				title: {
					text: 'Percentage of strains'
				},
				max :100,
				min :0
			},
			tooltip: {
				enabled: false,
				formatter: function() {
					return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ this.y +'°C';
				}
			},
			plotOptions: {
				series: {
					cursor: 'pointer',
					point: {
						events: {
							click: function() {
								hs.htmlExpand(null, {
									pageOrigin: {
										x: this.pageX,
										y: this.pageY
									},
									headingText: "Amino acid : " + this.series.name,
									maincontentText: 'Year: ' + categories[this.x] +' - '+
										this.y.toFixed(2) +'%',
									width: 200
								});
							}
						}
					},
					marker: {
						lineWidth: 1
					}
				}
			},

			series: []
		};
		
		var i = 0;
		var series = {};
		series.name = 'GLY';
		series.data = gly_area;
		series.type = 'area';
		series.color = '#FFA500';
		options.series.push(series);
		$.each(fixations['data_points'], function(index, value) { 
			var series = {};
			series.name = index;
			series.data = value;
			options.series.push(series);
			i = i + 1;
		});
		console.log(options.series);
		/*options.series[0].data = allVisits;
		options.series[1].data = newVisitors;

		chart = new Highcharts.Chart(options);/*/
		chart = new Highcharts.Chart(options);
		
	}
	
	
		
	</script>
	<? $gly_sites = array(8,22,38,285,165,81,63,126,122,246,276,133,144,45); ?>
      <!-- Example row of columns -->
      <div class="row">
        <div class="span6">
		
		
		<h4>Select a glycosylation site</h4>
		<form class="well form-inline" id="gly_form" action="" method="post">
			<label>Residue</label>
			<select class="span1" id="gly_site">
				<? foreach($gly_sites as $gly_site) : ?>
				<option value="<?= $gly_site ?>"><?= $gly_site ?></option>
				<? endforeach; ?>
			</select>
			<input type="text" class="span1" id="radius" title="Radius in angstroms" rel="tooltip" placeholder="radius" value="17.5">
			<button type="submit" id="submit_btn" class="btn btn-primary">Go</button>
		</form>		
		
		<h4>Nearby residues</h4>
		<div class="row" id="nearby_residues">
		</div>
		<div class="row">
			<div class="span3">
				<a href="#" class="uncheck_all">uncheck all</a> | <a href="#" class="check_all">check all</a>
			</div>			
			<div class="span3">
				<button type="submit" id="select_nearby_residues" class="btn btn-primary pull-right">View</button>
			</div>
		</div>
		<br />

        </div>
        <div class="span6">
			<div class="row">
				<div class="span6">
				 <div id="canvas" class="pull-right">
						<script>
							jmolApplet(500, "load 1HGD-A-1.pdb; select protein; cartoons; color white;wireframe 75; spacefill +0.1; set hoverCallback 'jmolHoverCallback';  set pickCallback 'jmolPickCallback'; ");
						</script>
					</div>
				</div>
				
				<div class="span6">
					<form class="form-inline pull-right" id="centroid_form" action="#" method="post">
					<label>Centroid</label>
					<input type="text" class="span1" id="centroid" title="Centroid" rel="tooltip" placeholder="radius" value="">
					<button type="submit" id="submit_btn" class="btn btn-primary">Go</button>
					</form>
				</div>
			</div>
			

			
       </div>        
	   <div class="span12">
			<div class="row" id="mini_graphs">

			</div>	   
		</div>	   
	   <div class="span12">
	   
			<?/*<h4>Fixations</h4>*/?>
	       <div class="btn-group">
				<a class="btn" href="#">Select a residue</a>
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				<span class="caret"></span>
				</a>
				<ul id="fixations_dropdown" class="dropdown-menu">
				</ul>
			</div><br />
		<div id="bio_graph"></div>
       </div>
      </div>
	  
	  
	<script type="text/html" id="mini_graphs_tpl">
	<![CDATA[
		<div class="span4"><a href="#"  style="width: 100%; display: block;background: #000; border: 1px solid #fff; height: 150px;" data-residue="<%= this.residue2 %>"><img src="/bio/gly/ajax/draw_fixation_charts.php?residue=<%= this.residue2 %>&gly=<%= this.gly_site %>" /></a></div>
	]]>
	</script>	
	
	<script type="text/html" id="nearby_residue_tpl">
	<![CDATA[
		<div class="span1 check_residue" title="<%= this.dist %> A" data-residue="<%= this.residue2 %>"><input type="checkbox" checked="checked" class="neighbour_residue" name="neighbour_residue" value="<%= this.residue2 %>" /> <%= this.residue2 %></div>
	]]>
	</script>	
	
	<script type="text/html" id="fixations_dropdown_tpl">
	<![CDATA[
		<li><a href="#" data-residue="<%= this.residue2 %>">Residue <%= this.residue2 %> <span id="corr_<%= this.residue2 %>"></span></a></li>
	]]>
	</script>
