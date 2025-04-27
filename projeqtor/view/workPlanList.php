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
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projeqtor.php";
scriptLog('   ->/view/workPlanList.php');
require_once '../tool/planningListFunction.php';

$startDate=date('Y-m-d');
$endDate=null;
$user=getSessionUser();
$saveDates=false;
$projectDate=Parameter::getUserParameter('projectDate');

$saveShowProject=Parameter::getUserParameter('planningShowProject');
$showPoolForResource=Parameter::getUserParameter('workPlanShowPoolForResource');
$showResourceWithoutWork=Parameter::getUserParameter('workPlanShowResourceWithoutWork');
$saveShowProjectColor=Parameter::getUserParameter('planningShowProjectColor');
$saveShowLateColor=Parameter::getUserParameter('planningShowLateColor');
$showWorkDecimals=(Parameter::getUserParameter('workPlanShowWorkDecimals') != '0')?1:0;

if(Parameter::getUserParameter('startDatePlanView') != ""){
  $startDate = Parameter::getUserParameter('startDatePlanView');
}else{
  $startDate=date('Y-m-d');
}
if(Parameter::getUserParameter('endDateWorkPlanView') != ""){
  $endDate = Parameter::getUserParameter('endDateWorkPlanView');
}else{
  $endDate=null;
}

if($projectDate){
  $saveDates=false;
  $startDate=null;
  $endDate=null;
}

$displayWidthPlan="1920";
if (RequestHandler::isCodeSet('destinationWidth')) {
  $displayWidthPlan=RequestHandler::getNumeric('destinationWidth');
}
$displayListDiv=$displayWidthPlan;

$paramworkPlanDurationMode='';
if(sessionValueExists("workPlanDurationMode") and getSessionValue("workPlanDurationMode")!=''){
  $paramworkPlanDurationMode=getSessionValue("workPlanDurationMode");
}

$project = (sessionValueExists('project'))?pq_trim(getSessionValue('project')):'*';
$selectResourceName = (sessionValueExists('selectResourceName'))?pq_trim(getSessionValue('selectResourceName')):'';
$selectPoolName = (sessionValueExists('selectPoolName'))?pq_trim(getSessionValue('selectPoolName')):'';
$selectedTeamName = (sessionValueExists('teamName'))?pq_trim(getSessionValue('teamName')):'';
$selectedOrganizationName = (sessionValueExists('organizationName'))?pq_trim(getSessionValue('organizationName')):'';
?>
   
<div id="mainWorkPlanDivContainer" dojoType="dijit.layout.BorderContainer">
	<div dojoType="dijit.layout.ContentPane" region="top" id="listHeaderDiv" height="27px"
	style="z-index: 3; position: relative; overflow: visible !important;">
		<table width="100%" style="height:36px" class="listTitle" >
		  <tr height="32px">
		    <td style="vertical-align:top; min-width:100px; width:15%;">
		      <table >
    		    <tr height="32px">
      		    <td width="50px" style="min-width:50px;<?php if (isNewGui()) echo 'position:relative;top:2px';?>" align="center">
                <?php echo formatIcon('WorkPlan', 32, null, true);?>
                </td>
                <td style="min-width:100px"><span class="title" style="<?php echo (isNewGui())?'max-width:400px;white-space:nowrap':'max-width:250px;white-space:normal';?>"><?php echo i18n('DynamicWorkPlan');?></span></td>
      		  </tr>
    		  </table>
		    </td>
		    <td>   
		      <form dojoType="dijit.form.Form" id="listForm" action="" method="" >
		        <input type="hidden" id="planningType" name="planningType" value="workPlan" />
		        <input type="hidden" name="workPlanSelectedResource" id="workPlanSelectedResource" value="" />       
		        <table style="width: 100%;">
		          <tr>
		            <td style="padding-right:70px;"><?php drawResourceTeamOrga('workPlan', false);?></td>
	              </tr>
	              <tr>
                    <td>
                      <table style="margin-left:5px;">
                        <tr>
                          <td style="text-align:right;padding-left: 7px;"><?php echo i18n('displayStartDate');?>&nbsp;</td><td style="padding-right:10px;"><?php drawFieldStartDate();?></td>
                          <td style="text-align:right;"><?php echo i18n('displayEndDate');?>&nbsp;</td><td><?php drawWorkPlanFieldEndDate();?></td>
                          <td style="padding-left:10px;"><?php drawOptionAllProject();?>&nbsp;&nbsp;</td>
                          <td>
                            <ul id="workPlanDurationMode" name="workPlanDurationMode" data-dojo-type="dojox/mobile/TabBar" data-dojo-props='barType:"segmentedControl"'>
                              <li id="workPlanDurationMode0" onClick="changeParamWorkPlanDurationMode('0')" data-dojo-type="dojox/mobile/TabBarButton"   <?php if($paramworkPlanDurationMode=='0'){ echo "data-dojo-props='selected:true'"; }?> > <?php echo i18n('colWeekPeriod');?></li>
                              <li id="workPlanDurationMode1" onClick="changeParamWorkPlanDurationMode('1')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramworkPlanDurationMode=='1'){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('colMonths');?></li>
                              <li id="workPlanDurationMode2" onClick="changeParamWorkPlanDurationMode('2')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramworkPlanDurationMode=='2'){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('quarter');?></li>
                              <li id="workPlanDurationMode3" onClick="changeParamWorkPlanDurationMode('3')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramworkPlanDurationMode=='3'){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('colSemester');?></li>
                              <li id="workPlanDurationMode4" onClick="changeParamWorkPlanDurationMode('4')" data-dojo-type="dojox/mobile/TabBarButton" <?php if($paramworkPlanDurationMode=='4'){ echo "data-dojo-props='selected:true'"; }?> ><?php echo i18n('colYear');?></li>
                            </ul>
                          </td>
                        </tr>
                      </table>
                    </td>
                    <td>
                      <button id="buttonRefresh" dojoType="dijit.form.Button" showlabel="false"
                        title="<?php echo i18n('buttonRefreshList');?>"
                        iconClass="dijitButtonIcon dijitButtonIconRefresh" class="detailButton">
                        <script type="dojo/connect" event="onClick" args="evt">
                          refreshJsonPlanning();
                        </script>
                      </button>
                    </td>
                    <td style="width:50px;padding-right:10px;">
                      <div dojoType="dijit.form.DropDownButton"							    
    		             id="extraButtonPlanning" jsId="extraButtonPlanning" name="extraButtonPlanning" 
    		             showlabel="false" class="comboButton" iconClass="dijitButtonIcon dijitButtonIconExtraButtons" class="detailButton" 
    		             title="<?php echo i18n('extraButtons');?>">
                           <div dojoType="dijit.TooltipDialog" class="white" id="extraButtonImputationDialog"
				              style="position: absolute; top: 50px; right: 40%">        
                             <table>
                               <tr style="width:100%;">
                                  <td><?php drawDisplayField('workPlan');?></td>
	                             </tr>
	                           </table>
                           </div>
                      </div>
                    </td>
                  </tr>
	           </table>
		      </form>
		    </td>
		  </tr>
		  <tr height="24px">
		    <td id="workPlanGraphScale" colspan="2" style="width:100%"></td>
		  </tr>
		</table>
		<div dojoType="dijit.layout.ContentPane" id="workPlanJsonData" jsId="workPlanJsonData" 
          style="display: none">
		  <?php
		  if($project == '*' and !$selectResourceName and !$selectPoolName and !$selectedTeamName and !$selectedOrganizationName){
		    // dont call jsonWorkPlan
		  }else{
		    include '../tool/jsonWorkPlan.php';
		  }
          ?>
		</div>
	</div>
	<div dojoType="dijit.layout.ContentPane" region="center" id="workPlanGridContainerDiv">
   <div id="submainWorkPlanDivContainer" dojoType="dijit.layout.BorderContainer"
    style="border-top:1px solid #ffffff;">
     <div dojoType="dijit.layout.ContentPane" region="center" 
      style="height:100%; overflow:hidden;" class="ganttDiv" 
      id="workPlanGanttChartDIV" name="workPlanGanttChartDIV" >
       <div id="mainRightWorkPlanDivContainer" dojoType="dijit.layout.BorderContainer">
         <div dojoType="dijit.layout.ContentPane" region="top" 
          style="width:100%; height:45px; overflow:hidden;" class="ganttDiv"
          id="workPlanTopGanttChartDIV" name="workPlanTopGanttChartDIV">
         </div>
         <div dojoType="dijit.layout.ContentPane" region="center" 
          style="width:100%; overflow-x:scroll; overflow-y:scroll; position: relative; top:-10px;" class="ganttDiv"
          id="workPlanRightGanttChartDIV" name="workPlanRightGanttChartDIV"
          onScroll="dojo.byId('workPlanRightside').style.left='-'+(this.scrollLeft+1)+'px'">
         </div>
       </div>
     </div>
   </div>
	</div>
</div>
