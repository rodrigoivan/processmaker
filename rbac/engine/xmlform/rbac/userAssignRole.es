<?
$this->tree = unserialize( 'O:8:"xml_node":5:{s:4:"name";s:8:"dynaForm";s:4:"type";s:4:"open";s:5:"value";s:1:"
";s:10:"attributes";a:3:{s:4:"name";s:18:"userAssignRole.xml";s:7:"basedir";s:0:"";s:5:"width";s:3:"400";}s:8:"children";a:14:{i:0;O:8:"xml_node":5:{s:4:"name";s:11:"DYNA_HEADER";s:4:"type";s:4:"open";s:5:"value";s:3:"
  ";s:10:"attributes";a:2:{s:4:"type";s:5:"title";s:5:"group";s:1:"5";}s:8:"children";a:8:{i:0;O:8:"xml_node":5:{s:4:"name";s:2:"es";s:4:"type";s:8:"complete";s:5:"value";s:24:"Asignar Rol a un Usuario";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:1;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:2;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:3;O:8:"xml_node":5:{s:4:"name";s:2:"en";s:4:"type";s:8:"complete";s:5:"value";s:19:"Assign Role to User";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:4;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:5;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:6;O:8:"xml_node":5:{s:4:"name";s:2:"po";s:4:"type";s:8:"complete";s:5:"value";s:24:"Asignar Rol a un usuario";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:7;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}}}i:1;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:2;O:8:"xml_node":5:{s:4:"name";s:8:"USR_NAME";s:4:"type";s:4:"open";s:5:"value";s:59:"
  SELECT UID, USR_USERNAME FROM USERS WHERE UID = @@UID
  ";s:10:"attributes";a:2:{s:4:"type";s:8:"dropdown";s:13:"sqlconnection";s:4:"RBAC";}s:8:"children";a:8:{i:0;O:8:"xml_node":5:{s:4:"name";s:2:"es";s:4:"type";s:8:"complete";s:5:"value";s:8:"Usuario:";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:1;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:2;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:3;O:8:"xml_node":5:{s:4:"name";s:2:"en";s:4:"type";s:8:"complete";s:5:"value";s:5:"User:";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:4;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:5;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:6;O:8:"xml_node":5:{s:4:"name";s:2:"po";s:4:"type";s:8:"complete";s:5:"value";s:8:"Usuario:";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:7;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}}}i:3;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:4;O:8:"xml_node":5:{s:4:"name";s:15:"USR_APPLICATION";s:4:"type";s:4:"open";s:5:"value";s:69:"
  SELECT UID, APP_CODE FROM APPLICATION WHERE UID NOT IN @#INAPPS
  ";s:10:"attributes";a:3:{s:4:"type";s:8:"dropdown";s:15:"dependentfields";s:8:"USR_ROLE";s:13:"sqlconnection";s:4:"RBAC";}s:8:"children";a:8:{i:0;O:8:"xml_node":5:{s:4:"name";s:2:"es";s:4:"type";s:4:"open";s:5:"value";s:12:"Aplicación:";s:10:"attributes";a:0:{}s:8:"children";a:1:{i:0;O:8:"xml_node":5:{s:4:"name";s:6:"option";s:4:"type";s:8:"complete";s:5:"value";s:18:"select application";s:10:"attributes";a:1:{s:4:"name";s:1:"0";}s:8:"children";a:0:{}}}}i:1;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:2;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:3;O:8:"xml_node":5:{s:4:"name";s:2:"en";s:4:"type";s:4:"open";s:5:"value";s:12:"Application:";s:10:"attributes";a:0:{}s:8:"children";a:1:{i:0;O:8:"xml_node":5:{s:4:"name";s:6:"option";s:4:"type";s:8:"complete";s:5:"value";s:18:"select application";s:10:"attributes";a:1:{s:4:"name";s:1:"0";}s:8:"children";a:0:{}}}}i:4;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:5;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:6;O:8:"xml_node":5:{s:4:"name";s:2:"po";s:4:"type";s:4:"open";s:5:"value";s:11:"Aplicacion:";s:10:"attributes";a:0:{}s:8:"children";a:1:{i:0;O:8:"xml_node":5:{s:4:"name";s:6:"option";s:4:"type";s:8:"complete";s:5:"value";s:18:"select application";s:10:"attributes";a:1:{s:4:"name";s:1:"0";}s:8:"children";a:0:{}}}}i:7;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}}}i:5;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:6;O:8:"xml_node":5:{s:4:"name";s:8:"USR_ROLE";s:4:"type";s:4:"open";s:5:"value";s:78:"
  SELECT UID, ROL_CODE FROM ROLE WHERE ROL_APPLICATION = @#USR_APPLICATION
  ";s:10:"attributes";a:2:{s:4:"type";s:8:"dropdown";s:13:"sqlconnection";s:4:"RBAC";}s:8:"children";a:8:{i:0;O:8:"xml_node":5:{s:4:"name";s:2:"es";s:4:"type";s:8:"complete";s:5:"value";s:4:"Rol:";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:1;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:2;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:3;O:8:"xml_node":5:{s:4:"name";s:2:"en";s:4:"type";s:8:"complete";s:5:"value";s:5:"Role:";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:4;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:5;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:6;O:8:"xml_node":5:{s:4:"name";s:2:"po";s:4:"type";s:8:"complete";s:5:"value";s:4:"Rol:";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:7;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}}}i:7;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:8;O:8:"xml_node":5:{s:4:"name";s:7:"BSUBMIT";s:4:"type";s:4:"open";s:5:"value";s:3:"
  ";s:10:"attributes";a:1:{s:4:"type";s:6:"submit";}s:8:"children";a:8:{i:0;O:8:"xml_node":5:{s:4:"name";s:2:"es";s:4:"type";s:8:"complete";s:5:"value";s:9:"Continuar";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:1;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:2;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:3;O:8:"xml_node":5:{s:4:"name";s:2:"en";s:4:"type";s:8:"complete";s:5:"value";s:8:"Continue";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:4;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:5;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:2:"  ";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:6;O:8:"xml_node":5:{s:4:"name";s:2:"po";s:4:"type";s:8:"complete";s:5:"value";s:9:"Continuar";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:7;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}}}i:9;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:10;O:8:"xml_node":5:{s:4:"name";s:3:"UID";s:4:"type";s:8:"complete";s:5:"value";s:1:"
";s:10:"attributes";a:1:{s:4:"type";s:11:"phpvariable";}s:8:"children";a:0:{}}i:11;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}i:12;O:8:"xml_node":5:{s:4:"name";s:6:"INAPPS";s:4:"type";s:8:"complete";s:5:"value";s:1:"
";s:10:"attributes";a:1:{s:4:"type";s:11:"phpvariable";}s:8:"children";a:0:{}}i:13;O:8:"xml_node":5:{s:4:"name";s:0:"";s:4:"type";s:5:"cdata";s:5:"value";s:1:"
";s:10:"attributes";a:0:{}s:8:"children";a:0:{}}}}' );
$this->id = unserialize( 's:46:"cGNXUnoxwrBvcDVYaXRhVGczcHlueEtEWm1KR28yWnc___";' );
$this->name = unserialize( 's:18:"userAssignRole.xml";' );
$this->fields = unserialize( 'a:7:{s:11:"DYNA_HEADER";O:19:"xmlform_field_title":8:{s:4:"name";s:11:"DYNA_HEADER";s:4:"type";s:5:"title";s:5:"label";s:24:"Asignar Rol a un Usuario";s:5:"owner";N;s:5:"group";s:1:"5";s:4:"mode";s:4:"edit";s:3:"sql";s:0:"";s:7:"options";a:0:{}}s:8:"USR_NAME";O:22:"xmlform_field_dropdown":15:{s:4:"name";s:8:"USR_NAME";s:4:"type";s:8:"dropdown";s:5:"label";s:8:"Usuario:";s:5:"owner";N;s:5:"group";i:0;s:4:"mode";s:4:"edit";s:12:"defaultValue";s:0:"";s:8:"required";b:0;s:15:"dependentFields";s:0:"";s:8:"readonly";b:0;s:6:"option";a:0:{}s:13:"sqlConnection";s:4:"RBAC";s:3:"sql";s:56:"SELECT UID, USR_USERNAME FROM USERS WHERE UID = @@UID
  ";s:9:"sqlOption";a:0:{}s:7:"options";a:0:{}}s:15:"USR_APPLICATION";O:22:"xmlform_field_dropdown":15:{s:4:"name";s:15:"USR_APPLICATION";s:4:"type";s:8:"dropdown";s:5:"label";s:12:"Aplicación:";s:5:"owner";N;s:5:"group";i:0;s:4:"mode";s:4:"edit";s:12:"defaultValue";s:0:"";s:8:"required";b:0;s:15:"dependentFields";s:8:"USR_ROLE";s:8:"readonly";b:0;s:6:"option";a:1:{i:0;s:18:"select application";}s:13:"sqlConnection";s:4:"RBAC";s:3:"sql";s:66:"SELECT UID, APP_CODE FROM APPLICATION WHERE UID NOT IN @#INAPPS
  ";s:9:"sqlOption";a:0:{}s:7:"options";a:1:{i:0;s:18:"select application";}}s:8:"USR_ROLE";O:22:"xmlform_field_dropdown":15:{s:4:"name";s:8:"USR_ROLE";s:4:"type";s:8:"dropdown";s:5:"label";s:4:"Rol:";s:5:"owner";N;s:5:"group";i:0;s:4:"mode";s:4:"edit";s:12:"defaultValue";s:0:"";s:8:"required";b:0;s:15:"dependentFields";s:0:"";s:8:"readonly";b:0;s:6:"option";a:0:{}s:13:"sqlConnection";s:4:"RBAC";s:3:"sql";s:75:"SELECT UID, ROL_CODE FROM ROLE WHERE ROL_APPLICATION = @#USR_APPLICATION
  ";s:9:"sqlOption";a:0:{}s:7:"options";a:0:{}}s:7:"BSUBMIT";O:20:"xmlform_field_submit":8:{s:4:"name";s:7:"BSUBMIT";s:4:"type";s:6:"submit";s:5:"label";s:9:"Continuar";s:5:"owner";N;s:5:"group";i:0;s:4:"mode";s:4:"edit";s:3:"sql";s:0:"";s:7:"options";a:0:{}}s:3:"UID";O:13:"xmlform_field":8:{s:4:"name";s:3:"UID";s:4:"type";s:11:"phpvariable";s:5:"label";N;s:5:"owner";N;s:5:"group";i:0;s:4:"mode";s:4:"edit";s:3:"sql";s:0:"";s:7:"options";a:0:{}}s:6:"INAPPS";O:13:"xmlform_field":8:{s:4:"name";s:6:"INAPPS";s:4:"type";s:11:"phpvariable";s:5:"label";N;s:5:"owner";N;s:5:"group";i:0;s:4:"mode";s:4:"edit";s:3:"sql";s:0:"";s:7:"options";a:0:{}}}' );
$this->width = unserialize( 's:3:"400";' );
$this->height = unserialize( 'i:600;' );
$this->title = unserialize( 's:0:"";' );
$this->home = unserialize( 's:51:"/home/santos/newgulliver/trunk/rbac/engine/xmlform/";' );
$this->parsedFile = unserialize( 's:73:"/home/santos/newgulliver/trunk/rbac/engine/xmlform/rbac/userAssignRole.es";' );
$this->type = unserialize( 's:7:"xmlform";' );
$this->fileName = unserialize( 's:74:"/home/santos/newgulliver/trunk/rbac/engine/xmlform/rbac/userAssignRole.xml";' );
$this->scriptFile = unserialize( 's:78:"/home/santos/newgulliver/trunk/rbac/public_html/xmlform/rbac/userAssignRole.js";' );
$this->scriptURL = unserialize( 's:31:"/xmlform/rbac/userAssignRole.js";' );
$this->sql = unserialize( 'N;' );
$this->sqlConnection = unserialize( 'N;' );
$this->values = unserialize( 'N;' );
$this->action = unserialize( 's:0:"";' );
$this->ajaxServer = unserialize( 's:0:"";' );
$this->enableTemplate = unserialize( 'b:0;' );
?>