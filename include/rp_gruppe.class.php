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
 * Authors: Andreas Moik <moik@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/../../../include/basis_db.class.php');


class rp_gruppe extends basis_db
{
	public $bezeichnung;
	public $reportgruppe_id;
	public $reportgruppe_parent_id;
	public $sortorder;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	public $new;
	public $errormsg;

	public $result = array();
	public $gruppe = array();
	public $recursive = array();

	public function __construct($reportgruppe_id=null)
	{
		parent::__construct();

		$this->bezeichnung = "";
		$this->reportgruppe_id = "";
		$this->reportgruppe_parent_id = "";
		$this->sortorder = null;

		if(!is_null($reportgruppe_id))
			$this->load($reportgruppe_id);
		else
			$this->new=true;
	}


	public function load($reportgruppe_id)
	{
		//reportgruppe_id auf gueltigkeit pruefen
		if(!is_numeric($reportgruppe_id) || $reportgruppe_id == '')
		{
			$this->errormsg = 'reportgruppe_id must be a number!';
			return false;
		}
		//Lesen der Daten aus der Datenbank
		$qry = 'SELECT * FROM addon.tbl_rp_gruppe WHERE reportgruppe_id='.$reportgruppe_id.';';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->reportgruppe_id					= $row->reportgruppe_id;
			$this->bezeichnung							= $row->bezeichnung;
			$this->reportgruppe_parent_id		= $row->reportgruppe_parent_id;
			$this->sortorder								= $row->sortorder;
			$this->updateamum								= $row->updateamum;
			$this->updatevon								= $row->updatevon;
			$this->insertamum								= $row->insertamum;
			$this->insertvon								= $row->insertvon;
		}
		$this->new=false;
		return true;
	}




	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $reportgruppe_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		if($this->reportgruppe_id != "" && $this->reportgruppe_id === $this->reportgruppe_parent_id)
		{
			$this->errormsg = 'reportgruppe_id darf nicht gleich reportgruppe_parent_id sein';
			return false;
		}

		if($this->new)
		{

			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO addon.tbl_rp_gruppe (bezeichnung, reportgruppe_parent_id, sortorder,
					  insertamum, insertvon) VALUES('.
					  $this->db_add_param($this->bezeichnung).', '.
					  $this->db_add_param($this->reportgruppe_parent_id, FHC_INTEGER).', '.
					  $this->db_add_param($this->sortorder, FHC_INTEGER).', '.
					  'now(), '.
					  $this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob reportgruppe_id eine gueltige Zahl ist
			if(!is_numeric($this->reportgruppe_id))
			{
				$this->errormsg = 'reportgruppe_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE addon.tbl_rp_gruppe SET'.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				' reportgruppe_parent_id='.$this->db_add_param($this->reportgruppe_parent_id, FHC_INTEGER).', '.
				' sortorder='.$this->db_add_param($this->sortorder, FHC_INTEGER).', '.
				' updateamum= now(), '.
      	' updatevon='.$this->db_add_param($this->updatevon).
      	' WHERE reportgruppe_id='.$this->db_add_param($this->reportgruppe_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('addon.tbl_rp_gruppe_reportgruppe_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->reportgruppe_id = $row->id;
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}

		}
		else
		{
			$this->errormsg = 'Fehler beim Update des reportgruppe_id-Datensatzes';
			return false;
		}
		return $this->reportgruppe_id;
	}

	/**
	 * Loescht einen Eintrag
	 *
	 * @param $reportgruppe_id
	 * @return true wenn ok, sonst false
	 */
	public function delete($reportgruppe_id)
	{
		$qry = "DELETE FROM addon.tbl_rp_gruppe WHERE reportgruppe_id=".$this->db_add_param($reportgruppe_id).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Löschen des Eintrages';
			return false;
		}
	}

	/**
	 * Laedt alle Gruppen
	 *
	 * @return true wenn ok, sonst false
	 */
	public function loadAll()
	{
		//Lesen der Daten aus der Datenbank
		$qry = "
				SELECT *
				FROM addon.tbl_rp_gruppe AS gr
				ORDER BY gr.sortorder ASC;
			";


		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$this->result[] = $row;
		}
		return true;
	}



	/**
	 * Gruppenzuordnung zu einer Reportgruppe holen
	 *
	 * @param $reportgruppe_id
	 * @return true wenn ok, sonst false
	 */
	public function getGruppenzuordnung($reportgruppe_id)
	{
		$this->gruppe = array();		//eventuelle alte einträge löschen

		$qry = "
			SELECT *
				FROM addon.tbl_rp_gruppenzuordnung
				WHERE reportgruppe_id = " . $this->db_add_param($reportgruppe_id) . ";";


		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$this->gruppe[] = $row;
		}
		return true;
	}


	/**
	 * Laedt die Gruppen Rekursiv
	 *
	 * @return true wenn ok, sonst false
	 */
	public function loadRecursive()
	{
		$buf = $this->result;

		foreach($buf as $key => $d)
		{
			$found = false;
			if(!is_null($d->reportgruppe_parent_id))
			{
				$found = $this->findRecursive($d->reportgruppe_parent_id, $buf);

				if($found)
				{
					if(!isset($found->children))
					{
						$found->children = array();
					}

					$found->children[] = $d;
					unset($buf[$key]);
				}
				else
				{
					//sollte wegen constraints nie eintreten!
					$this->errormsg = "ParentID nicht gefunden!(".$d->reportgruppe_parent_id.")";
					return false;
				}
			}
		}

		$this->recursive = $buf;

		return true;
	}

	private function findRecursive($pid, $data)
	{
		foreach($data as $d)
		{
			if($d->reportgruppe_id === $pid)		//gefunden
			{
				return $d;
			}
			else if(isset($d->children))				//hat children -> eine ebene tiefer
			{
				if($this->findRecursive($pid, $d->children))
					return $this->findRecursive($pid, $d->children);
			}
		}
		return false;
	}




	/**
	 * Gibt die anzahl der Kind-Reportgruppen zurück
	 *
	 * @param $reportgruppe_id
	 * @return anzahl der Childs
	 */
	public function childCount($reportgruppe_id)
	{

		//reportgruppe_id auf gueltigkeit pruefen
		if(!is_numeric($reportgruppe_id) || $reportgruppe_id == '')
		{
			$this->errormsg = 'reportgruppe_id must be a number!';
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = '
				SELECT count(1)
				FROM addon.tbl_rp_gruppe
				WHERE reportgruppe_parent_id='.$reportgruppe_id.';';


		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		if($ret = $this->db_fetch_object())
		{
			return intval($ret->count);
		}

		$this->errormsg = 'Fehler beim Laden der Daten';
		return false;
	}

	/**
	 * Gibt die Kind-Reportgruppen eine Reportgruppe zurück
	 *
	 * @param $reportgruppe_id
	 * @return Childs
	 */
	public function loadGroupChildren($reportgruppe_id)
	{

		//reportgruppe_id auf gueltigkeit pruefen
		if(!is_numeric($reportgruppe_id) || $reportgruppe_id == '')
		{
			$this->errormsg = 'reportgruppe_id must be a number!';
			return false;
		}

		if (!$this->loadRecursive())
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
		else
		{
			$gruppechildren = array();
			$gruppe = array();

			$gruppe[] = $this->getChildrenTree($this->recursive, $reportgruppe_id);

			$this->getChildrenList($gruppe, $gruppechildren);
			$this->result = $gruppechildren;
			return true;
		}
	}

	/**
	 * Gibt die Kind-Reportgruppen in From einer Liste (nicht verschachtelt) zurück
	 * @param $gruppen
	 * @param $childrenlist Liste wo Kinder gespeichert werden
	 */
	private function getChildrenList($gruppen, &$childrenlist)
	{
		foreach ($gruppen as $gruppe)
		{
			$childrenlist[] = $gruppe;

			if (!empty($gruppe->children))
			{
				$this->getChildrenList($gruppe->children, $childrenlist);
			}
		}
	}

	/**
	 * Gibt die Kind-Reportgruppen einer parent-Reportgruppe in verschachtelter Form zurück
	 * @param $gruppen alle Gruppen in verschachtelter Form
	 * @param $reportgruppe_id id der parent-Reportgruppe
	 */
	private function getChildrenTree($gruppen, $reportgruppe_id)
	{
		if(!is_numeric($reportgruppe_id) || $reportgruppe_id == '')
		{
			$this->errormsg = 'reportgruppe_id must be a number!';
			return false;
		}

		foreach ($gruppen as $gruppe)
		{
			if ($gruppe->reportgruppe_id === $reportgruppe_id)
			{
				return $gruppe;
			}
			elseif (!empty($gruppe->children))
			{
				$childrenkey = $this->getChildrenTree($gruppe->children, $reportgruppe_id);
				if ($childrenkey)
					return $childrenkey;
			}
		}

		return false;
	}
}

