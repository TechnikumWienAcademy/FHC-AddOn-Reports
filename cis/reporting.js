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
	if(report_id !== undefined)
	{
		url = '../vilesci/report_generate.php';

		var getStr= "?report_id=" + report_id;

		for(var k in get_params)
			getStr += "&"+k+"="+get_params[k];


		window.open(url + getStr);
	}

	else if(typeof url !== "undefined")
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

				//.show und resize müssen dürfen nicht nach dem hineinschreiben
				//der daten in das div ausgeführt werden. Bei .show() stimmt die größe nicht
				//und bei resize korrumpiert der resize-Prozess die animation der Charts
				$('#content').show();
				$(window).trigger('resize');
				$('#content').html(data);
			}
		});
	}
	else
		alert("Es wurden keine korrekten Daten angegeben!")
}


function showFilter(statistik_kurzbz, report_id, chart_id)
{
	$(window).trigger('resize');

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

function resizeContent()
{
	if($("#sidebar").css("display") === "block")
	{
		$('#content').parent().removeClass('col-sm-12').addClass('col-sm-9');
	}
	else
	{
		$('#content').parent().removeClass('col-sm-9').addClass('col-sm-12');
	}

  $('#content').parent().height($(window).height() - 160);
  $('#content').height("100%");
  $('#content').width($('#content').parent().width());

  $('#pivot').width("1%");
  $('.pvtUi').width("1%");

  $('.pvtRendererArea').width("100%");
  $('.pvtRendererArea').css("overflow","auto");

  $('#content').css("overflow-y", "visible");
  $('#content').css("overflow-x", "auto");

}


function showSidebar(num, type)
{
	resizeContent();
	$('#sidebar').show();
	$('.reports_sidebar_entry').hide();
	$('.report_'+num+"_"+type).show();
	$('.hide-button').show();

	$('#sidebar').attr('data-menu', type);

	$(window).trigger('resize');
}


$(function()
{
	$('#sidebar').hide();

	resizeContent();

  $(window).resize(function() {
  resizeContent();
  }).resize();
});


function hideSidebar()
{
	resizeContent();
	// Sidebar ausblenden
	$('#sidebar').hide();

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
