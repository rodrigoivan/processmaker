<?php
  	
  $oHeadPublisher =& headPublisher::getSingleton();
 //$oHeadPublisher->setExtSkin( 'xtheme-gray');
  $oHeadPublisher->addExtJsScript('bpmn/MyWorkflow',true );    //adding a javascript file .js
  $oHeadPublisher->addExtJsScript('bpmn/pmosExt', true );    //adding a javascript file .js
  $oHeadPublisher->addExtJsScript('bpmn/TaskContext', true );    //adding a javascript file .js
  $oHeadPublisher->addExtJsScript('bpmn/ProcessMapContext', true );    //adding a javascript file .js
  $oHeadPublisher->addExtJsScript('bpmn/processmap', true );    //adding a javascript file .js
  $oHeadPublisher->addExtJsScript('bpmn/Annotation' );
  $oHeadPublisher->addExtJsScript('bpmn/FlowConnector');
  $oHeadPublisher->addExtJsScript('bpmn/GroupFigure');
  $oHeadPublisher->addExtJsScript('bpmn/bpmnShapes', true); //
  $oHeadPublisher->addExtJsScript('bpmn/LoopingSubProcess'); //
  $oHeadPublisher->addExtJsScript('bpmn/LoopingTask'); //
  $oHeadPublisher->addExtJsScript('bpmn/Dataobject'); //
  $oHeadPublisher->addExtJsScript('bpmn/Pool',true);
  $oHeadPublisher->addExtJsScript('bpmn/Lane');
    
  $oHeadPublisher->addExtJsScript('bpmn/EventEmptyStart');
  $oHeadPublisher->addExtJsScript('bpmn/EventMessageStart');
  $oHeadPublisher->addExtJsScript('bpmn/EventRuleStart');
  $oHeadPublisher->addExtJsScript('bpmn/EventTimerStart');
  $oHeadPublisher->addExtJsScript('bpmn/EventSignalStart');
  $oHeadPublisher->addExtJsScript('bpmn/EventMulStart');
  $oHeadPublisher->addExtJsScript('bpmn/EventLinkStart');
  $oHeadPublisher->addExtJsScript('bpmn/EventEmptyInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventMessageRecInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventMessageSendInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventTimerInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventBoundaryTimerInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventErrorInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventCompInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventRuleInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventCancelInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventInterSignal');
  $oHeadPublisher->addExtJsScript('bpmn/EventMultipleInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventLinkInter');
  $oHeadPublisher->addExtJsScript('bpmn/EventEmptyEnd');
  $oHeadPublisher->addExtJsScript('bpmn/EventMessageEnd');
  $oHeadPublisher->addExtJsScript('bpmn/EventErrorEnd');
  $oHeadPublisher->addExtJsScript('bpmn/EventCompEnd');
  $oHeadPublisher->addExtJsScript('bpmn/EventTerminateEnd');
  $oHeadPublisher->addExtJsScript('bpmn/EventEndSignal');
  $oHeadPublisher->addExtJsScript('bpmn/EventMultipleEnd');
  $oHeadPublisher->addExtJsScript('bpmn/EventCancelEnd');
  $oHeadPublisher->addExtJsScript('bpmn/EventLinkEnd');

  $oHeadPublisher->addExtJsScript('bpmn/GatewayInclusive' );
  $oHeadPublisher->addExtJsScript('bpmn/GatewayExclusiveData' );
  $oHeadPublisher->addExtJsScript('bpmn/GatewayExclusiveEvent' );
  $oHeadPublisher->addExtJsScript('bpmn/GatewayParallel' );
  $oHeadPublisher->addExtJsScript('bpmn/GatewayComplex' );
  $oHeadPublisher->addExtJsScript('bpmn/GridPanel');
  $oHeadPublisher->addExtJsScript('bpmn/SubProcess' );
  $oHeadPublisher->addExtJsScript('bpmn/AwesomeUploader' );
  $oHeadPublisher->addExtJsScript('bpmn/Ext.ux.form.FileUploadField' );
  $oHeadPublisher->addExtJsScript('bpmn/Ext.ux.XHRUpload' );
  $oHeadPublisher->addExtJsScript('bpmn/swfupload' );
  $oHeadPublisher->addExtJsScript('bpmn/swfupload.swfobject' );
  $oHeadPublisher->addExtJsScript('bpmn/ProcessOptions',true);

  $oHeadPublisher->addContent( 'bpmn/processmap'); //adding a html file  .html.
  G::RenderPage('publish', 'extJs');
 