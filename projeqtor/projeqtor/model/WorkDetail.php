<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

require_once('_securityCheck.php');

class WorkDetail extends SqlElement {
	
	public $id;
	public $idWork;
	public $work;
	public $idWorkCategory;
	public $uncertainties;
	public $progress;
  	
	private static $_databaseCriteria = array();
	private static $_databaseColumnName = array();
	/** ==========================================================================
	 * Constructor
	 * @param $id Int the id of the object in the database (null if not stored yet)
	 * @return void
	 */

	
	/** ========================================================================
	 * Return the specific database criteria
	 * @return String the databaseTableName
	 */
	protected function getStaticDatabaseCriteria() {
	  return self::$_databaseCriteria;
	}
	
	/** ==========================================================================
	 * Construct
	 * @return void
	 */
	function __construct($id = NULL, $withoutDependentObjects=false) {
		parent::__construct($id,$withoutDependentObjects);
	}
	
	public function save() {
	  $result = parent::save();
	  return $result;
	}
	
    public function deleteControl(){
	  $result="";
	  if (! $result) {
	    $result=parent::deleteControl();
	  }
	  return $result;
	}
	/** ==========================================================================
	 * Destructor
	 * @return void
	 */
	function __destruct() {
		parent::__destruct();
	}
	/**
	 * ========================================================================
	 * Return the specific databaseColumnName
	 *
	 * @return String the databaseTableName
	 */
	protected function getStaticDatabaseColumnName() {
	  return self::$_databaseColumnName;
	}
	
	
}
?>