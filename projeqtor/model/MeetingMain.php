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

/** ============================================================================
 * Meeting
 */ 
require_once('_securityCheck.php');

class MeetingMain extends SqlElement {

  // List of fields that will be exposed in general user interface
  public $_sec_description;
  public $id;    // redefine $id to specify its visible place 
  public $reference;
  public $name;
  public $idMeetingType;
  public $idProject;
  public $idPeriodicMeeting;
  public $isPeriodic;
  public $periodicOccurence;
  public $meetingDate;
  public $_lib_from;
  public $meetingStartTime;
  public $_lib_to;
  public $meetingEndTime;
  public $location;
  public $_spe_buttonSendMail;
  public $_spe_startMeeting;
  public $idUser;
  public $description;
  public $_sec_treatment;
  public $idActivity;
  public $idStatus;
  public $idResource;
  public $handled;
  public $handledDate;
  public $done;
  public $doneDate;
  public $idle;
  public $idleDate;
  public $cancelled;
  public $_lib_cancelled;
  public $result;
  public $_sec_Attendees;
  public $_spe_showClosedAssignment;
  public $_Assignment=array();
  public $attendees;
  public $_spe_buttonAssignTeam;
  public $_sec_progress_left;
  public $MeetingPlanningElement;
  public $_sec_predecessor;
  public $_Dependency_Predecessor=array();
  public $_sec_successor;
  public $_Dependency_Successor=array();
  public $meetingStartDateTime;
  public $meetingEndDateTime;
  public $_sec_Link;
  public $_Link=array();
  public $_Attachment=array();
  public $_Note=array();
  public $_nbColMax=3;
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="5%" ># ${id}</th>
    <th field="nameProject" width="15%" >${idProject}</th>
    <th field="nameMeetingType" width="15%" >${idMeetingType}</th>
    <th field="meetingDate" formatter="dateFormatter" width="10%" >${meetingDate}</th>
    <th field="meetingStartTime" formatter="dateFormatter" width="10%" >${meetingStartTime}</th>  
    <th field="name" width="30%" >${name}</th>
    <th field="colorNameStatus" width="15%" formatter="colorNameFormatter">${idStatus}</th>
    ';

  private static $_fieldsAttributes=array("id"=>"nobr", "reference"=>"readonly",
                                  "idProject"=>"required",
                                  "idMeetingType"=>"required",
                                  "meetingDate"=>"required, nobr",
                                  "_lib_from"=>'nobr',
                                  "_lib_to"=>'nobr',
                                  "meetingStartTime"=>'nobr',
                                  "idUser"=>"hidden",
                                  "idStatus"=>"required",
                                  "handled"=>"nobr",
                                  "done"=>"nobr",
                                  "idle"=>"nobr",
																  "idPeriodicMeeting"=>"hidden",
																  "isPeriodic"=>"readonly",
																  "periodicOccurence"=>"hidden",
                                  "idleDate"=>"nobr",
                                  "cancelled"=>"nobr",
                                  "meetingStartDateTime"=>"hidden",
                                  "meetingEndDateTime"=>"hidden"
  );  
  
  private static $_colCaptionTransposition = array('result'=>'minutes', 
  'description'=>'meetingAgenda',
  'idResource'=>'responsible', 
  'idActivity'=>'parentActivity',
  'attendees'=>'otherAttendees',
  'meetingStartDateTime'=>'meetingStartTime',
  'meetingEndDateTime'=>'meetingEndTime'
  );
  
  //private static $_databaseColumnName = array('idResource'=>'idUser');
  private static $_databaseColumnName = array();
    
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

  public function setAttributes() {
    if ($this->isPeriodic) {
      $this->idActivity=null;
      self::$_fieldsAttributes['idActivity']='hidden';
      self::$_fieldsAttributes['isPeriodic']='readonly';
      self::$_fieldsAttributes['periodicOccurence']='display';
    } else {
    	self::$_fieldsAttributes['isPeriodic']="hidden";
    	self::$_fieldsAttributes['periodicOccurence']="hidden";
    	//unset($this->isPeriodic);
    }  	
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
   * Return the specific databaseTableName
   * @return String the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return self::$_databaseColumnName;
  }
  
// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return String the validation javascript (for dojo framework)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="idStatus") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= htmlGetJsTable('Status', 'setIdleStatus', 'tabStatusIdle');
      $colScript .= htmlGetJsTable('Status', 'setDoneStatus', 'tabStatusDone');
      $colScript .= '  var setIdle=0;';
      $colScript .= '  var filterStatusIdle=dojo.filter(tabStatusIdle, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusIdle, function(item, i) {setIdle=item.setIdleStatus;});';
      $colScript .= '  if (setIdle==1) {';
      $colScript .= '    dijit.byId("idle").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idle").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  var setDone=0;';
      $colScript .= '  var filterStatusDone=dojo.filter(tabStatusDone, function(item){return item.id==dijit.byId("idStatus").value;});';
      $colScript .= '  dojo.forEach(filterStatusDone, function(item, i) {setDone=item.setDoneStatus;});';
      $colScript .= '  if (setDone==1) {';
      $colScript .= '    dijit.byId("done").set("checked", true);';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("done").set("checked", false);';
      $colScript .= '  }';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="initialDueDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("actualDueDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("actualDueDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';     
    } else if ($colName=="actualDueDate") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (dijit.byId("initialDueDate").get("value")==null) { ';
      $colScript .= '    dijit.byId("initialDueDate").set("value", this.value); ';
      $colScript .= '  } ';
      $colScript .= '  formChanged();';
      $colScript .= '</script>';           
    } else     if ($colName=="idle") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("idleDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("idleDate").set("value", curDate); ';
      $colScript .= '    }';
//      $colScript .= '    if (! dijit.byId("done").get("checked")) {';
//       $colScript .= '      dijit.byId("done").set("checked", true);';
//       $colScript .= '    }';  
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("idleDate").set("value", null); ';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    } else if ($colName=="done") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("doneDate").get("value")==null) {';
      $colScript .= '      var curDate = new Date();';
      $colScript .= '      dijit.byId("doneDate").set("value", curDate); ';
      $colScript .= '    }';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("doneDate").set("value", null); ';
      $colScript .= '    if (dijit.byId("idle").get("checked")) {';
      $colScript .= '      dijit.byId("idle").set("checked", false);';
      $colScript .= '    }'; 
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;
  }

  public function drawSpecificItem($item){
    global $print;
    $result="";
    $canUpdate=securityGetAccessRightYesNo('menuMeeting', 'update', $this) == "YES";
    if ($item=='buttonSendMail') {
      if ($print or !$canUpdate or !$this->id or $this->idle or $this->done) {
        return "";
      }
      $result .= '<tr><td valign="top" class="label"><label></label></td><td>';
      $result .= '<button id="sendMailToAttendees" dojoType="dijit.form.Button" showlabel="true"';
      $result .= ' title="' . i18n('sendMailToAttendees') . '" class="roundedVisibleButton" >';
      $result .= '<span>' . i18n('sendMailToAttendees') . '</span>';
      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
      $result .= '   if (checkFormChangeInProgress()) {return false;}';
      $result .=  '  loadContent("../tool/sendMail.php","resultDivMain","objectForm",true);';
      $result .= '</script>';
      $result .= '</button>';
      $result .= '</td></tr>';
      return $result;
    }
    // Gautier ticket #2096
    if ($item=='buttonAssignTeam') {
      if ($print or !$canUpdate  or !$this->id or $this->idle or $this->done) {
        return "";
      }
      $result .= '<tr><td valign="top" class="label"><label></label></td><td>';
      $result .= '<button id="attendeesAllTeam" dojoType="dijit.form.Button" showlabel="true" onClick ="assignTeamForMeeting()"';
      $result .= ' title="' . i18n('buttonAssignWholeTeam') . '" class="roundedVisibleButton">';
      $result .= '<span>' . i18n('buttonAssignWholeTeam') . '</span>';
      $result .= '</button>';
      $result .= '</td></tr>';
      return $result;
    }
    if($item=="startMeeting"){
      if ($print or !$canUpdate or ! $this->id or $this->idle or $this->done ) {
        return "";
      }
      $result .= '<tr><td valign="top" class="label"><label></label></td><td>';
      $result .= '<button id="startMeeting" dojoType="dijit.form.Button" showlabel="true"';
      $result .= ' title="' . i18n('liveMeetingStart') . '" class="roundedVisibleButton">';
      $result .= '<span>' . i18n('liveMeetingStart') . '</span>';
      $result .=  '<script type="dojo/connect" event="onClick" args="evt">';
      $result .= '   if (checkFormChangeInProgress()) {return false;}';
      if(!$this->result and $this->description){
        $result .= ' var actionYes=function(){';
        $result .=  '  loadContent("../view/liveMeetingView.php?idMeeting='.$this->id.'&saveDescription=true", "centerDiv");';
        $result .= '};';
        $result .= 'var actionNo=function(){';
        $result .=  '  loadContent("../view/liveMeetingView.php?idMeeting='.$this->id.'&saveDescription=false", "centerDiv");';
        $result .= '};';
        $result .= ' showQuestion(i18n(\'saveMeetingDescription\'), actionYes, actionNo);';
      }else{
        $result .=  '  loadContent("../view/liveMeetingView.php?idMeeting='.$this->id.'&saveDescription=false", "centerDiv");';
      }
      $result .= '</script>';
      $result .= '</button>';
      $result .= '</td></tr>';
      return $result;
    }
    
    if (!$print and $item=='showClosedAssignment'){
      $showClosed=(Parameter::getUserParameter($item)=='1' or Parameter::getUserParameter($item)=='')?true:false;
      $result.='<div style="position:absolute;right:60px;top:3px;">';
      $result.='<label for="'.$item.'" class="dijitTitlePaneTitle" style="border:0;font-weight:normal !important;height:'.((isNewGui())?'20':'10').'px;width:'.((isNewGui())?'50':'150').'px">'.i18n('labelShowIdle'.((isNewGui())?'Short':'')).'</label>';
      if ($this->idle == 1){
        $result.='<div id="'.$item.'" style="'.((isNewGui())?'margin-top:14px':'').'" dojoType="dijit.form.CheckBox" type="checkbox" '.('checked').' readonly';
        $result.=' title="'.i18n('labelShowIdle').'" >';
      }else{
        $result.='<div id="'.$item.'" style="'.((isNewGui())?'margin-top:14px':'').'" dojoType="dijit.form.CheckBox" type="checkbox" '.(($showClosed)?'checked':'');
        $result.=' title="'.i18n('labelShowIdle').'" >';
        $result.='<script type="dojo/connect" event="onChange" args="evt">';
        $result.=' saveUserParameter("'.$item.'",((this.checked)?"1":"0"));';
        $result.=' if (checkFormChangeInProgress()) {return false;}';
        $result.=' loadContent("objectDetail.php", "detailDiv", "listForm");';
        $result.=' </script>';
      }
      $result.='</div>';
      $result.='</div>';
      return $result;
    }
    
  }
  
  public function deleteControl() { 
    $result='';
    $canDeleteRealWork = false;
    $crit = array('idProfile' => getSessionUser()->getProfile ( $this ), 'scope' => 'canDeleteRealWork');
    $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', $crit );
    if ($habil and $habil->id and $habil->rightAccess == '1') {
    	$canDeleteRealWork = true;
    }
    if ($this->MeetingPlanningElement and $this->MeetingPlanningElement->realWork>0 and !$canDeleteRealWork) {
      $result.='<br/>' . i18n('msgUnableToDeleteRealWork');
    }
    if ($result=='') {
      $result .= parent::deleteControl();
    }
    return $result;
  }

  public function control(){
    $result="";
    $old=$this->getOld();
    if ($this->MeetingPlanningElement and $this->MeetingPlanningElement->id
      and ($this->idActivity!=$old->idActivity or $this->idProject!=$old->idProject)){
      if (pq_trim($this->idActivity)) {
        $parentType='Activity';
        $parentId=$this->idActivity;
      } else {
        $parentType='Project';
        $parentId=$this->idProject;
      }
      $result.=$this->MeetingPlanningElement->controlHierarchicLoop($parentType, $parentId);
    }
    if (pq_trim($this->idActivity)) {
      $parentActivity=new Activity($this->idActivity);
      if ($parentActivity->idProject!=$this->idProject) {
        $result.='<br/>' . i18n('msgParentActivityInSameProject');
      }
    }
    if (pq_trim($this->meetingStartTime) and pq_trim($this->meetingEndTime) and $this->meetingStartTime>$this->meetingEndTime) {
      $result.='<br/>' . i18n('errorStartEndDates',array(i18n('colMeetingStartTime'),i18n('colMeetingEndTime')));
    }
    $defaultControl=parent::control();
    if ($defaultControl!='OK') {
      $result.=$defaultControl;
    }if ($result=="") {
      $result='OK';
    }
    return $result;
  }
  
  public function save() {
  	$old=$this->getOld();
  	$oldResource = $old->idResource;
  	if (! $this->name) {
      $this->name=SqlList::getNameFromId('MeetingType',$this->idMeetingType) . " " . $this->meetingDate;
  	}
    $listTeam=array_map('pq_strtolower',SqlList::getList('Team','name'));
    $listName=array_map('pq_strtolower',SqlList::getList('Affectable'));
    $listUserName=array_map('pq_strtolower',SqlList::getList('Affectable','userName'));
    $listInitials=array_map('pq_strtolower',SqlList::getList('Affectable','initials'));
    $this->MeetingPlanningElement->idle=$this->idle;
    $this->MeetingPlanningElement->done=$this->done;
    $this->MeetingPlanningElement->cancelled=$this->cancelled;
    if ($this->attendees) {
      $listAttendees=pq_explode(',',pq_str_replace(';',',',$this->attendees));
      $this->attendees="";
      foreach ($listAttendees as $attendee) {
      	$stockAttendee=$attendee;
        $attendee=pq_strtolower(pq_trim($attendee));
        if (in_array($attendee,$listName)) {
          $this->attendees.=($this->attendees)?', ':'';
          $aff=new Affectable(array_search($attendee,$listName));
          $this->attendees.='"' . $aff->name . '"';
          if ($aff->email) {
            $this->attendees.=' <' . $aff->email . '>';
          }
        } else if (in_array($attendee,$listUserName)) {
          $this->attendees.=($this->attendees)?', ':'';
          $aff=new Affectable(array_search($attendee,$listUserName));
          $this->attendees.='"' . (($aff->name)?$aff->name:$stockAttendee) . '"';
          if ($aff->email) {
            $this->attendees.=' <' . $aff->email . '>';
          }
        } else if (in_array($attendee,$listInitials)) {
          $this->attendees.=($this->attendees)?', ':'';
          $aff=new Affectable(array_search($attendee,$listInitials));        
          $this->attendees.='"' . ( ($aff->name)?$aff->name:(($aff->userName)?$aff->userName:$stockAttendee)) . '"';
          if ($aff->email) {
            $this->attendees.=' <' . $aff->email . '>';
          }
        } else if (in_array($attendee,$listTeam)) {
          $this->attendees.=($this->attendees)?', ':'';
          $id=array_search($attendee,$listTeam);
          $aff=new Affectable();
          $lst=$aff->getSqlElementsFromCriteria(array('idTeam'=>$id));
          foreach ($lst as $aff) {
            $this->attendees.=($this->attendees)?', ':'';
            $this->attendees.='"' . ( ($aff->name)?$aff->name:(($aff->userName)?$aff->userName:$stockAttendee)) . '"';
            if ($aff->email) {
              $this->attendees.=' <' . $aff->email . '>';
            }
          }
        } else {
          $this->attendees.=($this->attendees)?', ':'';
          $this->attendees.=$stockAttendee;
        }
      }
      $this->attendees=str_ireplace(',  ', ', ', $this->attendees);
      $this->attendees=str_ireplace(',  ', ', ', $this->attendees);
    }
    $this->MeetingPlanningElement->validatedStartDate=$this->meetingDate;
    $this->MeetingPlanningElement->validatedEndDate=$this->meetingDate;
    if (! $this->MeetingPlanningElement->assignedWork) {
    	$this->MeetingPlanningElement->plannedStartDate=$this->meetingDate;
      $this->MeetingPlanningElement->plannedEndDate=$this->meetingDate;
    }
    if (pq_trim($this->idProject)!=pq_trim($old->idProject) or pq_trim($this->idActivity)!=pq_trim($old->idActivity) 
    or pq_trim($this->idPeriodicMeeting)!=pq_trim($old->idPeriodicMeeting)) {
      if (pq_trim($this->idPeriodicMeeting)) {
        $parent=new PeriodicMeeting($this->idPeriodicMeeting);
        $this->idProject=$parent->idProject;
      }
      $this->MeetingPlanningElement->wbs=null;
      $this->MeetingPlanningElement->wbsSortable=null;
    }
    if($this->description==""){
      $meetingType = new MeetingType($this->idMeetingType);
      $this->description=$meetingType->description;
    }
    $this->meetingStartDateTime=$this->meetingDate.' '.$this->meetingStartTime;
    $this->meetingEndDateTime=$this->meetingDate.' '.$this->meetingEndTime;
    if (pq_strlen($this->meetingStartDateTime)>19) $this->meetingStartDateTime=pq_substr($this->meetingStartDateTime, -19);
    if (pq_strlen($this->meetingEndDateTime)>19) $this->meetingEndDateTime=pq_substr($this->meetingEndDateTime, -19);
    if (pq_strlen($this->meetingStartTime)>8) $this->meetingStartTime=pq_substr($this->meetingStartTime, -8);
    if (pq_strlen($this->meetingEndTime)>8) $this->meetingEndTime=pq_substr($this->meetingEndTime, -8);
    
    $defaultSave=parent::save();
    if ( (Parameter::getGlobalParameter('autoSetAssignmentByResponsible')=="YES"
        and (!SqlElement::isCopyInProgress() or Synchronization::$_createSyncItemInProgress==true))
        or RequestHandler::isCodeSet('selectedResource') ){
      $proj=new Project($this->idProject,true);
      $type=new Type($proj->idProjectType);
      $resource=(RequestHandler::isCodeSet('selectedResource'))?RequestHandler::getValue('selectedResource'):$this->idResource;
      if ($type->code!='ADM' and $resource and ! pq_trim ( $oldResource ) and pq_trim ( $resource ) != ''){
        // Add assignment for responsible
        $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', array(
            'idProfile' => getSessionUser ()->getProfile ( $this->idProject ),
            'scope' => 'assignmentEdit') );
        
        if ($habil and $habil->rightAccess == 1) {
          $ass = new Assignment();
          $crit = array('idResource' => $resource, 'refType' => 'Meeting', 'refId' => $this->id);
          $cpt=$ass->countSqlElementsFromCriteria($crit);
          if ($cpt == 0) {
            $ass->idProject = $this->idProject;
            $ass->refType = 'Meeting';
            $ass->refId = $this->id;
            $ass->idResource = $resource;
            $ass->assignedWork = 0;
            $ass->realWork = 0;
            $ass->leftWork = 0;
            $ass->plannedWork = 0;
            $ass->notPlannedWork = 0;
            $ass->rate = '100';
            $defaultSave .= $ass->save();
          }
        }
      }
    }
    return $defaultSave;
  }

  function sendMail($canSend=false) {
  	$paramMailSender=Parameter::getGlobalParameter('paramMailSender');
    $paramMailReplyTo=Parameter::getGlobalParameter('paramMailReplyTo');
    $paramTimezone=Parameter::getGlobalParameter('paramDefaultTimezone');
    $lstDest=pq_explode(',',$this->attendees);
    if (count($this->_Assignment)>0) {
    	foreach ($this->_Assignment as $ass) {
    		$res=new Affectable($ass->idResource);
    		$resMail=(($res->name)?$res->name:$res->userName);
    		$resMail.=(($res->email)?' <'.$res->email.'>':'');
    		$lstDest[]=$resMail;
    	}
    }
    $lstMail=array();
    foreach ($lstDest as $dest) {
      $to="";
      $name="";
      $dest=pq_trim($dest);
      $start=pq_strpos($dest,'<');
      if ($start>0) {
        $end=pq_strpos($dest,'>');
        $to=pq_trim(pq_substr( $dest, $start+1, $end-$start-1));
        $name=pq_trim(pq_substr($dest,0,$start));
      } else if (pq_strpos($dest,'@')>0){
        $to=$dest;
        $name=$to;
      }
      if ($to) {
        if (!$name) {
          $name=$to;
        }
        $lstMail[$name]=$to;
      }
    }   
    $sent=0;
    $vcal = "BEGIN:VCALENDAR\r\n";
    //$vcal .= "PRODID:-//ProjeQtOr//Meeting//EN\r\n";
    $vcal .= "PRODID:-//Microsoft Corporation//Outlook 12.0 MIMEDIR//EN\r\n";
    $vcal .= "VERSION:2.0\r\n";
    //$vcal .= "METHOD:REQUEST\r\n";
    $vcal .= "METHOD:REQUEST\r\n";
    $vcal .= "BEGIN:VEVENT\r\n";
    $user=getSessionUser();
    
    //$vcal .= ';SENT-BY="MAILTO:'.$paramMailSender.'"';
    if (Parameter::getGlobalParameter('invitesFromMailSender')==true) {
      $vcal .= "ORGANIZER;CN=PROJEQTOR - ".(($user->resourceName)?$user->resourceName:$user->name);
      $vcal .= ":MAILTO:$paramMailSender\r\n";
    } else {
      $vcal .= "ORGANIZER;CN=".(($user->resourceName)?$user->resourceName:$user->name);
      $vcal .= ":MAILTO:$user->email\r\n";
    }
    foreach($lstMail as $name=>$to) {
      //$vcal .= "ATTENDEE;CN=\"$name\";ROLE=REQ-PARTICIPANT;RSVP=FALSE:MAILTO:$to\r\n";
      //$vcal .= "ATTENDEE;ROLE=REQ-PARTICIPANT;CN=\"$name\":MAILTO:$to\r\n";
      $vcal .= "ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;";
      $vcal .= 'CN='.pq_str_replace(array("\r\n","\n","\r"," "),array("","","","_"),$name);
      $vcal .= ":MAILTO:".pq_str_replace(array("\r\n","\n"," "),array("","",""),$to)."\r\n";
    }
    $srv="projeqtor.org";
    if (isset($_SERVER['SERVER_NAME'])) {$srv=$_SERVER['SERVER_NAME'];}
    $vcal .= "UID:Meeting-".$this->id."-".$srv."\r\n";
    //$vcal .= "DTSTAMP:".date('Ymd').'T'.date('His')."\r\n";
    date_default_timezone_set($paramTimezone);
    // Start hour
    if ($this->meetingStartTime) $dtStart=pq_strtotime($this->meetingDate.' '.$this->meetingStartTime);
    else if ($this->meetingEndTime) $dtStart=pq_strtotime('-1 hour',pq_strtotime($this->meetingDate.' '.$this->meetingEndTime));
    else $dtStart=pq_strtotime($this->meetingDate.' '.getDailyHours($this->idProject,'startAM', true));
    // End hour
    if ($this->meetingEndTime) $dtEnd=pq_strtotime($this->meetingDate.' '.$this->meetingEndTime);
    else if ($this->meetingStartTime) $dtEnd=pq_strtotime('+1 hour',$dtStart);
    else $dtEnd=pq_strtotime($this->meetingDate.' '.getDailyHours($this->idProject,'endPM', true));
    //
    $vcal .= "DTSTART:".gmdate('Ymd',$dtStart).'T'.gmdate('Hi',$dtStart)."00Z\r\n";
    $vcal .= "DTEND:".gmdate('Ymd',$dtEnd).'T'.gmdate('Hi',$dtEnd)."00Z\r\n";
    $vcal .= "DTSTAMP:".gmdate('Ymd',$dtStart).'T'.gmdate('Hi',$dtStart)."00Z\r\n";
    if (pq_trim($this->location) != "") $vcal .= "LOCATION:$this->location\r\n";
    $vcal .= "CATEGORIES:ProjeQtOr\r\n"; 
    $vcal .= "SUMMARY:$this->name\r\n";
    $vcal .= "PRIORITY:5\r\n";
    if (pq_trim($this->description) != ""){
      $html2text=new Html2Text($this->description);
      $textDesc = "DESCRIPTION:".pq_str_replace(array("\r\n","\n"),array("\\n","\\n"),$html2text->gettext())."\r\n";
      $testDescTab=projeqtor_mb_str_split($textDesc, 60);
      $textDesc="";
      $nbLines=0;
      $lastCar='';
      foreach ($testDescTab as $tab) {
        $nbLines+=1;
        $nextCar='';
        if (pq_substr($tab,-1)=="\\") {
          $nextCar="\\";
          $tab=pq_substr($tab, 0,-1); 
        }
        $textDesc.=(($nbLines>1)?' ':'').$lastCar.$tab."\r\n";
        $lastCar=$nextCar;
      }
      $vcal.=$textDesc;
      //$vcal .="X-ALT-DESC;FMTTYPE=3Dtext/html:".$this->description;
    } else {
      $vcal .= "DESCRIPTION: \r\n";
    }
    $vcal .= "TRANSP:OPAQUE\r\n";
	  $vcal .= "X-MICROSOFT-CDO-BUSYSTATUS:TENTATIVE\r\n";
	  $vcal .= "X-MICROSOFT-CDO-IMPORTANCE:1\r\n";
	  $vcal .= "X-MICROSOFT-CDO-INTENDEDSTATUS:BUSY\r\n";
	  $vcal .= "X-MICROSOFT-DISALLOW-COUNTER:FALSE\r\n";
	  $vcal .= "X-MS-OLK-AUTOSTARTCHECK:FALSE\r\n";
	  $vcal .= "X-MS-OLK-CONFTYPE:0\r\n";
	  $vcal .= "BEGIN:VALARM\r\n";
    $vcal .= "ACTION:DISPLAY\r\n";
    
    $vcal .= "TRIGGER;RELATED=START:-PT00H15M00S\r\n";
    $vcal .= "END:VALARM\r\n";
    $vcal .= "END:VEVENT\r\n";
	  $vcal .= "X-MS-OLK-FORCEINSPECTOROPEN:TRUE\r\n";
    $vcal .= "END:VCALENDAR\r\n";
    $sender=$paramMailSender;
    $replyTo=($user->email)?$user->email:$paramMailReplyTo;
    $headers = "From: $sender\r\n";
    $headers .= "Reply-To: $replyTo\r\n";
    $headers .= "MIME-version: 1.0\r\n";
    //$headers .= "Content-Type: text/calendar; charset=\"utf-8\"; method=\"REQUEST\"\r\n";
    //$headers .= "Content-Type: multipart/alternative\r\n";
    //$headers .="boundary=--boundary_1016_7f2c68a5-e9c8-4b05-ad1c-d8886a0b573a\r\n";
    //$headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "X-Mailer: Microsoft Office Outlook 12.0";
    //mail($to, $this->description, $vcal, $headers);
    $destList="";
    foreach($lstMail as $name=>$to) {
      $destList.=($destList)?',':'';
      $destList.=$to;
      $sent++;
    }

    $result=sendMail($destList, $this->name, $vcal, $this, $headers,$sender,$canSend);
    if (! $result) {
    	$sent=0;
    	$destList="";
    } 
    return pq_str_replace(',', ', ', $destList);
  }
  
  public Static function removeDupplicateAttendees($refType, $refId) {
  	$obj=new $refType($refId);
  	if (! $refId) return;
  	$addr=pq_explode(', ',$obj->attendees);
  	$mails=array();
  	foreach ($addr as $ind=>$add) {
  		$mailStart=pq_strpos($add,'<');
  		$mailEnd=pq_strpos($add,'>');
  		if ($mailStart and $mailEnd) {
  			$mails[pq_trim(pq_substr($add,$mailStart+1,$mailEnd-$mailStart-1))]=$ind;
  		} else {
  			$mails[pq_trim($add)]=$ind;
  		}
  	}
  	$ass=new Assignment();
  	$assList=$ass->getSqlElementsFromCriteria(array('refType'=>$refType,'refId'=>$refId));
  	foreach ($assList as $ass) {
  		$aff=new Affectable($ass->idResource);
  		if (array_key_exists($aff->email, $mails)) {
  			unset ($addr[$mails[$aff->email]]);
  		}
  	}
  	$newAttendee="";
  	foreach ($addr as $add) {
  		$newAttendee.=(($newAttendee)?', ':'').$add;
  	}
    if ($newAttendee!=$obj->attendees) {
    	$obj->attendees=$newAttendee;
    	$obj->save();
    	echo "saved";
    }
  }
  
  // gautier ticket #2315
  public function copyTo($newClass, $newType, $newName, $newProject,$setOrigin, $withNotes, $withAttachments,$withLinks, $withAssignments=false, $withAffectations=false, $toProject=null, $toActivity=null, $copyToWithResult=false, $copyToWithActivityPrice=false, $copyToWithStatus=false , $copyToWithSubTask=false, $moveAfterCreate=null){
    if($this->isPeriodic != 1){
      $result = parent::copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks, $withAssignments, $withAffectations, $toProject, $toActivity, $copyToWithResult, $copyToWithActivityPrice, $copyToWithStatus, false, $moveAfterCreate);
    } else {
      $result=$this;
      $result->_copyResult="OK";
    } 
      
    if ($newClass=='Meeting') {
      $ass=new Assignment();
      $crit=array('refId'=>$this->id,'refType'=>'Meeting');
      $list=$ass->getSqlElementsFromCriteria($crit);
      foreach ($list as $ass) {
        $newAss = new Assignment();
        $newAss->idResource= $ass->idResource;
        $newAss->refId = $result->id;
        $newAss->refType = 'Meeting';
        $newAss->assignedWork = $ass->assignedWork;
        $newAss->leftWork = $ass->assignedWork;
        $newAss->idProject = $toProject;
        $newAss->save();
      }
      PlanningElement::updateSynthesis('Meeting',$result->id);
    }
    return $result;
  }
}
?>