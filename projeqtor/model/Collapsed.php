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

/* ============================================================================
 * RiskType defines the type of a risk.
 */  
require_once('_securityCheck.php'); 
class Collapsed extends SqlElement {

  // Define the layout that will be used for lists
   public $scope;
   public $idUser;
  
   public $_noHistory=true; // Will never save history for this object
   
   /** ==========================================================================
   * Constructor
   * @param $id Int the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  

  /** ========================================================================
   * Return the specific database criteria
   * @return String the databaseTableName
   */

  public static function collapse($scope,$userId=null) {
  	if ($userId===null) $userId=self::getUserId();
  	$crit=array('scope'=>$scope, 'idUser'=>$userId);
    $col=SqlElement::getSingleSqlElementFromCriteria('Collapsed', $crit);
    if (!$col or !$col->id) {
      $col=new Collapsed();
      $colList=$col->getSqlElementsFromCriteria($crit);
      if (count($colList)>1) $col->purge("scope='$scope' and idUser=$userId");
      $col->scope=$scope;
      $col->idUser=$userId;
      $saveCol=$col->save();
    }
    $list=self::getCollaspedList();
    $list[$scope]=true;
    self::setCollaspedList($list);
  }
  
  public static function expand($scope,$userId=null) {
  	if ($userId===null) $userId=self::getUserId();
  	$crit=array('scope'=>$scope, 'idUser'=>$userId);
    $col=SqlElement::getSingleSqlElementFromCriteria('Collapsed', $crit);
  	if ($col and $col->id) {
  		$col->delete();
  	}
  	
    $list=self::getCollaspedList();
    if (array_key_exists($scope, $list)) {
      unset($list[$scope]);
    }
    self::setCollaspedList($list);
  }
  
  private static function getUserId() {
  	if (sessionUserExists()) {
  		$user=getSessionUser();
  		return $user->id;
  	} else {
  		return null;
  	}
  }
  
  public static function getCollaspedList() {
    if (! sessionValueExists('collapsed') ) {
      self::initialiseCollapsedList();
    }
    return getSessionValue('collapsed');
  }
  
  private static function setCollaspedList($list) { 
  	setSessionValue('collapsed', $list);
  }
  
  private static function initialiseCollapsedList() {
  	$list=array();
  	$col=new Collapsed();
  	$crit=array('idUser'=>'0'); // Retreive default collapsed from Customization screen
  	$listCol=$col->getSqlElementsFromCriteria($crit, false);
  	foreach($listCol as $col) {
  	  $list[$col->scope]=true;
  	}
  	$crit=array('idUser'=>self::getUserId());
  	$listCol=$col->getSqlElementsFromCriteria($crit, false);
  	foreach($listCol as $col) {
  		$list[$col->scope]=true;
  	}
  	self::setCollaspedList($list);
  }
}  
?>