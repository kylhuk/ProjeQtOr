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
 * Stauts defines list stauts an activity or action can get in (lifecylce).
 */ 
require_once('_securityCheck.php');
class ResourceTeamMain extends ResourceMain {
  // extends SqlElement, so has $id
  
  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;
  public $_spe_image;
  public $name;
  public $userName;
  public $initials;
  public $email;
  public $capacity=1;
  public $idCalendarDefinition;
  public $idProfile;
  public $idOrganization;
  public $idTeam;
  public $phone;
  public $mobile;
  public $fax;
  // ADD tLaguerie Ticket #396
  public $startDate;
  // END tLaguerie Ticket #396
  public $isContact;
  public $isUser;
  // MTY - LEAVE SYSTEM
  public $isEmployee;
  public $student;
  public $subcontractor;
  public $isLeaveManager;
  // MTY - LEAVE SYSTEM
  public $idle;
  // ADD tLaguerie Ticket #396
  public $endDate;
  // END tLaguerie Ticket #396
  
  public $description;
  public $_sec_ResourceCost;
  public $idRole;
  public $_ResourceCost=array();
  public $_sec_Affectations;
  public $_spe_affectations;
  public $_spe_affectationGraph;
  public $_sec_AffectationsResourceTeam;
  public $_spe_affectationsResourceTeam;
  public $_spe_affectationResourceTeamGraph;
  public $_sec_resourceCapacity;
  public $_spe_resourceCapacity;
  public $_sec_resourceSurbooking;
  public $_spe_resourceSurbooking;
  public $_sec_resourceIncompatible;
  public $_spe_resourceIncompatible;
  public $_sec_resourceSupport;
  public $_spe_resourceSupport;
  public $_sec_Miscellaneous;
  public $_sec_Asset;
  public $_spe_asset;
  public $isLdap;
  public $dontReceiveTeamMails;
  public $password;
  public $crypto;
  public $isResourceTeam;
  public $maxDailyWork;
  public $maxWeeklyWork;
  public $_sec_affectationResourceTeamResource;
  public $_spe_affectationResourceTeamResource;
  public $_nbColMax=3;
  
	  private static $_layout='
      <th field="id" formatter="numericFormatter" width="5%"># ${id}</th>
      <th field="name" width="20%">${realName}</th>
      <th field="photo" formatter="thumb32" width="5%">${photo}</th>
      <th field="initials" width="10%">${initials}</th>  
	    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
	  
	  private static $_fieldsAttributes=array(
	      "name"=>"required, truncatedWidth100",
	      "idCalendarDefinition"=>"required,truncatedWidth100",
	      "userName"=>"hidden" ,
	      "email"=>"hidden" ,
	      "capacity"=>"hidden" ,
	      "idOrganization"=>"" ,
	      "idTeam"=>"" ,
	      "phone"=>"hidden" ,
	      "mobile"=>"hidden" ,
	      "fax"=>"hidden" ,
	      "isContact"=>"hidden" ,
 	      "isUser"=>"hidden" ,
	      "isResourceTeam"=>"hidden" ,
	      "isMaterial"=>"hidden" ,
	      "crypto"=>"hidden",
	      "idProfile"=>"hidden" ,
	      "_sec_Miscellaneous"=>"hidden" ,
	      "_sec_resourceCapacity"=>"hidden",
         "_spe_resourceCapacity"=>"hidden",
	      "_sec_resourceIncompatible"=>"hidden",
	      "_spe_resourceIncompatible"=>"hidden",
	      "_sec_resourceSupport"=>"hidden",
	      "_spe_resourceSupport"=>"hidden",
	      "dontReceiveTeamMails"=>"hidden",
        "isEmployee"=>"hidden", //Hide the attribute isEmployee for the leaveManagement
        "isLeaveManager"=>"hidden", //Hide the attribute isLeaveManager for the leaveManagement
        "_sec_ResourceSkill"=>"hidden",
	      "_spe_resourceSkill"=>"hidden",
	      "contactFunction"=>"hidden",
	      "maxDailyWork"=>"hidden",
	      "maxWeeklyWork"=>"hidden",
	      "_sec_Asset"=>"hidden",
	      "_spe_asset"=>"hidden",
	      "_sec_affectationResourceTeamResource"=>"hidden",
	      "_spe_affectationResourceTeamResource"=>"hidden"
	  );
	  
	private static $_databaseColumnName = array('name'=>'fullName',
                                              'userName'=>'name');
	private static $_colCaptionTransposition = array('idRole'=>'mainRole', 'name'=>'realName');
  private static $_databaseTableName = 'resource';
  private static $_databaseCriteria = array('isResourceTeam'=>'1','isResource'=>'1');
   /** ==========================================================================
   * Constructor
   * @param $id Int the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
    unset($this->_sec_affectationResourceTeamResource);
    unset($this->_spe_affectationResourceTeamResource);    
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
  /** ==========================================================================
   * Return the specific layout
   * @return String the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  /** ==========================================================================
   * Return the specific fieldsAttributes
   * @return Array the fieldsAttributes  
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }
  
    /** ============================================================================
   * Return the specific colCaptionTransposition
   * @return String the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld=null) {
    return self::$_colCaptionTransposition;
  }
  
  /** ========================================================================
   * Return the specific databaseColumnName
   * @return String the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
    /** ========================================================================
   * Return the specific databaseTableName
   * @return String the databaseTableName
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix=Parameter::getGlobalParameter('paramDbPrefix');
    return $paramDbPrefix . self::$_databaseTableName;
  }
  
  /** ========================================================================
   * Return the specific database criteria
   * @return String the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return self::$_databaseCriteria;
  }
  
  public function delete() {
  // Delete planned work for the assignment
    $result = parent::delete();
    if (! pq_strpos($result,'id="lastOperationStatus" value="OK"')) {
      return $result;
    }
    $aff=new Affectation();
    $affList=$aff->purge('idResource='.Sql::fmtId($this->id));
    return $result;
  }
  
  public static function buildTeamCapacityPeriod($idResourceAff, $showIdle=false){
    $periods=ResourceTeamAffectation::buildResourcePeriods($idResourceAff,$showIdle);
    $capacityPeriod=array();
    foreach ($periods as $per){
      $capacityPeriod[$idResourceAff][$per['start']]['capacity']=$per['rate'];
      $capacityPeriod[$idResourceAff][$per['start']]['startDate']=$per['start'];
      $capacityPeriod[$idResourceAff][$per['start']]['endDate']=$per['end'];
    }
    return $capacityPeriod;
  }
  
  private static $_cachedResourcesList=array();
  public static function getResourcesList($id) {
    if (isset(self::$_cachedResourcesList[$id])) return self::$_cachedResourcesList[$id];
    $rta=new ResourceTeamAffectation();
    $list=$rta->getSqlElementsFromCriteria(array('idResourceTeam'=>$id, 'idle'=>'0'));
    $result=array();
    $result[$id]=$rta; 
    foreach ($list as $res) {
      $result[$res->idResource]=$res; //array('rate'=>$res->rate,'start'=>$res->startDate,'end'=>$res->endDate);
    }
    self::$_cachedResourcesList[$id]=$result;
    return $result;
  }
}
?>