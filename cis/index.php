<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 * 			Robert Hofer <robert.hofer@technikum-wien.at>
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/statistik.class.php');
require_once('../include/chart.class.php');
require_once('../include/report.class.php');

$statistik = new statistik();
$chart = new chart();
$report = new report();

if(!$statistik->getAnzahlGruppe(true)
    || !$chart->getAnzahlGruppe(true)
    || !$report->getAnzahlGruppe(true))
{
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="icon" href="../../../favicon.ico">

		<title>Reports</title>

		<link rel="stylesheet" type="text/css" href="../../../submodules/bootstrap/dist/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="../include/css/offcanvas.css">
		<link rel="stylesheet" type="text/css" href="reporting.css">
		<link rel="stylesheet" type="text/css" href="../../../submodules/pivottable/dist/pivot.css">
		<link rel="stylesheet" href="../include/css/charts.css" type="text/css">
		<link rel="stylesheet" href="../include/css/jquery-ui.1.11.2.min.css" type="text/css">

		<script type="text/javascript" src="../include/js/jquery-1.11.2.min.js"></script>
        <script type="text/javascript" src="../include/js/jquery-ui.1.11.2.min.js"></script>
        
		<script type="text/javascript" src="../../../submodules/pivottable/dist/pivot.js"></script>
        <script type="text/javascript" src="../../../submodules/pivottable/dist/gchart_renderers.js"></script>
		
		<script src="../include/js/highcharts/highcharts-custom.js" type="application/javascript"></script>
		<script src="../include/js/highcharts/main.js" type="application/javascript"></script>
        <script type="text/javascript" src="../../../submodules/pivottable/examples/ext/jquery.ui.touch-punch.min.js"></script>

        <!-- <script type="text/javascript" src="../../../submodules/pivottable/examples/ext/jquery-1.8.3.min.js"></script> -->
        <!--<script type="text/javascript" src="../../../include/js/jquery-ui-1.11.4.custom.min.js"></script>-->
		<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
		<!--<script src="../include/js/ie10-viewport-bug-workaround.js"></script>-->

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
        <!-- <script type="text/javascript" src="../../../submodules/pivottable/examples/ext/jquery-ui-1.9.2.custom.min.js"></script> -->
        <!-- <script type="text/javascript" src="../../../submodules/pivottable/examples/ext/d3.v3.min.js"></script>-->
        <!--<script type="text/javascript" src="https://www.google.com/jsapi"></script>-->
        <!--<script type="text/javascript" src="../../../submodules/pivottable/dist/d3_renderers.js"></script>-->
        
        <!-- <script src="../include/js/spidergraph/jquery.spidergraph.js" type="application/javascript"></script> -->
		<!-- <link rel="stylesheet" href="../include/css/spider.css" type="text/css">
		<link rel="stylesheet" href="../include/css/xchart.css" type="text/css" /> -->
		
        
        <!-- ngGrid -->
		<?php //echo chart::getAllHtmlHead() ?>
		
	</head>

	<body>
		<div id="test">
			<div class="navbar navbar-fixed-top navbar-inverse" role="navigation">
				<div class="container">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
							<span class="sr-only">Navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="#">
                            <img src="../data/fhtw_logo_white.png" height="25" />
                            <span>Reporting</span>
                        </a>
					</div>
					<div class="collapse navbar-collapse">
						<ul class="nav navbar-nav">
							<li class="dropdown">
								<a href="#data" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">Data <span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu" data-name="data">
									<?php foreach($statistik->result AS $gruppen): ?>
										<li>
											<a href="#" data-gruppe="<?php echo str_replace(' ', '', $gruppen->gruppe); ?>">
												<?php echo $gruppen->gruppe ?>
												<span class="badge">
													<?php echo $gruppen->anzahl ?>
												</span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
							<li class="dropdown">
								<a href="#charts" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">Charts <span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu" data-name="charts">
									<?php foreach($chart->result AS $gruppen): ?>
										<li>
											<a href="#" data-gruppe="<?php echo str_replace(' ', '', $gruppen->gruppe); ?>">
												<?php echo $gruppen->gruppe ?>
												<span class="badge">
													<?php echo $gruppen->anzahl ?>
												</span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
							<li class="dropdown">
								<a href="#reports" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">Reports <span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu" data-name="reports">
                                    <?php foreach($report->result as $gruppen): ?>
                                        <li>
                                            <a href="#" data-gruppe="<?php echo str_replace(' ', '', $gruppen->gruppe) ?>">
                                                <?php echo $gruppen->gruppe ?>
                                                <span class="badge">
                                                    <?php echo $gruppen->anzahl ?>
                                                </span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div id="spinner" style="display:none; width:100%; top:50px; position:absolute; z-index:10;">
				<div class="progress">
	  				<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
						Loading Data
	  				</div>
				</div>
			</div>

			<div class="container-fluid">

				<div class="row row-offcanvas row-offcanvas-right">

					<div class="col-xs-12 col-sm-9">
						<p class="pull-right visible-xs">
							<button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
						</p>
						<div id="welcome">
							<div class="jumbotron">
								<h2>FH Technikum Wien</h2>
								<p>Sie befinden sich im Reporting-System der FH Technikum Wien. Das System ist in 3 Bereiche gegliedert.</p>
							</div>
							<div class="row">
								<div class="col-6 col-sm-6 col-lg-4">
									<h2>Data</h2>
									<p>Auswertungen werden in einer Tabelle mittels Zahlen gezeigt. Klassische Features wie Sortierten und Gruppieren sind hier möglich.</p>
									<p><button class="btn btn-default" type="button" data-dropdown="data">Jump &raquo;</button></p>
								</div>
								<div class="col-6 col-sm-6 col-lg-4">
									<h2>Charts</h2>
									<p>Um Daten schneller zu Überblicken sind Charts sehr behilflich. Diversen Ansichten wie Bar-Diagramme oder Spidergraphs sind hier in Verwendung.</p>
									<p><button class="btn btn-default" type="button" data-dropdown="charts">Jump &raquo;</button></p>
								</div>
								<div class="col-6 col-sm-6 col-lg-4">
									<h2>Reports</h2>
									<p>Speziell angefertigte Reports sind ein Kombination aus Daten, Charts und Texten. Teilweise sind diese auch in anderen Formaten wie zB. PDF verfügbar.</p>
									<p><button class="btn btn-default" type="button" data-dropdown="reports">Jump &raquo;</button></p>
								</div>
							</div>
						</div>

						<div style="display: none;" id="filter" class="col-xs-12 col-sm-9">
							<form class="form-inline" onsubmit="return false">
							<div id="filter-input" style="float: left"></div>
							<button id="run-filter" class="btn btn-default" type="submit">Run</button>
							</form>
						</div>

						<div style="display: none;" id="content">
						</div>

					</div>

					<div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar" role="navigation">
						<?php foreach($statistik->result AS $gruppen): ?>
							<div style="display: none;" id="datagroup_<?php echo str_replace(' ', '', $gruppen->gruppe) ?>" class="list-group">
								<ul class="nav">
									<?php
									$data = new statistik();
									$data->getGruppe($gruppen->gruppe, true);
									foreach($data->result AS $dat): ?>
										<li>
											<a href="#" data-statistik-kurzbez="<?php echo urlencode($dat->statistik_kurzbz) ?>" class="list-group-item">
												<?php echo $dat->bezeichnung ?>
											</a>
										</li>
									<?php endforeach; ?>
									<li class="hide-button">
										<a href="#">
											<span class="glyphicon glyphicon-chevron-up"></span>
										</a>
									</li>
								</ul>
							</div>
						<?php endforeach; ?>
						<?php foreach($chart->result AS $gruppen): ?>
							<div style="display: none;" id="chartsgroup_<?php echo str_replace(' ', '', $gruppen->gruppe) ?>" class="list-group">
								<ul class="nav">
									<?php
									$data = new chart();
									$data->getGruppe($gruppen->gruppe, true);
									foreach($data->result AS $dat): ?>
										<li>
											<a href="#" data-statistik-kurzbez="<?php echo urlencode($dat->statistik_kurzbz) ?>" class="list-group-item" data-chart-id="<?php echo $dat->chart_id ?>">
												<?php echo $dat->title ?>
											</a>
										</li>
									<?php endforeach; ?>
									<li class="hide-button">
										<a href="#">
											<span class="glyphicon glyphicon-chevron-up"></span>
										</a>
									</li>
								</ul>
							</div>
						<?php endforeach; ?>
						<?php foreach($report->result AS $gruppen): ?>
							<div style="display: none;" id="reportsgroup_<?php echo str_replace(' ', '', $gruppen->gruppe) ?>" class="list-group">
								<ul class="nav">
									<?php
									$data = new report();
									$data->getGruppe($gruppen->gruppe, true);
									foreach($data->result AS $dat): ?>
										<li>
                                            <span class="list-group-item">
                                                <a href="#" data-report-id="<?php echo $dat->report_id ?>">
                                                    <?php echo $dat->title ?>
                                                </a>
                                                <a href="../data/Report<?php echo $dat->report_id ?>.pdf" class="pull-right">
                                                    <img src="pdfIcon.png" width="20" alt="pdf"/>
                                                </a>
                                            </span>
										</li>
									<?php endforeach; ?>
									<li class="hide-button">
										<a href="#">
											<span class="glyphicon glyphicon-chevron-up"></span>
										</a>
									</li>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				</div>


				<hr>

				<footer>
					<p>&copy; FH Technikum Wien 2015</p>
				</footer>

			</div>
			<script src="../../../submodules/bootstrap/dist/js/bootstrap.min.js"></script>
			<script src="../include/js/offcanvas.js"></script>
			<script type="text/javascript" src="reporting.js"></script>
			<?php if(preg_match(',/var/www/hofer/,', __FILE__)): ?>
			<script type="text/javascript">
				$(function() {
					setTimeout(function() {
						$('ul[data-name="reports"] a[data-gruppe="Obst"]').trigger('click');
						$('#reportsgroup_Obst *[data-report-id="2"]').trigger('click');
//						setTimeout(function() {
//							$('#Studiensemester').val('WS2014');
//						}, 50);
//						setTimeout(function() {
//							$('#run-filter').trigger('click');
//						}, 100);
					}, 100);
				});
			</script>
			<?php endif; ?>
		</div>
	</body>
</html>
