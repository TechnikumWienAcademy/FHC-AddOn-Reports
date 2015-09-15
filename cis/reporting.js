/*
 * Copyright (C) 2014 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Authors: Robert Hofer <robert.hofer@technikum-wien.at>
 */


function showSidebar(num, type)
{
	$('#sidebar').show();
	$('.reports_sidebar_entry').hide();
	$('.report_'+num+"_"+type).show();
	$('.hide-button').show();

	$('#sidebar').attr('data-menu', type);
	$('#content').parent().removeClass('col-sm-12').addClass('col-sm-9');

	$(window).trigger('resize');
}


$(function()
{
	// Charts auf volle groesse aendern
	$('#content').parent().removeClass('col-sm-9').addClass('col-sm-12');

	// Pivot auf volle groesse aendern
	$('.pvtRendererArea').css('width','100%');

	$('.hide-button').hide();

	$('.navbar-brand').on('click', function()
	{
		$('#sidebar div').hide();
		$('#content').hide();
		$('#iframe_content').hide();
		$('#filter').hide();
		$("#welcome").show();
	});



	$('#sidebar a').on('click', function()
	{
		var statistik_kurzbz = $(this).attr('data-statistik-kurzbez'),
		chart_id = $(this).attr('data-chart-id'),
		report_id = $(this).attr('data-report-id');

		if($(this).closest('li').hasClass('hide-button'))
		{
			// Sidebar ausblenden
			$('#sidebar').hide();

			// Charts auf volle groesse aendern
			$('#content').parent().removeClass('col-sm-9').addClass('col-sm-12');

			// Pivot auf volle groesse aendern
			$('.pvtRendererArea').css('width','100%');

			$(window).trigger('resize');
			return;
		}

		$('#welcome,#content').hide();
		$('#filter').show();
		$("#filter-input-PdfLink").hide();
		
		$('#filter-input').load('filter.php?type=data&statistik_kurzbz=' + statistik_kurzbz + '&report_id=' + report_id, function()
		{
			if(typeof report_id !== 'undefined')
			{
				$("#filter-input-PdfLink").attr("href", "../data/Report"+report_id+".pdf");
				$("#filter-input-PdfLink").show();
			}
			else if(!$.trim($('#filter-input').html()) && typeof report_id === 'undefined')
			{
				$('#run-filter').trigger('click');
				$('#filter').hide();
			}
		});

		$('#filter-input').attr(
		{
			'data-chart-id': chart_id,
			'data-statistik-kurzbz': statistik_kurzbz,
			'data-report-id': report_id
		});

	});

	$('#welcome button').on('click', function()
	{

		var link = $('ul.nav li.dropdown [href="#' + $(this).attr('data-dropdown') + '"]');

		$('.navbar-collapse').collapse('show');

		link.dropdown('toggle');
		return false;
	});

	$('#run-filter').on('click', function()
	{
		$('#filter').hide();

		var inputs = $('#filter-input > *'),
			chart_id = $('#filter-input').attr('data-chart-id'),
			data_statistik_kurzbz = $('#filter-input').attr('data-statistik-kurzbz'),
            report_id = $('#filter-input').attr('data-report-id'),
			get_params = {},
			url;

		for(var i = 0; i < inputs.length; i++)
		{

			var input = $(inputs[i]);

			get_params[input.attr('id')] = input.val();
		}

		if($('#sidebar').attr('data-menu') === 'charts')
		{
			url = 'chart.php';
			get_params.chart_id = chart_id;

		}
		else
		if($('#sidebar').attr('data-menu') === 'data')
		{
			url = 'grid.php';
			get_params.statistik_kurzbz = data_statistik_kurzbz;
		}

		if($('#sidebar').attr('data-menu') === 'reports')
		{

			var iframe = $(document.createElement('iframe'));

			iframe.attr
			({
				src: '../data/Report' + report_id + '.html'
			});

			iframe.css(
			{
				border: '0',
				width: '100%',
				height: '800px'
			});

			$('#content').html(iframe).show();

		}
		else
		{
			$('#spinner').show();

			$.ajax(
			{
				url: url,
				data: get_params,
				success: function(data)
				{
					$('#spinner').hide();
					charts = [];
					$('#content').html(data).show();
					initCharts();
				}
			});
		}

	});
});
