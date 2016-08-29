<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Moik           < moik@technikum-wien.at >.
 */

$error = "";

if(!isset($_REQUEST["action"]))
	$error = "wrong parameters";
else
{
	switch($_REQUEST["action"])
	{
		case "cleanup":
			foreach(getAllReportFolders() as $report)
			{
				if(!recurseRmdir(sys_get_temp_dir() .  "/" . $report))
					$error = "remove Failed";
			}
			break;
		case "hasOldReports":
			if(count(getAllReportFolders()) < 1)
				$error = "false";
			break;
		default:
			$error = "wrong action";
	}
}

if($error == "")
{
	echo "true";
}
else
{
	echo $error;
}












	function getAllReportFolders()
	{
		$ffs = scandir(sys_get_temp_dir());
		$list = array();
		foreach ( $ffs as $ff )
		{
			if($ff != '.'
			&& $ff != '..'
			&& strlen($ff) == 21	// report folders are always createt with "reports_[UNIQID]"(=21 chars)
			&& substr($ff, 0, 8) == "reports_"
			)
			{
				$list[] = $ff;
			}
		}
		return $list;
	}

	function recurseRmdir($dir)
	{
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file)
		{
			(is_dir("$dir/$file")) ? recurseRmdir("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

?>