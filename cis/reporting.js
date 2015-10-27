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
 *					Andreas moik <moik@technikum-wien.at>
 */

function die(msg)
{
	document.body.innerHTML = msg;
	throw new Error(msg);
}

function loadChart(chart_id, statistik_kurzbz)
{
	showFilter(statistik_kurzbz, undefined, chart_id);
}

function loadReport(report_id)
{
	showFilter(undefined, report_id, undefined);
}

function loadStatistik(statistik_kurzbz)
{
	showFilter(statistik_kurzbz, undefined, undefined);
}


function loadData(statistik_kurzbz, report_id, chart_id, get_params)
{
	// generisch
	$('#filter').hide();

	var url = undefined;

	//charts
	if(chart_id !== undefined && chart_id !== undefined)
	{
		get_params.chart_id = chart_id;
		url = 'chart.php';
	}
	//pivots
	else if(statistik_kurzbz !== undefined)
	{
		get_params.statistik_kurzbz = statistik_kurzbz;
		url = 'grid.php';
	}
	//reports
	else if(report_id !== undefined)
	{
		get_params.report_id = report_id;
		url = '../vilesci/report_generate.php';
	}

	if(typeof url !== "undefined")
	{
		$('#spinner').show();
		$('#welcome').hide();

		$.ajax(
		{
			url: url,
			data: get_params,
      error: function (xhr, ajaxOptions, thrownError)
      {
				$('#spinner').hide();
        die("Fehler: " + xhr.status + " \"" + thrownError + "\"");
		  },
			success: function(data)
			{
					$('#spinner').hide();
					$('#filter').hide();
					$('#content').empty();
					$('#content').html('<iframe id="conentIframe" width="100%" height="100%" frameborder="0" id="content" style="overflow-x: scroll;"></iframe>');

					var all = '';
					all += '<html lang="en">';
					all += '<head>';
					all += '<meta charset="utf-8">';
					all += '<script type="text/javascript" src="../include/js/jquery-1.11.2.min.js"></script>';
					all += '<script type="text/javascript" src="../include/js/pivottable/pivot.js"></script>';
					all += '<script type="text/javascript" src="../include/js/pivottable/gchart_renderers.js"></script>';
					all += '<script src="../include/js/highcharts/highcharts-custom.js" type="application/javascript"></script>';
					all += '<link rel="stylesheet" type="text/css" href="../include/js/pivottable/pivot.css">';
					all += '<script src="../include/js/highcharts/main.js" type="application/javascript"></script>';
					all += '<link rel="stylesheet" href="../include/css/charts.css" type="text/css">';
					all += '<link rel="stylesheet" href="../include/css/jquery-ui.1.11.2.min.css" type="text/css">';
					all += '<script type="text/javascript" src="../include/js/jquery-ui.1.11.2.min.js"></script>';
					all += '<script type="text/javascript" src="../include/js/pivottable/jquery.ui.touch-punch.min.js"></script>';
					all += '<script src="../include/js/offcanvas.js"></script>';
					all += '</head>';
					all += '<body onload="javascript:resizeIframe(this);">';
					all += data;
					all += '</body>';

					var ifrm = document.getElementById('conentIframe');
					ifrm = (ifrm.contentWindow) ? ifrm.contentWindow : (ifrm.contentDocument.document) ? ifrm.contentDocument.document : ifrm.contentDocument;
					ifrm.document.open();
					ifrm.document.write(all);
					ifrm.document.close();
					$('#content').show();

					$(window).trigger('resize');

					// Pivot auf volle groesse aendern
					$('.pvtRendererArea').css('width','100%');
			}
		});
	}
	else
		alert("Es wurden keine korrekten Daten angegeben!")
}

function showFilter(statistik_kurzbz, report_id, chart_id)
{

	$('#spinner').hide();
	$('#welcome').hide();
	$('#content').hide();
	$('#filter').show();
	$("#filter-PdfLink").hide();
	$("#filter-debugLink").hide();

	$('#filter-input').load('filter.php?type=data&statistik_kurzbz=' + statistik_kurzbz + '&report_id=' + report_id, function()
	{
		if(typeof debug !== "undefined")
			$("#filter-debugLink").show();

		//pdf links gibt es nur bei reports
		if(typeof report_id !== 'undefined')
		{
			$("#filter-PdfLink").show();
		}
		//wenn keine filter existieren
		if(!$.trim($('#filter-input').html()) && report_id === undefined)
		{
			//laden wir direkt die daten
			loadData(statistik_kurzbz, report_id, chart_id,{});
		}
	});

	$('#filter-input').removeAttr('data-chart_id');
	$('#filter-input').removeAttr('data-statistik_kurzbz');
	$('#filter-input').removeAttr('data-report_id');

	$('#filter-input').attr(
	{
		'data-chart_id': chart_id,
		'data-statistik_kurzbz': statistik_kurzbz,
		'data-report_id': report_id
	});
}


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

    $(window).resize(function() {
        $('#content').height( $(window).height() - 100 );
    }).resize();
});



function hideSidebar()
{
	// Sidebar ausblenden
	$('#sidebar').hide();

	// Charts auf volle groesse aendern
	$('#content').parent().removeClass('col-sm-9').addClass('col-sm-12');

	// Pivot auf volle groesse aendern
	$('.pvtRendererArea').css('width','100%');

	$(window).trigger('resize');
}

function runFilter(type)
{
	$('#filter').hide();

	var inputs = $('#filter-input > *'),
		chart_id = $('#filter-input').attr('data-chart_id'),
		statistik_kurzbz = $('#filter-input').attr('data-statistik_kurzbz'),
		      report_id = $('#filter-input').attr('data-report_id'),
		get_params = {},
		url;

	get_params.type = type;

	for(var i = 0; i < inputs.length; i++)
	{
		var input = $(inputs[i]);
		if(input.attr("id") !== undefined)
			get_params[input.attr('id')] = input.val();
	}

	loadData(statistik_kurzbz, report_id, chart_id, get_params);
}

function resizeIframe(obj)
{
	obj.style.height = obj.contentWindow.document.body.scrollHeight + "px";
}
