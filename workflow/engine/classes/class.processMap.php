<?php

/**
 * class.processMap.php
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.23
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * For more information, contact Colosa Inc, 2566 Le Jeune Rd.,
 * Coral Gables, FL, 33134, USA, or email info@colosa.com.
 *
 */
G::LoadThirdParty('pear/json', 'class.json');
G::LoadClass('groups');
G::LoadClass('tasks');
G::LoadClass('xmlfield_InputPM');
G::LoadClass('calendar');


require_once 'classes/model/AppDelegation.php';
require_once 'classes/model/CaseTracker.php';
require_once 'classes/model/CaseTrackerObject.php';
require_once 'classes/model/Configuration.php';
require_once 'classes/model/Content.php';
require_once 'classes/model/DbSource.php';
require_once 'classes/model/Dynaform.php';
require_once 'classes/model/Event.php';
require_once 'classes/model/Groupwf.php';
require_once 'classes/model/InputDocument.php';
require_once 'classes/model/ObjectPermission.php';
require_once 'classes/model/OutputDocument.php';
require_once 'classes/model/Process.php';
require_once 'classes/model/ProcessUser.php';
require_once 'classes/model/ReportTable.php';
require_once 'classes/model/Route.php';
require_once 'classes/model/CaseScheduler.php';
require_once 'classes/model/LogCasesScheduler.php';
require_once 'classes/model/Step.php';
require_once 'classes/model/StepSupervisor.php';
require_once 'classes/model/StepTrigger.php';
require_once 'classes/model/SubProcess.php';
require_once 'classes/model/SwimlanesElements.php';
require_once 'classes/model/Task.php';
require_once 'classes/model/TaskUser.php';
require_once 'classes/model/Triggers.php';
require_once 'classes/model/Users.php';

/**
 * processMap - Process Map class
 * @package ProcessMaker
 * @author Julio Cesar Laura Avendano
 * @copyright 2007 COLOSA
 */
class processMap {
  /*
   * Load the process map data
   * @param string $sProcessUID
   * @param boolean $bView
   * @param string $sApplicationUID
   * @param integer $iDelegation
   * @param string $sTask
   * @return string
   */

  function load($sProcessUID, $bView = false, $sApplicationUID = '', $iDelegation = 0, $sTask = '', $bCT = false) {
    try {
      $oProcess = new Process ( );

      $aRow = $oProcess->load($sProcessUID);
      $oPM->title->label = strip_tags($aRow ['PRO_TITLE']);
      $oPM->title->position->x = $aRow ['PRO_TITLE_X'];
      $oPM->title->position->y = $aRow ['PRO_TITLE_Y'];
      $oPM->task = array();
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(TaskPeer::PRO_UID);
      $oCriteria->addSelectColumn(TaskPeer::TAS_UID);
      $oCriteria->addSelectColumn(ContentPeer::CON_VALUE);
      $oCriteria->addSelectColumn(TaskPeer::TAS_START);
      $oCriteria->addSelectColumn(TaskPeer::TAS_POSX);
      $oCriteria->addSelectColumn(TaskPeer::TAS_POSY);
      $oCriteria->addSelectColumn(TaskPeer::TAS_COLOR);
      $oCriteria->addSelectColumn(TaskPeer::TAS_TYPE);
      $aConditions = array();
      $aConditions [] = array(0 => TaskPeer::TAS_UID, 1 => ContentPeer::CON_ID);
      $aConditions [] = array(0 => ContentPeer::CON_CATEGORY, 1 => DBAdapter::getStringDelimiter () . 'TAS_TITLE' . DBAdapter::getStringDelimiter ());
      $aConditions [] = array(0 => ContentPeer::CON_LANG, 1 => DBAdapter::getStringDelimiter () . SYS_LANG . DBAdapter::getStringDelimiter ());
      $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
      $oCriteria->add(TaskPeer::PRO_UID, $sProcessUID);
      $oDataset = TaskPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow1 = $oDataset->getRow()) {
        $oTask = null;
        $oTask->uid = $aRow1 ['TAS_UID'];
        $oTask->task_type = $aRow1 ['TAS_TYPE'];
        if ($aRow1 ['TAS_TYPE'] == 'NORMAL') {
          if (($aRow1 ['CON_VALUE'] == "")) { //There is no Label in Current SYS_LANG language so try to find in English - by default
            $oTask1 = new Task ( );
            $aFields1 = $oTask1->load($oTask->uid);
            $aRow1 ['CON_VALUE'] = $oTask1->getTasTitle();
          }
          $oTask->label = strip_tags($aRow1 ['CON_VALUE']);
        } else {
          $oCriteria = new Criteria('workflow');
          $del = DBAdapter::getStringDelimiter ();
          $oCriteria->add(SubProcessPeer::PRO_PARENT, $aRow1 ['PRO_UID']);
          $oCriteria->add(SubProcessPeer::TAS_PARENT, $aRow1 ['TAS_UID']);

          $oCriteria->addAsColumn('TAS_TITLE', 'C1.CON_VALUE');
          $oCriteria->addAlias("C1", 'CONTENT');
          $tasTitleConds = array();
          $tasTitleConds [] = array(SubProcessPeer::TAS_PARENT, 'C1.CON_ID');
          $tasTitleConds [] = array('C1.CON_CATEGORY', $del . 'TAS_TITLE' . $del);
          $tasTitleConds [] = array('C1.CON_LANG', $del . SYS_LANG . $del);
          $oCriteria->addJoinMC($tasTitleConds, Criteria::LEFT_JOIN);

          $oDatasetX = SubProcessPeer::doSelectRS($oCriteria);
          $oDatasetX->setFetchmode(ResultSet::FETCHMODE_ASSOC);
          $oDatasetX->next();
          $aRowx = $oDatasetX->getRow();
          if ($oProcess->exists($aRowx ['PRO_UID'])) {
            //$aRowy = $oProcess->load($aRowx['PRO_UID']);
            //$oTask->label = $aRowy['PRO_TITLE'];
            $oTask->label = $aRowx ['TAS_TITLE'];
          } else {
            $oTask->label = strip_tags($aRow1 ['CON_VALUE']);
          }
        }
        $oTask->taskINI = (strtolower($aRow1 ['TAS_START']) == 'true' ? true : false);
        $oTask->position->x = (int) $aRow1 ['TAS_POSX'];
        $oTask->position->y = (int) $aRow1 ['TAS_POSY'];
        $oTask->derivation = null;
        $oTask->derivation->to = array();
        $oCriteria = new Criteria('workflow');
        $oCriteria->add(RoutePeer::PRO_UID, $sProcessUID);
        $oCriteria->add(RoutePeer::TAS_UID, $aRow1 ['TAS_UID']);
        $oDataset2 = RoutePeer::doSelectRS($oCriteria);
        $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oDataset2->next();
        while ($aRow2 = $oDataset2->getRow()) {
          switch ($aRow2 ['ROU_TYPE']) {
            case 'SEQUENTIAL' :
              $aRow2 ['ROU_TYPE'] = 0;
              break;
            case 'SELECT' :
              $aRow2 ['ROU_TYPE'] = 1;
              break;
            case 'EVALUATE' :
              $aRow2 ['ROU_TYPE'] = 2;
              break;
            case 'PARALLEL' :
              $aRow2 ['ROU_TYPE'] = 3;
              break;
            case 'PARALLEL-BY-EVALUATION' :
              $aRow2 ['ROU_TYPE'] = 4;
              break;
            case 'SEC-JOIN' :
              $aRow2 ['ROU_TYPE'] = 5;
              break;
            case 'DISCRIMINATOR' :
              $aRow2 ['ROU_TYPE'] = 8;
              break;
          }
          $oTo = null;
          $oTo->task = $aRow2 ['ROU_NEXT_TASK'];
          $oTo->condition = $aRow2 ['ROU_CONDITION'];
          $oTo->executant = $aRow2 ['ROU_TO_LAST_USER'];
          $oTo->optional = $aRow2 ['ROU_OPTIONAL'];
          $oTask->derivation->type = $aRow2 ['ROU_TYPE'];
          $oTask->derivation->to [] = $oTo;
          $oDataset2->next();
        }
        if ($bCT) {
          $oCriteria = new Criteria('workflow');
          $oCriteria->addSelectColumn('COUNT(*) AS CANT');
          $oCriteria->addSelectColumn('MIN(DEL_FINISH_DATE) AS FINISH');
          $oCriteria->add(AppDelegationPeer::APP_UID, $sApplicationUID);
          $oCriteria->add(AppDelegationPeer::TAS_UID, $aRow1 ['TAS_UID']);
          $oDataset2 = AppDelegationPeer::doSelectRS($oCriteria);
          $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
          $oDataset2->next();
          $aRow2 = $oDataset2->getRow();
          $oCriteria = new Criteria('workflow');
          $oCriteria->addSelectColumn('DEL_FINISH_DATE');
          $oCriteria->add(AppDelegationPeer::APP_UID, $sApplicationUID);
          $oCriteria->add(AppDelegationPeer::TAS_UID, $aRow1 ['TAS_UID']);
          $oCriteria->add(AppDelegationPeer::DEL_FINISH_DATE, null);
          $oDataset2 = AppDelegationPeer::doSelectRS($oCriteria);
          $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
          $oDataset2->next();
          $aRow3 = $oDataset2->getRow();
          if ($aRow3) {
            $aRow2 ['FINISH'] = '';
          }
          if (($aRow2 ['FINISH'] == null) && ($aRow1 ['TAS_UID'] == $sTask)) {
            $oTask->color = '#FF0000';
          } else {
            if ($aRow2 ['CANT'] != 0) {
              if ($aRow2 ['FINISH'] == null) {
                //$oTask->color = '#FF9900';
                $oTask->color = '#FF0000';
              } else {
                $oTask->color = '#006633';
              }
            } else { //$oTask->color = '#939598';
            }
          }
        } else {
          if ($bView && ($sApplicationUID != '') && ($iDelegation > 0) && ($sTask != '')) {
            $oCriteria = new Criteria('workflow');
            $oCriteria->addSelectColumn('COUNT(*) AS CANT');
            $oCriteria->addSelectColumn('MIN(DEL_FINISH_DATE) AS FINISH');
            $oCriteria->add(AppDelegationPeer::APP_UID, $sApplicationUID);
            $oCriteria->add(AppDelegationPeer::TAS_UID, $aRow1 ['TAS_UID']);
            $oDataset2 = AppDelegationPeer::doSelectRS($oCriteria);
            $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $oDataset2->next();
            $aRow2 = $oDataset2->getRow();
            $oCriteria = new Criteria('workflow');
            $oCriteria->addSelectColumn('DEL_FINISH_DATE');
            $oCriteria->add(AppDelegationPeer::APP_UID, $sApplicationUID);
            $oCriteria->add(AppDelegationPeer::TAS_UID, $aRow1 ['TAS_UID']);
            $oCriteria->add(AppDelegationPeer::DEL_FINISH_DATE, null);
            $oDataset2 = AppDelegationPeer::doSelectRS($oCriteria);
            $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $oDataset2->next();
            $aRow3 = $oDataset2->getRow();
            if ($aRow3) {
              $aRow2 ['FINISH'] = '';
            }
            if (($aRow2 ['FINISH'] == null) && ($aRow1 ['TAS_UID'] == $sTask)) {
              $oTask->color = '#FF0000';
            } else {
              if ($aRow2 ['CANT'] != 0) {
                if ($aRow2 ['FINISH'] == null) {
                  $oTask->color = '#FF9900';
                } else {
                  $oTask->color = '#006633';
                }
              } else {
                $oTask->color = '#939598';
              }
            }
          }
        }

        $msg = array();
        G::LoadClass('derivation');
        $Derivation = new Derivation();
        $users = $Derivation->getAllUsersFromAnyTask($aRow1 ['TAS_UID']);
        $sw_error = false;
        if (count($users) == 0) {
          $sw_error = true;
          $msg[] = G::LoadTranslation('ID_NO_USERS');
        }

        G::LoadClass('ArrayPeer');
        $stepsCriteria = $this->getStepsCriteria($aRow1 ['TAS_UID']);
        $oDatasetSteps = ArrayBasePeer::doSelectRS($stepsCriteria);
        $oDatasetSteps->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oDatasetSteps->next();
        $countDynaform = 0;
        $countOutput = 0;
        $countInput = 0;
        $countExternal = 0;

        while ($aRowSteps = $oDatasetSteps->getRow()) {
          switch ($aRowSteps ['STEP_TYPE_OBJ']) {
            case 'DYNAFORM' :
              $countDynaform++;
              break;
            case 'INPUT_DOCUMENT' :
              $countInput++;
              break;
            case 'OUTPUT_DOCUMENT' :
              $countOutput++;
              break;
            case 'EXTERNAL' :
              $countExternal++;
              break;
          }
          $oDatasetSteps->next();
        }
        $totalSteps = $countDynaform + $countInput + $countOutput + $countExternal;
        if ($totalSteps == 0) {
          $sw_error = true;
          $msg[] = G::LoadTranslation('ID_TASK_NO_STEPS');
        }
        if ($sw_error) {
          $oTask->statusIcons[] = array('label' => implode(",", $msg), 'icon' => '/images/alert.gif', 'message' => implode(", ", $msg), 'url' => '');
        }

        $oPM->task [] = $oTask;
        $oDataset->next();
      }
      $oPM->executant [] = G::LoadTranslation('ID_RULES_AND_USER_GROUPS');
      $oPM->executant [] = G::LoadTranslation('ID_ADD_USER_OF_TASK');
      $oPM->tasExtra [0]->label = '-- ' . G::LoadTranslation('ID_END_OF_PROCESS') . ' --';
      $oPM->tasExtra [0]->uid = 'end';
      $oPM->tasExtra [1]->label = '-- ' . G::LoadTranslation('ID_TAREA_COLGANTE') . ' --';
      $oPM->tasExtra [1]->uid = 'leaf';
      $oPM->guide = array();
      $oPM->text = array();
      $oPM->statusIcons = array();
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(SwimlanesElementsPeer::SWI_UID);
//        $oCriteria->addSelectColumn ( ContentPeer::CON_VALUE );
      $oCriteria->addAsColumn("CON_VALUE", "CASE WHEN CONTENT.CON_VALUE IS NULL THEN (SELECT DISTINCT MAX(A.CON_VALUE) FROM CONTENT A WHERE SWIMLANES_ELEMENTS.SWI_UID=A.CON_ID  ) ELSE CONTENT.CON_VALUE  END ");
      $oCriteria->addSelectColumn(SwimlanesElementsPeer::SWI_TYPE);
      $oCriteria->addSelectColumn(SwimlanesElementsPeer::SWI_X);
      $oCriteria->addSelectColumn(SwimlanesElementsPeer::SWI_Y);
      $aConditions = array();
      $aConditions [] = array(0 => SwimlanesElementsPeer::SWI_UID, 1 => ContentPeer::CON_ID);
      $aConditions [] = array(0 => ContentPeer::CON_CATEGORY, 1 => DBAdapter::getStringDelimiter () . 'SWI_TEXT' . DBAdapter::getStringDelimiter ());
      $aConditions [] = array(0 => ContentPeer::CON_LANG, 1 => DBAdapter::getStringDelimiter () . SYS_LANG . DBAdapter::getStringDelimiter ());
      $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
      $oCriteria->add(SwimlanesElementsPeer::PRO_UID, $sProcessUID);
      $oDataset = SwimlanesElementsPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        switch (strtolower($aRow ['SWI_TYPE'])) {
          case 'line' :
            $oGuide = null;
            $oGuide->uid = $aRow ['SWI_UID'];
            $oGuide->position = ($aRow ['SWI_X'] > 0 ? $aRow ['SWI_X'] : $aRow ['SWI_Y']);
            $oGuide->direction = ($aRow ['SWI_X'] > 0 ? 'vertical' : 'horizontal');
            $oPM->guide [] = $oGuide;
            break;
          case 'text' :
            $oText = null;
            $oText->uid = $aRow ['SWI_UID'];
            $oText->label = strip_tags(($aRow ['CON_VALUE'] != '' ? str_replace(chr(92), '&#92;', str_replace('<', '&lt;', $aRow ['CON_VALUE'])) : '-'));
            // $oText->label       = '->' . $aRow ['CON_VALUE'] . '<-' ;
            $oText->position->x = $aRow ['SWI_X'];
            $oText->position->y = $aRow ['SWI_Y'];
            $oPM->text [] = $oText;
            break;
        }
        $oDataset->next();
      }
      $oPM->derivation = array('Sequential', 'Evaluate (manual)', 'Evaluate (auto)', 'Parallel (fork)', 'Parallel by evaluation (fork)', 'Parallel (sequential join)', 'Parallel (sequential main join)');

      $oJSON = new Services_JSON ( );
      return $oJSON->encode($oPM);
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Create a Process
   * @param array $aData
   * @return boolean
   */

  function createProcess($aData) {
    try {
      $oProcess = new Process ( );
      return $oProcess->create($aData);
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Update a Process
   * @param array $aData
   * @return boolean
   */

  function updateProcess($aData) {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($aData ['PRO_UID']);
      return $oProcess->update($aData);
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Edit the Process Map information
   * @param string $sProcessUID
   * @return boolean
   */

  function editProcess($sProcessUID) {
    try {
      $oProcess = new Process ( );
      if (!is_null($oProcess)) {

        $calendar = new Calendar ( );
        $calendarObj = $calendar->getCalendarList(true, true);

        global $_DBArray;

        $_DBArray ['availableCalendars'] = $calendarObj ['array'];

        $_SESSION ['_DBArray'] = $_DBArray;
        $aFields = $oProcess->load($sProcessUID);
        $aFields ['THETYPE'] = 'UPDATE';
        $calendarInfo = $calendar->getCalendarFor($sProcessUID, $sProcessUID, $sProcessUID);
        //If the function returns a DEFAULT calendar it means that this object doesn't have assigned any calendar
        $aFields ['PRO_CALENDAR'] = $calendarInfo ['CALENDAR_APPLIED'] != 'DEFAULT' ? $calendarInfo ['CALENDAR_UID'] : "";

        global $G_PUBLISH;
        $G_PUBLISH = new Publisher ( );
        $G_PUBLISH->AddContent('xmlform', 'xmlform', 'processes/processes_Edit', '', $aFields, 'processes_Save');
        G::RenderPage('publish', 'raw');
        return true;
      } else {
        throw (new Exception('This row doesn\'t exists!'));
      }
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Delete a Process
   * @param string $sProcessUID
   * @return boolean
   */

  function deleteProcess($sProcessUID) {
    try {
      G::LoadClass('case');
      G::LoadClass('reportTables');
      //Instance all classes necesaries
      $oProcess = new Process ( );
      $oDynaform = new Dynaform ( );
      $oInputDocument = new InputDocument ( );
      $oOutputDocument = new OutputDocument ( );
      $oTrigger = new Triggers ( );
      $oRoute = new Route ( );
      $oSwimlaneElement = new SwimlanesElements ( );
      $oConfiguration = new Configuration ( );
      $oDbSource = new DbSource ( );
      $oReportTable = new ReportTables ( );
      $oCaseTracker = new CaseTracker ( );
      $oCaseTrackerObject = new CaseTrackerObject ( );
      //Delete the applications of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(ApplicationPeer::PRO_UID, $sProcessUID);
      $oDataset = ApplicationPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $oCase = new Cases ( );
      while ($aRow = $oDataset->getRow()) {
        $oCase->removeCase($aRow ['APP_UID']);
        $oDataset->next();
      }
      //Delete the tasks of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(TaskPeer::PRO_UID, $sProcessUID);
      $oDataset = TaskPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $this->deleteTask($aRow ['TAS_UID']);
        $oDataset->next();
      }
      //Delete the dynaforms of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);
      $oDataset = DynaformPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $oDynaform->remove($aRow ['DYN_UID']);
        $oDataset->next();
      }
      //Delete the input documents of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(InputDocumentPeer::PRO_UID, $sProcessUID);
      $oDataset = InputDocumentPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $oInputDocument->remove($aRow ['INP_DOC_UID']);
        $oDataset->next();
      }
      //Delete the output documents of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(OutputDocumentPeer::PRO_UID, $sProcessUID);
      $oDataset = OutputDocumentPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $oOutputDocument->remove($aRow ['OUT_DOC_UID']);
        $oDataset->next();
      }

      //Delete the triggers of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(TriggersPeer::PRO_UID, $sProcessUID);
      $oDataset = TriggersPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $oTrigger->remove($aRow ['TRI_UID']);
        $oDataset->next();
      }

      //Delete the routes of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(RoutePeer::PRO_UID, $sProcessUID);
      $oDataset = RoutePeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $oRoute->remove($aRow ['ROU_UID']);
        $oDataset->next();
      }
      //Delete the swimlanes elements of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(SwimlanesElementsPeer::PRO_UID, $sProcessUID);
      $oDataset = SwimlanesElementsPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $oSwimlaneElement->remove($aRow ['SWI_UID']);
        $oDataset->next();
      }
      //Delete the configurations of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(ConfigurationPeer::PRO_UID, $sProcessUID);
      $oDataset = ConfigurationPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $oConfiguration->remove($aRow ['CFG_UID'], $aRow ['OBJ_UID'], $aRow ['PRO_UID'], $aRow ['USR_UID'], $aRow ['APP_UID']);
        $oDataset->next();
      }
      //Delete the DB sources of process
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(DbSourcePeer::PRO_UID, $sProcessUID);
      $oDataset = DbSourcePeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {

        /**
         * note added by gustavo cruz gustavo-at-colosa-dot-com 27-01-2010
         * in order to solve the bug 0004389, we use the validation function Exists
         * inside the remove function in order to verify if the DbSource record
         * exists in the Database, however there is a strange behavior within the
         * propel engine, when the first record is erased somehow the "_deleted"
         * attribute of the next row is set to true, so when propel tries to erase
         * it, obviously it can't and trows an error. With the "Exist" function
         * we ensure that if there is the record in the database, the _delete attribute must be false.
         *
         * note added by gustavo cruz gustavo-at-colosa-dot-com 28-01-2010
         * I have just identified the source of the issue, when is created a $oDbSource DbSource object
         * it's used whenever a record is erased or removed in the db, however the problem
         * it's that the same object is used every time, and the delete method invoked
         * sets the _deleted attribute to true when its called, of course as we use
         * the same object, the first time works fine but trowns an error with the
         * next record, cos it's the same object and the delete method checks if the _deleted
         * attribute it's true or false, the attrib _deleted is setted to true the
         * first time and later is never changed, the issue seems to be part of
         * every remove function in the model classes, not only DbSource
         * i recommend that a more general solution must be achieved to resolve
         * this issue in every model class, to prevent future problems.
         * */
        $oDbSource->remove($aRow ['DBS_UID']);
        $oDataset->next();
      }
      //Delete the supervisors
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(ProcessUserPeer::PRO_UID, $sProcessUID);
      ProcessUserPeer::doDelete($oCriteria);
      //Delete the object permissions
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(ObjectPermissionPeer::PRO_UID, $sProcessUID);
      ObjectPermissionPeer::doDelete($oCriteria);
      //Delete the step supervisors
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(StepSupervisorPeer::PRO_UID, $sProcessUID);
      StepSupervisorPeer::doDelete($oCriteria);
      //Delete the report tables
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(ReportTablePeer::PRO_UID, $sProcessUID);
      $oDataset = ReportTablePeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $oReportTable->deleteReportTable($aRow ['REP_TAB_UID']);
        $oDataset->next();
      }
      //Delete case tracker configuration
      $oCaseTracker->remove($sProcessUID);
      //Delete case tracker objects
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
      ProcessUserPeer::doDelete($oCriteria);
      //Delete the process
      try {
        $oProcess->remove($sProcessUID);
      } catch (Exception $oError) { //nada
      }
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Save the process title position
   * @param string sProcessUID
   * @param integer $iX
   * @param integer $iY
   * @return boolean
   */

  function saveTitlePosition($sProcessUID = '', $iX = 0, $iY = 0) {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $aFields ['PRO_UID'] = $sProcessUID;
      $aFields ['PRO_TITLE_X'] = $iX;
      $aFields ['PRO_TITLE_Y'] = $iY;
      $oProcess->update($aFields);
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Steps of Tasks
   * @param string $sProcessUID
   * @param string $sTaskUID
   * @return boolean
   */

  function steps($sProcessUID = '', $sTaskUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);
      $aFields = array();
      $aFields ['PROCESS'] = $sProcessUID;
      $aFields ['TASK'] = $sTaskUID;
      $aFields ['CONFIRM'] = G::LoadTranslation('ID_MSG_CONFIRM_DELETE_STEP');
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'steps/steps_List', $this->getStepsCriteria($sTaskUID), $aFields);
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the steps list criteria object
   * @param string $sTaskUID
   * @return array
   */

  function getStepsCriteria($sTaskUID = '') {
    try {
      //call plugin
      $oPluginRegistry = &PMPluginRegistry::getSingleton ();
      $externalSteps = $oPluginRegistry->getSteps();

      $aSteps = array();
      $aSteps [] = array('STEP_TITLE' => 'char', 'STEP_UID' => 'char', 'STEP_TYPE_OBJ' => 'char', 'STEP_CONDITION' => 'char', 'STEP_POSITION' => 'integer');
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(StepPeer::TAS_UID, $sTaskUID);
      $oCriteria->addAscendingOrderByColumn(StepPeer::STEP_POSITION);
      $oDataset = StepPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $urlEdit = '';
        $linkEditValue = '';

        switch ($aRow ['STEP_TYPE_OBJ']) {
          case 'DYNAFORM' :
            $oDynaform = new Dynaform ( );
            $aFields = $oDynaform->load($aRow ['STEP_UID_OBJ']);
            $sTitle = $aFields ['DYN_TITLE'];
            /** @@@init2 PROCCESS FOR DIRECT EDIT LINK @by erik@colosa.com ON DATE 02/06/2008 18:48:13 */
            $DYN_UID = $aFields ['DYN_UID'];
            $urlEdit = "dynaformEdit('" . $DYN_UID . "', '" . $aRow ['PRO_UID'] . "');";
            $linkEditValue = 'Edit';
            /** @@@end2 */
            break;
          case 'INPUT_DOCUMENT' :
            $oInputDocument = new InputDocument ( );
            $aFields = $oInputDocument->getByUid($aRow ['STEP_UID_OBJ']);
            if( $aFields === false )
              continue;
            $sTitle = $aFields ['INP_DOC_TITLE'];
            break;
          case 'OUTPUT_DOCUMENT' :
            $oOutputDocument = new OutputDocument ( );
            $aFields = $oOutputDocument->getByUid($aRow ['STEP_UID_OBJ']);

            if( $aFields === false )
              continue;
            $sTitle = $aFields ['OUT_DOC_TITLE'];
            break;
          case 'EXTERNAL' :
            $sTitle = 'unknown ' . $aRow ['STEP_UID'];
            foreach ($externalSteps as $key => $val) {
              if ($val->sStepId == $aRow ['STEP_UID_OBJ']) {
                $sTitle = $val->sStepTitle;
                if (trim($val->sSetupStepPage) != '') {
                  $urlEdit = "externalStepEdit('" . $aRow ['STEP_UID'] . "', '" . $val->sSetupStepPage . "');";
                  $linkEditValue = 'Edit';
                } else {
                  $urlEdit = "";
                  $linkEditValue = '';
                }
              }
            }
            break;
        }
        $aSteps [] = array('STEP_TITLE' => $sTitle, 'STEP_UID' => $aRow ['STEP_UID'], 'STEP_TYPE_OBJ' => $aRow ['STEP_TYPE_OBJ'], 'STEP_CONDITION' => $aRow ['STEP_CONDITION'], 'STEP_POSITION' => $aRow ['STEP_POSITION'], 'urlEdit' => $urlEdit, 'linkEditValue' => $linkEditValue, 'PRO_UID' => $aRow ['PRO_UID']);
        $oDataset->next();
      }

      global $_DBArray;
      $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['steps'] = $aSteps;
      $_SESSION ['_DBArray'] = $_DBArray;
      G::LoadClass('ArrayPeer');
      $oCriteria = new Criteria('dbarray');
      $oCriteria->setDBArrayTable('steps');
      $oCriteria->addAscendingOrderByColumn('STEP_POSITION');
      return $oCriteria;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the step triggers list criteria object
   * @param string $sStepUID
   * @param string $sTaskUID
   * @param string $sType
   * @return object
   */

  function getStepTriggersCriteria($sStepUID = '', $sTaskUID = '', $sType = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn('C.CON_VALUE');
    $oCriteria->addSelectColumn('STEP_UID');
    $oCriteria->addSelectColumn('TRI_UID');
    $oCriteria->addSelectColumn('ST_TYPE');
    $oCriteria->addSelectColumn(StepTriggerPeer::ST_POSITION);
    $oCriteria->addAsColumn('TRI_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(StepTriggerPeer::TRI_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'TRI_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(StepTriggerPeer::STEP_UID, $sStepUID);
    $oCriteria->add(StepTriggerPeer::TAS_UID, $sTaskUID);
    $oCriteria->add(StepTriggerPeer::ST_TYPE, $sType);
    $oCriteria->addAscendingOrderByColumn(StepTriggerPeer::ST_POSITION);
    return $oCriteria;
  }

  /*
   * Return the available building blocks list criteria object
   * @param string $sProcessUID
   * @param string $sTaskUID
   * @return object
   */

  function getAvailableBBCriteria($sProcessUID = '', $sTaskUID = '') {
    try {
      $oTasks = new Tasks ( );
      $aSteps = $oTasks->getStepsOfTask($sTaskUID);
      $sUIDs = array();
      foreach ($aSteps as $aStep) {
        $sUIDs [] = $aStep ['STEP_UID_OBJ'];
      }
      $aBB = array();
      $aBB [] = array('STEP_UID' => 'char', 'STEP_TITLE' => 'char', 'STEP_TYPE_OBJ' => 'char', 'STEP_MODE' => 'char');
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(DynaformPeer::DYN_UID);
      $oCriteria->addAsColumn('DYN_TITLE', 'C.CON_VALUE');
      $oCriteria->addAlias('C', 'CONTENT');
      $aConditions = array();
      $aConditions [] = array(DynaformPeer::DYN_UID, 'C.CON_ID');
      $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter);
      $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
      $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
      $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);
      $oCriteria->add(DynaformPeer::DYN_UID, $sUIDs, Criteria::NOT_IN);
      $oCriteria->add(DynaformPeer::DYN_TYPE, 'xmlform');
      $oDataset = DynaformPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $i = 0;
      while ($aRow = $oDataset->getRow()) {
        $i++;
        $aBB [] = array('STEP_UID' => $aRow ['DYN_UID'], 'STEP_TITLE' => $aRow ['DYN_TITLE'], 'STEP_TYPE_OBJ' => 'DYNAFORM', 'STEP_MODE' => '<select id="STEP_MODE_' . $aRow ['DYN_UID'] . '">
                                            <option value="EDIT">Edit</option>
                                            <option value="VIEW">View</option>
                                           </select>');
        $oDataset->next();
      }
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(InputDocumentPeer::INP_DOC_UID);
      $oCriteria->addAsColumn('INP_DOC_TITLE', 'C.CON_VALUE');
      $oCriteria->addAlias('C', 'CONTENT');
      $aConditions = array();
      $aConditions [] = array(InputDocumentPeer::INP_DOC_UID, 'C.CON_ID');
      $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter);
      $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
      $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
      $oCriteria->add(InputDocumentPeer::PRO_UID, $sProcessUID);
      $oCriteria->add(InputDocumentPeer::INP_DOC_UID, $sUIDs, Criteria::NOT_IN);
      $oDataset = InputDocumentPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $aBB [] = array('STEP_UID' => $aRow ['INP_DOC_UID'], 'STEP_TITLE' => $aRow ['INP_DOC_TITLE'], 'STEP_TYPE_OBJ' => 'INPUT_DOCUMENT', 'STEP_MODE' => '<input type="hidden" id="STEP_MODE_' . $aRow ['INP_DOC_UID'] . '">');
        $oDataset->next();
      }
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(OutputDocumentPeer::OUT_DOC_UID);
      $oCriteria->addAsColumn('OUT_DOC_TITLE', 'C.CON_VALUE');
      $oCriteria->addAlias('C', 'CONTENT');
      $aConditions = array();
      $aConditions [] = array(OutputDocumentPeer::OUT_DOC_UID, 'C.CON_ID');
      $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'OUT_DOC_TITLE' . $sDelimiter);
      $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
      $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
      $oCriteria->add(OutputDocumentPeer::PRO_UID, $sProcessUID);
      $oCriteria->add(OutputDocumentPeer::OUT_DOC_UID, $sUIDs, Criteria::NOT_IN);
      $oDataset = OutputDocumentPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $aBB [] = array('STEP_UID' => $aRow ['OUT_DOC_UID'], 'STEP_TITLE' => $aRow ['OUT_DOC_TITLE'], 'STEP_TYPE_OBJ' => 'OUTPUT_DOCUMENT', 'STEP_MODE' => '<input type="hidden" id="STEP_MODE_' . $aRow ['OUT_DOC_UID'] . '">');
        $oDataset->next();
      }

      //call plugin
      $oPluginRegistry = &PMPluginRegistry::getSingleton ();
      $externalSteps = $oPluginRegistry->getSteps();
      if (is_array($externalSteps) && count($externalSteps) > 0) {
        foreach ($externalSteps as $key => $stepVar) {
          $aBB [] = array('STEP_UID' => $stepVar->sStepId, 'STEP_TITLE' => $stepVar->sStepTitle, 'STEP_TYPE_OBJ' => 'EXTERNAL', 'STEP_MODE' => '<input type="hidden" id="STEP_MODE_' . $stepVar->sStepId . '">');
        }
      }

      global $_DBArray;
      $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['availableBB'] = $aBB;
      $_SESSION ['_DBArray'] = $_DBArray;
      G::LoadClass('ArrayPeer');
      $oCriteria = new Criteria('dbarray');
      $oCriteria->setDBArrayTable('availableBB');
      $oCriteria->addAscendingOrderByColumn('STEP_TYPE_OBJ');
      $oCriteria->addAscendingOrderByColumn('STEP_TITLE');
      return $oCriteria;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Users assigned to Tasks
   * @param string $sProcessUID
   * @param string $sTaskUID
   * @return boolean
   */

  function users($sProcessUID = '', $sTaskUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);

      $_SESSION ['iType'] = 1;

      $aFields ['TASK'] = $sTaskUID;
      $aFields ['TYPE'] = $_SESSION ['iType'];
      $aFields ['OF_TO_ASSIGN'] = G::LoadTranslation('ID_DE_ASSIGN');
      $aFields ['CONFIRM'] = G::LoadTranslation('ID_MSG_CONFIRM_DEASIGN_USER_GROUP_MESSAGE');
      $aFields ['UIDS'] = "'0'";

      $oTasks = new Tasks ( );
      $oGroups = new Groups ( );
      $aAux1 = $oTasks->getGroupsOfTask($sTaskUID, $_SESSION ['iType']);
      foreach ($aAux1 as $aGroup) {
        $aAux2 = $oGroups->getUsersOfGroup($aGroup ['GRP_UID']);
        foreach ($aAux2 as $aUser) {
          $aFields ['UIDS'] .= ",'" . $aUser ['USR_UID'] . "'";
        }
      }
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $oTask = new Task ( );
      $aTask = $oTask->load($sTaskUID);

      if ($aFields ['TAS_TYPE'] == 'TRUE') {
        $G_PUBLISH->AddContent('propeltable', 'paged-table', 'users/users_ShortList', $this->getTaskUsersCriteria($sTaskUID, $_SESSION ['iType']), $aFields);
      } else {
        $G_PUBLISH->AddContent('propeltable', 'paged-table', 'users/users_ShortList2', $this->getTaskUsersCriteria($sTaskUID, $_SESSION ['iType']), $aFields);
      }

      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Users Adhoc assigned to Tasks
   * @param string $sProcessUID
   * @param string $sTaskUID
   * @return boolean
   */

  function users_adhoc($sProcessUID = '', $sTaskUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);

      $_SESSION ['iType'] = 2;

      $aFields ['TASK'] = $sTaskUID;
      $aFields ['TYPE'] = $_SESSION ['iType'];
      $aFields ['OF_TO_ASSIGN'] = G::LoadTranslation('ID_DE_ASSIGN');
      $aFields ['CONFIRM'] = G::LoadTranslation('ID_MSG_CONFIRM_DEASIGN_USER_GROUP_MESSAGE');
      $aFields ['UIDS'] = "'0'";

      $oTasks = new Tasks ( );
      $oGroups = new Groups ( );
      $aAux1 = $oTasks->getGroupsOfTask($sTaskUID, $_SESSION ['iType']);
      foreach ($aAux1 as $aGroup) {
        $aAux2 = $oGroups->getUsersOfGroup($aGroup ['GRP_UID']);
        foreach ($aAux2 as $aUser) {
          $aFields ['UIDS'] .= ",'" . $aUser ['USR_UID'] . "'";
        }
      }
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $oTask = new Task ( );
      $aTask = $oTask->load($sTaskUID);

      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'users/users_ShortListAdhoc', $this->getTaskUsersCriteria($sTaskUID, $_SESSION ['iType']), $aFields);

      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the tasks users list criteria object
   * @param string $sTaskUID
   * @param integer $iType
   * @return array
   */

  function getTaskUsersCriteria($sTaskUID = '', $iType = 1) {
    try {
      $aUsers = array();
      $aUsers [] = array('LABEL' => 'char', 'TAS_UID' => 'char', 'USR_UID' => 'char', 'TU_TYPE' => 'integer', 'TU_RELATION' => 'integer');
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria = new Criteria('workflow');
      $oCriteria->addAsColumn('GRP_TITLE', 'C.CON_VALUE');
      $oCriteria->addSelectColumn(TaskUserPeer::TAS_UID);
      $oCriteria->addSelectColumn(TaskUserPeer::USR_UID);
      $oCriteria->addSelectColumn(TaskUserPeer::TU_TYPE);
      $oCriteria->addSelectColumn(TaskUserPeer::TU_RELATION);
      $oCriteria->addAlias('C', 'CONTENT');
      $aConditions = array();
      $aConditions [] = array(TaskUserPeer::USR_UID, 'C.CON_ID');
      $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'GRP_TITLE' . $sDelimiter);
      $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
      $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
      $oCriteria->add(TaskUserPeer::TAS_UID, $sTaskUID);
      $oCriteria->add(TaskUserPeer::TU_TYPE, $iType);
      $oCriteria->add(TaskUserPeer::TU_RELATION, 2);
      $oDataset = TaskUserPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $c = 0;
      while ($aRow = $oDataset->getRow()) {
        $c++;
        $oGroup = new Groupwf ( );
        $aFields = $oGroup->load($aRow ['USR_UID']);
        if ($aFields ['GRP_STATUS'] == 'ACTIVE') {
          $oCriteria = new Criteria('workflow');
          $oCriteria->addSelectColumn('COUNT(*) AS MEMBERS_NUMBER');
          $oCriteria->add(GroupUserPeer::GRP_UID, $aRow ['USR_UID']);
          $oDataset2 = GroupUserPeer::doSelectRS($oCriteria);
          $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
          $oDataset2->next();
          $aRow2 = $oDataset2->getRow();
        } else {
          $aRow2 ['GROUP_INACTIVE'] = '<strong>(' . G::LoadTranslation('ID_GROUP_INACTIVE') . ')</strong>';
        }
        $aUsers [] = array('LABEL' => (!isset($aRow2 ['GROUP_INACTIVE']) ? $aRow ['GRP_TITLE'] . ' <a href="#" onclick="usersGroup(\'' . $aRow ['USR_UID'] . '\', \'' . $c . '\');return false;"><font color="green"><strong>(' . $aRow2 ['MEMBERS_NUMBER'] . ' ' . ((int) $aRow2 ['MEMBERS_NUMBER'] == 1 ? G::LoadTranslation('ID_USER') : G::LoadTranslation('ID_USERS')) . ')</strong></font></a> <br /><div id="users' . $c . '" style="display: none"></div>' : $aRow ['GRP_TITLE'] . ' ' . $aRow2 ['GROUP_INACTIVE']), 'TAS_UID' => $aRow ['TAS_UID'], 'USR_UID' => $aRow ['USR_UID'], 'TU_TYPE' => $aRow ['TU_TYPE'], 'TU_RELATION' => $aRow ['TU_RELATION']);
        $oDataset->next();
      }
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
      $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
      $oCriteria->addSelectColumn(TaskUserPeer::TAS_UID);
      $oCriteria->addSelectColumn(TaskUserPeer::USR_UID);
      $oCriteria->addSelectColumn(TaskUserPeer::TU_TYPE);
      $oCriteria->addSelectColumn(TaskUserPeer::TU_RELATION);
      $oCriteria->addJoin(TaskUserPeer::USR_UID, UsersPeer::USR_UID, Criteria::LEFT_JOIN);
      $oCriteria->add(TaskUserPeer::TAS_UID, $sTaskUID);
      $oCriteria->add(TaskUserPeer::TU_TYPE, $iType);
      $oCriteria->add(TaskUserPeer::TU_RELATION, 1);
      $oDataset = TaskUserPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $aUsers [] = array('LABEL' => $aRow ['USR_FIRSTNAME'] . ' ' . $aRow ['USR_LASTNAME'], 'TAS_UID' => $aRow ['TAS_UID'], 'USR_UID' => $aRow ['USR_UID'], 'TU_TYPE' => $aRow ['TU_TYPE'], 'TU_RELATION' => $aRow ['TU_RELATION']);
        $oDataset->next();
      }
      global $_DBArray;
      $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['taskUsers'] = $aUsers;
      $_SESSION ['_DBArray'] = $_DBArray;
      G::LoadClass('ArrayPeer');
      $oCriteria = new Criteria('dbarray');
      $oCriteria->setDBArrayTable('taskUsers');
      $oCriteria->addDescendingOrderByColumn(TaskUserPeer::TU_RELATION);
      $oCriteria->addAscendingOrderByColumn('LABEL');
      return $oCriteria;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the available users and users groups list criteria object
   * @param string $sTaskUID
   * @param integer $iType
   * @return object
   */

  function getAvailableUsersCriteria($sTaskUID = '', $iType = 1) {
    try {
      $oTasks = new Tasks ( );
      $aAux = $oTasks->getGroupsOfTask($sTaskUID, $iType);
      $aUIDS1 = array();
      $aUIDS2 = array();
      foreach ($aAux as $aGroup) {
        $aUIDS1 [] = $aGroup ['GRP_UID'];
      }
      $aAux = $oTasks->getUsersOfTask($sTaskUID, $iType);
      foreach ($aAux as $aUser) {
        $aUIDS2 [] = $aUser ['USR_UID'];
      }
      $aUsers = array();
      $aUsers [] = array('LABEL' => 'char', 'TAS_UID' => 'char', 'USR_UID' => 'char', 'TU_TYPE' => 'integer', 'TU_RELATION' => 'integer');
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(GroupwfPeer::GRP_UID);
      $oCriteria->addAsColumn('GRP_TITLE', 'C.CON_VALUE');
      $oCriteria->addAlias('C', 'CONTENT');
      $aConditions = array();
      $aConditions [] = array(GroupwfPeer::GRP_UID, 'C.CON_ID');
      $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'GRP_TITLE' . $sDelimiter);
      $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
      $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
      $oCriteria->add(GroupwfPeer::GRP_STATUS, 'ACTIVE');
      $oCriteria->add(GroupwfPeer::GRP_UID, $aUIDS1, Criteria::NOT_IN);
      //$oCriteria->add(GroupwfPeer::GRP_UID, '', Criteria::NOT_EQUAL);
      $oDataset = GroupwfPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $c = 0;
      while ($aRow = $oDataset->getRow()) {
        $c++;
        $oCriteria = new Criteria('workflow');
        $oCriteria->addSelectColumn('COUNT(*) AS MEMBERS_NUMBER');
        $oCriteria->add(GroupUserPeer::GRP_UID, $aRow ['GRP_UID']);
        $oDataset2 = GroupUserPeer::doSelectRS($oCriteria);
        $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oDataset2->next();
        $aRow2 = $oDataset2->getRow();
        $aUsers [] = array('LABEL' => $aRow ['GRP_TITLE'] . ' <a href="#" onclick="usersGroup(\'' . $aRow ['GRP_UID'] . '\', \'' . $c . '\');return false;"><font color="green"><strong>(' . $aRow2 ['MEMBERS_NUMBER'] . ' ' . ((int) $aRow2 ['MEMBERS_NUMBER'] == 1 ? G::LoadTranslation('ID_USER') : G::LoadTranslation('ID_USERS')) . ')</strong></font></a> <br /><div id="users' . $c . '" style="display: none"></div>', 'TAS_UID' => $sTaskUID, 'USR_UID' => $aRow ['GRP_UID'], 'TU_TYPE' => $iType, 'TU_RELATION' => 2);
        $oDataset->next();
      }
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(UsersPeer::USR_UID);
      $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
      $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
      $oCriteria->add(UsersPeer::USR_STATUS, 'ACTIVE');
      $oCriteria->add(UsersPeer::USR_UID, $aUIDS2, Criteria::NOT_IN);
      $oDataset = UsersPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ($aRow = $oDataset->getRow()) {
        $aUsers [] = array('LABEL' => $aRow ['USR_FIRSTNAME'] . ' ' . $aRow ['USR_LASTNAME'], 'TAS_UID' => $sTaskUID, 'USR_UID' => $aRow ['USR_UID'], 'TU_TYPE' => $iType, 'TU_RELATION' => 1);
        $oDataset->next();
      }
      global $_DBArray;
      $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['availableUsers'] = $aUsers;
      $_SESSION ['_DBArray'] = $_DBArray;
      G::LoadClass('ArrayPeer');
      $oCriteria = new Criteria('dbarray');
      $oCriteria->setDBArrayTable('availableUsers');
      $oCriteria->addDescendingOrderByColumn(TaskUserPeer::TU_RELATION);
      $oCriteria->addAscendingOrderByColumn('LABEL');
      return $oCriteria;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Conditions of the steps
   * @param string $sProcessUID
   * @param string $sTaskUID
   * @return boolean
   */

  function stepsConditions($sProcessUID = '', $sTaskUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);

      $aFields ['PROCESS'] = $sProcessUID;
      $aFields ['TASK'] = $sTaskUID;
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'steps/conditions_List', $this->getStepsCriteria($sTaskUID), $aFields);
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Triggers of the steps
   * @param string $sProcessUID
   * @param string $sTaskUID
   * @return boolean
   */

  function stepsTriggers($sProcessUID = '', $sTaskUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);

      $_SESSION ['PROCESS'] = $sProcessUID;
      $_SESSION ['TASK'] = $sTaskUID;
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('view', 'steps/triggers_Tree');
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Add a new task
   * @param string $sProcessUID
   * @param integer $iX
   * @param integer $iY
   * @return string
   */

  function addTask($sProcessUID = '', $iX = 0, $iY = 0) {
    try {
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn('TAS_UID');
      $oCriteria->add(TaskPeer::PRO_UID, $sProcessUID);
      $oDataset = TaskPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $aTasks = array();
      $iTaskNumber = 0;
      while ($aRow = $oDataset->getRow()) {
        $aTasks [] = $aRow ['TAS_UID'];
        $iTaskNumber++;
        $oDataset->next();
      }
      if ($iTaskNumber == 0) {
        $iTaskNumber = 1;
      }
      $bContinue = false;
      while (!$bContinue) {
        $oCriteria = new Criteria('workflow');
        $oCriteria->addSelectColumn('COUNT(*) AS TIMES');
        $oCriteria->add(ContentPeer::CON_ID, $aTasks, Criteria::IN);
        $oCriteria->add(ContentPeer::CON_CATEGORY, 'TAS_TITLE');
        $oCriteria->add(ContentPeer::CON_LANG, SYS_LANG);
        $oCriteria->add(ContentPeer::CON_VALUE, G::LoadTranslation('ID_TASK') . ' ' . $iTaskNumber);
        $oDataset = ContentPeer::doSelectRS($oCriteria);
        $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oDataset->next();
        $aRow = $oDataset->getRow();
        if ((int) $aRow ['TIMES'] > 0) {
          $iTaskNumber += 1;
        } else {
          $bContinue = true;
        }
      }
      $oTask = new Task ( );
      $oNewTask->label = G::LoadTranslation('ID_TASK') . ' ' . $iTaskNumber;
      $oNewTask->uid = $oTask->create(array('PRO_UID' => $sProcessUID, 'TAS_TITLE' => $oNewTask->label, 'TAS_POSX' => $iX, 'TAS_POSY' => $iY));
      $oNewTask->statusIcons = array();
      $oNewTask->statusIcons[] = array('label' => '', 'icon' => '/images/alert.gif', 'message' => '', 'url' => '');
      $oJSON = new Services_JSON ( );
      return $oJSON->encode($oNewTask);
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Edit the task properties
   * @param string $sTaskUID
   * @return boolean
   */

  function editTaskProperties($sTaskUID = '', $iForm = 1, $iIndex = 0) {
    try {
      switch ($iForm) {
        case 1 :
          $sFilename = 'tasks/tasks_Definition.xml';
          break;
        case 2 :
          $sFilename = 'tasks/tasks_AssignmentRules.xml';
          break;
        case 3 :
          $sFilename = 'tasks/tasks_TimingControl.xml';
          break;
        case 4 :
          $sFilename = 'tasks/tasks_Owner.xml';
          break;
        case 5 :
          $sFilename = 'tasks/tasks_Permissions.xml';
          break;
        case 6 :
          $sFilename = 'tasks/tasks_Labels.xml';
          break;
        case 7 :
          $sFilename = 'tasks/tasks_Notifications.xml';
          break;
      }
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);
      $aFields ['INDEX'] = $iIndex;
      $aFields ['IFORM'] = $iForm;
      $aFields ['LANG'] = SYS_LANG;

      if ($iForm == 3) { //Load Calendar Information
        $calendar = new Calendar ( );
        $calendarObj = $calendar->getCalendarList(true, true);

        global $_DBArray;

        $_DBArray ['availableCalendars'] = $calendarObj ['array'];

        $_SESSION ['_DBArray'] = $_DBArray;

        $calendarInfo = $calendar->getCalendarFor($sTaskUID, $sTaskUID, $sTaskUID);

        //If the function returns a DEFAULT calendar it means that this object doesn't have assigned any calendar
        $aFields ['TAS_CALENDAR'] = $calendarInfo ['CALENDAR_APPLIED'] != 'DEFAULT' ? $calendarInfo ['CALENDAR_UID'] : "";
      }
      global $G_PUBLISH;
      G::LoadClass('xmlfield_InputPM');
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('xmlform', 'xmlform', $sFilename, '', $aFields);
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Save the tasks positions
   * @param string $sTaskUID
   * @param integer $iX
   * @param integer $iY
   * @return integer
   */

  function saveTaskPosition($sTaskUID = '', $iX = 0, $iY = 0) {
    try {
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);

      $aFields ['TAS_UID'] = $sTaskUID;
      $aFields ['TAS_POSX'] = $iX;
      $aFields ['TAS_POSY'] = $iY;
      return $oTask->update($aFields);
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Delete a task
   * @param string $sTaskUID
   * @return boolean
   */

  function deleteTask($sTaskUID = '') {
    try {
      $oTasks = new Tasks ( );
      $oTasks->deleteTask($sTaskUID);
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Add a new guide
   * @param string $sProcessUID
   * @param integer $iPosition
   * @param string $sDirection
   * @return string
   */

  function addGuide($sProcessUID = '', $iPosition = 0, $sDirection = 'vertical') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oSL = new SwimlanesElements ( );
      switch ($sDirection) {
        case 'vertical' :
          $oNewGuide->uid = $oSL->create(array('PRO_UID' => $sProcessUID, 'SWI_TYPE' => 'LINE', 'SWI_X' => $iPosition, 'SWI_Y' => 0));
          break;
        case 'horizontal' :
          $oNewGuide->uid = $oSL->create(array('PRO_UID' => $sProcessUID, 'SWI_TYPE' => 'LINE', 'SWI_X' => 0, 'SWI_Y' => $iPosition));
          break;
      }
      $oJSON = new Services_JSON ( );
      return $oJSON->encode($oNewGuide);
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Save the guide position
   * @param string $sSEUID
   * @param integer $iPosition
   * @param string $sDirection
   * @return integer
   */

  function saveGuidePosition($sSEUID = '', $iPosition = 0, $sDirection = 'vertical') {
    try {
      $oSL = new SwimlanesElements ( );
      $aFields = $oSL->load($sSEUID);
      switch ($sDirection) {
        case 'vertical' :
          return $oSL->update(array('SWI_UID' => $sSEUID, 'SWI_X' => $iPosition));
          break;
        case 'horizontal' :
          return $oSL->update(array('SWI_UID' => $sSEUID, 'SWI_Y' => $iPosition));
          break;
      }
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Delete a guide
   * @param string $sUID
   * @return boolean
   */

  function deleteGuide($sSEUID = '') {
    try {
      $oSL = new SwimlanesElements ( );
      $aFields = $oSL->load($sSEUID);
      $oSL->remove($sSEUID);
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Delete all guides
   * @param string $sProcessUID
   * @return boolean
   */

  function deleteGuides($sProcessUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(SwimlanesElementsPeer::PRO_UID, $sProcessUID);
      $oCriteria->add(SwimlanesElementsPeer::SWI_TYPE, 'LINE');
      SwimlanesElementsPeer::doDelete($oCriteria);
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Add a new text
   * @param string $sProcessUID
   * @param string $sLabel
   * @param integer $iX
   * @param integer $iY
   * @return string
   */

  function addText($sProcessUID = '', $sLabel = '', $iX = 0, $iY = 0) {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oSL = new SwimlanesElements ( );
      $oNewText->uid = $oSL->create(array('PRO_UID' => $sProcessUID, 'SWI_TYPE' => 'TEXT', 'SWI_TEXT' => $sLabel, 'SWI_X' => $iX, 'SWI_Y' => $iY));
      $oJSON = new Services_JSON ( );
      return $oJSON->encode($oNewText);
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Update a text
   * @param string $sSEUID
   * @param string $sLabel
   * @return integer
   */

  function updateText($sSEUID = '', $sLabel = '') {
    try {
      $oSL = new SwimlanesElements ( );
      $aFields = $oSL->load($sSEUID);
      return $oSL->update(array('SWI_UID' => $sSEUID, 'SWI_TEXT' => $sLabel));
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Save the text position
   * @param string $sSEUID
   * @param integer $iX
   * @param integer $iY
   * @return integer
   */

  function saveTextPosition($sSEUID = '', $iX = 0, $iY = 0) {
    try {
      $oSL = new SwimlanesElements ( );
      $aFields = $oSL->load($sSEUID);
      return $oSL->update(array('SWI_UID' => $sSEUID, 'SWI_X' => $iX, 'SWI_Y' => $iY));
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Delete a text
   * @param string $sSEUID
   * @return boolean
   */

  function deleteText($sSEUID = '') {
    try {
      $oSL = new SwimlanesElements ( );
      $aFields = $oSL->load($sSEUID);
      $oSL->remove($sSEUID);
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Presents a small list of dynaforms of the process
   * @param string $sProcessUID
   * @return boolean
   */

  function dynaformsList($sProcessUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'dynaforms/dynaforms_ShortList', $this->getDynaformsCriteria($sProcessUID), $aFields);
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the dynaforms list criteria object
   * @param string $sProcessUID
   * @return object
   */

  function getDynaformsCriteria($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(DynaformPeer::DYN_UID);
    $oCriteria->addSelectColumn(DynaformPeer::PRO_UID);
    $oCriteria->addSelectColumn(DynaformPeer::DYN_TYPE);
    $oCriteria->addAsColumn('DYN_TITLE', 'C1.CON_VALUE');
    $oCriteria->addAsColumn('DYN_DESCRIPTION', 'C2.CON_VALUE');
    $oCriteria->addAlias('C1', 'CONTENT');
    $oCriteria->addAlias('C2', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C1.CON_ID');
    $aConditions [] = array('C1.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter);
    $aConditions [] = array('C1.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C2.CON_ID');
    $aConditions [] = array('C2.CON_CATEGORY', $sDelimiter . 'DYN_DESCRIPTION' . $sDelimiter);
    $aConditions [] = array('C2.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);

    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $dynaformArray = array();
    $dynaformArray [] = array('d' => 'char');
    while ($aRow = $oDataset->getRow()) {
      if (($aRow ['DYN_TITLE'] == NULL) || ($aRow ['DYN_TITLE'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label
        $aRow ['DYN_TITLE'] = Content::Load("DYN_TITLE", "", $aRow ['DYN_UID'], SYS_LANG);
      }
      if (($aRow ['DYN_DESCRIPTION'] == NULL) || ($aRow ['DYN_DESCRIPTION'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label
        $aRow ['DYN_DESCRIPTION'] = Content::Load("DYN_DESCRIPTION", "", $aRow ['DYN_UID'], SYS_LANG);
      }
      $dynaformArray [] = $aRow;
      $oDataset->next();
    }


    return $oCriteria;
  }

  /**
   * getDynaformsList
   *
   * @param  string    $sProcessUID
   * @return array     $dynaformArray
   */
  function getDynaformsList($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(DynaformPeer::DYN_UID);
    $oCriteria->addSelectColumn(DynaformPeer::PRO_UID);
    $oCriteria->addSelectColumn(DynaformPeer::DYN_TYPE);
    $oCriteria->addAsColumn('DYN_TITLE', 'C1.CON_VALUE');
    $oCriteria->addAsColumn('DYN_DESCRIPTION', 'C2.CON_VALUE');
    $oCriteria->addAlias('C1', 'CONTENT');
    $oCriteria->addAlias('C2', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C1.CON_ID');
    $aConditions [] = array('C1.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter);
    $aConditions [] = array('C1.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C2.CON_ID');
    $aConditions [] = array('C2.CON_CATEGORY', $sDelimiter . 'DYN_DESCRIPTION' . $sDelimiter);
    $aConditions [] = array('C2.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);

    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $dynaformArray = array();
    while ($aRow = $oDataset->getRow()) {
      if (($aRow ['DYN_TITLE'] == NULL) || ($aRow ['DYN_TITLE'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label
        $aRow ['DYN_TITLE'] = Content::Load("DYN_TITLE", "", $aRow ['DYN_UID'], SYS_LANG);
      }
      if (($aRow ['DYN_DESCRIPTION'] == NULL) || ($aRow ['DYN_DESCRIPTION'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label
        $aRow ['DYN_DESCRIPTION'] = Content::Load("DYN_DESCRIPTION", "", $aRow ['DYN_UID'], SYS_LANG);
      }
      $dynaformArray [] = $aRow;
      $oDataset->next();
    }

    return $dynaformArray;
  }

  /*
   * Presents a small list of output documents of the process
   * @param string $sProcessUID
   * @return boolean
   */

  function outputdocsList($sProcessUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'outputdocs/outputdocs_ShortList', $this->getOutputDocumentsCriteria($sProcessUID), $aFields);
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the output documents list criteria object
   * @param string $sProcessUID
   * @return object
   */

  function getOutputDocumentsCriteria($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(OutputDocumentPeer::OUT_DOC_UID);
    $oCriteria->addSelectColumn(OutputDocumentPeer::OUT_DOC_TYPE);
    $oCriteria->addSelectColumn(OutputDocumentPeer::PRO_UID);
    $oCriteria->addAsColumn('OUT_DOC_TITLE', 'C1.CON_VALUE');
    $oCriteria->addAsColumn('OUT_DOC_DESCRIPTION', 'C2.CON_VALUE');
    $oCriteria->addAlias('C1', 'CONTENT');
    $oCriteria->addAlias('C2', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(OutputDocumentPeer::OUT_DOC_UID, 'C1.CON_ID');
    $aConditions [] = array('C1.CON_CATEGORY', $sDelimiter . 'OUT_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C1.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $aConditions = array();
    $aConditions [] = array(OutputDocumentPeer::OUT_DOC_UID, 'C2.CON_ID');
    $aConditions [] = array('C2.CON_CATEGORY', $sDelimiter . 'OUT_DOC_DESCRIPTION' . $sDelimiter);
    $aConditions [] = array('C2.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(OutputDocumentPeer::PRO_UID, $sProcessUID);

    $oDataset = OutputDocumentPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $outputDocArray = array();
    $outputDocArray [] = array('d' => 'char');
    while ($aRow = $oDataset->getRow()) {
      if (($aRow ['OUT_DOC_TITLE'] == NULL) || ($aRow ['OUT_DOC_TITLE'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label
        $outputDocument = new OutputDocument ( );
        $outputDocumentObj = $outputDocument->load($aRow ['OUT_DOC_UID']);
        $aRow ['OUT_DOC_TITLE'] = $outputDocumentObj ['OUT_DOC_TITLE'];
        $aRow ['OUT_DOC_DESCRIPTION'] = $outputDocumentObj ['OUT_DOC_DESCRIPTION'];
      }
      $outputDocArray [] = $aRow;
      $oDataset->next();
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['outputDocArray'] = $outputDocArray;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('outputDocArray');

    return $oCriteria;
  }

  /*
   * Presents a small list of input documents of the process
   * @param string $sProcessUID Process UID
   * @return void
   */

  function inputdocsList($sProcessUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'inputdocs/inputdocs_ShortList', $this->getInputDocumentsCriteria($sProcessUID), $aFields);
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the input documents list criteria object
   * @param string $sProcessUID
   * @return object
   */

  function getInputDocumentsCriteria($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(InputDocumentPeer::INP_DOC_UID);
    $oCriteria->addSelectColumn(InputDocumentPeer::PRO_UID);
    $oCriteria->addAsColumn('INP_DOC_TITLE', 'C1.CON_VALUE');
    $oCriteria->addAsColumn('INP_DOC_DESCRIPTION', 'C2.CON_VALUE');
    $oCriteria->addAlias('C1', 'CONTENT');
    $oCriteria->addAlias('C2', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(InputDocumentPeer::INP_DOC_UID, 'C1.CON_ID');
    $aConditions [] = array('C1.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C1.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $aConditions = array();
    $aConditions [] = array(InputDocumentPeer::INP_DOC_UID, 'C2.CON_ID');
    $aConditions [] = array('C2.CON_CATEGORY', $sDelimiter . 'INP_DOC_DESCRIPTION' . $sDelimiter);
    $aConditions [] = array('C2.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(InputDocumentPeer::PRO_UID, $sProcessUID);

    $oDataset = InputDocumentPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $inputDocArray = "";
    $inputDocArray [] = array('INP_DOC_UID' => 'char', 'PRO_UID' => 'char', 'INP_DOC_TITLE' => 'char', 'INP_DOC_DESCRIPTION' => 'char');
    while ($aRow = $oDataset->getRow()) {
      if (($aRow ['INP_DOC_TITLE'] == NULL) || ($aRow ['INP_DOC_TITLE'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label
        $inputDocument = new InputDocument ( );
        $inputDocumentObj = $inputDocument->load($aRow ['INP_DOC_UID']);
        $aRow ['INP_DOC_TITLE'] = $inputDocumentObj ['INP_DOC_TITLE'];
        $aRow ['INP_DOC_DESCRIPTION'] = $inputDocumentObj ['INP_DOC_DESCRIPTION'];
      }
      $inputDocArray [] = $aRow;
      $oDataset->next();
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');

    $_DBArray ['inputDocArrayMain'] = $inputDocArray;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('inputDocArrayMain');

    return $oCriteria;
  }

  /*
   * Presents a small list of triggers of the process
   * @param string $sProcessUID
   * @return void
   */

  function triggersList($sProcessUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'triggers/triggers_ShortList', $this->getTriggersCriteria($sProcessUID), $aFields);
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the triggers list criteria object
   * @param string $sProcessUID
   * @return object
   */

  function getTriggersCriteria($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(TriggersPeer::TRI_UID);
    $oCriteria->addSelectColumn(TriggersPeer::PRO_UID);
    $oCriteria->addAsColumn('TRI_TITLE', 'C1.CON_VALUE');
    $oCriteria->addAsColumn('TRI_DESCRIPTION', 'C2.CON_VALUE');
    $oCriteria->addAlias('C1', 'CONTENT');
    $oCriteria->addAlias('C2', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(TriggersPeer::TRI_UID, 'C1.CON_ID');
    $aConditions [] = array('C1.CON_CATEGORY', $sDelimiter . 'TRI_TITLE' . $sDelimiter);
    $aConditions [] = array('C1.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $aConditions = array();
    $aConditions [] = array(TriggersPeer::TRI_UID, 'C2.CON_ID');
    $aConditions [] = array('C2.CON_CATEGORY', $sDelimiter . 'TRI_TITLE' . $sDelimiter);
    $aConditions [] = array('C2.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(TriggersPeer::PRO_UID, $sProcessUID);
    $oCriteria->addAscendingOrderByColumn('TRI_TITLE');


    $oDataset = TriggersPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $triggersArray = "";
    $triggersArray [] = array('TRI_UID' => 'char', 'PRO_UID' => 'char', 'TRI_TITLE' => 'char', 'TRI_DESCRIPTION' => 'char');
    while ($aRow = $oDataset->getRow()) {

      if (($aRow ['TRI_TITLE'] == NULL) || ($aRow ['TRI_TITLE'] == "")) { // There is no transaltion for this Trigger name, try to get/regenerate the label
        $triggerO = new Triggers ( );
        $triggerObj = $triggerO->load($aRow ['TRI_UID']);
        $aRow ['TRI_TITLE'] = $triggerObj ['TRI_TITLE'];
        $aRow ['TRI_DESCRIPTION'] = $triggerObj ['TRI_DESCRIPTION'];
      }
      $triggersArray [] = $aRow;
      $oDataset->next();
    }


    return $oCriteria;
  }

  /*
   * Return the triggers list in a array
   * @param string $sProcessUID
   * @return array
   */

  function getTriggers($sProcessUID = '') {
    $aTriggers = Array();
    $oCriteria = $this->getTriggersCriteria($sProcessUID);

    $oDataset = RoutePeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);

    while ($oDataset->next())
      array_push($aTriggers, $oDataset->getRow());

    return $aTriggers;
  }

  /*
   * Presents a small list of Scheduled Tasks of the process
   * @param string $sProcessUID
   * @return void
   */

  function caseSchedulerList($sProcessUID = '') {
    try {
      /* $oCaseScheduler = new CaseScheduler();
        $aRows = $oCaseScheduler->getAll();

        //$oCaseScheduler->caseSchedulerCron();
        // g::pr($aRows); die;

        $fieldNames = Array(
        'SCH_UID' => 'char',
        'SCH_NAME' => 'char',
        'PRO_UID' => 'char',
        'TAS_UID' => 'char',
        'SCH_TIME_NEXT_RUN' => 'char',
        'SCH_LAST_RUN_TIME' => 'char',
        'SCH_STATE' => 'char',
        'SCH_LAST_STATE' => 'char',
        'USR_UID' => 'char',
        'SCH_OPTION' => 'char',
        'SCH_START_TIME' => 'char',
        'SCH_START_DATE' => 'char',
        'SCH_DAYS_PERFORM_TASK' => 'char',
        'SCH_EVERY_DAYS' => 'char',
        'SCH_WEEK_DAYS' => 'char',
        'SCH_START_DAY' => 'char',
        'SCH_MONTHS' => 'char',
        'SCH_END_DATE' => 'char',
        'SCH_REPEAT_EVERY' => 'char',
        'SCH_REPEAT_UNTIL' => 'char',
        'SCH_REPEAT_STOP_IF_RUNNING' => 'char',
        'PRO_PARENT' => 'char',
        'PRO_TIME' => 'char',
        'PRO_TIMEUNIT' => 'char',
        'PRO_STATUS' => 'char',
        'PRO_TYPE_DAY' => 'char',
        'PRO_TYPE' => 'char',
        'PRO_ASSIGNMENT' => 'char',
        'PRO_SHOW_MAP' => 'char',
        'PRO_SHOW_MESSAGE' => 'char',
        'PRO_SHOW_DELEGATE' => 'char',
        'PRO_SHOW_DYNAFORM' => 'char',
        'PRO_CATEGORY' => 'char',
        'PRO_SUB_CATEGORY' => 'char',
        'PRO_INDUSTRY' => 'char',
        'PRO_UPDATE_DATE' => 'char',
        'PRO_CREATE_DATE' => 'char',
        'PRO_CREATE_USER' => 'char',
        'PRO_HEIGHT' => 'char',
        'PRO_WIDTH' => 'char',
        'PRO_TITLE_X' => 'char',
        'PRO_TITLE_Y' => 'char',
        'PRO_DEBUG' => 'char',
        'PRO_TITLE' => 'char',
        'PRO_DESCRIPTION' => 'char',
        'TAS_TYPE' => 'char',
        'TAS_DURATION' => 'char',
        'TAS_DELAY_TYPE' => 'char',
        'TAS_TEMPORIZER' => 'char',
        'TAS_TYPE_DAY' => 'char',
        'TAS_TIMEUNIT' => 'char',
        'TAS_ALERT' => 'char',
        'TAS_PRIORITY_VARIABLE' => 'char',
        'TAS_ASSIGN_TYPE' => 'char',
        'TAS_ASSIGN_VARIABLE' => 'char',
        'TAS_ASSIGN_LOCATION' => 'char',
        'TAS_ASSIGN_LOCATION_ADHOC' => 'char',
        'TAS_TRANSFER_FLY' => 'char',
        'TAS_LAST_ASSIGNED' => 'char',
        'TAS_USER' => 'char',
        'TAS_CAN_UPLOAD' => 'char',
        'TAS_VIEW_UPLOAD' => 'char',
        'TAS_VIEW_ADDITIONAL_DOCUMENTATION' => 'char',
        'TAS_CAN_CANCEL' => 'char',
        'TAS_OWNER_APP' => 'char',
        'STG_UID' => 'char',
        'TAS_CAN_PAUSE' => 'char',
        'TAS_CAN_SEND_MESSAGE' => 'char',
        'TAS_CAN_DELETE_DOCS' => 'char',
        'TAS_SELF_SERVICE' => 'char',
        'TAS_START' => 'char',
        'TAS_TO_LAST_USER' => 'char',
        'TAS_SEND_LAST_EMAIL' => 'char',
        'TAS_DERIVATION' => 'char',
        'TAS_POSX' => 'char',
        'TAS_POSY' => 'char',
        'TAS_COLOR' => 'char',
        'TAS_TITLE' => 'char',
        'TAS_DESCRIPTION' => 'char',
        'TAS_DEF_TITLE' => 'char',
        'TAS_DEF_DESCRIPTION' => 'char',
        'TAS_DEF_PROC_CODE' => 'char',
        'TAS_DEF_MESSAGE' => 'char'
        );


        $aRows = array_merge(Array($fieldNames), $aRows);

        // g::pr($aRows); die;

        global $_DBArray;
        $_DBArray['cases_scheduler']   = $aRows;
        $_SESSION['_DBArray'] = $_DBArray;
        G::LoadClass('ArrayPeer');
        $oCriteria = new Criteria('dbarray');
        $oCriteria->setDBArrayTable('cases_scheduler');
        $G_PUBLISH = new Publisher;

        $G_PUBLISH->AddContent('propeltable', 'paged-table', '/cases/cases_Scheduler_List', $oCriteria, array('CONFIRM' => G::LoadTranslation('ID_MSG_CONFIRM_DELETE_CASE_SCHEDULER')));
        G::RenderPage('publish');
        //return true; */
      $schedulerPath = SYS_URI . "cases/cases_Scheduler_List";
      $html = "<iframe  WIDTH=820 HEIGHT=530 FRAMEBORDER=0 src='" . $schedulerPath .'?PRO_UID=' .$sProcessUID. "'></iframe>";
      echo $html;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Presents a small list of Scheduled Task Logs of the process
   * @param string $sProcessUID
   * @return void
   */

  function logCaseSchedulerList($sProcessUID = '') {
    try {
      $oLogCaseScheduler = new LogCasesScheduler();
      $aRows = $oLogCaseScheduler->getAll();

      $fieldNames = Array(
          'PRO_UID' => 'char',
          'TAS_UID' => 'char',
          'USR_NAME' => 'char',
          'EXEC_DATE' => 'char',
          'EXEC_HOUR' => 'char',
          'RESULT' => 'char',
          'SCH_UID' => 'char',
          'WS_CREATE_CASE_STATUS' => 'char',
          'WS_ROUTE_CASE_STATUS' => 'char',
      );

      $aRows = array_merge(Array($fieldNames), $aRows);

      $_DBArray['log_cases_scheduler'] = $aRows;
      $_SESSION['_DBArray'] = $_DBArray;

      $oCriteria = new Criteria('dbarray');
      $oCriteria->setDBArrayTable('log_cases_scheduler');    
     
      $G_PUBLISH = new Publisher;
      $G_PUBLISH->ROWS_PER_PAGE = 10;
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'cases/cases_Scheduler_Log', $oCriteria);
      $G_PUBLISH->oPropelTable->rowsPerPage = 10;
      G::RenderPage('publish', 'blank');
    } catch (Exception $oError) {
      throw ($oError);
    }
  }
  
  /*
   * Presents a small list of messages of the process
   * @param string $sProcessUID
   * @return void
   */

  function messagesList($sProcessUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      global $G_PUBLISH;
      $aFields ['PRO_UID'] = $sProcessUID;
      $aFields ['SYS_LANG'] = SYS_LANG;
      $G_PUBLISH = new Publisher ( );
      //$G_PUBLISH->AddContent('pagedtable', 'paged-table', 'messages/messages_ShortList', $this->getMessagesCriteria($sProcessUID));
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Presents a small list of report tables of the process
   * @param string $sProcessUID
   * @return void
   */

  function reportTablesList($sProcessUID = '') {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'reportTables/reportTables_ShortList', $this->getReportTablesCriteria($sProcessUID), $aFields);
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the report tables list criteria object
   * @param string $sProcessUID
   * @return object
   */

  function getReportTablesCriteria($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(ReportTablePeer::REP_TAB_UID);
    $oCriteria->addSelectColumn(ReportTablePeer::PRO_UID);
    // $oCriteria->addAsColumn ( 'REP_TAB_TITLE', 'C.CON_VALUE' );
    $oCriteria->addAsColumn('REP_TAB_TITLE', "CASE WHEN C.CON_VALUE IS NULL THEN (SELECT DISTINCT MAX(A.CON_VALUE) FROM CONTENT A WHERE A.CON_ID = REPORT_TABLE.REP_TAB_UID ) ELSE C.CON_VALUE  END ");
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(ReportTablePeer::REP_TAB_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'REP_TAB_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(ReportTablePeer::PRO_UID, $sProcessUID);
    return $oCriteria;
  }

  /*
   * Show the current pattern
   * @param string $sProcessUID
   * @param string $sTaskUID
   * @return boolean
   */

  function currentPattern($sProcessUID, $sTaskUID) {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);
      $aFields = array();
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(RoutePeer::PRO_UID, $sProcessUID);
      $oCriteria->add(RoutePeer::TAS_UID, $sTaskUID);
      $oCriteria->addAscendingOrderByColumn(RoutePeer::ROU_CASE);
      $oDataset = RoutePeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $aRow = $oDataset->getRow();
      if (is_array($aRow)) {
        $aFields ['ROU_TYPE'] = $aRow ['ROU_TYPE'];
        $aFields ['ROU_TYPE_OLD'] = $aRow ['ROU_TYPE'];
        switch ($aRow ['ROU_TYPE']) {
          case 'SEQUENTIAL' :
          case 'SEC-JOIN' :
            $aFields ['ROU_UID'] = $aRow ['ROU_UID'];
            $aFields ['ROU_NEXT_TASK'] = $aRow ['ROU_NEXT_TASK'];
            $aFields ['ROU_TO_LAST_USER'] = $aRow ['ROU_TO_LAST_USER'];
            if ($aRow ['ROU_TYPE'] == 'SEQUENTIAL') {
              $sXmlform = 'patterns_Sequential';
            } else {
              $sXmlform = 'patterns_ParallelJoin';
            }
            break;
          case 'SELECT' :
            $aFields ['GRID_SELECT_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
            $aFields ['GRID_SELECT_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
            $aFields ['GRID_SELECT_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
            $aFields ['GRID_SELECT_TYPE'] ['ROU_TO_LAST_USER'] [$aRow ['ROU_CASE']] = $aRow ['ROU_TO_LAST_USER'];
            while ($aRow = $oDataset->getRow()) {
              $aFields ['GRID_SELECT_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
              $aFields ['GRID_SELECT_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
              $aFields ['GRID_SELECT_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
              $aFields ['GRID_SELECT_TYPE'] ['ROU_TO_LAST_USER'] [$aRow ['ROU_CASE']] = $aRow ['ROU_TO_LAST_USER'];
              $oDataset->next();
            }
            $sXmlform = 'patterns_Select';
            break;
          case 'EVALUATE' :
            G::LoadClass('xmlfield_InputPM');
            $aFields ['GRID_EVALUATE_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
            $aFields ['GRID_EVALUATE_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
            $aFields ['GRID_EVALUATE_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
            $aFields ['GRID_EVALUATE_TYPE'] ['ROU_TO_LAST_USER'] [$aRow ['ROU_CASE']] = $aRow ['ROU_TO_LAST_USER'];
            while ($aRow = $oDataset->getRow()) {
              $aFields ['GRID_EVALUATE_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
              $aFields ['GRID_EVALUATE_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
              $aFields ['GRID_EVALUATE_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
              $aFields ['GRID_EVALUATE_TYPE'] ['ROU_TO_LAST_USER'] [$aRow ['ROU_CASE']] = $aRow ['ROU_TO_LAST_USER'];
              $oDataset->next();
            }
            $sXmlform = 'patterns_Evaluate';
            break;
          case 'PARALLEL' :
            $aFields ['GRID_PARALLEL_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
            $aFields ['GRID_PARALLEL_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
            while ($aRow = $oDataset->getRow()) {
              $aFields ['GRID_PARALLEL_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
              $aFields ['GRID_PARALLEL_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
              $oDataset->next();
            }
            $sXmlform = 'patterns_Parallel';
            break;
          case 'PARALLEL-BY-EVALUATION' :
            G::LoadClass('xmlfield_InputPM');
            $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
            $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
            $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
            while ($aRow = $oDataset->getRow()) {
              $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
              $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
              $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
              $oDataset->next();
            }
            $sXmlform = 'patterns_ParallelByEvaluation';
            break;
            case 'DISCRIMINATOR' :
               G::LoadClass ( 'xmlfield_InputPM' );
               $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']]       = $aRow ['ROU_UID'];
               $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
               $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
               $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_OPTIONAL'] [$aRow ['ROU_CASE']] = $aRow ['ROU_OPTIONAL'];
               G::LoadClass('tasks');
               $oTasks = new Tasks();
               $routeData = $oTasks->getRouteByType($sProcessUID, $aRow['ROU_NEXT_TASK'], $aRow['ROU_TYPE']);
               $aFields['ROUTE_COUNT'] =  count($routeData);
               $sXmlform = 'patterns_Discriminator';
            break;
        }
      }
      $aFields ['action'] = 'savePattern';
      $aFields ['LANG'] = SYS_LANG;
      $aFields ['PROCESS'] = $sProcessUID;
      $aFields ['TASK'] = $sTaskUID;
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('xmlform', 'xmlform', 'patterns/' . $sXmlform, '', $aFields, '../patterns/patterns_Ajax');
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Show the new pattern form
   * @param string $sProcessUID
   * @param string $sTaskUID
   * @param string $sNextTask
   * @param string $sType
   * @return boolean
   */

  function newPattern($sProcessUID, $sTaskUID, $sNextTask, $sType) {
    try {
      $oProcess = new Process ( );
      $aFields = $oProcess->load($sProcessUID);
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);
      $aFields = array();
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(RoutePeer::PRO_UID, $sProcessUID);
      $oCriteria->add(RoutePeer::TAS_UID, $sTaskUID);
      $oCriteria->addAscendingOrderByColumn(RoutePeer::ROU_CASE);
      $oDataset = RoutePeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $aRow = $oDataset->getRow();
      if (is_array($aRow)) {
        $aFields ['ROU_TYPE_OLD'] = $aRow ['ROU_TYPE'];
        if ($sType == $aFields ['ROU_TYPE_OLD']) {
          switch ($sType) {
            case 'SELECT' :
              $aFields ['GRID_SELECT_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
              $aFields ['GRID_SELECT_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
              $aFields ['GRID_SELECT_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
              $aFields ['GRID_SELECT_TYPE'] ['ROU_TO_LAST_USER'] [$aRow ['ROU_CASE']] = $aRow ['ROU_TO_LAST_USER'];
              while ($aRow = $oDataset->getRow()) {
                $aFields ['GRID_SELECT_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
                $aFields ['GRID_SELECT_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
                $aFields ['GRID_SELECT_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
                $aFields ['GRID_SELECT_TYPE'] ['ROU_TO_LAST_USER'] [$aRow ['ROU_CASE']] = $aRow ['ROU_TO_LAST_USER'];
                $oDataset->next();
              }
              break;
            case 'EVALUATE' :
              $aFields ['GRID_EVALUATE_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
              $aFields ['GRID_EVALUATE_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
              $aFields ['GRID_EVALUATE_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
              $aFields ['GRID_EVALUATE_TYPE'] ['ROU_TO_LAST_USER'] [$aRow ['ROU_CASE']] = $aRow ['ROU_TO_LAST_USER'];
              while ($aRow = $oDataset->getRow()) {
                $aFields ['GRID_EVALUATE_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
                $aFields ['GRID_EVALUATE_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
                $aFields ['GRID_EVALUATE_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
                $aFields ['GRID_EVALUATE_TYPE'] ['ROU_TO_LAST_USER'] [$aRow ['ROU_CASE']] = $aRow ['ROU_TO_LAST_USER'];
                $oDataset->next();
              }
              break;
            case 'PARALLEL' :
              $aFields ['GRID_PARALLEL_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
              $aFields ['GRID_PARALLEL_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
              while ($aRow = $oDataset->getRow()) {
                $aFields ['GRID_PARALLEL_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
                $aFields ['GRID_PARALLEL_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
                $oDataset->next();
              }
              break;
            case 'PARALLEL-BY-EVALUATION' :
              $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
              $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
              $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
              while ($aRow = $oDataset->getRow()) {
                $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']] = $aRow ['ROU_UID'];
                $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
                $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
                $oDataset->next();
              }
              break;
            case 'DISCRIMINATOR' :
              $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']]       = $aRow ['ROU_UID'];
              $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
              $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
              $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_OPTIONAL'] [$aRow ['ROU_CASE']] = $aRow ['ROU_OPTIONAL'];
              while ( $aRow = $oDataset->getRow () ) {
                $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_UID'] [$aRow ['ROU_CASE']]       = $aRow ['ROU_UID'];
                $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_NEXT_TASK'] [$aRow ['ROU_CASE']] = $aRow ['ROU_NEXT_TASK'];
                $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_CONDITION'] [$aRow ['ROU_CASE']] = $aRow ['ROU_CONDITION'];
                $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_OPTIONAL'] [$aRow ['ROU_CASE']] = $aRow ['ROU_OPTIONAL'];
                $oDataset->next ();
              }
              break;
          }
        } else {

        }
      }
      switch ($sType) {
        case 'SEQUENTIAL' :
        case 'SEC-JOIN' :
          $aFields ['ROU_NEXT_TASK'] = $sNextTask;
          break;
        case 'SELECT' :
          $iRow = (isset($aFields ['GRID_SELECT_TYPE']) ? count($aFields ['GRID_SELECT_TYPE'] ['ROU_UID']) + 1 : 0);
          $aFields ['GRID_SELECT_TYPE'] ['ROU_UID'] [$iRow] = '';
          $aFields ['GRID_SELECT_TYPE'] ['ROU_NEXT_TASK'] [$iRow] = $sNextTask;
          $aFields ['GRID_SELECT_TYPE'] ['ROU_CONDITION'] [$iRow] = '';
          $aFields ['GRID_SELECT_TYPE'] ['ROU_TO_LAST_USER'] [$iRow] = '';
          break;
        case 'EVALUATE' :
          $iRow = (isset($aFields ['GRID_PARALLEL_EVALUATION_TYPE']) ? count($aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_UID']) + 1 : 0);
          $aFields ['GRID_EVALUATE_TYPE'] ['ROU_UID'] [$iRow] = '';
          $aFields ['GRID_EVALUATE_TYPE'] ['ROU_NEXT_TASK'] [$iRow] = $sNextTask;
          $aFields ['GRID_EVALUATE_TYPE'] ['ROU_CONDITION'] [$iRow] = '';
          $aFields ['GRID_EVALUATE_TYPE'] ['ROU_TO_LAST_USER'] [$iRow] = '';
          break;
        case 'PARALLEL' :
          $iRow = (isset($aFields ['GRID_PARALLEL_TYPE']) ? count($aFields ['GRID_PARALLEL_TYPE'] ['ROU_UID']) + 1 : 0);
          $aFields ['GRID_PARALLEL_TYPE'] ['ROU_UID'] [$iRow] = '';
          $aFields ['GRID_PARALLEL_TYPE'] ['ROU_NEXT_TASK'] [$iRow] = $sNextTask;
          $aFields ['GRID_PARALLEL_TYPE'] ['ROU_CONDITION'] [$iRow] = '';
          $aFields ['GRID_PARALLEL_TYPE'] ['ROU_TO_LAST_USER'] [$iRow] = '';
          break;
        case 'PARALLEL-BY-EVALUATION' :
          $iRow = (isset($aFields ['GRID_PARALLEL_EVALUATION_TYPE']) ? count($aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_UID']) + 1 : 0);
          $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_UID'] [$iRow] = '';
          $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_NEXT_TASK'] [$iRow] = $sNextTask;
          $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_CONDITION'] [$iRow] = '';
          $aFields ['GRID_PARALLEL_EVALUATION_TYPE'] ['ROU_TO_LAST_USER'] [$iRow] = '';
          break;
        case 'DISCRIMINATOR' :
          $iRow = (isset ( $aFields ['GRID_DISCRIMINATOR_TYPE'] ) ? count ( $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_UID'] ) + 1 : 0);
          $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_UID'] [$iRow]          = '';
          $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_NEXT_TASK'] [$iRow]    = $sNextTask;
          $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_CONDITION'] [$iRow]    = '';
          $aFields ['GRID_DISCRIMINATOR_TYPE'] ['ROU_TO_LAST_USER'] [$iRow] = '';
          break;
      }
      $aFields ['action'] = 'savePattern';
      $aFields ['LANG'] = SYS_LANG;
      $aFields ['PROCESS'] = $sProcessUID;
      $aFields ['TASK'] = $sTaskUID;
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('xmlform', 'xmlform', 'patterns/patterns_Current', '', $aFields, '../patterns/patterns_Ajax');
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /**
   * getNumberOfRoutes
   *
   * @param  string    $sProcessUID Default value empty
   * @param  string    $sTaskUID    Default value empty
   * @param  string    $sNextTask   Default value empty
   * @param  string    $sType       Default value empty
   * @return intenger  ( int ) $aRow ['ROUTE_NUMBER']
   */
  function getNumberOfRoutes($sProcessUID = '', $sTaskUID = '', $sNextTask = '', $sType = '') {
    try {
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn('COUNT(*) AS ROUTE_NUMBER');
      $oCriteria->add(RoutePeer::PRO_UID, $sProcessUID);
      $oCriteria->add(RoutePeer::TAS_UID, $sTaskUID);
      $oCriteria->add(RoutePeer::ROU_NEXT_TASK, $sNextTask);
      $oCriteria->add(RoutePeer::ROU_TYPE, $sType);
      $oDataset = RoutePeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $aRow = $oDataset->getRow();
      return (int) $aRow ['ROUTE_NUMBER'];
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /**
   * saveNewPattern
   *
   * @param  string    $sProcessUID Default value empty
   * @param  string    $sTaskUID    Default value empty
   * @param  string    $sNextTask   Default value empty
   * @param  string    $sType       Default value empty
   * @return array     void
   */
  function saveNewPattern($sProcessUID = '', $sTaskUID = '', $sNextTask = '', $sType = '') {
    try {
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn('COUNT(*) AS ROUTE_NUMBER');
      $oCriteria->add(RoutePeer::PRO_UID, $sProcessUID);
      $oCriteria->add(RoutePeer::TAS_UID, $sTaskUID);
      $oCriteria->add(RoutePeer::ROU_TYPE, $sType);
      $oDataset = RoutePeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $aRow = $oDataset->getRow();
      $aFields ['PRO_UID'] = $sProcessUID;
      $aFields ['TAS_UID'] = $sTaskUID;
      $aFields ['ROU_NEXT_TASK'] = $sNextTask;
      $aFields ['ROU_TYPE'] = $sType;
      $aFields ['ROU_CASE'] = (int) $aRow ['ROUTE_NUMBER'] + 1;

      $oRoute = new Route ( );
      $oRoute->create($aFields);
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Delete a derivation rule
   * @param string $sTaskUID
   * @return boolean
   */

  function deleteDerivation($sTaskUID = '') {
    try {
      $oTask = new Task ( );
      $aFields = $oTask->load($sTaskUID);
      $oCriteria = new Criteria('workflow');
      $oCriteria->add(RoutePeer::TAS_UID, $sTaskUID);
      RoutePeer::doDelete($oCriteria);
      return true;
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /**
   * getConditionProcessList
   *
   * @return object    $oCriteria
   */
  function getConditionProcessList() {
    $aProcesses = array();
    $aProcesses [] = array('PRO_UID' => 'char', 'PRO_TITLE' => 'char', 'PRO_DESCRIPTION' => 'char', 'PRO_STATUS' => 'char', 'PRO_CATEGORY' => 'char', 'PRO_CATEGORY_LABEL' => 'char');
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(ProcessPeer::PRO_UID);
    $oCriteria->add(ProcessPeer::PRO_STATUS, 'DISABLED', Criteria::NOT_EQUAL);
    $oDataset = ProcessPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);



    $oDataset->next();
    $oProcess = new Process ( );
    while ($aRow = $oDataset->getRow()) {
      $aProcess = $oProcess->load($aRow ['PRO_UID']);
      $aProcesses [] = array('PRO_UID' => $aProcess ['PRO_UID'], 'PRO_TITLE' => $aProcess ['PRO_TITLE'], 'PRO_DESCRIPTION' => $aProcess ['PRO_DESCRIPTION'], 'PRO_STATUS' => ($aProcess ['PRO_STATUS'] == 'ACTIVE' ? G::LoadTranslation('ID_ACTIVE') : G::LoadTranslation('ID_INACTIVE')), 'PRO_CATEGORY' => $aProcess ['PRO_CATEGORY'], 'PRO_CATEGORY_LABEL' => $aProcess ['PRO_CATEGORY_LABEL']);
      $oDataset->next();
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['processes'] = $aProcesses;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('processes');
    return $oCriteria;
  }

  /*
   * Show the dynaforms for the supervisors
   * @param string $sProcessUID
   * @return boolean
   */

  function supervisorDynaforms($sProcessUID) {
    try {
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'dynaforms/dynaforms_Supervisor', $this->getSupervisorDynaformsCriteria($sProcessUID), array('PRO_UID' => $sProcessUID));
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /**
   * supervisorInputs
   *
   * @param  string     $sProcessUID
   * @return boolean    true
   * @throw  Exception  $oError
   */
  function supervisorInputs($sProcessUID) {
    try {
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'inputdocs/inputdocs_Supervisor', $this->getSupervisorInputsCriteria($sProcessUID), array('PRO_UID' => $sProcessUID));
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /**
   * webEntry
   *
   * @param  string    $sProcessUID
   * @return boolean    true
   * @throw  Exception  $oError
   */
  function webEntry($sProcessUID) {
    try {
      global $G_PUBLISH;
      global $G_FORM;
      $G_PUBLISH = new Publisher ( );

      if (G::is_https ())
        $http = 'https://';
      else
        $http = 'http://';

      $link = $http . $_SERVER ['HTTP_HOST'] . '/sys' . SYS_SYS . '/' . SYS_LANG . '/' . SYS_SKIN . '/' . $sProcessUID . '/';

      $row = array();
      $c = 0;

      /*
        $oTask = new Task ( );
        $TaskFields = $oTask->kgetassigType ( $sProcessUID , $tas='');
       */
      $TaskFields ['TAS_ASSIGN_TYPE'] = '';
      $row [] = array('W_TITLE' => '', 'W_DELETE' => '', 'TAS_ASSIGN_TYPE' => $TaskFields ['TAS_ASSIGN_TYPE']);

      if (is_dir(PATH_DATA . "sites" . PATH_SEP . SYS_SYS . PATH_SEP . "public" . PATH_SEP . $sProcessUID)) {
        $dir = opendir(PATH_DATA . "sites" . PATH_SEP . SYS_SYS . PATH_SEP . "public" . PATH_SEP . $sProcessUID);
        while ($archivo = readdir($dir)) { //print $archivo." **** <hr>";
          if ($archivo != '.') {
            if ($archivo != '..') {
              $one = 0;
              $two = 0;

              $alink = $link . $archivo;

              $one = count(explode('wsClient.php', $archivo));
              $two = count(explode('Post.php', $archivo));

              if ($one == 1 && $two == 1) {
                $arlink = "<a href='" . $alink . "' target='blank'><font color='#9999CC'>" . $alink . "</font></a>";
                $linkdelete = sprintf("<a href='javascript:webEntry_delete(\"%s\",\"%s\",\"%s\");'><font color='red'>delete</font></a>", $alink, $archivo, $sProcessUID);
                $row [] = array('W_LINK' => $arlink, 'W_FILENAME' => $archivo, 'W_PRO_UID' => $sProcessUID);
              }
            }
          }
        }
      }
      global $_DBArray;
      $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['reports'] = $row;
      $_SESSION ['_DBArray'] = $_DBArray;
      G::LoadClass('ArrayPeer');
      $oCriteria = new Criteria('dbarray');
      $oCriteria->setDBArrayTable('reports');

      //if ($TaskFields['TAS_ASSIGN_TYPE'] == 'BALANCED') {
      //$G_PUBLISH->AddContent('xmlform', 'xmlform', 'dynaforms/dynaforms_WebEntry', '', array('PRO_UID' => $sProcessUID, 'LANG' => SYS_LANG));
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'dynaforms/dynaforms_WebEntryList', $oCriteria, array('PRO_UID' => $sProcessUID, 'LANG' => SYS_LANG));
      /* }else{
        $aMessage['MESSAGE'] = G::loadTranslation(  'WEBEN_ONLY_BALANCED' );
        $G_PUBLISH->AddContent ( 'xmlform', 'xmlform', 'login/showMessage', '',$aMessage );

        } */
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /**
   * webEntry_new
   *
   * @param   string     $sProcessUID
   * @return  boolean    true
   * @throw   Exception  $oError
   */
  function webEntry_new($sProcessUID) {
    try {
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('xmlform', 'xmlform', 'dynaforms/dynaforms_WebEntry', '', array('PRO_UID' => $sProcessUID, 'LANG' => SYS_LANG));
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the supervisors dynaforms list criteria object
   * @param string $sProcessUID
   * @return object
   */

  function getSupervisorDynaformsCriteria($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_UID);
    $oCriteria->addSelectColumn(StepSupervisorPeer::PRO_UID);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_TYPE_OBJ);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_UID_OBJ);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_POSITION);
    $oCriteria->addAsColumn('DYN_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(StepSupervisorPeer::STEP_UID_OBJ, DynaformPeer::DYN_UID);
    $aConditions [] = array(StepSupervisorPeer::STEP_TYPE_OBJ, $sDelimiter . 'DYNAFORM' . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(StepSupervisorPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(StepSupervisorPeer::STEP_TYPE_OBJ, 'DYNAFORM');
    $oCriteria->addAscendingOrderByColumn(StepSupervisorPeer::STEP_POSITION);
    return $oCriteria;
  }

  /*
   * Return the supervisors dynaforms list criteria object
   * @param string $sProcessUID
   * @return object
   */

  function getSupervisorInputsCriteria($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_UID);
    $oCriteria->addSelectColumn(StepSupervisorPeer::PRO_UID);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_TYPE_OBJ);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_UID_OBJ);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_POSITION);
    $oCriteria->addAsColumn('INP_DOC_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(StepSupervisorPeer::STEP_UID_OBJ, InputDocumentPeer::INP_DOC_UID);
    $aConditions [] = array(StepSupervisorPeer::STEP_TYPE_OBJ, $sDelimiter . 'INPUT_DOCUMENT' . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $aConditions = array();
    $aConditions [] = array(InputDocumentPeer::INP_DOC_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(StepSupervisorPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(StepSupervisorPeer::STEP_TYPE_OBJ, 'INPUT_DOCUMENT');
    $oCriteria->addAscendingOrderByColumn(StepSupervisorPeer::STEP_POSITION);
    return $oCriteria;
  }

  /*
   * Show the available dynaforms for the supervisors
   * @param string $sProcessUID
   * @return boolean
   */

  function availableSupervisorDynaforms($sProcessUID) {
    try {
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'dynaforms/dynaforms_AvailableSupervisorDynaforms', $this->getAvailableSupervisorDynaformsCriteria($sProcessUID), array('PRO_UID' => $sProcessUID));
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Show the available input documents for the supervisors
   * @param string $sProcessUID
   * @return boolean
   */

  function availableSupervisorInputs($sProcessUID) {
    try {
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'inputdocs/inputdocs_AvailableSupervisorInputs', $this->getAvailableSupervisorInputsCriteria($sProcessUID), array('PRO_UID' => $sProcessUID));
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /*
   * Return the available supervisors dynaforms list criteria object
   * @param string $sProcessUID
   * @return object
   */

  function getAvailableSupervisorDynaformsCriteria($sProcessUID = '') {
    $oCriteria = $this->getSupervisorDynaformsCriteria($sProcessUID);
    $oDataset = StepSupervisorPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aUIDS = array();
    while ($aRow = $oDataset->getRow()) {
      $aUIDS [] = $aRow ['STEP_UID_OBJ'];
      $oDataset->next();
    }
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(DynaformPeer::DYN_UID);
    $oCriteria->addSelectColumn(DynaformPeer::PRO_UID);
    $oCriteria->addAsColumn('DYN_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(DynaformPeer::DYN_TYPE, 'xmlform');
    $oCriteria->add(DynaformPeer::DYN_UID, $aUIDS, Criteria::NOT_IN);
    return $oCriteria;
  }

  /*
   * Return the available supervisors input documents list criteria object
   * @param string $sProcessUID
   * @return object
   */

  function getAvailableSupervisorInputsCriteria($sProcessUID = '') {
    $oCriteria = $this->getSupervisorInputsCriteria($sProcessUID);
    $oDataset = StepSupervisorPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aUIDS = array();
    while ($aRow = $oDataset->getRow()) {
      $aUIDS [] = $aRow ['STEP_UID_OBJ'];
      $oDataset->next();
    }
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(InputDocumentPeer::INP_DOC_UID);
    $oCriteria->addSelectColumn(InputDocumentPeer::PRO_UID);
    $oCriteria->addAsColumn('INP_DOC_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(InputDocumentPeer::INP_DOC_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(InputDocumentPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(InputDocumentPeer::INP_DOC_UID, $aUIDS, Criteria::NOT_IN);
    return $oCriteria;
  }

  /**
   * assignSupervisorStep
   *
   * @param  string    $sProcessUID
   * @param  string    $sObjType
   * @param  string    $sObjUID
   * @return void
   */
  function assignSupervisorStep($sProcessUID, $sObjType, $sObjUID) {
    $oStepSupervisor = new StepSupervisor ( );
    $oStepSupervisor->create(array('PRO_UID' => $sProcessUID, 'STEP_TYPE_OBJ' => $sObjType, 'STEP_UID_OBJ' => $sObjUID, 'STEP_POSITION' => $oStepSupervisor->getNextPosition($sProcessUID, $sObjType)));
  }

  /**
   * removeSupervisorStep
   *
   * @param  string    $sStepUID
   * @param  string    $sProcessUID
   * @param  string    $sObjType
   * @param  string    $sObjUID
   * @param  integer   $iPosition
   * @return void
   */
  function removeSupervisorStep($sStepUID, $sProcessUID, $sObjType, $sObjUID, $iPosition) {
    $oStepSupervisor = new StepSupervisor ( );
    $oStepSupervisor->remove($sStepUID);
    $oStepSupervisor->reorderPositions($sProcessUID, $iPosition, $sObjType);
  }

  /**
   * listProcessesUser
   *
   * @param  string           $sProcessUID
   * @return object(Criteria) $oCriteria
   */
  function listProcessesUser($sProcessUID) {

    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(ProcessUserPeer::PU_UID);
    $oCriteria->addSelectColumn(ProcessUserPeer::USR_UID);
    $oCriteria->addSelectColumn(ProcessUserPeer::PRO_UID);
    $oCriteria->addSelectColumn(ProcessUserPeer::PU_TYPE);
    $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_EMAIL);
    $oCriteria->addJoin(ProcessUserPeer::USR_UID, UsersPeer::USR_UID, Criteria::LEFT_JOIN);
    $oCriteria->add(ProcessUserPeer::PRO_UID, $sProcessUID);
    return $oCriteria;
  }

  /**
   * listNoProcessesUser
   *
   * @param  string           $sProcessUID
   * @return object(Criteria) $oCriteria
   */
  function listNoProcessesUser($sProcessUID) {
    G::LoadSystem('rbac');

    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(ProcessUserPeer::USR_UID);
    $oCriteria->add(ProcessUserPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(ProcessUserPeer::PU_TYPE, 'SUPERVISOR');
    $oDataset = ProcessUserPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aUIDS = array();
    while ($aRow = $oDataset->getRow()) {
      $aUIDS [] = $aRow ['USR_UID'];
      $oDataset->next();
    }
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(UsersPeer::USR_UID);
    $oCriteria->add(UsersPeer::USR_UID, $aUIDS, Criteria::NOT_IN);
    $oDataset = UsersPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aUIDS = array();
    $oRBAC = RBAC::getSingleton ();
    while ($aRow = $oDataset->getRow()) {
      $oRBAC->loadUserRolePermission($oRBAC->sSystem, $aRow ['USR_UID']);
      $aPermissions = $oRBAC->aUserInfo [$oRBAC->sSystem] ['PERMISSIONS'];
      $bInclude = false;
      foreach ($aPermissions as $aPermission) {
        if ($aPermission ['PER_CODE'] == 'PM_SUPERVISOR') {
          $bInclude = true;
        }
      }
      if ($bInclude) {
        $aUIDS [] = $aRow ['USR_UID'];
      }
      $oDataset->next();
    }
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(UsersPeer::USR_UID);
    $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
    $oCriteria->add(UsersPeer::USR_UID, $aUIDS, Criteria::IN);
    return $oCriteria;
  }

  /**
   * assignProcessUser
   *
   * @param  string    $sProcessUID
   * @param  string    $sUsrUID
   * @return void
   */
  function assignProcessUser($sProcessUID, $sUsrUID) {
    $oProcessUser = new ProcessUser ( );
    $oProcessUser->create(array('PU_UID' => G::generateUniqueID (), 'PRO_UID' => $sProcessUID, 'USR_UID' => $sUsrUID, 'PU_TYPE' => 'SUPERVISOR'));
  }

  /**
   * removeProcessUser
   *
   * @param  string    $sPUUID
   * @return void
   */
  function removeProcessUser($sPUUID) {
    $oProcessUser = new ProcessUser ( );
    $oProcessUser->remove($sPUUID);
  }

  /**
   * getObjectsPermissionsCriteria
   *
   * @param  string    $sProcessUID
   * @return object(Criteria) $oCriteria
   */
  function getObjectsPermissionsCriteria($sProcessUID) {
    G::LoadClass('case');
    Cases::verifyTable ();
    $aObjectsPermissions = array();
    $aObjectsPermissions [] = array('OP_UID' => 'char', 'TASK_TARGET' => 'char', 'GROUP_USER' => 'char', 'TASK_SOURCE' => 'char', 'OBJECT_TYPE' => 'char', 'OBJECT' => 'char', 'PARTICIPATED' => 'char', 'ACTION' => 'char', 'OP_CASE_STATUS' => 'char');
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_UID);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::TAS_UID);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::USR_UID);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_USER_RELATION);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_TASK_SOURCE);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_PARTICIPATE);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_OBJ_TYPE);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_OBJ_UID);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_ACTION);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_CASE_STATUS);
    $oCriteria->add(ObjectPermissionPeer::PRO_UID, $sProcessUID);
    $oDataset = ObjectPermissionPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      //Obtain task target
      if (($aRow ['TAS_UID'] != '') && ($aRow ['TAS_UID'] != '0')) {
        try {
          $oTask = new Task ( );
          $aFields = $oTask->load($aRow ['TAS_UID']);
          $sTaskTarget = $aFields ['TAS_TITLE'];
        } catch (Exception $oError) {
          $sTaskTarget = 'All Tasks';
        }
      } else {
        $sTaskTarget = G::LoadTranslation('ID_ANY_TASK');
      }
      //Obtain user or group
      if ($aRow ['OP_USER_RELATION'] == 1) {
        $oUser = new Users ( );
        $aFields = $oUser->load($aRow ['USR_UID']);
        $sUserGroup = $aFields ['USR_FIRSTNAME'] . ' ' . $aFields ['USR_LASTNAME'] . ' (' . $aFields ['USR_USERNAME'] . ')';
      } else {
        $oGroup = new Groupwf ( );
        if ($aRow ['USR_UID'] != '') {
          try {
            $aFields = $oGroup->load($aRow ['USR_UID']);
            $sUserGroup = $aFields ['GRP_TITLE'];
          } catch (Exception $oError) {
            $sUserGroup = '(GROUP DELETED)';
          }
        } else {
          $sUserGroup = G::LoadTranslation('ID_ANY');
        }
      }
      //Obtain task source
      if (($aRow ['OP_TASK_SOURCE'] != '') && ($aRow ['OP_TASK_SOURCE'] != '0')) {
        try {
          $oTask = new Task ( );
          $aFields = $oTask->load($aRow ['OP_TASK_SOURCE']);
          $sTaskSource = $aFields ['TAS_TITLE'];
        } catch (Exception $oError) {
          $sTaskSource = 'All Tasks';
        }
      } else {
        $sTaskSource = G::LoadTranslation('ID_ANY_TASK');
      }
      //Obtain object and type
      switch ($aRow ['OP_OBJ_TYPE']) {
        case 'ANY' :
          $sObjectType = G::LoadTranslation('ID_ALL');
          $sObject = G::LoadTranslation('ID_ALL');
          break;       
        case 'DYNAFORM' :
          $sObjectType = G::LoadTranslation('ID_DYNAFORM');
          if (($aRow ['OP_OBJ_UID'] != '') && ($aRow ['OP_OBJ_UID'] != '0')) {
            $oDynaform = new Dynaform ( );
            $aFields = $oDynaform->load($aRow ['OP_OBJ_UID']);
            $sObject = $aFields ['DYN_TITLE'];
          } else {
            $sObject = G::LoadTranslation('ID_ALL');
          }
          break;
        case 'INPUT' :
          $sObjectType = G::LoadTranslation('ID_INPUT_DOCUMENT');
          if (($aRow ['OP_OBJ_UID'] != '') && ($aRow ['OP_OBJ_UID'] != '0')) {
            $oInputDocument = new InputDocument ( );
            $aFields = $oInputDocument->load($aRow ['OP_OBJ_UID']);
            $sObject = $aFields ['INP_DOC_TITLE'];
          } else {
            $sObject = G::LoadTranslation('ID_ALL');
          }
          break;
        case 'OUTPUT' :
          $sObjectType = G::LoadTranslation('ID_OUTPUT_DOCUMENT');
          if (($aRow ['OP_OBJ_UID'] != '') && ($aRow ['OP_OBJ_UID'] != '0')) {
            $oOutputDocument = new OutputDocument ( );
            $aFields = $oOutputDocument->load($aRow ['OP_OBJ_UID']);
            $sObject = $aFields ['OUT_DOC_TITLE'];
          } else {
            $sObject = G::LoadTranslation('ID_ALL');
          }
          break;
      }
      //Participated
      if ($aRow ['OP_PARTICIPATE'] == 0) {
        $sParticipated = G::LoadTranslation('ID_NO');
      } else {
        $sParticipated = G::LoadTranslation('ID_YES');
      }
      //Obtain action (permission)
      $sAction = G::LoadTranslation('ID_' . $aRow ['OP_ACTION']);
      //Add to array
      $aObjectsPermissions [] = array('OP_UID' => $aRow ['OP_UID'], 'TASK_TARGET' => $sTaskTarget, 'GROUP_USER' => $sUserGroup, 'TASK_SOURCE' => $sTaskSource, 'OBJECT_TYPE' => $sObjectType, 'OBJECT' => $sObject, 'PARTICIPATED' => $sParticipated, 'ACTION' => $sAction, 'OP_CASE_STATUS' => $aRow ['OP_CASE_STATUS']);
      $oDataset->next();
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['objectsPermissions'] = $aObjectsPermissions;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('objectsPermissions');
    return $oCriteria;
  }

  function getExtObjectsPermissions($sProcessUID) {
    G::LoadClass('case');
    Cases::verifyTable ();
    $aObjectsPermissions = array();
    //$aObjectsPermissions [] = array('OP_UID' => 'char', 'TASK_TARGET' => 'char', 'GROUP_USER' => 'char', 'TASK_SOURCE' => 'char', 'OBJECT_TYPE' => 'char', 'OBJECT' => 'char', 'PARTICIPATED' => 'char', 'ACTION' => 'char', 'OP_CASE_STATUS' => 'char');
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_UID);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::TAS_UID);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::USR_UID);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_USER_RELATION);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_TASK_SOURCE);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_PARTICIPATE);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_OBJ_TYPE);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_OBJ_UID);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_ACTION);
    $oCriteria->addSelectColumn(ObjectPermissionPeer::OP_CASE_STATUS);
    $oCriteria->add(ObjectPermissionPeer::PRO_UID, $sProcessUID);
    $oDataset = ObjectPermissionPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      //Obtain task target
      if (($aRow ['TAS_UID'] != '') && ($aRow ['TAS_UID'] != '0')) {
        try {
          $oTask = new Task ( );
          $aFields = $oTask->load($aRow ['TAS_UID']);
          $sTaskTarget = $aFields ['TAS_TITLE'];
        } catch (Exception $oError) {
          $sTaskTarget = 'All Tasks';
        }
      } else {
        $sTaskTarget = G::LoadTranslation('ID_ANY_TASK');
      }
      //Obtain user or group
      if ($aRow ['OP_USER_RELATION'] == 1) {
        $oUser = new Users ( );
        $aFields = $oUser->load($aRow ['USR_UID']);
        $sUserGroup = $aFields ['USR_FIRSTNAME'] . ' ' . $aFields ['USR_LASTNAME'] . ' (' . $aFields ['USR_USERNAME'] . ')';
      } else {
        $oGroup = new Groupwf ( );
        if ($aRow ['USR_UID'] != '') {
          try {
            $aFields = $oGroup->load($aRow ['USR_UID']);
            $sUserGroup = $aFields ['GRP_TITLE'];
          } catch (Exception $oError) {
            $sUserGroup = '(GROUP DELETED)';
          }
        } else {
          $sUserGroup = G::LoadTranslation('ID_ANY');
        }
      }
      //Obtain task source
      if (($aRow ['OP_TASK_SOURCE'] != '') && ($aRow ['OP_TASK_SOURCE'] != '0')) {
        try {
          $oTask = new Task ( );
          $aFields = $oTask->load($aRow ['OP_TASK_SOURCE']);
          $sTaskSource = $aFields ['TAS_TITLE'];
        } catch (Exception $oError) {
          $sTaskSource = 'All Tasks';
        }
      } else {
        $sTaskSource = G::LoadTranslation('ID_ANY_TASK');
      }
      //Obtain object and type
      switch ($aRow ['OP_OBJ_TYPE']) {
        case 'ANY' :
          $sObjectType = G::LoadTranslation('ID_ALL');
          $sObject = G::LoadTranslation('ID_ALL');
          break;        
        case 'DYNAFORM' :
          $sObjectType = G::LoadTranslation('ID_DYNAFORM');
          if (($aRow ['OP_OBJ_UID'] != '') && ($aRow ['OP_OBJ_UID'] != '0')) {
            $oDynaform = new Dynaform ( );
            $aFields = $oDynaform->load($aRow ['OP_OBJ_UID']);
            $sObject = $aFields ['DYN_TITLE'];
          } else {
            $sObject = G::LoadTranslation('ID_ALL');
          }
          break;
        case 'INPUT' :
          $sObjectType = G::LoadTranslation('ID_INPUT_DOCUMENT');
          if (($aRow ['OP_OBJ_UID'] != '') && ($aRow ['OP_OBJ_UID'] != '0')) {
            $oInputDocument = new InputDocument ( );
            $aFields = $oInputDocument->load($aRow ['OP_OBJ_UID']);
            $sObject = $aFields ['INP_DOC_TITLE'];
          } else {
            $sObject = G::LoadTranslation('ID_ALL');
          }
          break;
        case 'OUTPUT' :
          $sObjectType = G::LoadTranslation('ID_OUTPUT_DOCUMENT');
          if (($aRow ['OP_OBJ_UID'] != '') && ($aRow ['OP_OBJ_UID'] != '0')) {
            $oOutputDocument = new OutputDocument ( );
            $aFields = $oOutputDocument->load($aRow ['OP_OBJ_UID']);
            $sObject = $aFields ['OUT_DOC_TITLE'];
          } else {
            $sObject = G::LoadTranslation('ID_ALL');
          }
          break;
      }
      //Participated
      if ($aRow ['OP_PARTICIPATE'] == 0) {
        $sParticipated = G::LoadTranslation('ID_NO');
      } else {
        $sParticipated = G::LoadTranslation('ID_YES');
      }
      //Obtain action (permission)
      $sAction = G::LoadTranslation('ID_' . $aRow ['OP_ACTION']);
      //Add to array
      $aObjectsPermissions [] = array('OP_UID' => $aRow ['OP_UID'], 'TASK_TARGET' => $sTaskTarget, 'GROUP_USER' => $sUserGroup, 'TASK_SOURCE' => $sTaskSource, 'OBJECT_TYPE' => $sObjectType, 'OBJECT' => $sObject, 'PARTICIPATED' => $sParticipated, 'ACTION' => $sAction, 'OP_CASE_STATUS' => $aRow ['OP_CASE_STATUS']);
      $oDataset->next();
    }
    return $aObjectsPermissions;
  }

  /**
   * objectsPermissionsList
   *
   * @param  string   $sProcessUID
   * @return boolean  true
   */
  function objectsPermissionsList($sProcessUID) {
    global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );
    $G_PUBLISH->AddContent('propeltable', 'paged-table', 'processes/processes_ObjectsPermissionsList', $this->getObjectsPermissionsCriteria($sProcessUID), array('PRO_UID' => $sProcessUID));
    G::RenderPage('publish', 'raw');
    return true;
  }

  /**
   * newObjectPermission
   *
   * @param  string   $sProcessUID
   * @return boolean  true
   */
  function newObjectPermission($sProcessUID) {
    $aUsersGroups = array();
    $aUsersGroups [] = array('UID' => 'char', 'LABEL' => 'char');
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(GroupwfPeer::GRP_UID);
    $oCriteria->addAsColumn('GRP_TITLE', ContentPeer::CON_VALUE);
    $aConditions = array();
    $aConditions [] = array(GroupwfPeer::GRP_UID, ContentPeer::CON_ID);
    $aConditions [] = array(ContentPeer::CON_CATEGORY, DBAdapter::getStringDelimiter () . 'GRP_TITLE' . DBAdapter::getStringDelimiter ());
    $aConditions [] = array(ContentPeer::CON_LANG, DBAdapter::getStringDelimiter () . SYS_LANG . DBAdapter::getStringDelimiter ());
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(GroupwfPeer::GRP_STATUS, 'ACTIVE');
    $oDataset = GroupwfPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aUsersGroups [] = array('UID' => '2|' . $aRow ['GRP_UID'], 'LABEL' => $aRow ['GRP_TITLE'] . ' (' . G::LoadTranslation('ID_GROUP') . ')');
      $oDataset->next();
    }
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(UsersPeer::USR_UID);
    $oCriteria->addSelectColumn(UsersPeer::USR_USERNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
    $oCriteria->add(UsersPeer::USR_STATUS, 'ACTIVE');
    $oDataset = UsersPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aUsersGroups [] = array('UID' => '1|' . $aRow ['USR_UID'], 'LABEL' => $aRow ['USR_FIRSTNAME'] . ' ' . $aRow ['USR_LASTNAME'] . ' (' . $aRow ['USR_USERNAME'] . ')');
      $oDataset->next();
    }
    $aAllObjects = array();
    $aAllObjects [] = array('UID' => 'char', 'LABEL' => 'char');
    $aAllDynaforms = array();
    $aAllDynaforms [] = array('UID' => 'char', 'LABEL' => 'char');
    $aAllInputs = array();
    $aAllInputs [] = array('UID' => 'char', 'LABEL' => 'char');
    $aAllOutputs = array();
    $aAllOutputs [] = array('UID' => 'char', 'LABEL' => 'char');
    $oCriteria = $this->getDynaformsCriteria($sProcessUID);
    $oCriteria->add(DynaformPeer::DYN_TYPE, 'XMLFORM');
    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aRow ['DYN_TITLE'] = (isset($aRow ['DYN_TITLE'])) ? $aRow ['DYN_TITLE'] : '';
      $aAllObjects [] = array('UID' => 'DYNAFORM|' . $aRow ['DYN_UID'],
          'LABEL' => $aRow ['DYN_TITLE'] . ' (' . G::LoadTranslation('ID_DYNAFORM') . ')');
      $aAllDynaforms [] = array('UID' => $aRow ['DYN_UID'],
          'LABEL' => $aRow ['DYN_TITLE']);
      $oDataset->next();
    }
    G::LoadClass('ArrayPeer');
    $oDataset = ArrayBasePeer::doSelectRS($this->getInputDocumentsCriteria($sProcessUID));
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAllObjects [] = array('UID' => 'INPUT_DOCUMENT|' . $aRow ['INP_DOC_UID'],
          'LABEL' => $aRow ['INP_DOC_TITLE'] . ' (' . G::LoadTranslation('ID_INPUT_DOCUMENT') . ')');
      $aAllInputs [] = array('UID' => $aRow ['INP_DOC_UID'],
          'LABEL' => $aRow ['INP_DOC_TITLE']);
      $oDataset->next();
    }
    G::LoadClass('ArrayPeer');
    $oDataset = ArrayBasePeer::doSelectRS($this->getOutputDocumentsCriteria($sProcessUID));
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAllObjects [] = array('UID' => 'OUTPUT_DOCUMENT|' . $aRow ['OUT_DOC_UID'], 'LABEL' => $aRow ['OUT_DOC_TITLE'] . ' (' . G::LoadTranslation('ID_OUTPUT_DOCUMENT') . ')');
      $aAllOutputs [] = array('UID' => $aRow ['OUT_DOC_UID'], 'LABEL' => $aRow ['OUT_DOC_TITLE']);
      $oDataset->next();
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['usersGroups'] = $aUsersGroups;
    $_DBArray ['allObjects'] = $aAllObjects;
    $_DBArray ['allDynaforms'] = $aAllDynaforms;
    $_DBArray ['allInputs'] = $aAllInputs;
    $_DBArray ['allOutputs'] = $aAllOutputs;
    $_SESSION ['_DBArray'] = $_DBArray;
    global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'processes/processes_NewObjectPermission', '', array('LANG' => SYS_LANG, 'PRO_UID' => $sProcessUID, 'ID_DELETE' => G::LoadTranslation('ID_DELETE')), 'processes_SaveObjectPermission');
    G::RenderPage('publish', 'raw');
    return true;
  }

  /**
   * editObjectPermission
   *
   * @param  string    $sOP_UID
   * @param  string    $sProcessUID
   * @return void
   */
  function editObjectPermission($sOP_UID, $sProcessUID) {

    $oCriteria = new Criteria ( );
    $oCriteria->add(ObjectPermissionPeer::OP_UID, $sOP_UID);
    $oDataset = ObjectPermissionPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aRows = $oDataset->getRow();

    $oCriteria = new Criteria ( );
    $oCriteria->add(GroupwfPeer::GRP_UID, $aRows ['USR_UID']);
    if (GroupwfPeer::doCount($oCriteria) == 1)
      $user = '2|' . $aRows ['USR_UID'];

    $oCriteria = new Criteria ( );
    $oCriteria->add(UsersPeer::USR_UID, $aRows ['USR_UID']);
    if (UsersPeer::doCount($oCriteria) == 1)
      $user = '1|' . $aRows ['USR_UID'];

    $aFields ['LANG'] = SYS_LANG;
    $aFields ['OP_UID'] = $aRows ['OP_UID'];
    $aFields ['PRO_UID'] = $aRows ['PRO_UID'];
    $aFields ['OP_CASE_STATUS'] = $aRows ['OP_CASE_STATUS'];
    $aFields ['TAS_UID'] = $aRows ['TAS_UID'];
    $aFields ['GROUP_USER'] = $user;
    $aFields ['OP_TASK_SOURCE'] = $aRows ['OP_TASK_SOURCE'];
    $aFields ['OP_PARTICIPATE'] = $aRows ['OP_PARTICIPATE'];
    $aFields ['OP_OBJ_TYPE'] = $aRows ['OP_OBJ_TYPE'];
    $aFields ['OP_ACTION'] = $aRows ['OP_ACTION'];

    switch ($aRows ['OP_OBJ_TYPE']) {
      /* case 'ANY':
        $aFields['OP_OBJ_TYPE'] = '';
        break; */
      case 'DYNAFORM' :
        $aFields ['DYNAFORMS'] = $aRows ['OP_OBJ_UID'];
        break;
      case 'INPUT' :
        $aFields ['INPUTS'] = $aRows ['OP_OBJ_UID'];
        break;
      case 'OUTPUT' :
        $aFields ['OUTPUTS'] = $aRows ['OP_OBJ_UID'];
        break;
    }

    $aUsersGroups = array();
    $aUsersGroups [] = array('UID' => 'char', 'LABEL' => 'char');
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(GroupwfPeer::GRP_UID);
    $oCriteria->addAsColumn('GRP_TITLE', ContentPeer::CON_VALUE);
    $aConditions = array();
    $aConditions [] = array(GroupwfPeer::GRP_UID, ContentPeer::CON_ID);
    $aConditions [] = array(ContentPeer::CON_CATEGORY, DBAdapter::getStringDelimiter () . 'GRP_TITLE' . DBAdapter::getStringDelimiter ());
    $aConditions [] = array(ContentPeer::CON_LANG, DBAdapter::getStringDelimiter () . SYS_LANG . DBAdapter::getStringDelimiter ());
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(GroupwfPeer::GRP_STATUS, 'ACTIVE');
    $oDataset = GroupwfPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aUsersGroups [] = array('UID' => '2|' . $aRow ['GRP_UID'], 'LABEL' => $aRow ['GRP_TITLE'] . ' (' . G::LoadTranslation('ID_GROUP') . ')');
      $oDataset->next();
    }
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(UsersPeer::USR_UID);
    $oCriteria->addSelectColumn(UsersPeer::USR_USERNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
    $oCriteria->add(UsersPeer::USR_STATUS, 'ACTIVE');
    $oDataset = UsersPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aUsersGroups [] = array('UID' => '1|' . $aRow ['USR_UID'], 'LABEL' => $aRow ['USR_FIRSTNAME'] . ' ' . $aRow ['USR_LASTNAME'] . ' (' . $aRow ['USR_USERNAME'] . ')');
      $oDataset->next();
    }

    $aAllObjects = array();
    $aAllObjects [] = array('UID' => 'char', 'LABEL' => 'char');
    $aAllDynaforms = array();
    $aAllDynaforms [] = array('UID' => 'char', 'LABEL' => 'char');
    $aAllInputs = array();
    $aAllInputs [] = array('UID' => 'char', 'LABEL' => 'char');
    $aAllOutputs = array();
    $aAllOutputs [] = array('UID' => 'char', 'LABEL' => 'char');
    //dynaforms
    $oCriteria = $this->getDynaformsCriteria($aRows ['PRO_UID']);
    $oCriteria->add(DynaformPeer::DYN_TYPE, 'XMLFORM');
    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAllObjects [] = array('UID' => 'DYNAFORM|' . $aRow ['DYN_UID'], 'LABEL' => $aRow ['DYN_TITLE'] . ' (' . G::LoadTranslation('ID_DYNAFORM') . ')');
      $aAllDynaforms [] = array('UID' => $aRow ['DYN_UID'], 'LABEL' => $aRow ['DYN_TITLE']);
      $oDataset->next();
    }
    //inputs
    G::LoadClass('ArrayPeer');
    $oDataset = ArrayBasePeer::doSelectRS($this->getInputDocumentsCriteria($sProcessUID));
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAllObjects [] = array('UID' => 'INPUT_DOCUMENT|' . $aRow ['INP_DOC_UID'], 'LABEL' => $aRow ['INP_DOC_TITLE'] . ' (' . G::LoadTranslation('ID_INPUT_DOCUMENT') . ')');
      $aAllInputs [] = array('UID' => $aRow ['INP_DOC_UID'], 'LABEL' => $aRow ['INP_DOC_TITLE']);
      $oDataset->next();
    }
    //outputs
    G::LoadClass('ArrayPeer');
    $oDataset = ArrayBasePeer::doSelectRS($this->getOutputDocumentsCriteria($sProcessUID));
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAllObjects [] = array('UID' => 'OUTPUT_DOCUMENT|' . $aRow ['OUT_DOC_UID'], 'LABEL' => $aRow ['OUT_DOC_TITLE'] . ' (' . G::LoadTranslation('ID_OUTPUT_DOCUMENT') . ')');
      $aAllOutputs [] = array('UID' => $aRow ['OUT_DOC_UID'], 'LABEL' => $aRow ['OUT_DOC_TITLE']);
      $oDataset->next();
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['usersGroups'] = $aUsersGroups;
    $_DBArray ['allObjects'] = $aAllObjects;
    $_DBArray ['allDynaforms'] = $aAllDynaforms;
    $_DBArray ['allInputs'] = $aAllInputs;
    $_DBArray ['allOutputs'] = $aAllOutputs;
    $_SESSION ['_DBArray'] = $_DBArray;

    global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );

    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'processes/processes_EditObjectPermission', '', $aFields, 'processes_SaveEditObjectPermission');
    G::RenderPage('publish', 'raw');
  }

  /**
   * caseTracker
   *
   * @param  string   $sProcessUID
   * @return boolean  true
   */
  function caseTracker($sProcessUID) {
    $oCriteria = new Criteria ( );
    $oCriteria->add(CaseTrackerPeer::PRO_UID, $sProcessUID);
    $oCaseTracker = new CaseTracker ( );
    if (CaseTrackerPeer::doCount($oCriteria) === 0) {
      $aCaseTracker = array('PRO_UID' => $sProcessUID, 'CT_MAP_TYPE' => 'PROCESSMAP', 'CT_DERIVATION_HISTORY' => 1, 'CT_MESSAGE_HISTORY' => 1);
      $oCaseTracker->create($aCaseTracker);
    } else {
      $aCaseTracker = $oCaseTracker->load($sProcessUID);
    }
    global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'tracker/tracker_Configuration', '', $aCaseTracker, '../tracker/tracker_Save');
    G::RenderPage('publish', 'raw');
    return true;
  }

  /**
   * caseTrackerObjects
   *
   * @param  string     $sProcessUID
   * @return boolean    true
   */
  function caseTrackerObjects($sProcessUID) {
    global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );
    $G_PUBLISH->AddContent('propeltable', 'paged-table', 'tracker/tracker_objectsList', $this->getCaseTrackerObjectsCriteria($sProcessUID), array('PRO_UID' => $sProcessUID));
    G::RenderPage('publish', 'raw');
    return true;
  }

  /**
   * getCaseTrackerObjectsCriteria
   *
   * @param  string           $sProcessUID
   * @return object(Criteria) $oCriteria
   */
  function getCaseTrackerObjectsCriteria($sProcessUID) {
    $aObjects = array();
    $aObjects [] = array('CTO_TITLE' => 'char', 'CTO_UID' => 'char', 'CTO_TYPE_OBJ' => 'char', 'CTO_UID_OBJ' => 'char', 'CTO_CONDITION' => 'char', 'CTO_POSITION' => 'integer');
    $oCriteria = new Criteria('workflow');
    $oCriteria->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
    $oCriteria->addAscendingOrderByColumn(CaseTrackerObjectPeer::CTO_POSITION);
    $oDataset = CaseTrackerObjectPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      try {
        switch ($aRow ['CTO_TYPE_OBJ']) {
          case 'DYNAFORM' :
            $oDynaform = new Dynaform ( );
            $aFields = $oDynaform->load($aRow ['CTO_UID_OBJ']);
            $sTitle = $aFields ['DYN_TITLE'];
            break;
          case 'INPUT_DOCUMENT' :
            $oInputDocument = new InputDocument ( );
            $aFields = $oInputDocument->load($aRow ['CTO_UID_OBJ']);
            $sTitle = $aFields ['INP_DOC_TITLE'];
            break;
          case 'OUTPUT_DOCUMENT' :
            $oOutputDocument = new OutputDocument ( );
            $aFields = $oOutputDocument->load($aRow ['CTO_UID_OBJ']);
            $sTitle = $aFields ['OUT_DOC_TITLE'];
            break;
        }
        $aObjects [] = array('CTO_TITLE' => $sTitle, 'CTO_UID' => $aRow ['CTO_UID'], 'CTO_TYPE_OBJ' => $aRow ['CTO_TYPE_OBJ'], 'CTO_UID_OBJ' => $aRow ['CTO_UID_OBJ'], 'CTO_CONDITION' => $aRow ['CTO_CONDITION'], 'CTO_POSITION' => $aRow ['CTO_POSITION']);
      } catch (Exception $oError) { //Nothing
      }
      $oDataset->next();
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['objects'] = $aObjects;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('objects');
    $oCriteria->addAscendingOrderByColumn('CTO_POSITION');
    return $oCriteria;
  }

  /**
   * availableCaseTrackerObjects
   *
   * @param  string     $sProcessUID
   * @return boolean    true
   */
  function availableCaseTrackerObjects($sProcessUID) {
    global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );
    $G_PUBLISH->AddContent('propeltable', 'paged-table', 'tracker/tracker_AvailableCaseTrackerObjects', $this->getAvailableCaseTrackerObjectsCriteria($sProcessUID), array('PRO_UID' => $sProcessUID));
    G::RenderPage('publish', 'raw');
    return true;
  }

  /**
   * getAvailableCaseTrackerObjectsCriteria
   *
   * @param  string             $sProcessUID Default value empty
   * @return object(Criteria)   $oCriteria
   */
  function getAvailableCaseTrackerObjectsCriteria($sProcessUID = '') {
    $oCriteria = $this->getCaseTrackerObjectsCriteria($sProcessUID);
    $oDataset = CaseTrackerObjectPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aDynaformsUIDS = array();
    $aInputsUIDS = array();
    $aOutputsUIDS = array();
    while ($aRow = $oDataset->getRow()) {
      switch ($aRow ['CTO_TYPE_OBJ']) {
        case 'DYNAFORM' :
          $aDynaformsUIDS [] = $aRow ['CTO_UID_OBJ'];
          break;
        case 'INPUT_DOCUMENT' :
          $aInputsUIDS [] = $aRow ['CTO_UID_OBJ'];
          break;
        case 'OUTPUT_DOCUMENT' :
          $aOutputsUIDS [] = $aRow ['CTO_UID_OBJ'];
          break;
      }
      $oDataset->next();
    }
    $aAvailableObjects = array();
    $aAvailableObjects [] = array('OBJECT_UID' => 'char', 'OBJECT_TYPE' => 'char', 'OBJECT_TITLE' => 'char');
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(DynaformPeer::DYN_UID);
    $oCriteria->addAsColumn('DYN_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(DynaformPeer::DYN_TYPE, 'xmlform');
    $oCriteria->add(DynaformPeer::DYN_UID, $aDynaformsUIDS, Criteria::NOT_IN);
    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAvailableObjects [] = array('OBJECT_UID' => $aRow ['DYN_UID'], 'OBJECT_TYPE' => 'DYNAFORM', 'OBJECT_TITLE' => $aRow ['DYN_TITLE']);
      $oDataset->next();
    }
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(InputDocumentPeer::INP_DOC_UID);
    $oCriteria->addAsColumn('INP_DOC_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(InputDocumentPeer::INP_DOC_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(InputDocumentPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(InputDocumentPeer::INP_DOC_UID, $aInputsUIDS, Criteria::NOT_IN);
    $oDataset = InputDocumentPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAvailableObjects [] = array('OBJECT_UID' => $aRow ['INP_DOC_UID'], 'OBJECT_TYPE' => 'INPUT_DOCUMENT', 'OBJECT_TITLE' => $aRow ['INP_DOC_TITLE']);
      $oDataset->next();
    }
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(OutputDocumentPeer::OUT_DOC_UID);
    $oCriteria->addAsColumn('OUT_DOC_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(OutputDocumentPeer::OUT_DOC_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'OUT_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(OutputDocumentPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(OutputDocumentPeer::OUT_DOC_UID, $aOutputsUIDS, Criteria::NOT_IN);

    $oDataset = OutputDocumentPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAvailableObjects [] = array('OBJECT_UID' => $aRow ['OUT_DOC_UID'], 'OBJECT_TYPE' => 'OUTPUT_DOCUMENT', 'OBJECT_TITLE' => $aRow ['OUT_DOC_TITLE']);
      $oDataset->next();
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['availableObjects'] = $aAvailableObjects;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('availableObjects');
    return $oCriteria;
  }

  /**
   * assignCaseTrackerObject
   *
   * @param  string    $sProcessUID
   * @param  string    $sObjType
   * @param  string    $sObjUID
   * @return void
   */
  function assignCaseTrackerObject($sProcessUID, $sObjType, $sObjUID) {
    $oCriteria = new Criteria('workflow');
    $oCriteria->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
    $iPosition = CaseTrackerObjectPeer::doCount($oCriteria) + 1;
    $oCaseTrackerObject = new CaseTrackerObject ( );
    $ctoUID = $oCaseTrackerObject->create(array('PRO_UID' => $sProcessUID, 'CTO_TYPE_OBJ' => $sObjType, 'CTO_UID_OBJ' => $sObjUID, 'CTO_POSITION' => $iPosition));
    return $ctoUID;
  }

  /**
   * removeCaseTrackerObject
   *
   * @param  string    $sCTOUID
   * @param  string    $sProcessUID
   * @param  integer   $iPosition
   * @return void
   */
  function removeCaseTrackerObject($sCTOUID, $sProcessUID, $iPosition) {
    $oCaseTrackerObject = new CaseTrackerObject ( );
    $oCaseTrackerObject->remove($sCTOUID);
    $oCaseTrackerObject->reorderPositions($sProcessUID, $iPosition);
  }

  /**
   * upCaseTrackerObject
   *
   * @param  string    $sCTOUID
   * @param  string    $sProcessUID
   * @param  integer   $iPosition
   * @return void
   */
  function upCaseTrackerObject($sCTOUID, $sProcessUID, $iPosition) {
    if ($iPosition > 1) {
      $oCriteria1 = new Criteria('workflow');
      $oCriteria1->add(CaseTrackerObjectPeer::CTO_POSITION, $iPosition);
      $oCriteria2 = new Criteria('workflow');
      $oCriteria2->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
      $oCriteria2->add(CaseTrackerObjectPeer::CTO_POSITION, ($iPosition - 1));
      BasePeer::doUpdate($oCriteria2, $oCriteria1, Propel::getConnection('workflow'));

      $oCriteria1 = new Criteria('workflow');
      $oCriteria1->add(CaseTrackerObjectPeer::CTO_POSITION, ($iPosition - 1));
      $oCriteria2 = new Criteria('workflow');
      $oCriteria2->add(CaseTrackerObjectPeer::CTO_UID, $sCTOUID);
      BasePeer::doUpdate($oCriteria2, $oCriteria1, Propel::getConnection('workflow'));
    }
  }

  /**
   * downCaseTrackerObject
   *
   * @param  string    $sCTOUID
   * @param  string    $sProcessUID
   * @param  integer   $iPosition
   * @return void
   */
  function downCaseTrackerObject($sCTOUID, $sProcessUID, $iPosition) {
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn('COUNT(*) AS MAX_POSITION');
    $oCriteria->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
    $oDataset = CaseTrackerObjectPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aRow = $oDataset->getRow();
    if ($iPosition < (int) $aRow ['MAX_POSITION']) {
      $oCriteria1 = new Criteria('workflow');
      $oCriteria1->add(CaseTrackerObjectPeer::CTO_POSITION, $iPosition);
      $oCriteria2 = new Criteria('workflow');
      $oCriteria2->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
      $oCriteria2->add(CaseTrackerObjectPeer::CTO_POSITION, ($iPosition + 1));
      BasePeer::doUpdate($oCriteria2, $oCriteria1, Propel::getConnection('workflow'));
      $oCriteria1 = new Criteria('workflow');
      $oCriteria1->add(CaseTrackerObjectPeer::CTO_POSITION, ($iPosition + 1));
      $oCriteria2 = new Criteria('workflow');
      $oCriteria2->add(CaseTrackerObjectPeer::CTO_UID, $sCTOUID);
      BasePeer::doUpdate($oCriteria2, $oCriteria1, Propel::getConnection('workflow'));
    }
  }

  /**
   * processFilesManager
   *
   * @param  string    $sProcessUID
   * @return void
   */
  function processFilesManager($sProcessUID) {
    $aDirectories = array();
    $aDirectories [] = array('DIRECTORY' => 'char');
    $aDirectories [] = array('DIRECTORY' => '<a href="#" onclick="goToDirectory(\'' . $sProcessUID . '\', \'mailTemplates\', \'\');" class="pagedTableHeader">mailTemplates</a>');
    $aDirectories [] = array('DIRECTORY' => '<a href="#" onclick="goToDirectory(\'' . $sProcessUID . '\', \'public\', \'\');" class="pagedTableHeader">public</a>');
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['directories'] = $aDirectories;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('directories');
    global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );
    $G_PUBLISH->AddContent('propeltable', 'paged-table', 'processes/processes_DirectoriesList', $oCriteria);
    G::RenderPage('publish', 'raw');
  }

  /**
   * exploreDirectory
   *
   * @param  string    $sProcessUID
   * @param  string    $sMainDirectory
   * @param  string    $sCurrentDirectory
   * @return void
   */
  function exploreDirectory($sProcessUID, $sMainDirectory, $sCurrentDirectory) {
    switch ($sMainDirectory) {
      case 'mailTemplates' :
        $sDirectory = PATH_DATA_MAILTEMPLATES . $sProcessUID . PATH_SEP;
        break;
      case 'public' :
        $sDirectory = PATH_DATA_PUBLIC . $sProcessUID . PATH_SEP;
        break;
      default :
        die ();
        break;
    }
    G::verifyPath($sDirectory, true);
    $sDirectory .= $sCurrentDirectory . PATH_SEP;
    $aTheFiles = array();
    $aTheFiles [] = array('PATH' => 'char', 'EDIT' => 'char', 'DOWNLOAD_TEXT' => '', 'DOWNLOAD_JS' => '', 'DELETE_TEXT' => 'char', 'DELETE_JS' => 'char');
    $aDirectories = array();
    $aFiles = array();
    $oDirectory = dir($sDirectory);
    while ($sObject = $oDirectory->read()) {
      if (($sObject !== '.') && ($sObject !== '..')) {
        $sPath = $sDirectory . $sObject;
        if (is_dir($sPath)) {
          $aDirectories [] = array('PATH' => ($sCurrentDirectory != '' ? $sCurrentDirectory . PATH_SEP : '') . $sObject, 'DIRECTORY' => $sObject);
        } else {
          $aAux = pathinfo($sPath);
          $aFiles [] = array('FILE' => $sObject, 'EXT' => $aAux ['extension']);
        }
      }
    }
    $oDirectory->close();
    if ($sCurrentDirectory == '') {
      $aTheFiles [] = array('PATH' => '<a href="#" onclick="goToHome(\'' . $sProcessUID . '\');" class="pagedTableHeader">..</a>', 'EDIT' => '', 'DOWNLOAD_TEXT' => '', 'DOWNLOAD_JS' => '', 'DELETE_TEXT' => '', 'DELETE_JS' => '');
    } else {
      $aAux = explode(PATH_SEP, $sCurrentDirectory);
      array_pop($aAux);
      $aTheFiles [] = array('PATH' => '<a href="#" onclick="goToDirectory(\'' . $sProcessUID . '\', \'' . $sMainDirectory . '\', \'' . implode(PATH_SEP, $aAux) . '\');" class="pagedTableHeader">..</a>', 'EDIT' => '', 'DOWNLOAD_TEXT' => '', 'DOWNLOAD_JS' => '', 'DELETE_TEXT' => '', 'DELETE_JS' => '');
    }
    foreach ($aDirectories as $aDirectories) {
      $aTheFiles [] = array('PATH' => '<a href="#" onclick="goToDirectory(\'' . $sProcessUID . '\', \'' . $sMainDirectory . '\', \'' . $aDirectories ['PATH'] . '\');" class="pagedTableHeader">' . $aDirectories ['DIRECTORY'] . '</a>', 'EDIT' => '', 'DOWNLOAD_TEXT' => '', 'DOWNLOAD_JS' => '', 'DELETE_TEXT' => G::LoadTranslation('ID_DELETE'), 'DELETE_JS' => 'deleteDirectory(\'' . $sProcessUID . '\', \'' . $sMainDirectory . '\', \'' . $sCurrentDirectory . '\', \'' . $aDirectories ['DIRECTORY'] . '\');');
    }
    foreach ($aFiles as $aFile) {
      $aTheFiles [] = array('PATH' => $aFile ['FILE'], 'EDIT' => ($sMainDirectory == 'mailTemplates' ? 'Edit' : ''), 'EDIT_JS' => "editFile('{$sProcessUID}', @@PATH)", 'DOWNLOAD_TEXT' => G::LoadTranslation('ID_DOWNLOAD'), 'DOWNLOAD_JS' => 'downloadFile(\'' . $sProcessUID . '\', \'' . $sMainDirectory . '\', \'' . $sCurrentDirectory . '\', \'' . $aFile ['FILE'] . '\');', 'DELETE_TEXT' => G::LoadTranslation('ID_DELETE'), 'DELETE_JS' => 'deleteFile(\'' . $sProcessUID . '\', \'' . $sMainDirectory . '\', \'' . $sCurrentDirectory . '\', \'' . $aFile ['FILE'] . '\');');
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['objects'] = $aTheFiles;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('objects');
    $oCriteria->addAscendingOrderByColumn('DOWNLOAD_TEXT');
    global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );
    $G_PUBLISH->AddContent('propeltable', 'paged-table', 'processes/processes_FilesList', $oCriteria, array('PRO_UID' => $sProcessUID, 'MAIN_DIRECTORY' => $sMainDirectory, 'CURRENT_DIRECTORY' => $sCurrentDirectory));
    G::RenderPage('publish', 'raw');
  }

  /**
   * deleteFile
   *
   * @param  string    $sProcessUID
   * @param  string    $sMainDirectory
   * @param  string    $sCurrentDirectory
   * @param  string    $sFile
   * @return void
   */
  function deleteFile($sProcessUID, $sMainDirectory, $sCurrentDirectory, $sFile) {
    switch ($sMainDirectory) {
      case 'mailTemplates' :
        $sDirectory = PATH_DATA_MAILTEMPLATES . $sProcessUID . PATH_SEP . ($sCurrentDirectory != '' ? $sCurrentDirectory . PATH_SEP : '');
        break;
      case 'public' :
        $sDirectory = PATH_DATA_PUBLIC . $sProcessUID . PATH_SEP . ($sCurrentDirectory != '' ? $sCurrentDirectory . PATH_SEP : '');
        break;
      default :
        die ();
        break;
    }
    if (file_exists($sDirectory . $sFile)) {
      unlink($sDirectory . $sFile);
    }
  }

  /**
   * deleteDirectory
   *
   * @param  string    $sProcessUID
   * @param  string    $sMainDirectory
   * @param  string    $sCurrentDirectory
   * @param  string    $sDirToDelete
   * @return void
   */
  function deleteDirectory($sProcessUID, $sMainDirectory, $sCurrentDirectory, $sDirToDelete) {
    switch ($sMainDirectory) {
      case 'mailTemplates' :
        $sDirectory = PATH_DATA_MAILTEMPLATES . $sProcessUID . PATH_SEP . ($sCurrentDirectory != '' ? $sCurrentDirectory . PATH_SEP : '');
        break;
      case 'public' :
        $sDirectory = PATH_DATA_PUBLIC . $sProcessUID . PATH_SEP . ($sCurrentDirectory != '' ? $sCurrentDirectory . PATH_SEP : '');
        break;
      default :
        die ();
        break;
    }
    if (file_exists($sDirectory . $sDirToDelete)) {
      G::rm_dir($sDirectory . $sDirToDelete);
    }
  }

  function downloadFile($sProcessUID, $sMainDirectory, $sCurrentDirectory, $sFile) {
    switch ($sMainDirectory) {
      case 'mailTemplates' :
        $sDirectory = PATH_DATA_MAILTEMPLATES . $sProcessUID . PATH_SEP . ($sCurrentDirectory != '' ? $sCurrentDirectory . PATH_SEP : '');
        break;
      case 'public' :
        $sDirectory = PATH_DATA_PUBLIC . $sProcessUID . PATH_SEP . ($sCurrentDirectory != '' ? $sCurrentDirectory . PATH_SEP : '');
        break;
      default :
        die ();
        break;
    }
    if (file_exists($sDirectory . $sFile)) {
      G::streamFile($sDirectory . $sFile, true);
    }
  }

  /* For sub-process */

  /**
   * addSubProcess
   *
   * @param  string    $sProcessUID
   * @param  integer   $iX
   * @param  integer   $iY
   * @return void
   */
  function addSubProcess($sProcessUID = '', $iX = 0, $iY = 0) {
    try {
      $oTask = new Task ( );
      $oNewTask->label = 'Sub-Process'; //G::LoadTranslation('ID_TASK');
      $oNewTask->uid = $oTask->create(array('PRO_UID' => $sProcessUID, 'TAS_TITLE' => $oNewTask->label, 'TAS_POSX' => $iX, 'TAS_POSY' => $iY, 'TAS_TYPE' => 'SUBPROCESS'));
      $oJSON = new Services_JSON ( );

      $oOP = new SubProcess ( );
      $aData = array('SP_UID' => G::generateUniqueID (), 'PRO_UID' => 0, 'TAS_UID' => 0, 'PRO_PARENT' => $sProcessUID, 'TAS_PARENT' => $oNewTask->uid, 'SP_TYPE' => 'SIMPLE', 'SP_SYNCHRONOUS' => 0, 'SP_SYNCHRONOUS_TYPE' => 'ALL', 'SP_SYNCHRONOUS_WAIT' => 0, 'SP_VARIABLES_OUT' => '', 'SP_VARIABLES_IN' => '', 'SP_GRID_IN' => '');
      $oOP->create($aData);

      return $oJSON->encode($oNewTask);
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /**
   * deleteSubProcess
   *
   * @param   string    $sProcessUID
   * @param   string    $sTaskUID
   * @return  boolean   true
   * @throw   Exception $oError
   */
  function deleteSubProcess($sProcessUID = '', $sTaskUID = '') {
    try {
      $oTasks = new Tasks ( );
      $oTasks->deleteTask($sTaskUID);

      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn('SP_UID');
      $oCriteria->add(SubProcessPeer::PRO_PARENT, $sProcessUID);
      $oCriteria->add(SubProcessPeer::TAS_PARENT, $sTaskUID);
      $oDataset = SubProcessPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $aRow = $oDataset->getRow();

      $oSubProcess = new SubProcess ( );
      $oSubProcess->remove($aRow ['SP_UID']);

      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /**
   * subProcess_Properties
   *
   * @param   string    $sProcessUID
   * @param   string    $sTaskUID
   * @param   string    $sIndex
   * @return  void
   * @throw   Exception $oError
   */
  function subProcess_Properties($sProcessUID = '', $sTaskUID = '', $sIndex = '') {
    try { //echo "$sProcessUID = '', $sTaskUID = '', $sIndex = ''";
      $SP_VARIABLES_OUT = array();
      $SP_VARIABLES_IN = array();

      /* Prepare page before to show */
      global $_DBArray;
      $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['NewCase'] = $this->subProcess_TaskIni($sProcessUID);
      unset($_DBArray ['TheProcesses']);
      $_DBArray ['TheProcesses'] [] = array('pro_uid' => 'char', 'value' => 'char');
      $i = 0;
      foreach ($_DBArray ['NewCase'] as $aRow) {
        if ($i > 0) {
          $_DBArray ['TheProcesses'] [] = array('pro_uid' => $aRow ['pro_uid'] . '_' . $i, 'value' => $aRow ['value']);
        }
        $i++;
      }
      //print'<hr>';print_r($_DBArray['NewCase']);print'<hr>';
      $oCriteria = new Criteria('workflow');
      $del = DBAdapter::getStringDelimiter ();
      $oCriteria->add(SubProcessPeer::PRO_PARENT, $sProcessUID);
      $oCriteria->add(SubProcessPeer::PRO_PARENT, $sProcessUID);
      $oCriteria->add(SubProcessPeer::TAS_PARENT, $sTaskUID);

      $oCriteria->addAsColumn('CON_VALUE', 'C1.CON_VALUE', 'CON_TITLE');
      $oCriteria->addAlias("C1", 'CONTENT');
      $tasTitleConds = array();
      $tasTitleConds [] = array(SubProcessPeer::TAS_PARENT, 'C1.CON_ID');
      $tasTitleConds [] = array('C1.CON_CATEGORY', $del . 'TAS_TITLE' . $del);
      $tasTitleConds [] = array('C1.CON_LANG', $del . SYS_LANG . $del);
      $oCriteria->addJoinMC($tasTitleConds, Criteria::LEFT_JOIN);

      $oDataset = SubProcessPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $aRow = $oDataset->getRow();

      $aRow ['TASKS'] = $aRow ['TAS_UID'];
      //print "<hr>".$aRow['TASKS']."<hr>";
      //$aRow['SPROCESS_NAME'] = $aRow['TAS_TITLE'];
      $aRow ['TAS_TITLE'] = $aRow ['CON_VALUE'];
      $aRow ['SPROCESS_NAME'] = $aRow ['CON_VALUE'];
      $SP_VARIABLES_OUT = unserialize($aRow ['SP_VARIABLES_OUT']);
      if (is_array($SP_VARIABLES_OUT)) {
        $i = 1;
        foreach ($SP_VARIABLES_OUT as $indice => $valor) {
          $aRow ['grid1'] [$i] ['VAR_OUT1'] = $indice;
          $aRow ['grid1'] [$i] ['VAR_OUT2'] = $valor;
          $i++;
        }
      }

      $SP_VARIABLES_IN = unserialize($aRow ['SP_VARIABLES_IN']);
      if (is_array($SP_VARIABLES_IN)) {
        $j = 1;
        foreach ($SP_VARIABLES_IN as $indice => $valor) {
          $aRow ['grid2'] [$j] ['VAR_IN1'] = $indice;
          $aRow ['grid2'] [$j] ['VAR_IN2'] = $valor;
          $j++;
        }
      }
      $aRow ['INDEX'] = $sIndex;
      //print '<hr>';print_r($aRow);
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $G_PUBLISH->AddContent('xmlform', 'xmlform', 'processes/processes_subProcess', '', $aRow, 'processes_subProcessSave');
      G::RenderPage('publish', 'raw');
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /**
   * subProcess_TaskIni
   *
   * @param  string    $sProcessUID
   * @return array     $rows
   */
  function subProcess_TaskIni($sProcessUID) {
    $tasks = array();
    $aUIDS = array();
    $aUIDS [] = $sProcessUID;
    $c = new Criteria ( );
    $c->clearSelectColumns();
    $c->addSelectColumn(TaskPeer::TAS_UID);
    $c->addSelectColumn(TaskPeer::PRO_UID);
    $c->add(TaskPeer::TAS_START, 'TRUE');
    $c->add(TaskPeer::PRO_UID, $aUIDS, Criteria::NOT_IN);
    //$c->add(TaskPeer::PRO_UID, $sProcessUID, Criteria::NOT_EQUAL);
    $rs = TaskPeer::doSelectRS($c);
    $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $rs->next();
    $row = $rs->getRow();
    while (is_array($row)) {
      $tasks [] = array('TAS_UID' => $row ['TAS_UID'], 'PRO_UID' => $row ['PRO_UID']);
      $rs->next();
      $row = $rs->getRow();
    }
    $rows [] = array('uid' => 'char', 'value' => 'char', 'pro_uid' => 'char');
    foreach ($tasks as $key => $val) {
      $tasTitle = Content::load('TAS_TITLE', '', $val ['TAS_UID'], SYS_LANG);
      $proTitle = Content::load('PRO_TITLE', '', $val ['PRO_UID'], SYS_LANG);
      $title = " $proTitle ($tasTitle)";
      $rows [] = array('uid' => $val ['TAS_UID'], 'value' => $title, 'pro_uid' => $val ['PRO_UID']);
    }
    return $rows;
  }

  /**
   * eventsList
   *
   * @param   string     $sProcessUID
   * @param   string     $type
   * @return  boolean    true
   * @throw   Exception $oError
   */
  function eventsList($sProcessUID, $type) {
    try {
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $oHeadPublisher = & headPublisher::getSingleton ();
      $oHeadPublisher->addScriptFile('/jscore/events/events.js');

      switch ($type) {
        case 'message': $EVN_ACTION = "SEND_MESSAGE";
          break;
        case 'conditional': $EVN_ACTION = "EXECUTE_CONDITIONAL_TRIGGER";
          break;
        case 'multiple': $EVN_ACTION = "EXECUTE_TRIGGER";
          break;
      }

      $oCriteria = $this->getEventsCriteria($sProcessUID, $EVN_ACTION);

      $G_PUBLISH->AddContent('propeltable', 'paged-table', 'events/eventsShortList', $oCriteria, array('PRO_UID' => $sProcessUID, 'EVN_TYPE' => $EVN_ACTION));
      G::RenderPage('publish', 'raw');
      return true;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

  /* get all the events for a specified process */

  /**
   * getEventsCriteria
   *
   * @param  string           $sProcessUID
   * @param  string           $EVN_ACTION
   * @return object(Criteria) $oCriteria
   */
  function getEventsCriteria($sProcessUID, $EVN_ACTION) {
    try {
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn(EventPeer::EVN_UID);
      $oCriteria->addSelectColumn(EventPeer::EVN_ACTION);
      $oCriteria->addSelectColumn(EventPeer::EVN_STATUS);
      $oCriteria->addSelectColumn(EventPeer::EVN_WHEN_OCCURS);
      $oCriteria->addSelectColumn(EventPeer::EVN_RELATED_TO);

      $oCriteria->addAsColumn('EVN_DESCRIPTION', ContentPeer::CON_VALUE);
      $aConditions = array();
      $aConditions [] = array(EventPeer::EVN_UID, ContentPeer::CON_ID);
      $aConditions [] = array(ContentPeer::CON_CATEGORY, $sDelimiter . 'EVN_DESCRIPTION' . $sDelimiter);
      $aConditions [] = array(ContentPeer::CON_LANG, $sDelimiter . SYS_LANG . $sDelimiter);
      $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
      $oCriteria->add(EventPeer::PRO_UID, $sProcessUID);

      switch ($EVN_ACTION) {
        case 'SEND_MESSAGE' : $oCriteria->add(EventPeer::EVN_ACTION, "SEND_MESSAGE");
          break;
        case 'EXECUTE_CONDITIONAL_TRIGGER': $oCriteria->add(EventPeer::EVN_ACTION, "EXECUTE_CONDITIONAL_TRIGGER");
          break;
        case 'EXECUTE_TRIGGER' : $oCriteria->add(EventPeer::EVN_ACTION, "EXECUTE_TRIGGER");
          break;
      }

      return $oCriteria;
    } catch (Exception $oError) {
      throw ($oError);
    }
  }

// processMap
/****************************All Functions for New ProcessMap******************************************************/
  /*
  * Edit the Process Map information
  * @param string $sProcessUID
  * @return boolean
  */

  function editProcessNew($sProcessUID)
  {
    try {
      $oProcess = new Process ( );
      if (! is_null ( $oProcess )) {
        $calendar    = new Calendar ( );
        $calendarObj = $calendar->getCalendarList ( true, true );

        global $_DBArray;

        $_DBArray ['availableCalendars'] = $calendarObj ['array'];

        $_SESSION ['_DBArray'] = $_DBArray;
        $aFields = $oProcess->load ( $sProcessUID );
        $aFields ['THETYPE'] = 'UPDATE';
        $calendarInfo = $calendar->getCalendarFor ( $sProcessUID, $sProcessUID, $sProcessUID );
        //If the function returns a DEFAULT calendar it means that this object doesn't have assigned any calendar
        $aFields ['PRO_CALENDAR'] = $calendarInfo ['CALENDAR_APPLIED'] != 'DEFAULT' ? $calendarInfo ['CALENDAR_UID'] : "";

        return $aFields;
      } else {
        throw (new Exception ( 'This row doesn\'t exists!' ));
      }
    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }

 /*
  * Load all categories
  * @return array
  */

  function loadProcessCategory()
  {
    $aProcessCategory = '';
    require_once ( "classes/model/ProcessCategory.php" );
    $Criteria = new Criteria('workflow');
    $Criteria->clearSelectColumns ( );

    $Criteria->addSelectColumn (  ProcessCategoryPeer::CATEGORY_UID );
    $Criteria->addSelectColumn (  ProcessCategoryPeer::CATEGORY_PARENT );
    $Criteria->addSelectColumn (  ProcessCategoryPeer::CATEGORY_NAME );
    $Criteria->addSelectColumn (  ProcessCategoryPeer::CATEGORY_ICON );

    $Criteria->add (  processCategoryPeer::CATEGORY_UID, "xx" , CRITERIA::NOT_EQUAL );
    $oDataset = processCategoryPeer::doSelectRS ( $Criteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    while ( $aRow = $oDataset->getRow () ) {
      $aProcessCategory[] = $aRow;
      $oDataset->next ();
    }

    return $aProcessCategory;
  }

   /*
  * Save the tasks Width and Height
  * @param string $sTaskUID
  * @param integer $iX
  * @param integer $iY
  * @return integer
  */
  function saveTaskCordinates($sTaskUID = '', $iX = 110, $iY = 60)
  {
    try {
      $oTask   = new Task ( );
      $aFields = $oTask->load ( $sTaskUID );

      $aFields ['TAS_UID']    = $sTaskUID;
      $aFields ['TAS_WIDTH']  = $iX;
      $aFields ['TAS_HEIGHT'] = $iY;
      return $oTask->update ( $aFields );
    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }

   /*
  * Save the Annotation Width and Height
  * @param string $sSwimLaneUID
  * @param integer $iX
  * @param integer $iY
  * @return integer
  */
  function saveAnnotationCordinates($sSwimLaneUID = '', $iX = 110, $iY = 60)
  {
    try {
      $oSL   = new SwimlanesElements( );
      $aFields = $oSL->load ( $sSwimLaneUID );

      $aFields ['SWI_UID']    = $sSwimLaneUID;
      $aFields ['SWI_WIDTH']  = $iX;
      $aFields ['SWI_HEIGHT'] = $iY;
      return $oSL->update ( $aFields );
    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }

   /** get all the Active process
   *
   * SELECT PROCESS.PRO_UID AS UID, CONTENT.CON_VALUE AS VALUE FROM PROCESS, CONTENT
	WHERE (PROCESS.PRO_UID=CONTENT.CON_ID AND PROCESS.PRO_STATUS!='DISABLED' AND CONTENT.CON_CATEGORY='PRO_TITLE' AND CONTENT.CON_LANG='en')
	ORDER BY CONTENT.CON_VALUE
	]]>
   */
  function getAllProcesses()
  {

    $aProcesses    = array ();
    //$aProcesses [] = array ('PRO_UID' => 'char', 'PRO_TITLE' => 'char');
    $oCriteria     = new Criteria ( 'workflow' );
    $oCriteria->addSelectColumn ( ProcessPeer::PRO_UID );
    $oCriteria->add ( ProcessPeer::PRO_STATUS, 'DISABLED', Criteria::NOT_EQUAL );
    $oDataset = ProcessPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );

    $oDataset->next ();
    $oProcess = new Process ( );
    while ( $aRow = $oDataset->getRow () ) {
      $aProcess = $oProcess->load ( $aRow ['PRO_UID'] );
      $aProcesses [] = array ('value' => $aProcess ['PRO_UID'], 'name' => $aProcess ['PRO_TITLE'] );
      $oDataset->next ();
    }
    $oJSON = new Services_JSON ( );
    return $oJSON->encode ( $aProcesses);
  }

  /*
  * Return the steps list criteria object
  * @param string $sTaskUID
  * @return array
  *
  */
  function getDynaformList($sTaskUID = '')
  {
    try {
      //call plugin
      $oPluginRegistry = &PMPluginRegistry::getSingleton ();
      $externalSteps = $oPluginRegistry->getSteps ();

      $aSteps = array ();
      //$aSteps [] = array ('STEP_TITLE' => 'char', 'STEP_UID' => 'char', 'STEP_TYPE_OBJ' => 'char', 'STEP_CONDITION' => 'char', 'STEP_POSITION' => 'integer' );
      $oCriteria = new Criteria ( 'workflow' );
      $oCriteria->add ( StepPeer::TAS_UID, $sTaskUID );
      $oCriteria->addAscendingOrderByColumn ( StepPeer::STEP_POSITION );
      $oDataset = StepPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      while ( $aRow = $oDataset->getRow () ) {
        $urlEdit = '';
        $linkEditValue = '';

        switch ($aRow ['STEP_TYPE_OBJ']) {
          case 'DYNAFORM' :
            $oDynaform = new Dynaform ( );
            $aFields = $oDynaform->load ( $aRow ['STEP_UID_OBJ'] );
            $sTitle = $aFields ['DYN_TITLE'];
            $DYN_UID = $aFields ['DYN_UID'];
            break;
        }
        $aSteps [] = array ('name' => $sTitle, 'value' => $DYN_UID);
        $oDataset->next ();
      }
      return $aSteps;
    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }

   function listNewWebEntry($sProcessUID,$sEventUID)
  {
    try {
      global $G_PUBLISH;
      global $G_FORM;
      $G_PUBLISH = new Publisher ( );

      require_once 'classes/model/Event.php';
      $oEvent = new Event();
      $arlink = '';
      $oEvent = EventPeer::retrieveByPK($sEventUID);
      if (!is_null($oEvent)) {
      $oData = $oEvent->load($sEventUID);

      if($oData['EVN_ACTION'] != '' && $oData['EVN_ACTION'] != 'WEB_ENTRY')
      {
          require_once 'classes/model/Content.php';
          $oContent = new Content();
          $dynTitle = $oContent->load('DYN_TITLE', '', $oData['EVN_ACTION'], 'en');

          if (G::is_https ())
            $http = 'https://';
          else
            $http = 'http://';

          $link = $http . $_SERVER ['HTTP_HOST'] . '/sys' . SYS_SYS . '/' . SYS_LANG . '/' . SYS_SKIN . '/' . $sProcessUID . '/';

          $row = array ();
          $c   = 0;

          /*
          $oTask = new Task ( );
          $TaskFields = $oTask->kgetassigType ( $sProcessUID , $tas='');
          */
          $TaskFields ['TAS_ASSIGN_TYPE'] = '';
          //$row [] = array ('W_TITLE' => '', 'W_DELETE' => '', 'TAS_ASSIGN_TYPE' => $TaskFields ['TAS_ASSIGN_TYPE'] );

          if (is_dir ( PATH_DATA . "sites" . PATH_SEP . SYS_SYS . PATH_SEP . "public" . PATH_SEP . $sProcessUID )) {
            $dir = opendir ( PATH_DATA . "sites" . PATH_SEP . SYS_SYS . PATH_SEP . "public" . PATH_SEP . $sProcessUID );
            $dynTitle = str_replace ( ' ', '_', str_replace ( '/', '_', $dynTitle ) );
            $alink = $link . $dynTitle.'.php';
            $arlink     = "<a href='" . $alink . "' target='blank'><font color='#9999CC'>" . $alink . "</font></a>";
          }
        }
      }
     $row []     = array ('W_LINK' => $arlink);
     $oJSON = new Services_JSON ( );
     return $oJSON->encode ($row);
    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }


  /*
  * Users assigned to Tasks
  * @param string $sProcessUID
  * @param string $sTaskUID
  * @return boolean
  */
  function usersExtList($sProcessUID = '', $sTaskUID = '')
  {
    try {
      $oProcess = new Process ( );
      $aFields  = $oProcess->load ( $sProcessUID );
      $oTask    = new Task ( );
      $aFields  = $oTask->load ( $sTaskUID );

      $_SESSION ['iType'] = 1;

      $aFields ['TASK']         = $sTaskUID;
      $aFields ['TYPE']         = $_SESSION ['iType'];
      $aFields ['OF_TO_ASSIGN'] = G::LoadTranslation ( 'ID_DE_ASSIGN' );
      $aFields ['CONFIRM']      = G::LoadTranslation ( 'ID_MSG_CONFIRM_DEASIGN_USER_GROUP_MESSAGE' );
      $aFields ['UIDS']         = "'0'";

      $oTasks  = new Tasks ( );
      $oGroups = new Groups ( );
      $aAux1   = $oTasks->getGroupsOfTask ( $sTaskUID, $_SESSION ['iType'] );
      foreach ( $aAux1 as $aGroup ) {
        $aAux2 = $oGroups->getUsersOfGroup ( $aGroup ['GRP_UID'] );
        foreach ( $aAux2 as $aUser ) {
          $aFields ['UIDS'] .= ",'" . $aUser ['USR_UID'] . "'";
        }
      }
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher ( );
      $oTask = new Task ( );
      $aTask = $oTask->load ( $sTaskUID );

      $this->getExtTaskUsersCriteria ( $sTaskUID, $_SESSION ['iType'] );
      return $_SESSION ['_DBArray']['taskUsers'];

    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }

  function getExtTaskUsersCriteria($sTaskUID = '', $iType = 1)
  {
    try {
      $aUsers     = array ();
      $aUsers []  = array ('LABEL' => 'char', 'TAS_UID' => 'char', 'USR_UID' => 'char', 'TU_TYPE' => 'integer', 'TU_RELATION' => 'integer' );
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria  = new Criteria ( 'workflow' );
      $oCriteria->addAsColumn ( 'GRP_TITLE', 'C.CON_VALUE' );
      $oCriteria->addSelectColumn ( TaskUserPeer::TAS_UID );
      $oCriteria->addSelectColumn ( TaskUserPeer::USR_UID );
      $oCriteria->addSelectColumn ( TaskUserPeer::TU_TYPE );
      $oCriteria->addSelectColumn ( TaskUserPeer::TU_RELATION );
      $oCriteria->addAlias ( 'C', 'CONTENT' );
      $aConditions    = array ();
      $aConditions [] = array (TaskUserPeer::USR_UID, 'C.CON_ID' );
      $aConditions [] = array ('C.CON_CATEGORY', $sDelimiter . 'GRP_TITLE' . $sDelimiter );
      $aConditions [] = array ('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
      $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
      $oCriteria->add ( TaskUserPeer::TAS_UID, $sTaskUID );
      $oCriteria->add ( TaskUserPeer::TU_TYPE, $iType );
      $oCriteria->add ( TaskUserPeer::TU_RELATION, 2 );
      $oDataset = TaskUserPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      $c = 0;
      while ( $aRow = $oDataset->getRow () ) {
        $c ++;
        $oGroup  = new Groupwf ( );
        $aFields = $oGroup->load ( $aRow ['USR_UID'] );
        if ($aFields ['GRP_STATUS'] == 'ACTIVE') {
          $oCriteria = new Criteria ( 'workflow' );
          $oCriteria->addSelectColumn ( 'COUNT(*) AS MEMBERS_NUMBER' );
          $oCriteria->add ( GroupUserPeer::GRP_UID, $aRow ['USR_UID'] );
          $oDataset2 = GroupUserPeer::doSelectRS ( $oCriteria );
          $oDataset2->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
          $oDataset2->next ();
          $aRow2 = $oDataset2->getRow ();
        } else {
          $aRow2 ['GROUP_INACTIVE'] = '<strong>(' . G::LoadTranslation ( 'ID_GROUP_INACTIVE' ) . ')</strong>';
        }
        //$aUsers [] = array ('LABEL' => (! isset ( $aRow2 ['GROUP_INACTIVE'] ) ? $aRow ['GRP_TITLE'] . ' <a href="#" onclick="usersGroup(\'' . $aRow ['USR_UID'] . '\', \'' . $c . '\');return false;"><font color="green"><strong>(' . $aRow2 ['MEMBERS_NUMBER'] . ' ' . (( int ) $aRow2 ['MEMBERS_NUMBER'] == 1 ? G::LoadTranslation ( 'ID_USER' ) : G::LoadTranslation ( 'ID_USERS' )) . ')</strong></font></a> <br /><div id="users' . $c . '" style="display: none"></div>' : $aRow ['GRP_TITLE'] . ' ' . $aRow2 ['GROUP_INACTIVE']), 'TAS_UID' => $aRow ['TAS_UID'], 'USR_UID' => $aRow ['USR_UID'], 'TU_TYPE' => $aRow ['TU_TYPE'], 'TU_RELATION' => $aRow ['TU_RELATION'] );
        $aUsers [] = array ('LABEL' => (! isset ( $aRow2 ['GROUP_INACTIVE'] ) ? $aRow ['GRP_TITLE'] . ' <font color="green"><strong>(' . $aRow2 ['MEMBERS_NUMBER'] . ' ' . (( int ) $aRow2 ['MEMBERS_NUMBER'] == 1 ? G::LoadTranslation ( 'ID_USER' ) : G::LoadTranslation ( 'ID_USERS' )) . ')</strong></font><br /><div id="users' . $c . '" style="display: none"></div>' : $aRow ['GRP_TITLE'] . ' ' . $aRow2 ['GROUP_INACTIVE']), 'TAS_UID' => $aRow ['TAS_UID'], 'USR_UID' => $aRow ['USR_UID'], 'TU_TYPE' => $aRow ['TU_TYPE'], 'TU_RELATION' => $aRow ['TU_RELATION'] );
        $oDataset->next ();
      }
      $oCriteria = new Criteria ( 'workflow' );
      $oCriteria->addSelectColumn ( UsersPeer::USR_FIRSTNAME );
      $oCriteria->addSelectColumn ( UsersPeer::USR_LASTNAME );
      $oCriteria->addSelectColumn ( TaskUserPeer::TAS_UID );
      $oCriteria->addSelectColumn ( TaskUserPeer::USR_UID );
      $oCriteria->addSelectColumn ( TaskUserPeer::TU_TYPE );
      $oCriteria->addSelectColumn ( TaskUserPeer::TU_RELATION );
      $oCriteria->addJoin ( TaskUserPeer::USR_UID, UsersPeer::USR_UID, Criteria::LEFT_JOIN );
      $oCriteria->add ( TaskUserPeer::TAS_UID, $sTaskUID );
      $oCriteria->add ( TaskUserPeer::TU_TYPE, $iType );
      $oCriteria->add ( TaskUserPeer::TU_RELATION, 1 );
      $oDataset = TaskUserPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      while ( $aRow = $oDataset->getRow () ) {
        $aUsers [] = array ('LABEL' => $aRow ['USR_FIRSTNAME'] . ' ' . $aRow ['USR_LASTNAME'], 'TAS_UID' => $aRow ['TAS_UID'], 'USR_UID' => $aRow ['USR_UID'], 'TU_TYPE' => $aRow ['TU_TYPE'], 'TU_RELATION' => $aRow ['TU_RELATION'] );
        $oDataset->next ();
      }
      global $_DBArray;
      $_DBArray = (isset ( $_SESSION ['_DBArray'] ) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['taskUsers'] = $aUsers;
      $_SESSION ['_DBArray']  = $_DBArray;
      G::LoadClass ( 'ArrayPeer' );
      $oCriteria = new Criteria ( 'dbarray' );
      $oCriteria->setDBArrayTable ( 'taskUsers' );
      $oCriteria->addDescendingOrderByColumn ( TaskUserPeer::TU_RELATION );
      $oCriteria->addAscendingOrderByColumn ( 'LABEL' );
      return $oCriteria;
    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }


  function getAvailableExtUsersCriteria($sTaskUID = '', $iType = 1)
  {
    try {
      $oTasks = new Tasks ( );
      $aAux   = $oTasks->getGroupsOfTask ( $sTaskUID, $iType );
      $aUIDS1 = array ();
      $aUIDS2 = array ();
      foreach ( $aAux as $aGroup ) {
        $aUIDS1 [] = $aGroup ['GRP_UID'];
      }
      $aAux = $oTasks->getUsersOfTask ( $sTaskUID, $iType );
      foreach ( $aAux as $aUser ) {
        $aUIDS2 [] = $aUser ['USR_UID'];
      }
      $aUsers     = array ();
      //$aUsers []  = array ('LABEL' => 'char', 'TAS_UID' => 'char', 'USR_UID' => 'char', 'TU_TYPE' => 'integer', 'TU_RELATION' => 'integer' );
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria  = new Criteria ( 'workflow' );
      $oCriteria->addSelectColumn ( GroupwfPeer::GRP_UID );
      $oCriteria->addAsColumn ( 'GRP_TITLE', 'C.CON_VALUE' );
      $oCriteria->addAlias ( 'C', 'CONTENT' );
      $aConditions    = array ();
      $aConditions [] = array (GroupwfPeer::GRP_UID, 'C.CON_ID' );
      $aConditions [] = array ('C.CON_CATEGORY', $sDelimiter . 'GRP_TITLE' . $sDelimiter );
      $aConditions [] = array ('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
      $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
      $oCriteria->add ( GroupwfPeer::GRP_STATUS, 'ACTIVE' );
      $oCriteria->add ( GroupwfPeer::GRP_UID, $aUIDS1, Criteria::NOT_IN );
      //$oCriteria->add(GroupwfPeer::GRP_UID, '', Criteria::NOT_EQUAL);
      $oDataset = GroupwfPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      $c = 0;
      while ( $aRow = $oDataset->getRow () ) {
        $c ++;
        $oCriteria = new Criteria ( 'workflow' );
        $oCriteria->addSelectColumn ( 'COUNT(*) AS MEMBERS_NUMBER' );
        $oCriteria->add ( GroupUserPeer::GRP_UID, $aRow ['GRP_UID'] );
        $oDataset2 = GroupUserPeer::doSelectRS ( $oCriteria );
        $oDataset2->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
        $oDataset2->next ();
        $aRow2     = $oDataset2->getRow ();
        //$aUsers [] = array ('LABEL' => $aRow ['GRP_TITLE'] . ' <a href="#" onclick="usersGroup(\'' . $aRow ['GRP_UID'] . '\', \'' . $c . '\');return false;"><font color="green"><strong>(' . $aRow2 ['MEMBERS_NUMBER'] . ' ' . (( int ) $aRow2 ['MEMBERS_NUMBER'] == 1 ? G::LoadTranslation ( 'ID_USER' ) : G::LoadTranslation ( 'ID_USERS' )) . ')</strong></font></a> <br /><div id="users' . $c . '" style="display: none"></div>', 'TAS_UID' => $sTaskUID, 'USR_UID' => $aRow ['GRP_UID'], 'TU_TYPE' => $iType, 'TU_RELATION' => 2 );
        $aUsers [] = array ('LABEL' => $aRow ['GRP_TITLE'] . ' <font color="green"><strong>(' . $aRow2 ['MEMBERS_NUMBER'] . ' ' . (( int ) $aRow2 ['MEMBERS_NUMBER'] == 1 ? G::LoadTranslation ( 'ID_USER' ) : G::LoadTranslation ( 'ID_USERS' )) . ')</strong></font><br /><div id="users' . $c . '" style="display: none"></div>', 'TAS_UID' => $sTaskUID, 'USR_UID' => $aRow ['GRP_UID'], 'TU_TYPE' => $iType, 'TU_RELATION' => 2 );
        $oDataset->next ();
      }
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria  = new Criteria ( 'workflow' );
      $oCriteria->addSelectColumn ( UsersPeer::USR_UID );
      $oCriteria->addSelectColumn ( UsersPeer::USR_FIRSTNAME );
      $oCriteria->addSelectColumn ( UsersPeer::USR_LASTNAME );
      $oCriteria->add ( UsersPeer::USR_STATUS, 'ACTIVE' );
      $oCriteria->add ( UsersPeer::USR_UID, $aUIDS2, Criteria::NOT_IN );
      $oDataset = UsersPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      while ( $aRow = $oDataset->getRow () ) {
        $aUsers [] = array ('LABEL' => $aRow ['USR_FIRSTNAME'] . ' ' . $aRow ['USR_LASTNAME'], 'TAS_UID' => $sTaskUID, 'USR_UID' => $aRow ['USR_UID'], 'TU_TYPE' => $iType, 'TU_RELATION' => 1 );
        $oDataset->next ();
      }
      global $_DBArray;
      $_DBArray = (isset ( $_SESSION ['_DBArray'] ) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['availableUsers'] = $aUsers;
      $_SESSION ['_DBArray'] = $_DBArray;

      //return $oCriteria;
      return $_SESSION ['_DBArray']['availableUsers'];
    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }

  /*
      * Return the Additional PM tables list created by user
  * @return object
  */
  function getExtAdditionalTablesList($sTab_UID='')
  {
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(AdditionalTablesPeer::ADD_TAB_UID);
    $oCriteria->addSelectColumn(AdditionalTablesPeer::ADD_TAB_NAME);
    $oCriteria->addSelectColumn(AdditionalTablesPeer::ADD_TAB_DESCRIPTION);
    $oCriteria->add(AdditionalTablesPeer::ADD_TAB_UID, '', Criteria::NOT_EQUAL);

    $oDataset = AdditionalTablesPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    while ( $aRow = $oDataset->getRow () ) {
        $aAdditionalTables [] = array ('ADD_TAB_UID' => $aRow ['ADD_TAB_UID'], 'ADD_TAB_NAME' => $aRow ['ADD_TAB_NAME'], 'ADD_TAB_DESCRIPTION' => $aRow ['ADD_TAB_DESCRIPTION']);
        $oDataset->next ();
      }     
    return $aAdditionalTables;
  }

  /*
  * Return the available building blocks list criteria object
  * @param string $sProcessUID
  * @param string $sTaskUID
  * @return object
  */
  function getExtAvailableBBCriteria($sProcessUID = '', $sTaskUID = '')
  {
    try {
      $oTasks = new Tasks ( );
      $aSteps = $oTasks->getStepsOfTask ( $sTaskUID );
      $sUIDs  = array ();
      foreach ( $aSteps as $aStep ) {
        $sUIDs [] = $aStep ['STEP_UID_OBJ'];
      }
      $aBB        = array ();
      $aBB []     = array ('STEP_UID' => 'char', 'STEP_TITLE' => 'char', 'STEP_TYPE_OBJ' => 'char', 'STEP_MODE' => 'char','STEP_UID_OBJ' => 'char' );
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria  = new Criteria ( 'workflow' );
      $oCriteria->addSelectColumn ( DynaformPeer::DYN_UID );
      $oCriteria->addAsColumn ( 'DYN_TITLE', 'C.CON_VALUE' );
      $oCriteria->addAlias ( 'C', 'CONTENT' );
      $aConditions    = array ();
      $aConditions [] = array (DynaformPeer::DYN_UID, 'C.CON_ID' );
      $aConditions [] = array ('C.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter );
      $aConditions [] = array ('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
      $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
      $oCriteria->add ( DynaformPeer::PRO_UID, $sProcessUID );
      $oCriteria->add ( DynaformPeer::DYN_UID, $sUIDs, Criteria::NOT_IN );
      $oCriteria->add ( DynaformPeer::DYN_TYPE, 'xmlform' );
      $oDataset = DynaformPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      $i = 0;
      while ( $aRow = $oDataset->getRow () ) {
        $i ++;
        /*$aBB [] = array ('STEP_UID' => $aRow ['DYN_UID'], 'STEP_TITLE' => $aRow ['DYN_TITLE'], 'STEP_TYPE_OBJ' => 'DYNAFORM', 'STEP_MODE' => '<select id="STEP_MODE_' . $aRow ['DYN_UID'] . '">
                                            <option value="EDIT">Edit</option>
                                            <option value="VIEW">View</option>
                                           </select>' );*/
        $aBB [] = array ('STEP_UID' => $aRow ['DYN_UID'], 'STEP_TITLE' => $aRow ['DYN_TITLE'], 'STEP_TYPE_OBJ' => 'DYNAFORM', 'STEP_MODE' => 'EDIT', 'STEP_UID_OBJ' => $aRow ['DYN_UID']);
        $oDataset->next ();
      }
      $oCriteria = new Criteria ( 'workflow' );
      $oCriteria->addSelectColumn ( InputDocumentPeer::INP_DOC_UID );
      $oCriteria->addAsColumn ( 'INP_DOC_TITLE', 'C.CON_VALUE' );
      $oCriteria->addAlias ( 'C', 'CONTENT' );
      $aConditions    = array ();
      $aConditions [] = array (InputDocumentPeer::INP_DOC_UID, 'C.CON_ID' );
      $aConditions [] = array ('C.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter );
      $aConditions [] = array ('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
      $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
      $oCriteria->add ( InputDocumentPeer::PRO_UID, $sProcessUID );
      $oCriteria->add ( InputDocumentPeer::INP_DOC_UID, $sUIDs, Criteria::NOT_IN );
      $oDataset = InputDocumentPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      while ( $aRow = $oDataset->getRow () ) {
        $aBB [] = array ('STEP_UID' => $aRow ['INP_DOC_UID'], 'STEP_TITLE' => $aRow ['INP_DOC_TITLE'], 'STEP_TYPE_OBJ' => 'INPUT_DOCUMENT', 'STEP_MODE' => '<input type="hidden" id="STEP_MODE_' . $aRow ['INP_DOC_UID'] . '">' );
        $oDataset->next ();
      }
      $oCriteria = new Criteria ( 'workflow' );
      $oCriteria->addSelectColumn ( OutputDocumentPeer::OUT_DOC_UID );
      $oCriteria->addAsColumn ( 'OUT_DOC_TITLE', 'C.CON_VALUE' );
      $oCriteria->addAlias ( 'C', 'CONTENT' );
      $aConditions    = array ();
      $aConditions [] = array (OutputDocumentPeer::OUT_DOC_UID, 'C.CON_ID' );
      $aConditions [] = array ('C.CON_CATEGORY', $sDelimiter . 'OUT_DOC_TITLE' . $sDelimiter );
      $aConditions [] = array ('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
      $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
      $oCriteria->add ( OutputDocumentPeer::PRO_UID, $sProcessUID );
      $oCriteria->add ( OutputDocumentPeer::OUT_DOC_UID, $sUIDs, Criteria::NOT_IN );
      $oDataset = OutputDocumentPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      while ( $aRow = $oDataset->getRow () ) {
        $aBB [] = array ('STEP_UID' => $aRow ['OUT_DOC_UID'], 'STEP_TITLE' => $aRow ['OUT_DOC_TITLE'], 'STEP_TYPE_OBJ' => 'OUTPUT_DOCUMENT', 'STEP_MODE' => '<input type="hidden" id="STEP_MODE_' . $aRow ['OUT_DOC_UID'] . '">' );
        $oDataset->next ();
      }

      //call plugin
      $oPluginRegistry = &PMPluginRegistry::getSingleton ();
      $externalSteps   = $oPluginRegistry->getSteps ();
      if (is_array ( $externalSteps ) && count ( $externalSteps ) > 0) {
        foreach ( $externalSteps as $key => $stepVar ) {
          $aBB [] = array ('STEP_UID' => $stepVar->sStepId, 'STEP_TITLE' => $stepVar->sStepTitle, 'STEP_TYPE_OBJ' => 'EXTERNAL', 'STEP_MODE' => '<input type="hidden" id="STEP_MODE_' . $stepVar->sStepId . '">' );
        }
      }

      global $_DBArray;
      $_DBArray = (isset ( $_SESSION ['_DBArray'] ) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['availableBB'] = $aBB;
      $_SESSION ['_DBArray'] = $_DBArray;
      G::LoadClass ( 'ArrayPeer' );
      $oCriteria = new Criteria ( 'dbarray' );
      $oCriteria->setDBArrayTable ( 'availableBB' );
      $oCriteria->addAscendingOrderByColumn ( 'STEP_TYPE_OBJ' );
      $oCriteria->addAscendingOrderByColumn ( 'STEP_TITLE' );
      //return $oCriteria;
      return $_SESSION ['_DBArray']['availableBB'];
    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }

  /*
  * Return the steps list criteria object
  * @param string $sTaskUID
  * @return array
  */
  function getExtStepsCriteria($sTaskUID = '')
  {
    try {
      //call plugin
      $oPluginRegistry = &PMPluginRegistry::getSingleton ();
      $externalSteps = $oPluginRegistry->getSteps ();

      $aSteps = array ();
      $aSteps [] = array ('STEP_TITLE' => 'char', 'STEP_UID' => 'char', 'STEP_TYPE_OBJ' => 'char', 'STEP_CONDITION' => 'char', 'STEP_POSITION' => 'integer','STEP_MODE' => 'char','STEP_UID_OBJ' => 'char' );
      $oCriteria = new Criteria ( 'workflow' );
      $oCriteria->add ( StepPeer::TAS_UID, $sTaskUID );
      $oCriteria->addAscendingOrderByColumn ( StepPeer::STEP_POSITION );
      $oDataset = StepPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      while ( $aRow = $oDataset->getRow () ) {
        $urlEdit = '';
        $linkEditValue = '';

        switch ($aRow ['STEP_TYPE_OBJ']) {
          case 'DYNAFORM' :
            $oDynaform = new Dynaform ( );
            $aFields = $oDynaform->load ( $aRow ['STEP_UID_OBJ'] );
            $sTitle = $aFields ['DYN_TITLE'];
            /** @@@init2 PROCCESS FOR DIRECT EDIT LINK @by erik@colosa.com ON DATE 02/06/2008 18:48:13*/
            $DYN_UID = $aFields ['DYN_UID'];
            $urlEdit = "dynaformEdit('" . $DYN_UID . "', '" . $aRow ['PRO_UID'] . "');";
            $linkEditValue = 'Edit';
            /** @@@end2*/
            break;
          case 'INPUT_DOCUMENT' :
            $oInputDocument = new InputDocument ( );
            $aFields = $oInputDocument->load ( $aRow ['STEP_UID_OBJ'] );
            $sTitle = $aFields ['INP_DOC_TITLE'];
            break;
          case 'OUTPUT_DOCUMENT' :
            $oOutputDocument = new OutputDocument ( );
            $aFields = $oOutputDocument->load ( $aRow ['STEP_UID_OBJ'] );
            $sTitle = $aFields ['OUT_DOC_TITLE'];
            break;
          case 'EXTERNAL' :
            $sTitle = 'unknown ' . $aRow ['STEP_UID'];
            foreach ( $externalSteps as $key => $val ) {
              if ($val->sStepId == $aRow ['STEP_UID_OBJ']) {
                $sTitle = $val->sStepTitle;
                if (trim ( $val->sSetupStepPage ) != '') {
                  $urlEdit = "externalStepEdit('" . $aRow ['STEP_UID'] . "', '" . $val->sSetupStepPage . "');";
                  $linkEditValue = 'Edit';
                } else {
                  $urlEdit = "";
                  $linkEditValue = '';
                }
              }
            }
            break;
        }
        $aSteps [] = array ('STEP_TITLE' => $sTitle, 'STEP_UID' => $aRow ['STEP_UID'], 'STEP_TYPE_OBJ' => $aRow ['STEP_TYPE_OBJ'], 'STEP_CONDITION' => $aRow ['STEP_CONDITION'], 'STEP_POSITION' => $aRow ['STEP_POSITION'], 'urlEdit' => $urlEdit, 'linkEditValue' => $linkEditValue, 'PRO_UID' => $aRow ['PRO_UID'],'STEP_MODE' => $aRow['STEP_MODE'],'STEP_UID_OBJ' => $aRow['STEP_UID_OBJ'] );
        $oDataset->next ();
      }

      global $_DBArray;
      $_DBArray = (isset ( $_SESSION ['_DBArray'] ) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['steps'] = $aSteps;
      $_SESSION ['_DBArray'] = $_DBArray;
      G::LoadClass ( 'ArrayPeer' );
      $oCriteria = new Criteria ( 'dbarray' );
      $oCriteria->setDBArrayTable ( 'steps' );
      $oCriteria->addAscendingOrderByColumn ( 'STEP_POSITION' );
      //return $oCriteria;
      return $_SESSION ['_DBArray']['steps'];
    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }

  /*
  * Return the steps trigger criteria array
  * @param string $sTaskUID
  * @return array
  */
  function getExtStepTriggersCriteria($sStepUID = '', $sTaskUID = '', $sType = '')
  {
    $aBB        = array ();
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria  = new Criteria ( 'workflow' );
    $oCriteria->addSelectColumn ( 'C.CON_VALUE' );
    $oCriteria->addSelectColumn ( 'STEP_UID' );
    $oCriteria->addSelectColumn ( 'TRI_UID' );
    $oCriteria->addSelectColumn ( 'ST_TYPE' );
    $oCriteria->addSelectColumn ( 'ST_CONDITION' );
    $oCriteria->addSelectColumn ( StepTriggerPeer::ST_POSITION );
    $oCriteria->addAsColumn ( 'TRI_TITLE', 'C.CON_VALUE' );
    $oCriteria->addAlias ( 'C', 'CONTENT' );
    $aConditions    = array ();
    $aConditions [] = array (StepTriggerPeer::TRI_UID, 'C.CON_ID' );
    $aConditions [] = array ('C.CON_CATEGORY', $sDelimiter . 'TRI_TITLE' . $sDelimiter );
    $aConditions [] = array ('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
    $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
    $oCriteria->add ( StepTriggerPeer::STEP_UID, $sStepUID );
    $oCriteria->add ( StepTriggerPeer::TAS_UID, $sTaskUID );
    $oCriteria->add ( StepTriggerPeer::ST_TYPE, $sType );
    $oCriteria->addAscendingOrderByColumn ( StepTriggerPeer::ST_POSITION );
    $oDataset = InputDocumentPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    while ( $aRow = $oDataset->getRow () ) {
        $aBB [] = array ('CON_VALUE' => $aRow ['CON_VALUE'],'STEP_UID' => $aRow ['STEP_UID'], 'ST_TYPE' => $aRow ['ST_TYPE'],'ST_POSITION' => $aRow ['ST_POSITION'],'ST_CONDITION' => $aRow ['ST_CONDITION'], 'TRI_UID' => $aRow ['TRI_UID'],'TRI_TITLE' => $aRow ['TRI_TITLE'] );
        $oDataset->next ();
      }
    return $aBB;
  }

  /*
  * Return the available step triggers list object
  * @param string $sStepUID
  * @param string $sTaskUID
  * @param string $sType
  * @return object
  */
  function getExtAvailableStepTriggersCriteria($sProcessUID = '',$sStepUID = '', $sTaskUID = '', $sType = '')
  {
      try
      {
      $oCriteria = new Criteria('workflow');
      $oCriteria->addSelectColumn('TRI_UID');
      $oCriteria->add(StepTriggerPeer::TAS_UID , $sTaskUID);
      $oCriteria->add(StepTriggerPeer::STEP_UID, $sStepUID);
      $oCriteria->add(StepTriggerPeer::ST_TYPE , $sType);
      $oDataset = StepTriggerPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      $sUIDs = "'0'";
      $aUIDs = array();
      while ($aRow = $oDataset->getRow()) {
      	$sUIDs .= ",'" . $aRow['TRI_UID'] . "'";
      	$aUIDs[] = $aRow['TRI_UID'];
      	$oDataset->next();
      }
      $sDelimiter = DBAdapter::getStringDelimiter ();
      $oCriteria = new Criteria('workflow');
      //$oCriteria->addSelectColumn ( ContentPeer::CON_ID );
      $oCriteria->addSelectColumn('TRI_UID');
      $oCriteria->addSelectColumn('C.CON_VALUE');
      $oCriteria->addAsColumn ( 'TRI_TITLE', 'C.CON_VALUE' );
      $oCriteria->addAlias ( 'C', 'CONTENT' );
      $aConditions    = array ();
      $aConditions [] = array ('TRI_UID', 'C.CON_ID' );
      $aConditions [] = array ('C.CON_CATEGORY', $sDelimiter . 'TRI_TITLE' . $sDelimiter );
      $aConditions [] = array ('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
      $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
      $oCriteria->add(TriggersPeer::TRI_UID, $aUIDs, Criteria::NOT_IN);
      $oCriteria->add(TriggersPeer::PRO_UID, $sProcessUID);
      $oDataset = TriggersPeer::doSelectRS($oCriteria);
      $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
      $oDataset->next();
      while ( $aRow = $oDataset->getRow () ) {
        $aBB [] = array ('CON_VALUE' => $aRow ['CON_VALUE'],'STEP_UID' => $sStepUID, 'ST_TYPE' => $sType,'TRI_UID' => $aRow ['TRI_UID'],'TRI_TITLE' => $aRow ['TRI_TITLE'] );
        $oDataset->next ();
      }
      return $aBB;
      }
      catch ( Exception $e ) {
  	throw ($e);
        }
  }

  /*
  * Return the dynaforms list array
  * @param string $sProcessUID
  * @return object
  */
  function getExtDynaformsList($sProcessUID = '')
  {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria  = new Criteria ( 'workflow' );
    $oCriteria->addSelectColumn ( DynaformPeer::DYN_UID );
    $oCriteria->addSelectColumn ( DynaformPeer::PRO_UID );
    $oCriteria->addSelectColumn ( DynaformPeer::DYN_TYPE );
    $oCriteria->addAsColumn ( 'DYN_TITLE', 'C1.CON_VALUE' );
    $oCriteria->addAsColumn ( 'DYN_DESCRIPTION', 'C2.CON_VALUE' );
    $oCriteria->addAlias ( 'C1', 'CONTENT' );
    $oCriteria->addAlias ( 'C2', 'CONTENT' );
    $aConditions    = array ();
    $aConditions [] = array (DynaformPeer::DYN_UID, 'C1.CON_ID' );
    $aConditions [] = array ('C1.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter );
    $aConditions [] = array ('C1.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
    $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
    $aConditions    = array ();
    $aConditions [] = array (DynaformPeer::DYN_UID, 'C2.CON_ID' );
    $aConditions [] = array ('C2.CON_CATEGORY', $sDelimiter . 'DYN_DESCRIPTION' . $sDelimiter );
    $aConditions [] = array ('C2.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
    $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
    $oCriteria->add ( DynaformPeer::PRO_UID, $sProcessUID );

    $oDataset = DynaformPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    $dynaformArray = array ();
    $dynaformArray [] = array ('d' => 'char' );
    while ( $aRow = $oDataset->getRow () ) {
      if (($aRow ['DYN_TITLE'] == NULL)||($aRow ['DYN_TITLE'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label

        $aRow ['DYN_TITLE'] = Content::Load("DYN_TITLE","",$aRow ['DYN_UID'],SYS_LANG);

      }
    if (($aRow ['DYN_DESCRIPTION'] == NULL)||($aRow ['DYN_DESCRIPTION'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label

        $aRow ['DYN_DESCRIPTION'] = Content::Load("DYN_DESCRIPTION","",$aRow ['DYN_UID'],SYS_LANG);

      }
      $dynaformArray [] = $aRow;
      $oDataset->next ();
    }

    return $dynaformArray;
  }


  /*
  * Return the Input Documents list array
  * @param string $sProcessUID
  * @return object
  */
  function getExtInputDocumentsCriteria($sProcessUID = '')
  {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria  = new Criteria ( 'workflow' );
    $oCriteria->addSelectColumn ( InputDocumentPeer::INP_DOC_UID );
    $oCriteria->addSelectColumn ( InputDocumentPeer::PRO_UID );
    $oCriteria->addAsColumn ( 'INP_DOC_TITLE', 'C1.CON_VALUE' );
    $oCriteria->addAsColumn ( 'INP_DOC_DESCRIPTION', 'C2.CON_VALUE' );
    $oCriteria->addAlias ( 'C1', 'CONTENT' );
    $oCriteria->addAlias ( 'C2', 'CONTENT' );
    $aConditions    = array ();
    $aConditions [] = array (InputDocumentPeer::INP_DOC_UID, 'C1.CON_ID' );
    $aConditions [] = array ('C1.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter );
    $aConditions [] = array ('C1.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
    $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
    $aConditions    = array ();
    $aConditions [] = array (InputDocumentPeer::INP_DOC_UID, 'C2.CON_ID' );
    $aConditions [] = array ('C2.CON_CATEGORY', $sDelimiter . 'INP_DOC_DESCRIPTION' . $sDelimiter );
    $aConditions [] = array ('C2.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
    $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
    $oCriteria->add ( InputDocumentPeer::PRO_UID, $sProcessUID );

    $oDataset = InputDocumentPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    $inputDocArray    = "";
    $inputDocArray [] = array ('INP_DOC_UID' => 'char', 'PRO_UID' => 'char', 'INP_DOC_TITLE' => 'char', 'INP_DOC_DESCRIPTION' => 'char' );
    while ( $aRow = $oDataset->getRow () ) {
      if (($aRow ['INP_DOC_TITLE'] == NULL)||($aRow ['INP_DOC_TITLE'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label
        $inputDocument                = new InputDocument ( );
        $inputDocumentObj             = $inputDocument->load ( $aRow ['INP_DOC_UID'] );
        $aRow ['INP_DOC_TITLE']       = $inputDocumentObj ['INP_DOC_TITLE'];
        $aRow ['INP_DOC_DESCRIPTION'] = $inputDocumentObj ['INP_DOC_DESCRIPTION'];
      }
      $inputDocArray [] = $aRow;
      $oDataset->next ();
    }
      /*global $_DBArray;
      $_DBArray = (isset ( $_SESSION ['_DBArray'] ) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['inputDocArrayMain'] = $inputDocArray;
      //$_SESSION ['_DBArray']['inputDocArrayMain']        = $_DBArray;*/


    return $inputDocArray;
  }

  /*
  * Return the Output Documents list array
  * @param string $sProcessUID
  * @return object
  */
  function getExtOutputDocumentsCriteria($sProcessUID = '')
  {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria  = new Criteria ( 'workflow' );
    $oCriteria->addSelectColumn ( OutputDocumentPeer::OUT_DOC_UID );
    $oCriteria->addSelectColumn ( OutputDocumentPeer::OUT_DOC_TYPE );
    $oCriteria->addSelectColumn ( OutputDocumentPeer::PRO_UID );
    $oCriteria->addAsColumn ( 'OUT_DOC_TITLE', 'C1.CON_VALUE' );
    $oCriteria->addAsColumn ( 'OUT_DOC_DESCRIPTION', 'C2.CON_VALUE' );
    $oCriteria->addAlias ( 'C1', 'CONTENT' );
    $oCriteria->addAlias ( 'C2', 'CONTENT' );
    $aConditions    = array ();
    $aConditions [] = array (OutputDocumentPeer::OUT_DOC_UID, 'C1.CON_ID' );
    $aConditions [] = array ('C1.CON_CATEGORY', $sDelimiter . 'OUT_DOC_TITLE' . $sDelimiter );
    $aConditions [] = array ('C1.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
    $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
    $aConditions    = array ();
    $aConditions [] = array (OutputDocumentPeer::OUT_DOC_UID, 'C2.CON_ID' );
    $aConditions [] = array ('C2.CON_CATEGORY', $sDelimiter . 'OUT_DOC_DESCRIPTION' . $sDelimiter );
    $aConditions [] = array ('C2.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter );
    $oCriteria->addJoinMC ( $aConditions, Criteria::LEFT_JOIN );
    $oCriteria->add ( OutputDocumentPeer::PRO_UID, $sProcessUID );

    $oDataset = OutputDocumentPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    $outputDocArray = array ();
    $outputDocArray [] = array ('d' => 'char' );
    while ( $aRow = $oDataset->getRow () ) {
      if (($aRow ['OUT_DOC_TITLE'] == NULL)||($aRow ['OUT_DOC_TITLE'] == "")) { // There is no transaltion for this Document name, try to get/regenerate the label
        $outputDocument               = new OutputDocument ( );
        $outputDocumentObj            = $outputDocument->load ( $aRow ['OUT_DOC_UID'] );
        $aRow ['OUT_DOC_TITLE']       = $outputDocumentObj ['OUT_DOC_TITLE'];
        $aRow ['OUT_DOC_DESCRIPTION'] = $outputDocumentObj ['OUT_DOC_DESCRIPTION'];
      }
      $outputDocArray [] = $aRow;
      $oDataset->next ();
    }
    global $_DBArray;
    $_DBArray                    = (isset ( $_SESSION ['_DBArray'] ) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['outputDocArray'] = $outputDocArray;
    $_SESSION ['_DBArray']       = $_DBArray;
    G::LoadClass ( 'ArrayPeer' );
    $oCriteria                   = new Criteria ( 'dbarray' );
    $oCriteria->setDBArrayTable ( 'outputDocArray' );

    return $outputDocArray;
  }

  /**
   * subProcess_Properties New Processmap
   *
   * @param   string    $sProcessUID
   * @param   string    $sTaskUID
   * @param   string    $sIndex
   * @param   string    $sType  (0=>'Asynchronous' , 1=>'Synchronous')
   * @return  void
   * @throw   Exception $oError
   */
  function subProcessExtProperties($sProcessUID = '', $sTaskUID = '', $sIndex = '',$sType = '')
  {
    try { //echo "$sProcessUID = '', $sTaskUID = '', $sIndex = ''";
      $SP_VARIABLES_OUT = array ();
      $SP_VARIABLES_IN  = array ();

      /* Prepare page before to show */
      global $_DBArray;
      $_DBArray = (isset ( $_SESSION ['_DBArray'] ) ? $_SESSION ['_DBArray'] : '');
      $_DBArray ['NewCase'] = $this->subProcess_TaskIni ( $sProcessUID );
      unset ( $_DBArray ['TheProcesses'] );
      $_DBArray ['TheProcesses'] [] = array ('pro_uid' => 'char', 'value' => 'char' );
      $i = 0;
      foreach ( $_DBArray ['NewCase'] as $aRow ) {
        if ($i > 0) {
          $_DBArray ['TheProcesses'] [] = array ('pro_uid' => $aRow ['pro_uid'] . '_' . $i, 'value' => $aRow ['value'] );
        }
        $i ++;
      }
      //print'<hr>';print_r($_DBArray['NewCase']);print'<hr>';
      $oCriteria = new Criteria ( 'workflow' );
      $del       = DBAdapter::getStringDelimiter ();
      $oCriteria->add ( SubProcessPeer::PRO_PARENT, $sProcessUID );
      $oCriteria->add ( SubProcessPeer::PRO_PARENT, $sProcessUID );
      $oCriteria->add ( SubProcessPeer::TAS_PARENT, $sTaskUID );

      $oCriteria->addAsColumn ( 'CON_VALUE', 'C1.CON_VALUE', 'CON_TITLE' );
      $oCriteria->addAlias ( "C1", 'CONTENT' );
      $tasTitleConds    = array ();
      $tasTitleConds [] = array (SubProcessPeer::TAS_PARENT, 'C1.CON_ID' );
      $tasTitleConds [] = array ('C1.CON_CATEGORY', $del . 'TAS_TITLE' . $del );
      $tasTitleConds [] = array ('C1.CON_LANG', $del . SYS_LANG . $del );
      $oCriteria->addJoinMC ( $tasTitleConds, Criteria::LEFT_JOIN );

      $oDataset = SubProcessPeer::doSelectRS ( $oCriteria );
      $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
      $oDataset->next ();
      $aRow = $oDataset->getRow ();
      /*while($aRow = $oDataset->getRow ())  {

        $aSubProcess [] = array ('CON_VALUE' => $aRow ['CON_VALUE'], 'TAS_UID' => $sTaskUID, 'TASKS' => $sTaskUID,'TAS_TITLE' => $aRow ['CON_VALUE'],
                                'SPROCESS_NAME' => $aRow ['CON_VALUE']
            );
        $oDataset->next ();
      }*/

      $aRow ['TASKS'] = $aRow ['TAS_UID'];
      //print "<hr>".$aRow['TASKS']."<hr>";
      //$aRow['SPROCESS_NAME'] = $aRow['TAS_TITLE'];
      $aRow ['TAS_TITLE'] = $aRow ['CON_VALUE'];
      $aRow ['SPROCESS_NAME'] = $aRow ['CON_VALUE'];

      if($sType == 0)
      {
          $SP_VARIABLES_OUT = unserialize ( $aRow ['SP_VARIABLES_OUT'] );
          if (is_array ( $SP_VARIABLES_OUT )) {
            $i = 0;
            $aRow1 = array();
            foreach ( $SP_VARIABLES_OUT as $indice => $valor ) {
              $aRow1   [$i]             =  $aRow;
              $aRow1   [$i]['VAR_OUT1'] =  $indice;
              $aRow1   [$i]['VAR_OUT2'] =  $valor;
              //$aRow1   [$i]['PROCESSES'] =  $_DBArray ['TheProcesses'];
              $i ++;
            }
          }
      }

      if($sType == 1)
      {
          $SP_VARIABLES_IN = unserialize ( $aRow ['SP_VARIABLES_IN'] );
          if (is_array ( $SP_VARIABLES_IN )) {
            $j = 0;
            foreach ( $SP_VARIABLES_IN as $indice => $valor ) {
              $aRow1  [$j]             = $aRow;
              $aRow1  [$j] ['VAR_IN1'] = $indice;
              $aRow1  [$j] ['VAR_IN2'] = $valor;
              //$aRow1   [$i]['PROCESSES'] =  $_DBArray ['TheProcesses'];
              $j ++;
            }
          }
      }
      $aRow ['INDEX'] = $sIndex;
      //print '<hr>';print_r($aRow);
      return $aRow1;
      //return $aSubProcess;

    } catch ( Exception $oError ) {
      throw ($oError);
    }
  }

  /**
   * listProcessesUser for Extjs
   *
   * @param  string           $sProcessUID
   * @return array(aProcessUser) $aProcessUser
   */
  function listExtProcessesUser($sProcessUID) {

    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(ProcessUserPeer::PU_UID);
    $oCriteria->addSelectColumn(ProcessUserPeer::USR_UID);
    $oCriteria->addSelectColumn(ProcessUserPeer::PRO_UID);
    $oCriteria->addSelectColumn(ProcessUserPeer::PU_TYPE);
    $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_EMAIL);
    $oCriteria->addJoin(ProcessUserPeer::USR_UID, UsersPeer::USR_UID, Criteria::LEFT_JOIN);
    $oCriteria->add(ProcessUserPeer::PRO_UID, $sProcessUID);

    $oDataset = ProcessUserPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    $aProcessUser = '';
    while ( $aRow = $oDataset->getRow () ) {
        $aProcessUser [] = array ('PU_UID' => $aRow ['PU_UID'],'USR_UID' => $aRow ['USR_UID'],'PU_TYPE' => $aRow ['PU_TYPE'],'USR_FIRSTNAME' => $aRow ['USR_FIRSTNAME'],'USR_LASTNAME' => $aRow ['USR_LASTNAME'],'USR_EMAIL' => $aRow ['USR_EMAIL']);
        $oDataset->next ();
    }
    return $aProcessUser;
  }

  /**
   * listExtNoProcessesUser for Extjs
   *
   * @param  string           $sProcessUID
   * @return array(aAvailableUser) $aAvailableUser
   */
  function listExtNoProcessesUser($sProcessUID) {
    G::LoadSystem('rbac');

    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(ProcessUserPeer::USR_UID);
    $oCriteria->add(ProcessUserPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(ProcessUserPeer::PU_TYPE, 'SUPERVISOR');
    $oDataset = ProcessUserPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aUIDS = array();
    while ($aRow = $oDataset->getRow()) {
      $aUIDS [] = $aRow ['USR_UID'];
      $oDataset->next();
    }
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(UsersPeer::USR_UID);
    $oCriteria->add(UsersPeer::USR_UID, $aUIDS, Criteria::NOT_IN);
    $oDataset = UsersPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aUIDS = array();
    $oRBAC = RBAC::getSingleton ();
    while ($aRow = $oDataset->getRow()) {
      $oRBAC->loadUserRolePermission($oRBAC->sSystem, $aRow ['USR_UID']);
      $aPermissions = $oRBAC->aUserInfo [$oRBAC->sSystem] ['PERMISSIONS'];
      $bInclude = false;
      foreach ($aPermissions as $aPermission) {
        if ($aPermission ['PER_CODE'] == 'PM_SUPERVISOR') {
          $bInclude = true;
        }
      }
      if ($bInclude) {
        $aUIDS [] = $aRow ['USR_UID'];
      }
      $oDataset->next();
    }
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(UsersPeer::USR_UID);
    $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
    $oCriteria->add(UsersPeer::USR_UID, $aUIDS, Criteria::IN);

    $oDataset = UsersPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    $aAvailableUser = '';
     while ( $aRow = $oDataset->getRow () ) {
        $aAvailableUser [] = array ('USR_UID' => $aRow ['USR_UID'],'USR_FIRSTNAME' => $aRow ['USR_FIRSTNAME'],'USR_LASTNAME' => $aRow ['USR_LASTNAME']);
        $oDataset->next ();
    }
    return $aAvailableUser;
  }

  /*
   * Return the supervisors dynaforms list array
   * @param string $sProcessUID
   * @return array
   */

  function getExtSupervisorDynaformsList($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_UID);
    $oCriteria->addSelectColumn(StepSupervisorPeer::PRO_UID);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_TYPE_OBJ);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_UID_OBJ);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_POSITION);
    $oCriteria->addSelectColumn(DynaformPeer::DYN_UID);
    $oCriteria->addAsColumn('DYN_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(StepSupervisorPeer::STEP_UID_OBJ, DynaformPeer::DYN_UID);
    $aConditions [] = array(StepSupervisorPeer::STEP_TYPE_OBJ, $sDelimiter . 'DYNAFORM' . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(StepSupervisorPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(StepSupervisorPeer::STEP_TYPE_OBJ, 'DYNAFORM');
    $oCriteria->addAscendingOrderByColumn(StepSupervisorPeer::STEP_POSITION);

    $oDataset = StepSupervisorPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    $aProcessDynaform = '';
     while ( $aRow = $oDataset->getRow () ) {
        $aProcessDynaform [] = array ('DYN_TITLE' => $aRow ['DYN_TITLE'],'DYN_UID' => $aRow ['DYN_UID'],'STEP_UID' => $aRow ['STEP_UID'],'STEP_UID_OBJ' => $aRow ['STEP_UID_OBJ'],'STEP_TYPE_OBJ' => $aRow ['STEP_TYPE_OBJ'],'STEP_POSITION' => $aRow ['STEP_POSITION']);
        $oDataset->next ();
    }
    return $aProcessDynaform;
  }

  /*
   * Return the available supervisors dynaforms list array
   * @param string $sProcessUID
   * @return array
   */

  function getExtAvailableSupervisorDynaformsList($sProcessUID = '') {
    $oCriteria = $this->getSupervisorDynaformsCriteria($sProcessUID);
    $oDataset = StepSupervisorPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aUIDS = array();
    while ($aRow = $oDataset->getRow()) {
      $aUIDS [] = $aRow ['STEP_UID_OBJ'];
      $oDataset->next();
    }
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(DynaformPeer::DYN_UID);
    $oCriteria->addSelectColumn(DynaformPeer::PRO_UID);
    $oCriteria->addAsColumn('DYN_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(DynaformPeer::DYN_TYPE, 'xmlform');
    $oCriteria->add(DynaformPeer::DYN_UID, $aUIDS, Criteria::NOT_IN);

    $oDataset = DynaformPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    $aAvailableProcessDynaform = '';
     while ( $aRow = $oDataset->getRow () ) {
        $aAvailableProcessDynaform [] = array ('DYN_TITLE' => $aRow ['DYN_TITLE'],'DYN_UID' => $aRow ['DYN_UID']);
        $oDataset->next ();
    }
    return $aAvailableProcessDynaform;
  }

  /*
   * Return the supervisors input document list array
   * @param string $sProcessUID
   * @return array
   */

  function getExtSupervisorInputsList($sProcessUID = '') {
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_UID);
    $oCriteria->addSelectColumn(StepSupervisorPeer::PRO_UID);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_TYPE_OBJ);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_UID_OBJ);
    $oCriteria->addSelectColumn(StepSupervisorPeer::STEP_POSITION);
    $oCriteria->addSelectColumn(InputDocumentPeer::INP_DOC_UID);
    $oCriteria->addAsColumn('INP_DOC_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(StepSupervisorPeer::STEP_UID_OBJ, InputDocumentPeer::INP_DOC_UID);
    $aConditions [] = array(StepSupervisorPeer::STEP_TYPE_OBJ, $sDelimiter . 'INPUT_DOCUMENT' . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $aConditions = array();
    $aConditions [] = array(InputDocumentPeer::INP_DOC_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(StepSupervisorPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(StepSupervisorPeer::STEP_TYPE_OBJ, 'INPUT_DOCUMENT');
    $oCriteria->addAscendingOrderByColumn(StepSupervisorPeer::STEP_POSITION);
    $oDataset = StepSupervisorPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    $aProcessInputDoc = '';
     while ( $aRow = $oDataset->getRow () ) {
        $aProcessInputDoc [] = array ('INP_DOC_TITLE' => $aRow ['INP_DOC_TITLE'],'INP_DOC_UID' => $aRow ['INP_DOC_UID'],'STEP_UID' => $aRow ['STEP_UID'],'STEP_UID_OBJ' => $aRow ['STEP_UID_OBJ'],'STEP_TYPE_OBJ' => $aRow ['STEP_TYPE_OBJ'],'STEP_POSITION' => $aRow ['STEP_POSITION']);
        $oDataset->next ();
    }
    return $aProcessInputDoc;
  }

  /*
   * Return the available supervisors input documents list array
   * @param string $sProcessUID
   * @return array
   */

  function getExtAvailableSupervisorInputsList($sProcessUID = '') {
    $oCriteria = $this->getSupervisorInputsCriteria($sProcessUID);
    $oDataset = StepSupervisorPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aUIDS = array();
    while ($aRow = $oDataset->getRow()) {
      $aUIDS [] = $aRow ['STEP_UID_OBJ'];
      $oDataset->next();
    }
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(InputDocumentPeer::INP_DOC_UID);
    $oCriteria->addSelectColumn(InputDocumentPeer::PRO_UID);
    $oCriteria->addAsColumn('INP_DOC_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(InputDocumentPeer::INP_DOC_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(InputDocumentPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(InputDocumentPeer::INP_DOC_UID, $aUIDS, Criteria::NOT_IN);
    $oDataset = InputDocumentPeer::doSelectRS ( $oCriteria );
    $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
    $oDataset->next ();
    $aAvailableProcessIODoc = '';
     while ( $aRow = $oDataset->getRow () ) {
        $aAvailableProcessIODoc [] = array ('INP_DOC_TITLE' => $aRow ['INP_DOC_TITLE'],'INP_DOC_UID' => $aRow ['INP_DOC_UID']);
        $oDataset->next ();
    }
    return $aAvailableProcessIODoc;
  } 

 /**
   * listDBSConnection
   *
   * @param  string           $sProcessUID
   * @return array(aDBList)   $aDBList
   */
 function getExtCriteriaDBSList($sProcessUID)
    {
       try
       {
        $sDelimiter = DBAdapter::getStringDelimiter();
        $oCriteria = new Criteria('workflow');
        $oCriteria->addSelectColumn(DbSourcePeer::DBS_UID);
        $oCriteria->addSelectColumn(DbSourcePeer::PRO_UID);
        $oCriteria->addSelectColumn(DbSourcePeer::DBS_TYPE);
        $oCriteria->addSelectColumn(DbSourcePeer::DBS_SERVER);
        $oCriteria->addSelectColumn(DbSourcePeer::DBS_DATABASE_NAME);
        $oCriteria->addSelectColumn(DbSourcePeer::DBS_USERNAME);
        $oCriteria->addSelectColumn(DbSourcePeer::DBS_PASSWORD);
        $oCriteria->addSelectColumn(DbSourcePeer::DBS_PORT);
        $oCriteria->addAsColumn('DBS_DESCRIPTION', 'C.CON_VALUE');
        $oCriteria->addAlias('C', 'CONTENT');
        $aConditions = array();
        $aConditions[] = array(DbSourcePeer::DBS_UID, 'C.CON_ID');
        $aConditions[] = array('C.CON_CATEGORY', $sDelimiter . 'DBS_DESCRIPTION' . $sDelimiter);
        $aConditions[] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
        $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
        $oCriteria->add(DbSourcePeer::PRO_UID, $sProcessUID);

        $oDataset = DbSourcePeer::doSelectRS ( $oCriteria );
        $oDataset->setFetchmode ( ResultSet::FETCHMODE_ASSOC );
        $oDataset->next ();
        while ( $aRow = $oDataset->getRow () ) {
            $aDBList [] = array ('DBS_UID' => $aRow ['DBS_UID'],'DBS_TYPE' => $aRow ['DBS_TYPE'],'DBS_SERVER' => $aRow ['DBS_SERVER'],'DBS_DATABASE_NAME' => $aRow ['DBS_DATABASE_NAME'],'DBS_USERNAME' => $aRow ['DBS_USERNAME'],'DBS_PASSWORD' => $aRow ['DBS_PASSWORD'],'DBS_DESCRIPTION' => $aRow ['DBS_DESCRIPTION'],'DBS_PORT' => $aRow ['DBS_PORT']);
            $oDataset->next ();
        }
        return $aDBList;
      }
      catch ( Exception $e ) {
            throw ($e);
        }
    }

    /**
   * newExtObjectPermission
   *
   * @param  string           $sProcessUID
   * @param  string           $sAction
   * @return array depending on action
   */
    function newExtObjectPermission($sProcessUID,$sAction)
    {
    $aAllTasks    = array();
    $aAllTasks [] = array('UID' => 'char', 'LABEL' => 'char');
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(TaskPeer::PRO_UID);
    $oCriteria->addSelectColumn(TaskPeer::TAS_UID);
    $oCriteria->addSelectColumn(ContentPeer::CON_VALUE);
    $aConditions = array();
    $aConditions [] = array(0 => TaskPeer::TAS_UID, 1 => ContentPeer::CON_ID);
    $aConditions [] = array(0 => ContentPeer::CON_CATEGORY, 1 => DBAdapter::getStringDelimiter () . 'TAS_TITLE' . DBAdapter::getStringDelimiter ());
    $aConditions [] = array(0 => ContentPeer::CON_LANG, 1 => DBAdapter::getStringDelimiter () . SYS_LANG . DBAdapter::getStringDelimiter ());
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(TaskPeer::PRO_UID, $sProcessUID);
    $oDataset = TaskPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAllTasks [] = array('UID' => $aRow ['TAS_UID'], 'LABEL' => $aRow ['CON_VALUE']);
      $oDataset->next();
    }
    $aUsersGroups = array();
    $aUsersGroups [] = array('UID' => 'char', 'LABEL' => 'char');
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(GroupwfPeer::GRP_UID);
    $oCriteria->addAsColumn('GRP_TITLE', ContentPeer::CON_VALUE);
    $aConditions = array();
    $aConditions [] = array(GroupwfPeer::GRP_UID, ContentPeer::CON_ID);
    $aConditions [] = array(ContentPeer::CON_CATEGORY, DBAdapter::getStringDelimiter () . 'GRP_TITLE' . DBAdapter::getStringDelimiter ());
    $aConditions [] = array(ContentPeer::CON_LANG, DBAdapter::getStringDelimiter () . SYS_LANG . DBAdapter::getStringDelimiter ());
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(GroupwfPeer::GRP_STATUS, 'ACTIVE');
    $oDataset = GroupwfPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aUsersGroups [] = array('UID' => '2|' . $aRow ['GRP_UID'], 'LABEL' => $aRow ['GRP_TITLE'] . ' (' . G::LoadTranslation('ID_GROUP') . ')');
      $oDataset->next();
    }
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(UsersPeer::USR_UID);
    $oCriteria->addSelectColumn(UsersPeer::USR_USERNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
    $oCriteria->addSelectColumn(UsersPeer::USR_LASTNAME);
    $oCriteria->add(UsersPeer::USR_STATUS, 'ACTIVE');
    $oDataset = UsersPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aUsersGroups [] = array('UID' => '1|' . $aRow ['USR_UID'], 'LABEL' => $aRow ['USR_FIRSTNAME'] . ' ' . $aRow ['USR_LASTNAME'] . ' (' . $aRow ['USR_USERNAME'] . ')');
      $oDataset->next();
    }
    $aAllObjects = array();
    $aAllObjects [] = array('UID' => 'char', 'LABEL' => 'char');
    $aAllDynaforms = array();
    $aAllDynaforms [] = array('UID' => 'char', 'LABEL' => 'char');
    $aAllInputs = array();
    $aAllInputs [] = array('UID' => 'char', 'LABEL' => 'char');
    $aAllOutputs = array();
    $aAllOutputs [] = array('UID' => 'char', 'LABEL' => 'char');
    $oCriteria = $this->getDynaformsCriteria($sProcessUID);
    $oCriteria->add(DynaformPeer::DYN_TYPE, 'XMLFORM');
    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aRow ['DYN_TITLE'] = (isset($aRow ['DYN_TITLE'])) ? $aRow ['DYN_TITLE'] : '';
      $aAllObjects [] = array('UID' => 'DYNAFORM|' . $aRow ['DYN_UID'],
          'LABEL' => $aRow ['DYN_TITLE'] . ' (' . G::LoadTranslation('ID_DYNAFORM') . ')');
      $aAllDynaforms [] = array('UID' => $aRow ['DYN_UID'],
          'LABEL' => $aRow ['DYN_TITLE']);
      $oDataset->next();
    }
    G::LoadClass('ArrayPeer');
    $oDataset = ArrayBasePeer::doSelectRS($this->getInputDocumentsCriteria($sProcessUID));
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAllObjects [] = array('UID' => 'INPUT_DOCUMENT|' . $aRow ['INP_DOC_UID'],
          'LABEL' => $aRow ['INP_DOC_TITLE'] . ' (' . G::LoadTranslation('ID_INPUT_DOCUMENT') . ')');
      $aAllInputs [] = array('UID' => $aRow ['INP_DOC_UID'],
          'LABEL' => $aRow ['INP_DOC_TITLE']);
      $oDataset->next();
    }
    G::LoadClass('ArrayPeer');
    $oDataset = ArrayBasePeer::doSelectRS($this->getOutputDocumentsCriteria($sProcessUID));
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAllObjects [] = array('UID' => 'OUTPUT_DOCUMENT|' . $aRow ['OUT_DOC_UID'], 'LABEL' => $aRow ['OUT_DOC_TITLE'] . ' (' . G::LoadTranslation('ID_OUTPUT_DOCUMENT') . ')');
      $aAllOutputs [] = array('UID' => $aRow ['OUT_DOC_UID'], 'LABEL' => $aRow ['OUT_DOC_TITLE']);
      $oDataset->next();
    }
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');

    switch($sAction){
        case 'tasks':
           return $aAllTasks;
            break;
        case 'users':
           return $aUsersGroups;
            break;
        case 'dynaform':
           return $aAllDynaforms;
            break;
        case 'input':
           return $aAllInputs;
            break;
        case 'output':
           return $aAllOutputs;
            break;
    }
  }

  function ExtcaseTracker($sProcessUID) {
    $oCriteria = new Criteria ( );
    $oCriteria->add(CaseTrackerPeer::PRO_UID, $sProcessUID);
    $oCaseTracker = new CaseTracker ( );
    if (CaseTrackerPeer::doCount($oCriteria) === 0) {
      $aCaseTracker = array('PRO_UID' => $sProcessUID, 'CT_MAP_TYPE' => 'PROCESSMAP', 'CT_DERIVATION_HISTORY' => 1, 'CT_MESSAGE_HISTORY' => 1);
      $oCaseTracker->create($aCaseTracker);
    } else {
      $aCaseTracker = $oCaseTracker->load($sProcessUID);
    }
    return $aCaseTracker;
    /*global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'tracker/tracker_Configuration', '', $aCaseTracker, '../tracker/tracker_Save');
    G::RenderPage('publish', 'raw');
    return true;*/
  }


  /**
   * getCaseTrackerObjectsCriteria
   *
   * @param  string           $sProcessUID
   * @return object(Criteria) $oCriteria
   */
  function getExtCaseTrackerObjectsCriteria($sProcessUID) {
    $aObjects = array();
    $aObjects [] = array('CTO_TITLE' => 'char', 'CTO_UID' => 'char', 'CTO_TYPE_OBJ' => 'char', 'CTO_UID_OBJ' => 'char', 'CTO_CONDITION' => 'char', 'CTO_POSITION' => 'integer');
    $oCriteria = new Criteria('workflow');
    $oCriteria->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
    $oCriteria->addAscendingOrderByColumn(CaseTrackerObjectPeer::CTO_POSITION);
    $oDataset = CaseTrackerObjectPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      try {
        switch ($aRow ['CTO_TYPE_OBJ']) {
          case 'DYNAFORM' :
            $oDynaform = new Dynaform ( );
            $aFields = $oDynaform->load($aRow ['CTO_UID_OBJ']);
            $sTitle = $aFields ['DYN_TITLE'];
            break;
          case 'INPUT_DOCUMENT' :
            $oInputDocument = new InputDocument ( );
            $aFields = $oInputDocument->load($aRow ['CTO_UID_OBJ']);
            $sTitle = $aFields ['INP_DOC_TITLE'];
            break;
          case 'OUTPUT_DOCUMENT' :
            $oOutputDocument = new OutputDocument ( );
            $aFields = $oOutputDocument->load($aRow ['CTO_UID_OBJ']);
            $sTitle = $aFields ['OUT_DOC_TITLE'];
            break;
        }
        $aObjects [] = array('CTO_TITLE' => $sTitle, 'CTO_UID' => $aRow ['CTO_UID'], 'CTO_TYPE_OBJ' => $aRow ['CTO_TYPE_OBJ'], 'CTO_UID_OBJ' => $aRow ['CTO_UID_OBJ'], 'CTO_CONDITION' => $aRow ['CTO_CONDITION'], 'CTO_POSITION' => $aRow ['CTO_POSITION']);


      }

      catch (Exception $oError) { //Nothing
      }
      $oDataset->next();
    }
   // return $aObjects;
    global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['objects'] = $aObjects;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('objects');
    $oCriteria->addAscendingOrderByColumn('CTO_POSITION');
    return $aObjects;
  }

  /**
   * availableCaseTrackerObjects
   *
   * @param  string     $sProcessUID
   * @return boolean    true
   */
  function availableExtCaseTrackerObjects($sProcessUID) {
    global $G_PUBLISH;
    $G_PUBLISH = new Publisher ( );
    $G_PUBLISH->AddContent('propeltable', 'paged-table', 'tracker/tracker_AvailableCaseTrackerObjects', $this->getAvailableCaseTrackerObjectsCriteria($sProcessUID), array('PRO_UID' => $sProcessUID));
    G::RenderPage('publish', 'raw');
    return true;
  }

  /**
   * getAvailableCaseTrackerObjectsCriteria
   *
   * @param  string             $sProcessUID Default value empty
   * @return object(Criteria)   $oCriteria
   */
  function getAvailableExtCaseTrackerObjects($sProcessUID = '') {
    $oCriteria = $this->getCaseTrackerObjectsCriteria($sProcessUID);
    $oDataset = CaseTrackerObjectPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aDynaformsUIDS = array();
    $aInputsUIDS = array();
    $aOutputsUIDS = array();
    while ($aRow = $oDataset->getRow()) {
      switch ($aRow ['CTO_TYPE_OBJ']) {
        case 'DYNAFORM' :
          $aDynaformsUIDS [] = $aRow ['CTO_UID_OBJ'];
          break;
        case 'INPUT_DOCUMENT' :
          $aInputsUIDS [] = $aRow ['CTO_UID_OBJ'];
          break;
        case 'OUTPUT_DOCUMENT' :
          $aOutputsUIDS [] = $aRow ['CTO_UID_OBJ'];
          break;
      }
      $oDataset->next();
    }
    $aAvailableObjects = array();
    $aAvailableObjects [] = array('OBJECT_UID' => 'char', 'OBJECT_TYPE' => 'char', 'OBJECT_TITLE' => 'char');
    $sDelimiter = DBAdapter::getStringDelimiter ();
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(DynaformPeer::DYN_UID);
    $oCriteria->addAsColumn('DYN_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(DynaformPeer::DYN_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'DYN_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(DynaformPeer::DYN_TYPE, 'xmlform');
    $oCriteria->add(DynaformPeer::DYN_UID, $aDynaformsUIDS, Criteria::NOT_IN);
    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAvailableObjects [] = array('OBJECT_UID' => $aRow ['DYN_UID'], 'OBJECT_TYPE' => 'DYNAFORM', 'OBJECT_TITLE' => $aRow ['DYN_TITLE']);
      $oDataset->next();
    }
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(InputDocumentPeer::INP_DOC_UID);
    $oCriteria->addAsColumn('INP_DOC_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(InputDocumentPeer::INP_DOC_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'INP_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(InputDocumentPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(InputDocumentPeer::INP_DOC_UID, $aInputsUIDS, Criteria::NOT_IN);
    $oDataset = InputDocumentPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAvailableObjects [] = array('OBJECT_UID' => $aRow ['INP_DOC_UID'], 'OBJECT_TYPE' => 'INPUT_DOCUMENT', 'OBJECT_TITLE' => $aRow ['INP_DOC_TITLE']);
      $oDataset->next();
    }
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(OutputDocumentPeer::OUT_DOC_UID);
    $oCriteria->addAsColumn('OUT_DOC_TITLE', 'C.CON_VALUE');
    $oCriteria->addAlias('C', 'CONTENT');
    $aConditions = array();
    $aConditions [] = array(OutputDocumentPeer::OUT_DOC_UID, 'C.CON_ID');
    $aConditions [] = array('C.CON_CATEGORY', $sDelimiter . 'OUT_DOC_TITLE' . $sDelimiter);
    $aConditions [] = array('C.CON_LANG', $sDelimiter . SYS_LANG . $sDelimiter);
    $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
    $oCriteria->add(OutputDocumentPeer::PRO_UID, $sProcessUID);
    $oCriteria->add(OutputDocumentPeer::OUT_DOC_UID, $aOutputsUIDS, Criteria::NOT_IN);

    $oDataset = OutputDocumentPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
      $aAvailableObjects [] = array('OBJECT_UID' => $aRow ['OUT_DOC_UID'], 'OBJECT_TYPE' => 'OUTPUT_DOCUMENT', 'OBJECT_TITLE' => $aRow ['OUT_DOC_TITLE']);
      $oDataset->next();
    }
    return $aAvailableObjects;
    /*global $_DBArray;
    $_DBArray = (isset($_SESSION ['_DBArray']) ? $_SESSION ['_DBArray'] : '');
    $_DBArray ['availableObjects'] = $aAvailableObjects;
    $_SESSION ['_DBArray'] = $_DBArray;
    G::LoadClass('ArrayPeer');
    $oCriteria = new Criteria('dbarray');
    $oCriteria->setDBArrayTable('availableObjects');
    return $oCriteria;*/
  }

  /**
   * assignCaseTrackerObject
   *
   * @param  string    $sProcessUID
   * @param  string    $sObjType
   * @param  string    $sObjUID
   * @return void
   */
  function assignExtCaseTrackerObject($sProcessUID, $sObjType, $sObjUID) {
    $oCriteria = new Criteria('workflow');
    $oCriteria->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
    $iPosition = CaseTrackerObjectPeer::doCount($oCriteria) + 1;
    $oCaseTrackerObject = new CaseTrackerObject ( );
    $oCaseTrackerObject->create(array('PRO_UID' => $sProcessUID, 'CTO_TYPE_OBJ' => $sObjType, 'CTO_UID_OBJ' => $sObjUID, 'CTO_POSITION' => $iPosition));
  }

  /**
   * removeCaseTrackerObject
   *
   * @param  string    $sCTOUID
   * @param  string    $sProcessUID
   * @param  integer   $iPosition
   * @return void
   */
  function removeExtCaseTrackerObject($sCTOUID, $sProcessUID, $iPosition) {
    $oCaseTrackerObject = new CaseTrackerObject ( );
    $oCaseTrackerObject->remove($sCTOUID);
    $oCaseTrackerObject->reorderPositions($sProcessUID, $iPosition);
  }

  /**
   * upCaseTrackerObject
   *
   * @param  string    $sCTOUID
   * @param  string    $sProcessUID
   * @param  integer   $iPosition
   * @return void
   */
  function upExtCaseTrackerObject($sCTOUID, $sProcessUID, $iPosition) {
    if ($iPosition > 1) {
      $oCriteria1 = new Criteria('workflow');
      $oCriteria1->add(CaseTrackerObjectPeer::CTO_POSITION, $iPosition);
      $oCriteria2 = new Criteria('workflow');
      $oCriteria2->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
      $oCriteria2->add(CaseTrackerObjectPeer::CTO_POSITION, ($iPosition - 1));
      BasePeer::doUpdate($oCriteria2, $oCriteria1, Propel::getConnection('workflow'));

      $oCriteria1 = new Criteria('workflow');
      $oCriteria1->add(CaseTrackerObjectPeer::CTO_POSITION, ($iPosition - 1));
      $oCriteria2 = new Criteria('workflow');
      $oCriteria2->add(CaseTrackerObjectPeer::CTO_UID, $sCTOUID);
      BasePeer::doUpdate($oCriteria2, $oCriteria1, Propel::getConnection('workflow'));
    }
  }

  /**
   * downCaseTrackerObject
   *
   * @param  string    $sCTOUID
   * @param  string    $sProcessUID
   * @param  integer   $iPosition
   * @return void
   */
  function downExtCaseTrackerObject($sCTOUID, $sProcessUID, $iPosition) {
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn('COUNT(*) AS MAX_POSITION');
    $oCriteria->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
    $oDataset = CaseTrackerObjectPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $aRow = $oDataset->getRow();
    if ($iPosition < (int) $aRow ['MAX_POSITION']) {
      $oCriteria1 = new Criteria('workflow');
      $oCriteria1->add(CaseTrackerObjectPeer::CTO_POSITION, $iPosition);
      $oCriteria2 = new Criteria('workflow');
      $oCriteria2->add(CaseTrackerObjectPeer::PRO_UID, $sProcessUID);
      $oCriteria2->add(CaseTrackerObjectPeer::CTO_POSITION, ($iPosition + 1));
      BasePeer::doUpdate($oCriteria2, $oCriteria1, Propel::getConnection('workflow'));
      $oCriteria1 = new Criteria('workflow');
      $oCriteria1->add(CaseTrackerObjectPeer::CTO_POSITION, ($iPosition + 1));
      $oCriteria2 = new Criteria('workflow');
      $oCriteria2->add(CaseTrackerObjectPeer::CTO_UID, $sCTOUID);
      BasePeer::doUpdate($oCriteria2, $oCriteria1, Propel::getConnection('workflow'));
    }
  }
}
