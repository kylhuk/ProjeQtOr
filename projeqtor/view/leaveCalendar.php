<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : Eliott LEGRAND (from Salto Consulting - 2018) 
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

// ELIOTT - LEAVE SYSTEM

/* ============================================================================
 * 
 * 
 */
  require_once "../tool/projeqtor.php";
  scriptLog('   ->/view/leaveCalendar.php');  
  $user=getSessionUser();
  if ($user->isEmployee==0) {
        $result = '<div class="messageNotificationWarning" style="height:50px;text-align:center">';
        $result .= i18n('YouMustBeAnEmployeeToAccessAtTheCalendar');
        $result .= '</div>';
        echo $result;
      return;
  }
    
  $idUser = $user->id;
  $profile=new Profile($user->idProfile);
  $manager = new EmployeeManager($idUser);
  $isManager = $manager->isManager();
  
  $leaveTypes = LeaveType::getList();

  $print=false;
  if (isset($_REQUEST['print'])) {
  	$print=true;
  }
  
?>

<input type="hidden" name="objectClassManual" id="objectClassManual" value="leaveCalendar" />
<input type="hidden" name="idEmployeeCalendar" id="idEmployeeCalendar" value="<?php echo $idUser; ?>" />
<input type="hidden" name="idUserCalendar" id="idUserCalendar" value="<?php echo $idUser; ?>" />
<input type="hidden" name="isManagerCalendar" id="isManagerCalendar" value="<?php echo ($isManager?1:0); ?>" />
<div  class="container" dojoType="dijit.layout.BorderContainer" >
  <div style="overflow: <?php echo(!$print)?'auto':'hidden';?>;padding-bottom:10px;overflow-y:hidden;" id="detailDiv" dojoType="dijit.layout.ContentPane" region="center">
    <!-- content of ObjectMain -->
<!--------------->
<!-- THE TITLE -->
<!--------------->
    <table class="listTitle" width="100%">
        <tr height="32px;" style="vertical-align: middle;">
            <td width="50px" align="center"><?php echo formatIcon("LeaveCalendar", 32, null, true);?></td>
            <td><span class="title"><?php echo i18n('menuLeaveCalendar');?>&nbsp;</span></td>
            <td>
<!--------------------------------------------------------------------------->
<!-- COMBO BOX FOR SELECTING THE EMPLOYEE FOR WHICH DISPLAY LEAVE CALENDAR -->
<!--------------------------------------------------------------------------->
                    <!-- Manager => List of managed employees -->
                    <?php if ($isManager) {?>
                    <div style="width:500px; margin:0 auto;">
                    <?php if(isNewGui()){?><table><tr><td><?php }?>
                        <label style="text-shadow: none;font-size:12px;width:165px;" for='leaveEmployee'><?php echo i18n("selectAnEmployee");?> <?php if(!isNewGui()){?>:<?php }?> 
                        </label>
                     <?php if(isNewGui()){?> &nbsp;&nbsp;  </td><td> <?php } ?>
                        <select id="leaveEmployeeSelect" name="leaveEmployeeSelect" dojoType="dijit.form.FilteringSelect"  data-dojo-id="leaveEmployeeSelect"
                                class="filterField roundedLeft"  style="width:200px;"  <?php  echo autoOpenFilteringSelect();?>
                        >
                        <?php 
                              htmlDrawOptionForReference('idEmployee',$idUser,null,true);
                        ?>        
                        </select>
                      <?php if(isNewGui()){?>  </td></tr></table> <?php } ?>
                    </div>
                    <?php }?>
            </td>
            <td style="position:relative;"></td>
        </tr>
    </table>
    
<!--------------------------------->
<!-- LIST OF EXISTING LEAVE TYPE -->
<!--------------------------------->
      <div style="font-size:12px;height:40px;margin-top:10px">
        <?php
        $countLine = 0;
            foreach($leaveTypes as $lvt) {
                $textColor = oppositeColor($lvt->color);
              echo '<span class="leaveType" style="background-color:'.$lvt->color.';color:'.$textColor.';margin:6px">&nbsp;'.$lvt->name.'&nbsp;</span>';
              if ($countLine == round(sizeof($leaveTypes) / 2) - 2)
                  echo "<br>";               
              $countLine += 1;
            }
        ?>
      </div>
    <table style="width:100%; height:100%;text-align:center;">
<!-------------------------------->
<!-- TAG FOR THE LEAVE CALENDAR -->
<!-------------------------------->
        <tr style="height:70%;width:100%;vertical-align:top;border-top:1px solid black;">
            <td colspan="2" style="width:50%;">
                
<!---------------------------->
<!-- TO LOAD LEAVE CALENDAR -->
<!---------------------------->                
                <!--to load the calendar, <script>leaveCalendarDisplay();</script> doesn't seem to work-->
                <img id='leaveCalendarDisplay' style="display: none;" src onerror='leaveCalendarDisplay()'>
<!------------------------------------------------------->
<!-- LIST OF STATUS OF WORKFLOW DEDICATED TO THE LEAVE -->
<!------------------------------------------------------->                
                <div style="margin-top:5px;">
                    <table style="height:2.5%; width:100%;font-size:12px;margin-left:15px;">
                        <tr style="width:100%;">
                        <?php
//                            $listStatus = Workflow::getLeaveMngListStatus();
                              $listStatus = LeaveType::getStatusList();
                            echo '
                                <td>
                                    <span class="leaveStatus" style="background-color:#000000; color:#FFFFFF;">
                                        &nbsp;U
                                    </span>&nbsp;'.i18n("unknown").'
                                </td>
                            ';
                            
                            foreach($listStatus as $key=>$status) {
                                $textColor = oppositeColor($status->color);
                                echo '
                                    <td>
                                        <span class="leaveStatus" style="background-color:'.$status->color.'; color:'.$textColor.';">
                                            &nbsp;'.pq_substr(pq_strtoupper($status->name),0,1).'
                                        </span>&nbsp;'.$status->name.'
                                    </td>
                                ';
                            }
                        ?>
                            <td>
                                <div style="display: inline;">
<!-------------------->
<!-- REFRESH BUTTON -->
<!-------------------->
                                    <button data-dojo-type="dijit/form/Button" 
                                            data-dojo-id="refreshCalendarButton"  <?php if(isNewGui()){ ?> class="dynamicTextButton" <?php } ?>
                                            type="button" 
                                            onclick=""><?php echo i18n("refreshTheCalendar"); ?></button>
<!------------------->
<!-- DATE SELECTOR -->
<!------------------->
                                  <?php if(isNewGui()){?>
                                    <input type="text"  id="widgetSelectDate"  data-dojo-id='widgetSelectDate'   data-dojo-type="dijit.form.DateTextBox" style="width:90px; text-align: center;" class="input roundedLeft"
                                          hasDownArrow="false" 
                                   <?php  if (sessionValueExists('browserLocaleDateFormatJs')) {
  							                             echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
  						                            }?>
                                           data-dojo-props="invalidMessage: '<?php echo i18n('invalidDate'); ?> !'"/>
                                           <?php }else{ ?>
                                       <input type="text"  id="widgetSelectDate"  data-dojo-id='widgetSelectDate'   data-dojo-type="dijit.form.DateTextBox" class="roundedLeft" 
                                   <?php  if (sessionValueExists('browserLocaleDateFormatJs')) {
  							               echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
  						            }?>
                                           data-dojo-props="invalidMessage: '<?php echo i18n('invalidDate'); ?> !'"/>
                                           <?php } ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
<?php 
$scrHeight=RequestHandler::getValue('destinationHeight');
$height=(isNewGui())?($scrHeight-140).'px':'90%';
?>                
                <div style="position:relative;width:100%;height:<?php echo $height;?>;left:8px;">
                    <div id="calendarNode"></div>                        
                </div>
<!-------------------------------------------------------------->
<!-- THE DIALOG BOX TO CREATE OR EDIT A LEAVE IN THE CALENDAR -->
<!-------------------------------------------------------------->                    
                <div id="leavePopup" 
                     data-dojo-type="dijit.Dialog" 
                     title="<?php echo i18n('leaveAttributes'); ?>">                        
                     <!-- leaveAttributes = Leave characteristics -->
                    <table>
                        <tr>
                            <td>
                                <input type='hidden' data-dojo-type="dijit/form/TextBox"  id='popupLeaveId' />
                                <label for='popupLeaveType'><?php echo i18n("colType");?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td>   
                                <?php
                                    $onChange  = "getWorkflowStatusesOfLeaveType('fromLeaveCalendar',dijit.byId('popupLeaveType').value);";
                                ?>
                                <select id='popupLeaveType' 
                                        dojoType='dijit.form.Select'
                                        onchange="<?php echo $onChange; ?>;"
                                        required>
                                </select>
                            </td>
                            <td style="float:left;margin-top:10px;">
                                <label for='popupStatus'><?php echo i18n("colIdStatus");?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td style="float:left;">
                                  <select id='popupStatus' 
                                        data-dojo-type='dijit.form.Select' 
                                        onchange="changesPopupStatus();calculateNbRemainingDays('fromLeaveCalendar',<?php echo $idUser; ?>, <?php echo $idUser; ?>);"
                                        required>
                                </select>
                            </td>
                              
                        </tr>
                        <tr>
                            <td>
                                <label for='popupStartDate'><?php echo i18n("colStartDate"); ?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td>
                                <input type='text' id="popupStartDate" 
                                       data-dojo-type="dijit/form/DateTextBox"
                                       <?php  if (sessionValueExists('browserLocaleDateFormatJs')) {
  							               echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
  						                }?>
                                       required="true" onchange="calculateHalfDaysForLeave('popupStartDate', 'popupEndDate', 'popupStartAM', 'popupStartPM', 'popupEndAM', 'popupEndPM', 'popupNbDays',<?php echo $idUser; ?>, <?php echo $idUser; ?>);calculateNbRemainingDays('fromLeaveCalendar',<?php echo $idUser; ?>, <?php echo $idUser; ?>);"/>
                            </td>

                            <td style="float:left;margin-top:9px;">
                                <label for="popupEndDate"><?php echo i18n("colEndDate"); ?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td style="float:left;">   
                                <input type="text" id="popupEndDate"
                                       data-dojo-type="dijit/form/DateTextBox"
                                       <?php  if (sessionValueExists('browserLocaleDateFormatJs')) {
  							               echo ' constraints="{datePattern:\''.getSessionValue('browserLocaleDateFormatJs').'\'}" ';
  						                }?>
                                       required="true" onchange="calculateHalfDaysForLeave('popupStartDate', 'popupEndDate', 'popupStartAM', 'popupStartPM', 'popupEndAM', 'popupEndPM', 'popupNbDays',<?php echo $idUser; ?>, <?php echo $idUser; ?>);calculateNbRemainingDays('fromLeaveCalendar',<?php echo $idUser; ?>, <?php echo $idUser; ?>);"/>
                            </td>
                        </tr>
                         <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr>
                            <td>
                                <label for="popupStartAM" style=";">
                                <input data-dojo-type="dijit/form/CheckBox" id="popupStartAM" checked onchange="changesPopupStartAM();">
                                <?php echo strtolower(i18n("morning")); ?></label>
                            </td>
                            <td>    
                                <label for="popupStartPM" style=";">
                                <input data-dojo-type="dijit/form/CheckBox" id="popupStartPM" onchange="changesPopupStartPM();">
                                 <?php echo strtolower(i18n("afternoon")); ?></label>
                            </td>
                            <td style="float:left;margin-top:9px;">
                                <label for="popupEndAM" style=";">
                                <input data-dojo-type="dijit/form/CheckBox" id="popupEndAM"  onchange="changesPopupEndAM();">
                                <?php echo strtolower(i18n("morning")); ?></label>
                            </td>
                            <td style="float:left;margin-top:9px;">
                                <label for="popupEndPM">
                                <input data-dojo-type="dijit/form/CheckBox" id="popupEndPM" checked onchange="changesPopupEndPM();">
                                <?php echo strtolower(i18n("afternoon")); ?></label>
                            </td>
                        </tr>
                         <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr>
                            <td>
                                <label for="popupNbDays"><?php echo i18n("colDays"); ?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td> 
                                <input type="text" id="popupNbDays" value="0"
                                       data-dojo-type="dijit/form/TextBox"
                                       readonly/>
                            </td>
                            <td style="float:left;margin-top:9px;">
                                   <label id="labelPopupNbRemainingDays" for="popupNbRemainingDays"><?php echo i18n("colLeft"); ?><?php echo Tool::getDoublePoint();?></label>
                             </td>
                             <td style="float:left;">
                                   <input type="text" id="popupNbRemainingDays" value=""
                                       data-dojo-type="dijit/form/TextBox"
                                       readonly/>
                            </td>  
                        </tr>
                        <tr>
                            <td>
                                <label for="popupReason"><?php echo i18n("colReason"); ?><?php echo Tool::getDoublePoint();?></label>
                            </td>
                            <td colspan="2">   
                                <input type="text" id="popupReason" data-dojo-type="dijit/form/TextBox" style="width: 40em;" data-dojo-props="maxLength:255"/>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                    <tr>
                            <td colspan="4" style="text-align:center;">
                              <button style="float:left;" data-dojo-type="dijit/form/Button" data-dojo-id="cancelButtonCalendarPopup" type="button"><?php echo i18n("buttonCancel"); ?></button>
                              <button data-dojo-type="dijit/form/Button" data-dojo-id="deleteButtonCalendarPopup" type="button"><?php echo i18n("buttonErase"); ?></button>
                              <button style="float:right;" data-dojo-type="dijit/form/Button" data-dojo-id="validateButtonCalendarPopup" type="button"><?php echo i18n("buttonValid"); ?></button>
                              <button style="float:right;" data-dojo-type="dijit/form/Button" data-dojo-id="validateAndSubmittedButtonCalendarPopup" type="button"><?php echo i18n("buttonValidAndSubmit"); ?></button>
                            </td>
                        </tr>
                        
                    </table>
                </div>
            </td>
            <td colspan="2" style="width:50%;">

            <?php
                // The actual contract for employee
                $critArrayEmpContract=array("idEmployee"=>(string)$idUser,"idle"=>'0');
                $userEmpContract = SqlElement::getFirstSqlElementFromCriteria("EmploymentContract", $critArrayEmpContract);
            ?>    
<!--------------------------------------------------------------->
<!-- CUSTOM QUANTITY OF LEAVE EARNED FOR THE SELECTED EMPLOYEE -->
<!--------------------------------------------------------------->
              <div id="customLeaveEarnedOfEmployee">
                  <?php
                    $echo = "<b>".i18n("extraActualLeaveEarned")."</b>";
                    $echo .= '<table style="width:96%; margin-left:2%; text-align:center; border: solid 1pt;">';
                    $echo .= '    <tr style="border: solid 1pt; height: 20px;">';
                    $echo .= '        <th style="text-align:center;"><b>'.i18n('leaveType').'</b></th>';
                    $echo .= '        <th style="text-align:center;"><b>'.i18n('colName').'</b></th>';
                    $echo .= '        <th style="text-align:center;"><b>'.i18n('colQuantity').'</b></th>';
                    $echo .= '    </tr>';
                    // The leave types
                    $find=false;
                    foreach($leaveTypes as $lvt) {
                        // Leave Type
                        $textColor = oppositeColor($lvt->color);

                        // Custom quantity
                        $critArrayCustom = array("idLeaveType"=>(string)$lvt->id,"idEmploymentContractType"=>(string)$userEmpContract->idEmploymentContractType);
                        $custom = new CustomEarnedRulesOfEmploymentContractType();
                        $customs = $custom->getSqlElementsFromCriteria($critArrayCustom);
                        if (!empty($customs)) {
                            $find=true;
                            $echo .= '<tr style="border: solid 1pt; height: 20px;">';
                            $echo .= '<td style="background-color:'.$lvt->color.';color:'.$textColor.';">'.$lvt->name.'</td>';
                            $first=true;
                            foreach ($customs as $custom) {
                                if (!$first) {
                                    $echo .= '<tr style="border: solid 1pt; height: 20px;">';
                                    $echo .= '<td style="text-align:right;" colspan="2">'.$custom->name.'</td>';
                                } else {
                                    $echo .= '<td>'.$custom->name.'</td>';
                                }    
                                $echo .= '<td>'.getCustomLeaveEarnedQuantity($custom).'</td>';
                                if (!$first) {
                                    $echo .= '</tr>';
                                }
                                $first=false;
                            }
                            $echo .= '</tr>';
                        }
                    }
                    $echo .= '</table>';
                    if ($find) {echo $echo;}
                  ?>
              </div>

<!------------------------------------------------------------------------>
<!-- SYNTHESIS OF LEAVE EARNED AND LEFT LEAVE FOR THE SELECTED EMPLOYEE -->
<!------------------------------------------------------------------------>
              <br/>
              <div id="summaryLeaveEarnedOfEmployee" style="font-size:12px;margin-bottom:5px;"><b style="margin-bottom:5px"><?php echo i18n("synthesisOfLeaveEarned"); ?></b>
                  <table style="width:96%; margin-left:2%; text-align:center; border: solid 1pt;">
                    <tr style="border: solid 1pt; height: 20px;">
                        <th style="text-align:center;"><b><?php echo i18n('colIdLeaveType'); ?></b></th>
                        <th style="text-align:center;"><b><?php echo pq_str_replace(' - ', '<br/>', i18n('colPeriodDuration')); ?></b></th>
                        <th style="text-align:center;"><b><?php echo lcfirst(i18n('leavePeriod')); ?></b></th>
                        <th style="text-align:center;"><b><?php echo i18n('colEarned'); ?></b></th>
                        <th style="text-align:center;"><b><?php echo i18n('recorded'); ?></b></th>
                        <th style="text-align:center;"><b><?php echo i18n('taken'); ?></b></th>
                        <th style="text-align:center;"><b><?php echo i18n('colLeft'); ?></b></th>
                        <th style="text-align:center;"><b><?php echo i18n('earnedPeriodPlusOne'); ?></b></th>
                    </tr>
                    
                    <?php
                        // The leave earned for employee
                        $leaveEarned=new Leave();
                        $lvsEarned = EmployeeLeaveEarned::getList(null,$idUser); 
                        $showClosedPeriods=(isset($paramShowClosedLeavePeriods) and $paramShowClosedLeavePeriods==true)?true:false;
                        // The contract for employee
                        $critContract=array("idEmployee"=>$idUser,"idle"=>"0");
                        $empContract = SqlElement::getFirstSqlElementFromCriteria("EmploymentContract",$critContract);
                        if (!isset($empContract->id)) {
                            $endDateContract = null;
                        } else {
                            $endDateContract = $empContract->endDate;
                        }
                        $validLeaves = [];
                        $employementContract = new EmploymentContract();
                        $userContractType = $employementContract->getSqlElementsFromCriteria(array("idEmployee"=>$idUser));
                    
                        $leaveTypeOfEmployment = new LeaveTypeOfEmploymentContractType();
                        if ($userContractType) {
                            $leaveTypeArray = $leaveTypeOfEmployment->getSqlElementsFromCriteria(array("idEmploymentContractType" => $userContractType[0]->idEmploymentContractType));
                            $leaveType = new LeaveType();
                            $leaveArray = $leaveType->getSqlElementsFromCriteria(array());
                            foreach ($leaveArray as $array)
                                foreach ($leaveTypeArray as $typeArray)
                                    if ($array->id == $typeArray->idLeaveType)
                                        array_push($validLeaves, $array->id);
                        }
                        
                        $quantity = 0;
//                         $daysLeft = 0;
//                         $daysTaken = 0;
                        $recorded=0;
                        $taken=0;
                        $left=0;
                        $alreadySummed=0;
                        foreach($lvsEarned as $lve) {
                            // Leave Type
                            $showLine=($showClosedPeriods or $lve->idle==0)?true:false;
                            if (!in_array($lve->idLeaveType, $validLeaves)) continue;
                            $lineColor=($lve->idle)?'background-color:#c0c0c0;':'';
                            $lvt=new LeaveType($lve->idLeaveType);
                            $textColor = oppositeColor($lvt->color);
                            if ($showLine) echo '<tr style="border: solid 1pt; height: 20px;'.$lineColor.'">';
                            if ($showLine) echo '<td style="background-color:'.$lvt->color.';color:'.$textColor.';">'.$lvt->name.'</td>';

                            // Period duration
                            $critArrayLvTypeOf = array("idLeaveType"=>(string)$lvt->id,"idEmploymentContractType"=>(string)$userEmpContract->idEmploymentContractType);
                            $lvTypeOfEmpContractType = SqlElement::getFirstSqlElementFromCriteria("LeaveTypeOfEmploymentContractType", $critArrayLvTypeOf);
                            if($lvTypeOfEmpContractType->periodDuration){
                                if ($showLine) echo '<td>'.$lvTypeOfEmpContractType->periodDuration.' '.i18n( ($lvTypeOfEmpContractType->periodDuration<=1 ? 'colMonth':'colMonths') ).'</td>';
                            }else{
                                if ($showLine) echo '<td> - </td>';
                            }
                            // Period
                            if ($lve->startDate==null) {
                                $theStartDate = "";
                            } else {
                                $theStartDate = (new DateTime($lve->startDate))->format("d/m/Y");
                            }
                            if ($lve->endDate==null) {
                                $theEndDate = "";
                            } else {
                                if ($endDateContract!=null) {
                                    $theEndDate = (new DateTime($endDateContract))->format("d/m/Y");                                    
                                } else {
                                    $theEndDate = (new DateTime($lve->endDate))->format("d/m/Y");
                                }    
                            }
                            if ($showLine) echo '<td>'.$theStartDate.'<br/>'.$theEndDate.'</td>';
                            
                            //to calculate the number of days already taken in this period and the total left
                            if($lve->quantity){
                                $quatity=number_format($lve->quantity+0,1);
                                $where="idEmployee=$idUser and idLeaveType=$lve->idLeaveType and accepted=1 and startDate <='".date("Y-m-d")."'";
                                $sumLeave= $leaveEarned->sumSqlElementsFromCriteria('nbDays',null,$where);
                                $recorded=number_format($lve->quantity - $lve->leftQuantity - $lve->leftQuantityBeforeClose, 1);
                                if($recorded==0)$sumLeave=0;
                                $tmpTaken=$sumLeave-$alreadySummed;
                                if ($tmpTaken>$lve->quantity) {
                                  $tmpTaken=$lve->quantity;
                                }
                                if ($tmpTaken>$recorded) {
                                  $tmpTaken=$recorded;
                                }
                                $alreadySummed+=$tmpTaken;
                                //$taken=$sumLeave;
                                $left=number_format($lve->leftQuantity, 1);
                                if ($showLine) echo '<td>'.htmlDisplayNumericWithoutTrailingZeros($quatity).'</td>';
                                if ($showLine) echo '<td>'.htmlDisplayNumericWithoutTrailingZeros($recorded).'</td>';
                                if ($showLine) echo '<td>'.htmlDisplayNumericWithoutTrailingZeros($tmpTaken).'</td>';
                                if ($showLine) echo '<td>'.htmlDisplayNumericWithoutTrailingZeros($left).'</td>';
                            } else {
                                $recorded=" - ";
                                $taken=" - ";
                                if($lve->poseWithoutRights==1){
                                  $where="idEmployee=$idUser and idLeaveType=$lve->idLeaveType and accepted=1 and startDate <='".date("Y-m-d")."'";
                                  $whereRecorded="idEmployee=$idUser and idLeaveType=$lve->idLeaveType and rejected<>1";
                                  $recorded=$leaveEarned->sumSqlElementsFromCriteria('nbDays',null,$whereRecorded);
                                  $taken=$leaveEarned->sumSqlElementsFromCriteria('nbDays',null,$where);
                                }
                                if ($showLine) echo '<td> - </td>';
                                if ($showLine) echo '<td id="summaryRecordedDays'.$lve->id.'">'.$recorded.'</td>';
                                if ($showLine) echo '<td id="summaryTakenDays'.$lve->id.'">'.$taken.'</td>';
                                if ($showLine) echo '<td id="summaryLeftDays'.$lve->id.'"> - </td>';
                            }
                            // to calculate the number of days due for the next period
                            $right = $lve->getLeavesRight(true,false);
                            if ($right['quantity']) {
                                if ($lve->leftQuantity<0) {
                                    $theQuantity = max(0,$right["quantity"]+$lve->leftQuantity);
                                } else {
                                    $theQuantity = $right["quantity"];
                                }
                                if ($showLine) echo '<td>'.htmlDisplayNumericWithoutTrailingZeros($theQuantity).'</td>';
                            } else {
                                if ($showLine) echo '<td> - </td>';                                
                            }
                            if ($showLine) echo '</tr>';
                        }
                    ?>

                </table>
              </div>
            </td>
            
        </tr>
<!--        
        <tr style="height:30%; width:100%;">
            <td style="width: 25%;border: solid 1pt;">MES ACTIVITES
            </td>
            
            <td style="width: 25%; border: solid 1pt;">MES PROCHAINES ECHEANCES
            </td>
            
            <td style="width: 25%; border: solid 1pt;">MES AVANTAGES
            </td>
            
            <td style="width: 25%; border: solid 1pt;">MES FRAIS
            </td>
            
        </tr>
            
-->        
    </table>
    <!-- end of Content -->
    
  </div>
</div>
<?php
  //include_once "../view/leaveCalendarPopupErrorAndResult.php";
