<?php
/**
 * Dashboard controller
 * @inherits Controller
 * @access public
 */

class Dashboard extends Controller {

  // Class properties
  private $pmDashlet;

  // Class constructor
  public function __construct() {
    G::LoadClass('pmDashlet');
    $this->pmDashlet = new PMDashlet();
  }

  // Functions for the dashboards users module - Start

  public function index($httpData) {
    try {
      $this->setJSVar('dashletsInstances', $this->getDashletsInstancesForCurrentUser());
      $this->includeExtJS('dashboard/index');
      $this->includeExtJSLib('ux/portal');
      G::RenderPage('publish', 'extJs');
    }
    catch (Exception $error) {
      //ToDo: Display a error message
    }
  }

  public function renderDashletInstance($data) {
    try {
      if (!isset($data->DAS_INS_UID)) {
        $data->DAS_INS_UID = '';
      }
      if ($data->DAS_INS_UID == '') {
        throw new Exception('Parameter "DAS_INS_UID" is empty.');
      }
      $this->pmDashlet->setup($data->DAS_INS_UID);

      if (!isset($_REQUEST['w']) ) {
        $width = 300;
      }
      else {
        $width = $_REQUEST['w'];
      }
      $this->pmDashlet->render( $width);
    }
    catch (Exception $error) {
      //ToDo: Render a image with the error message
    }
  }

  private function getDashletsInstancesForCurrentUser() {
    try {
      if (!isset($_SESSION['USER_LOGGED'])) {
        throw new Exception('The session has expired.');
      }
      return $this->pmDashlet->getDashletsInstancesForUser($_SESSION['USER_LOGGED']);
    }
    catch (Exception $error) {
      throw $error;
    }
  }

  // Functions for the dashboards users module - End

  // Functions for the dasboards administration module - Start

  public function dashletsList() {
    try {
      $this->includeExtJS('dashboard/dashletsList');
      $this->setView('dashboard/dashletsList');
      G::RenderPage('publish', 'extJs');
    }
    catch (Exception $error) {
      //ToDo: Display a error message
    }
  }

  public function getDashletsInstances($data) {
    $this->setResponseType('json');
    $result = new stdclass();
    $result->status = 'OK';
    try {
      if (!isset($data->start)) {
        $data->start = null;
      }
      if (!isset($data->limit)) {
        $data->limit = null;
      }
      $result->dashletsInstances = $this->pmDashlet->getDashletsInstances($data->start, $data->limit);
      $result->totalDashletsInstances = $this->pmDashlet->getDashletsInstancesQuantity();
    }
    catch (Exception $error) {
      $result->status = 'ERROR';
      $result->message = $error->getMessage();
    }
    return $result;
  }

  public function dashletInstanceForm($data) {
    try {
      $this->includeExtJS('dashboard/dashletInstanceForm', false);
      $this->setView('dashboard/dashletInstanceForm');
      if (!isset($data->DAS_INS_UID)) {
        $data->DAS_INS_UID = '';
      }
      if ($data->DAS_INS_UID != '') {
        $this->setJSVar('dashletInstance', $this->pmDashlet->getDashletInstance($data->DAS_INS_UID));
      }
      else {
        $this->setJSVar('dashletInstance', new stdclass());
      }
      G::RenderPage('publish', 'extJs');
      return null;
    }
    catch (Exception $error) {
      //ToDo: Display a error message
      error_log($error->getMessage());
    }
  }

  public function saveDashletInstance($data) {
    $this->setResponseType('json');
    $result = new stdclass();
    $result->status = 'OK';
    try {
      $this->pmDashlet->saveDashletInstance(get_object_vars($data));
    }
    catch (Exception $error) {
      $result->status = 'ERROR';
      $result->message = $error->getMessage();
    }
    return $result;
  }

  public function deleteDashletInstance($data) {
    $this->setResponseType('json');
    $result = new stdclass();
    $result->status = 'OK';
    try {
      if (!isset($data->DAS_INS_UID)) {
        $data->DAS_INS_UID = '';
      }
      if ($data->DAS_INS_UID == '') {
        throw new Exception('Parameter "DAS_INS_UID" is empty.');
      }
      $this->pmDashlet->deleteDashletInstance($data->DAS_INS_UID);
    }
    catch (Exception $error) {
      $result->status = 'ERROR';
      $result->message = $error->getMessage();
    }
    return $result;
  }

  public function getOwnersByType($type) {
    $this->setResponseType('json');
    $result = new stdclass();
    $result->status = 'OK';
    try {
      //ToDo: For the next release
    }
    catch (Exception $error) {
      $result->status = 'ERROR';
      $result->message = $error->getMessage();
    }
    return $result;
  }

  // Functions for the dasboards administration module - End

}