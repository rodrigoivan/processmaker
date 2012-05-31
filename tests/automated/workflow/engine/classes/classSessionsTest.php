<?php 
  require_once PATH_TRUNK . 'gulliver/thirdparty/smarty/libs/Smarty.class.php'; 
  require_once PATH_TRUNK . 'gulliver/system/class.xmlform.php'; 
  require_once PATH_TRUNK . 'gulliver/system/class.xmlDocument.php'; 
  require_once PATH_TRUNK . 'gulliver/thirdparty/propel/Propel.php' ;
  require_once PATH_TRUNK . 'gulliver/thirdparty/creole/Creole.php' ;
  require_once PATH_TRUNK . 'workflow/engine/classes/class.sessions.php'; 

  /** 
   * Generated by ProcessMaker Test Unit Generator on 2012-05-10 at 20:56:18.
  */ 

  class classSessionsTest extends PHPUnit_Framework_TestCase 
  { 
    /**
    * @covers Sessions::__construct
    * @todo   Implement test__construct().
    */
    public function test__construct() 
    { 
        if (class_exists('Sessions')) {
             $methods = get_class_methods( 'Sessions');
            $this->assertTrue( in_array( '__construct', $methods ), 'seems like this function is outside this class' ); 
        } 
    } 

    /**
    * @covers Sessions::getSessionUser
    * @todo   Implement testgetSessionUser().
    */
    public function testgetSessionUser() 
    { 
        if (class_exists('Sessions')) {
             $methods = get_class_methods( 'Sessions');
            $this->assertTrue( in_array( 'getSessionUser', $methods ), 'seems like this function is outside this class' ); 
        } 
    } 

    /**
    * @covers Sessions::verifySession
    * @todo   Implement testverifySession().
    */
    public function testverifySession() 
    { 
        if (class_exists('Sessions')) {
             $methods = get_class_methods( 'Sessions');
            $this->assertTrue( in_array( 'verifySession', $methods ), 'seems like this function is outside this class' ); 
        } 
    } 

    /**
    * @covers Sessions::registerGlobal
    * @todo   Implement testregisterGlobal().
    */
    public function testregisterGlobal() 
    { 
        if (class_exists('Sessions')) {
             $methods = get_class_methods( 'Sessions');
            $this->assertTrue( in_array( 'registerGlobal', $methods ), 'seems like this function is outside this class' ); 
        } 
    } 

    /**
    * @covers Sessions::getGlobal
    * @todo   Implement testgetGlobal().
    */
    public function testgetGlobal() 
    { 
        if (class_exists('Sessions')) {
             $methods = get_class_methods( 'Sessions');
            $this->assertTrue( in_array( 'getGlobal', $methods ), 'seems like this function is outside this class' ); 
        } 
    } 

    /**
    * @covers Sessions::getGlobals
    * @todo   Implement testgetGlobals().
    */
    public function testgetGlobals() 
    { 
        if (class_exists('Sessions')) {
             $methods = get_class_methods( 'Sessions');
            $this->assertTrue( in_array( 'getGlobals', $methods ), 'seems like this function is outside this class' ); 
        } 
    } 

    /**
    * @covers Sessions::deleteTmpfile
    * @todo   Implement testdeleteTmpfile().
    */
    public function testdeleteTmpfile() 
    { 
        if (class_exists('Sessions')) {
             $methods = get_class_methods( 'Sessions');
            $this->assertTrue( in_array( 'deleteTmpfile', $methods ), 'seems like this function is outside this class' ); 
        } 
    } 

  } 