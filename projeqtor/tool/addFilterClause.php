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

/** ===========================================================================
 * Save a note : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */
require_once "../tool/projeqtor.php";

$user=getSessionUser();
$comboDetail=false;
if (array_key_exists('comboDetail',$_REQUEST)) {
  $comboDetail=true;
}
if (! $comboDetail and ! $user->_arrayFilters) {
  $user->_arrayFilters=array();
} else if ($comboDetail and ! $user->_arrayFiltersDetail) {
  $user->_arrayFiltersDetail=array();
}

// Get the filter info
if (! array_key_exists('idFilterAttribute',$_REQUEST)) {
  throwError('idFilterAttribute parameter not found in REQUEST');
}
$idFilterAttribute=$_REQUEST['idFilterAttribute'];
$idFilterAttribute=preg_replace('/[^a-zA-Z0-9_]/','', pq_nvl($idFilterAttribute)); // Note: may need to be more permissive.

if (! array_key_exists('idFilterOperator',$_REQUEST)) {
  throwError('idFilterOperator parameter not found in REQUEST');
}
$idFilterOperator=$_REQUEST['idFilterOperator'];
// TODO (SECURITY) : test completness of test
//CHANGE qCazelles - Dynamic filter - Ticket #78
//Old
//if (preg_match('/^(([<>]|<>)?=|(NOT )?LIKE|hasSome|(NOT )?IN|is(Not)?Empty|<>|SORT|[<>]=now\+)$/', $idFilterOperator) != true) {
//New - Add of startBy
if (preg_match('/^(([<>]|<>)?=|(NOT )?LIKE|hasSome|(NOT )?IN|is(Not)?Empty|<>|<|>|SORT|[<>]=now\+|startBy)$/', pq_nvl($idFilterOperator)) != true) {
//END CHANGE qCazelles - Dynamic filter - Ticket #78
	traceHack("bad value for idFilterOperator ($idFilterOperator)");
	exit;
}

if (! array_key_exists('filterDataType',$_REQUEST)){
  throwError('filterDataType parameter not found in REQUEST');
}
$filterDataType=$_REQUEST['filterDataType'];
// TODO (SECURITY) : test completness of test
if (preg_match('/^(list|decimal|int|date|time|bool|refObject|varchar)$/', pq_nvl($filterDataType)) != true){
	traceHack("bad value for filterDataType ($filterDataType)");
	exit;
}


if (! array_key_exists('filterValue',$_REQUEST)) {
  throwError('filterValue parameter not found in REQUEST');
}
$filterValue=$_REQUEST['filterValue']; // Note: value is checked before use depending on context.

if (array_key_exists('filterValueList',$_REQUEST)) {
  $filterValueList=$_REQUEST['filterValueList']; // key => value pairs  - are escaped before use.
} else {
  $filterValueList=array();
}

$FilterCompareAttribute=RequestHandler::getValue('FilterCompareAttribute');

if (! array_key_exists('filterValueDate',$_REQUEST)) {
  throwError('filterValueDate parameter not found in REQUEST');
}
$filterValueDate=$_REQUEST['filterValueDate'];
Security::checkValidDateTime($filterValueDate);

if (! array_key_exists('filterValueCheckbox',$_REQUEST)) {
  $filterValueCheckbox=false;
} else {
  $filterValueCheckbox=true;
}

if (! array_key_exists('filterSortValueList',$_REQUEST)) {
  throwError('filterSortValueList parameter not found in REQUEST');
}
$filterSortValue=$_REQUEST['filterSortValueList']; 
Security::checkValidAlphanumeric($filterSortValue);

//ADD qCazelles - Dynamic filter - Ticket #78
if (! array_key_exists('orOperator',$_REQUEST)){
	throwError('orOperator parameter not found in REQUEST');
}
$orOperator=$_REQUEST['orOperator'];

if (! array_key_exists('filterDynamicParameter', $_REQUEST)) {
	$filterDynamicParameter=false;
}
else {
	$filterDynamicParameter=true;
}

if (! array_key_exists('filterCompareParameter', $_REQUEST)) {
  $filterCompareParameter=false;
}
else {
  $filterCompareParameter=true;
}
//END ADD qCazelles - Dynamic filter - Ticket #78

if (! array_key_exists('filterObjectClass',$_REQUEST)) {
  throwError('filterObjectClass parameter not found in REQUEST');
}
$filterObjectClass=$_REQUEST['filterObjectClass'];
$objectClass=($filterObjectClass=='Planning' or $filterObjectClass=='GlobalPlanning' or $filterObjectClass=='VersionsPlanning' or $filterObjectClass=='ResourcePlanning')?'Activity':$filterObjectClass;
$objectClass=(pq_substr($objectClass,0,7)=='Report_')?pq_substr($objectClass,7):$objectClass;
Security::checkValidClass($objectClass);

$name="";
if (array_key_exists('filterName',$_REQUEST)) {
  $name=$_REQUEST['filterName'];
}
pq_trim($name); // Note: filtered before use using htmlEncode()

$idLayout="";
if (array_key_exists('filterLayout',$_REQUEST)) {
  $idLayout=$_REQUEST['filterLayout'];
}

// Get existing filter info
if (!$comboDetail and array_key_exists($filterObjectClass,$user->_arrayFilters)) {
  $filterArray=$user->_arrayFilters[$filterObjectClass];
} else if ($comboDetail and array_key_exists($filterObjectClass,$user->_arrayFiltersDetail)) {
  $filterArray=$user->_arrayFiltersDetail[$filterObjectClass];
} else {
  $filterArray=array();
}

$obj=new $objectClass();
global $hideScope;
$hideScope="column";
// Add new filter
if ($idFilterAttribute and $idFilterOperator) {
  $arrayDisp=array();
  $arraySql=array();
  $dataType=$obj->getDataType($idFilterAttribute);
  $dataLength=$obj->getDataLength($idFilterAttribute);
  $foreignKey=null;
  $foreignKeyName=null;
  $pos=pq_strpos($idFilterAttribute, "__id");
  if ($pos>0) {
    $dataType='int';
    $dataLength='12';
    $foreignKey=foreignKeyWithoutAlias($idFilterAttribute);
    $foreignKeyName=i18n(pq_substr($idFilterAttribute,0,$pos));
    if (pq_substr($foreignKeyName, 0,1)=='[') {
      $obj=new $objectClass();
      $foreignKeyName=$obj->getColCaption($idFilterAttribute);
    }
    //$idFilterAttribute=$foreignKey;
  }
  $split=pq_explode('_',$idFilterAttribute);
  if (count($split)>1 and !$foreignKey) {
  	$externalClass=$split[0];
    $externalObj=new $externalClass();
    $foreignKey=$split[1];
    $foreignKeyName=$externalObj->getColCaption($split[1]);
    $arrayDisp["attribute"]=$externalObj->getColCaption($split[1]);
  } else {
  	//echo  $idFilterAttribute . "=>" . $obj->getColCaption($idFilterAttribute);
    if (pq_substr($idFilterAttribute,0,9)=='idContext') {
      $arrayDisp["attribute"]=SqlList::getNameFromId('ContextType',pq_substr($idFilterAttribute,9));
    } else {
      if ($foreignKeyName) $arrayDisp["attribute"]=$foreignKeyName;
      else $arrayDisp["attribute"]=$obj->getColCaption($idFilterAttribute);
    }
  }
  $arraySql["attribute"]=$obj->getDatabaseColumnName($idFilterAttribute);
  if ($idFilterOperator=="=" or $idFilterOperator==">=" or $idFilterOperator=="<=" or $idFilterOperator==">" or $idFilterOperator=="<" or $idFilterOperator=="<>") {
    $arrayDisp["operator"]=$idFilterOperator;
    $arraySql["operator"]=$idFilterOperator;
    if( $filterCompareParameter ){
      $splitCompare=pq_explode('_',$FilterCompareAttribute);
      if(isset($splitCompare[1])){
        $arrayDisp["value"]=$obj->getColCaption($splitCompare[1]);
      }else{
        $arrayDisp["value"]=$obj->getColCaption($FilterCompareAttribute);
      }
      if (count(explode('_',$FilterCompareAttribute))>1 and count(explode('_',$arraySql["attribute"]))==1) { // must revert attribute and value      
        $arraySql["value"]="[" . $arraySql["attribute"] . "]";
        $arraySql["attribute"]=pq_trim(Sql::str(htmlEncode($FilterCompareAttribute)),"'");
        if ($idFilterOperator==">=") $arraySql["operator"]="<=";
        else if ($idFilterOperator==">") $arraySql["operator"]="<";
        else if ($idFilterOperator=="<=") $arraySql["operator"]=">=";
        else if ($idFilterOperator=="<") $arraySql["operator"]=">";
      } else {
        $arraySql["value"]="[" . pq_trim(Sql::str(htmlEncode($FilterCompareAttribute)),"'") . "]";
      }
    }else if ($filterDataType=='date') {
      if ($idFilterOperator=="<=") {
        $arrayDisp["value"]="'" . htmlFormatDate($filterValueDate) . " 23:59:59'";
        $arraySql["value"]="'" . $filterValueDate . " 23:59:59'";
      } else if ($idFilterOperator=="=") {
        $arraySql["operator"]="BETWEEN";
        $arrayDisp["value"]="'" . htmlFormatDate($filterValueDate) . "'";
        $arraySql["value"]="'" . $filterValueDate . "' AND '" . $filterValueDate . " 23:59:59'";
      } else if ($idFilterOperator=="<>") { 
        $arraySql["operator"]="NOT BETWEEN";
        $arrayDisp["value"]="'" . htmlFormatDate($filterValueDate) . "'";
        $arraySql["value"]="'" . $filterValueDate . "' AND '" . $filterValueDate . " 23:59:59'";
      } else {
        $arrayDisp["value"]="'" . htmlFormatDate($filterValueDate) . "'";
        $arraySql["value"]="'" . $filterValueDate . "'";
      }
    }else if ($filterDataType=='bool') {
        $arrayDisp["value"]=($filterValueCheckbox)?i18n("displayYes"):i18n("displayNo");
        $arraySql["value"]=($filterValueCheckbox)?1:0;
    } else if ($filterDataType=='decimal' or $filterDataType=='numeric' or $filterDataType=='int') {
      $arrayDisp["value"]=floatval($filterValue);
      $arraySql["value"]=floatval($filterValue);
    }else {
      $arrayDisp["value"]="'" . htmlEncode($filterValue) . "'";
      $arraySql["value"]="'" . pq_trim(Sql::str(htmlEncode($filterValue)),"'") . "'";
    }
  } else if ($idFilterOperator=="LIKE" or $idFilterOperator=="hasSome") {
  	if ($filterDataType=='refObject' or $idFilterOperator=="hasSome") {
  		$arraySql["operator"]=' exists ';
  		if ($idFilterOperator=="hasSome") { 
  			$filterValue="";
  			$arrayDisp["value"]="";
  			$arrayDisp["operator"]=i18n("isNotEmpty");
  		} else {
  			$arrayDisp["operator"]=i18n("contains");
  			$arrayDisp["value"]="'" . pq_trim(Sql::str(htmlEncode($filterValue)),"'") . "'";
  		}
		  Security::checkValidClass($idFilterAttribute);
  		$refObj=new $idFilterAttribute();
  		$refObjTable=$refObj->getDatabaseTableName();
  		$table=$obj->getDatabaseTableName();
  		$arraySql["value"]=" ( select 'x' from $refObjTable "
  		. " where $refObjTable.refType=".Sql::str($objectClass)." "
  		. " and $refObjTable.refId=$table.id "
  		. " and $refObjTable.note ".((Sql::isMysql())?'LIKE':'ILIKE')." '%" . pq_trim(Sql::str(htmlEncode($filterValue)),"'") . "%' ) ";
  	} else {
      $arrayDisp["operator"]=i18n("contains");
      $arraySql["operator"]=(Sql::isMysql())?'LIKE':'ILIKE';
      $arrayDisp["value"]="'" . htmlEncode($filterValue) . "'";
      $arraySql["value"]="'%" . pq_trim(Sql::str(htmlEncode($filterValue)),"'") . "%'";
  	}
  } else if ($idFilterOperator=="NOT LIKE") {
    $arrayDisp["operator"]=i18n("notContains");
    $arraySql["operator"]=(Sql::isMysql())?'NOT LIKE':'NOT ILIKE';
    $arrayDisp["value"]="'" . htmlEncode($filterValue) . "'";
    $arraySql["value"]="'%" . pq_trim(Sql::str(htmlEncode($filterValue)),"'") . "%'";
  } else if ($idFilterOperator=="IN" or $idFilterOperator=="NOT IN") {
    $arrayDisp["operator"]=($idFilterOperator=="IN")?i18n("amongst"):i18n("notAmongst");
    $arraySql["operator"]=$idFilterOperator;
    $arrayDisp["value"]="";
    $arraySql["value"]="(";
    foreach ($filterValueList as $key=>$val) {
      $arrayDisp["value"].=($key==0)?"":", ";
      $arraySql["value"].=($key==0)?"":", ";
      if($idFilterAttribute=="refTypeExpense" or $idFilterAttribute=="refTypeIncome" ){
        $situation= new Situationable($val);
        $arrayDisp["value"].="'".Sql::fmtStr(i18n($situation->name))."'";
        $arraySql["value"].="'".Sql::fmtStr($situation->name)."'";
      }else{
        if ($idFilterAttribute=='idResourceSelect') $idFilterAttribute='idResourceAll';
        $arrayDisp["value"].="'" . Sql::fmtStr(SqlList::getNameFromId(Sql::fmtStr(pq_substr(($foreignKey)?$foreignKey:$idFilterAttribute,2)),$val)) . "'";
        $arraySql["value"].=Security::checkValidId($val);
      }
    }
    //$arrayDisp["value"].=")";
    $arraySql["value"].=")";
    if ($idFilterAttribute=="assignedResource__idResourceAll" and ! $filterDynamicParameter) {
      $arraySql["attribute"]='';
      $arraySql["operator"]=' exists ';
      $ass=new Assignment();
      $assTable=$ass->getDatabaseTableName();
      $obj=new $objectClass();
      $objTable=$obj->getDatabaseTableName();
      $arraySql["value"]="(select 'x' from $assTable where $assTable.refType='$objectClass' and $assTable.refId=$objTable.id and $assTable.idResource $idFilterOperator ".$arraySql["value"].")";
    }
  } else if ($idFilterOperator=="isEmpty") {
      $arrayDisp["operator"]=i18n("isEmpty");
      $arraySql["operator"]="is null";
      $arrayDisp["value"]="";
      $arraySql["value"]="";
      if ($idFilterAttribute=="assignedResource__idResourceAll") {
        $arraySql["attribute"]='';
        $arraySql["operator"]=' not exists ';
        $ass=new Assignment();
        $assTable=$ass->getDatabaseTableName();
        $obj=new $objectClass();
        $objTable=$obj->getDatabaseTableName();
        $arraySql["value"]="(select 'x' from $assTable where $assTable.refType='$objectClass' and $assTable.refId=$objTable.id )";
      }
  } else if ($idFilterOperator=="isNotEmpty") {
      $arrayDisp["operator"]=i18n("isNotEmpty");
      $arraySql["operator"]="is not null";
      $arrayDisp["value"]="";
      $arraySql["value"]="";
      if ($idFilterAttribute=="assignedResource__idResourceAll") {
        $arraySql["attribute"]='';
        $arraySql["operator"]=' exists ';
        $ass=new Assignment();
        $assTable=$ass->getDatabaseTableName();
        $obj=new $objectClass();
        $objTable=$obj->getDatabaseTableName();
        $arraySql["value"]="(select 'x' from $assTable where $assTable.refType='$objectClass' and $assTable.refId=$objTable.id )";
      }
  } else if ($idFilterOperator=="SORT") {
    $arrayDisp["operator"]=i18n("sortFilter");
    $arraySql["operator"]=$idFilterOperator;
    Security::checkValidAlphanumeric($filterSortValue);
    $arrayDisp["value"]=htmlEncode(i18n('sort' . pq_ucfirst($filterSortValue) ));
    $arraySql["value"]=$filterSortValue;
  } else if ($idFilterOperator=="<=now+") {
    $arrayDisp["operator"]="<= " . i18n('today') . (($filterValue>0 or 1)?' +':' ');
    $arraySql["operator"]="<=";
    $arrayDisp["value"]=htmlEncode(intval($filterValue)) . ' ' . i18n('days');
    if (preg_match('/[^\-0-9]/', pq_nvl($filterValue)) == true) {
      $filterValue="";
    }
    if (Sql::isPgsql()) {
      $arraySql["value"]= "NOW() + INTERVAL '" . intval($filterValue) . " day 23 hours 59 minutes'";
    } else {
      $arraySql["value"]= "ADDDATE(addtime(DATE(NOW()), '23:59:59'), INTERVAL (" . intval($filterValue) . ") DAY)";
    }
    if( $filterCompareParameter ){
      $splitCompare=pq_explode('_',$FilterCompareAttribute);
      if(isset($splitCompare[1])){
        $arrayDisp["value"]=$obj->getColCaption($splitCompare[1]);
      }else{
        $arrayDisp["value"]=$obj->getColCaption($FilterCompareAttribute);
      }
      $arraySql["value"]="'" . pq_trim(Sql::str(htmlEncode($FilterCompareAttribute)),"'") . "'";
    }
  } else if ($idFilterOperator==">=now+") {  
    $arrayDisp["operator"]=">= " . i18n('today') . (($filterValue>0 or 1)?' +':' ');
    $arraySql["operator"]=">=";
    $arrayDisp["value"]=htmlEncode(intval($filterValue)) . ' ' . i18n('days');
    if (preg_match('/[^\-0-9]/', pq_nvl($filterValue)) == true) {
      $filterValue="";
    }
    if (Sql::isPgsql()) {
      $arraySql["value"]= "CURRENT_DATE + INTERVAL '" . intval($filterValue) . " day'";
    } else {
      $arraySql["value"]= "ADDDATE(DATE(NOW()), INTERVAL (" . intval($filterValue) . ") DAY)";
    }
    if( $filterCompareParameter ){
          $splitCompare=pq_explode('_',$FilterCompareAttribute);
      if(isset($splitCompare[1])){
        $arrayDisp["value"]=$obj->getColCaption($splitCompare[1]);
      }else{
        $arrayDisp["value"]=$obj->getColCaption($FilterCompareAttribute);
      }
      $arraySql["value"]="'" . pq_trim(Sql::str(htmlEncode($FilterCompareAttribute)),"'") . "'";
    }
    //ADD qCazelles - Dynamic filter - Ticket #78
  } else if ($idFilterOperator=="startBy") {
  	$arrayDisp["operator"]=i18n("startBy");
  	$arraySql["operator"]="LIKE";
  	$arrayDisp["value"]="'".htmlEncode($filterValue)."'";
  	$arraySql["value"]="'".htmlEncode($filterValue)."%'";
    //END ADD qCazelles - Dynamic filter - Ticket #78
  } else {
     echo htmlGetErrorMessage(i18n('incorrectOperator'));
     exit;
  }
  //ADD qCazelles - Dynamic filter - Ticket #78
  if ($filterDynamicParameter) {

  	$arrayDisp["value"] = NULL;
  	$arraySql["value"] = NULL;
  	
  	if ($idFilterOperator=="=" or $idFilterOperator==">=" or $idFilterOperator=="<=" or $idFilterOperator==">" or $idFilterOperator=="<" or $idFilterOperator=="<>" or $idFilterOperator==">=now+" or $idFilterOperator=="<=now+") {
  		if ($filterDataType=='date' and $idFilterOperator!=">=now+" and $idFilterOperator!="<=now+") {
  			$arraySql["value"]='date';
  		}
  		elseif ($filterDataType=='bool') {
  			$arraySql["value"]='bool';
  		}
  		elseif ($idFilterOperator==">=now+" or $idFilterOperator=="<=now+") {
  			$arraySql["value"]='intDate';
  		}
  		else {
  			$arraySql["value"]='int';
  		}
  	}
  	elseif ($idFilterOperator=='startBy') {
  	  $arraySql["value"]='startBy';
  	}
  }
  
  //END ADD qCazelles - Dynamic filter - Ticket #78
  
  //CHANGE qCazelles - Dynamic filter - Ticket #78
  //Old
  //$filterArray[]=array("disp"=>$arrayDisp,"sql"=>$arraySql);
  //New
  $filterArray[]=array("disp"=>$arrayDisp,"sql"=>$arraySql,"isDynamic"=>($filterDynamicParameter ? "1" : "0"),"orOperator"=>$orOperator);
  //END CHANGE qCazelles - Dynamic filter - Ticket #78
  if ($idFilterAttribute=='idle' and $filterValueCheckbox) {
    $arrayDisp["attribute"]=i18n('labelShowIdle');
    $arrayDisp["operator"]="";
    $arrayDisp["value"]="";
    $arraySql["attribute"]='idle';
    $arraySql["operator"]='>=';
    $arraySql["value"]='0';
  }
  
  if (! $comboDetail) {
    $user->_arrayFilters[$filterObjectClass]=$filterArray;
  } else {
  	$user->_arrayFiltersDetail[$filterObjectClass]=$filterArray;
  }
}

//$user->_arrayFilters[$filterObjectClass . "FilterName"]=$name;
if (! $comboDetail) {
  $user->_arrayFilters[$filterObjectClass . "FilterName"]="";
  $user->_arrayFilters[$filterObjectClass . "FilterLayout"]="";
} else {
  $user->_arrayFiltersDetail[$filterObjectClass . "FilterName"]="";	
}
htmlDisplayFilterCriteria($filterArray,$name,$idLayout, $filterObjectClass); 

// save user (for filter saving)
setSessionUser($user);


?>