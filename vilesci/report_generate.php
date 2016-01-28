<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Moik 	< moik@technikum-wien.at >
 */
	require_once('../../../config/vilesci.config.inc.php');
	require_once('../../../include/globals.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/benutzerberechtigung.class.php');
	require_once('../include/rp_report.class.php');
	require_once('../include/rp_chart.class.php');
	require_once('../../../include/process.class.php');
	require_once('../../../include/filter.class.php');

	$iconsdir='/etc/asciidoc/images/icons';
	$chartTmpDir = sys_get_temp_dir() . "/charts_" . uniqid();


	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	// ************** Rechte Pruefen ********************
	$user = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);



	$htmlstr = '';
	// *************** Pruefen ob die benoetigten Programme vorhanden sind *******************

	if(!`which asciidoc`)
	{
		if($type == "debug")
			die('asciidoc ist auf diesem System nicht installiert');
		else
			die("Der Report konnte nicht erstellt werden!");
	}
	if(!`which dblatex`)
	{
		if($type == "debug")
			die('dbLatex ist auf diesem System nicht installiert');
		else
			die("Der Report konnte nicht erstellt werden!");
	}

	// *************** Asciidoc Version Prüfen *******************
	$asciidocHtmlVersion = "html5";		//standard

	exec('asciidoc --version'.' 2>&1', $out, $ret);
	$asciiVer = str_replace("asciidoc ","",$out);
	if(!version_compare ( "8.6.4" , $asciiVer[0], "<" ))
	{
		$htmlstr .= "<br><br><span style='color:red;'>Achtung: Diese Asciidoc Version unterstützt nur html4!</span>";
		$asciidocHtmlVersion = "html4";
	}

	// *************** Parameter pruefen und Daten laden *******************


	if(!isset($_REQUEST['type']))
		$type = "html";
	else
		$type = $_REQUEST['type'];

	$filter = new filter();
	$filter->loadAll();

	$report = new report();
	if(isset($_REQUEST['report_id']))
		$report->load((int)$_REQUEST['report_id']);
	else
		die('Es wurde keine report_id angegeben');
	$charts = new chart();
	if (!$charts->loadCharts($report->report_id))
	{
		if($type == "debug")
			die($charts->errormsg);
		else
			die("Der Report konnte nicht erstellt werden!");
	}

	//wenn der nutzer nicht für addon/reports berechtigt ist, wird abgefragt, ob der report oeffentlich ist
	//und ob der nutzer das recht für diesen report hat
	if(!$rechte->isBerechtigt("addon/reports"))
	{
		if($report->publish !== true)
			die("Dieser Report ist nicht Oeffentlich!");

		if(isset($report->berechtigung_kurzbz))
			if(!$rechte->isBerechtigt($report->berechtigung_kurzbz))
				die('Sie haben keine Berechtigung fuer diesen Report!');
	}

	//create the folder in temp, which will be removed afterwards
	if (!file_exists($chartTmpDir))
		mkdir($chartTmpDir, 0777, true);

	// ***** Define Filenames ******************
	$tmpFilename=$chartTmpDir.'Report'.$report->report_id.date('Y-m-d_H:i:s').'.tmp';
	$filename=$chartTmpDir.'Report'.$report->report_id;
	$docinfoFilename=$chartTmpDir.'Report'.$report->report_id.'-docinfo.xml';
	$htmlFilename=$chartTmpDir.'Report'.$report->report_id.'.html';
	$xmlFilename=$chartTmpDir.'Report'.$report->report_id.'.xml';
	$pdfFilename=$chartTmpDir.'Report'.$report->report_id.'.pdf';


	foreach($charts->chart as $chart)
	{
		if(isset($chart->statistik_kurzbz))
		{
			$chart->statistik = new statistik($chart->statistik_kurzbz);
			if (!$chart->statistik->loadData())
				cleanUpAndDie('Data not loaded!'.$chart->statistik->errormsg, $chartTmpDir);

			$vars = $chart->statistik->parseVars($chart->statistik->sql);

			$datafile=$chartTmpDir.'data'.$chart->statistik->statistik_kurzbz.'.csv';
			if (!$chart->statistik->writeCSV($datafile,',','"'))
			{
				if($type == "debug")
					cleanUpAndDie('File '.$datafile.' not written!'.$chart->statistik->errormsg, $chartTmpDir);
				else
					cleanUpAndDie("Der Report konnte nicht erstellt werden!", $chartTmpDir);
			}
			else
				$htmlstr.= '<br><br>File '.$datafile.' written!';


			$outputfilename=$chart->writePNG($chartTmpDir);

			if (!$outputfilename)
			{
				if($type == "debug")
					cleanUpAndDie($chart->errormsg, $chartTmpDir);
				else
					cleanUpAndDie("Der Report konnte nicht erstellt werden!", $chartTmpDir);
			}
			else
			{
				$htmlstr .= '<br><br>'.$outputfilename.' has been written!';
			}
		}

		if(isset($chart->description))
		{
			$mdfile=$chartTmpDir.'Chart'.$chart->chart_id.'.md';
			file_put_contents($mdfile, $chart->description);
		}

	}
	// @todo weitere parameter pruefen

	// *************** Startwerte Setzen ************************
	$crlf=PHP_EOL;
	$content = '';
	$errorstr = ''; //fehler beim insert

	switch ($report->format)
	{
		case 'asciidoc':
			$filename.='.asciidoc';
			$content.='= Report - '.$report->title.$crlf;
			$content.=$report->header.$crlf.$report->printParam('attr',$crlf).":chartDir: ".$chartTmpDir.$crlf;
			$content.=$crlf.'== Beschreibung'.$crlf.$report->description.$crlf;
			$content.=$crlf.'=== Parameter'.$crlf.'- Erstellung: *'.date("D, j M Y").'*'.$crlf.'- Datenstand: *'.date(DATE_RFC2822).'*'.$crlf.$report->printParam('param',$crlf).$crlf;
			$content.=$crlf.'<<<'.$crlf.$crlf.'== Report'.$crlf.$report->body.$crlf;
			$content.=$crlf.'== Hinweise'.$crlf.$report->footer.$crlf;
			break;
	}

	// **** Write DocInfo
	$fh=fopen($docinfoFilename,'w');
	fwrite($fh,$report->docinfo);
	fclose($fh);
	$htmlstr.= '<br><br>'.$docinfoFilename.' is written!';

	// ***** Write ContentFile
	$fh=fopen($filename,'w');
	fwrite($fh,$content);
	fclose($fh);
	$htmlstr.='<br><br>'.$filename.' is written!';

	// ****** Create Destination Files

	$cmd = 'asciidoc -o '.$htmlFilename.' -b '.$asciidocHtmlVersion.' -a theme=flask -a data-uri -a toc2 -a pygments -a icons -a iconsdir='.$iconsdir.' -a asciimath '.$filename;
	$htmlstr.=exec($cmd.' 2>&1', $out, $ret);
	$htmlstr.= '<br><br>'.$cmd;
	if($ret != 0)
	{
		$htmlstr.= '<br><br>Asciidoc fehlgeschlagen:<br>';
		foreach($out as $o)
			$htmlstr.= $o;
		if($type != "debug")
			cleanUpAndDie("Der Report konnte nicht erstellt werden!", $chartTmpDir);
	}
	if(count($out) > 0)
	{
		$htmlstr.= '<br><br>Asciidoc Warnungen:<br>';
		foreach($out as $o)
			$htmlstr.= $o;
	}
	$htmlstr.='<br><br>'.$htmlFilename.' is written!';



	$cmd = 'asciidoc -a docinfo -b docbook -o '.$xmlFilename.' '.$filename;
	$htmlstr.=exec($cmd.' 2>&1', $out, $ret);
	$htmlstr.= '<br><br>'.$cmd;
	if($ret != 0)
	{
		$htmlstr.= '<br><br>Asciidoc fehlgeschlagen:<br>';
		foreach($out as $o)
			$htmlstr.= $o;
		if($type != "debug")
			cleanUpAndDie("Der Report konnte nicht erstellt werden!", $chartTmpDir);
	}
	if(count($out) > 0)
	{
		$htmlstr.= '<br><br>Asciidoc Warnungen:<br>';
		foreach($out as $o)
			$htmlstr.= $o;
	}
	$htmlstr.='<br><br>'.$xmlFilename.' is written!';

	// DB Latex is tricky so i used a new process
	$command='dblatex -f docbook -t pdf -P latex.encoding=utf8 -P latex.unicode.use=1 -o '.$tmpFilename.' '.$xmlFilename;
	$htmlstr.= '<br><br>'.$command;
	$lastout=exec($command.' 2>&1', $out, $ret);
	if($ret)
	{
		$htmlstr.= '<br><br>dblatex fehlgeschlagen:<br>';
		foreach($out as $o)
			$htmlstr.= $o;
		if($type != "debug")
			cleanUpAndDie("Der Report konnte nicht erstellt werden!", $chartTmpDir);
	}

	$process = new process(escapeshellcmd($command));
	for ($i=0;$process->status() && $i<10;$i++)
	{
		$htmlstr.= '<br><br>The process is currently running';//ob_flush();flush();
		usleep(1000000); // wait for 1 Second
	}
	if ($process->status())
	{
		$process->stop();
		cleanUpAndDie('Timeout in dbLatex execution: <br>"'.escapeshellcmd($command).'"', $chartTmpDir);
	}
	elseif (@fopen($tmpFilename,'r'))
	{
		if (!rename($tmpFilename,$pdfFilename))
			cleanUpAndDie('Cannot remove File from '.$tmpFilename.' to '.$pdfFilename, $chartTmpDir);
	}
	else
	{
		if($type == "debug")
			cleanUpAndDie('Cannot read File: '.$tmpFilename.'<br>Maybe dblatex failed!'.escapeshellcmd($command), $chartTmpDir);
		else
			cleanUpAndDie("Der Report konnte nicht erstellt werden!", $chartTmpDir);
	}
	$htmlstr.='<br><br>'.$pdfFilename.' is written!';

	if($type == "pdf")
	{
		removeFolder($chartTmpDir);		//cleanup
		header('Content-type: application/force-download');
		header('Content-Disposition: attachment; filename="Report'.$report->report_id.'.pdf"');
		readfile($pdfFilename);

		//echo '<script>window.open("'.$pdfFilename.'","_blank");</script>';
		//echo '<script>window.open("'.$pdfFilename.'","_self");</script>';
	}
	else if($type == "debug")
	{
		removeFolder($chartTmpDir);		//cleanup
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
		echo '<html>';
		echo '<head>';
		echo '<title>Reports - Generate</title>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
		echo '<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">';
		echo '</head>';
		echo '<body style="background-color:#eeeeee;">';
		echo $htmlstr;
		echo '</body>';
		echo '</html>';
	}
	else
	{
		removeFolder($chartTmpDir);		//cleanup
		readfile($htmlFilename);
	}








	function cleanUpAndDie($msg, $chartTmpDir)
	{
		removeFolder($chartTmpDir);
		die($msg);
	}



	function removeFolder($dir)
	{
		if($dir == "/")
			return false;

		if (is_dir($dir) === true)
		{
			$files = array_diff(scandir($dir), array('.', '..'));

			foreach ($files as $file)
			{
				unlink($dir . "/" . $file);
			}

			return rmdir($dir);
		}

		return false;
	}
?>
