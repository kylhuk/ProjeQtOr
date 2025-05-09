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
 * HabilitationReport defines right to ech report for a profile.
 */ 
require_once('_securityCheck.php');
class HabilitationReport extends SqlElement {

  // extends SqlElement, so has $id
  public $id;    // redefine $id to specify its visible place 
  public $idProfile;
  public $idReport;
  public $allowAccess;
  public static $_skipRightControl=false;
  
  
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
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Execute specific query to dispatch updates so that if a sub-menu is activates
   * its main menu is also activated.
   * Also dispatch to unactivate main parameter if no-submenu is activated
   * @return void
   */
  static function correctUpdates() {

    return;
            
  }
  
  static function deleteDuplicate() {
    $hab=new HabilitationReport();
    $habList=$hab->getSqlElementsFromCriteria(array(), false, null, 'idReport, idProfile, id ');
    $idReport='';
    $idProfile='';
    foreach ($habList as $hab) {
      if ($hab->idReport==$idReport and $hab->idProfile==$idProfile) {
        $hab->delete();
      } else {
        $idReport=$hab->idReport;
        $idProfile=$hab->idProfile;
      }
    }
  }

}
?>