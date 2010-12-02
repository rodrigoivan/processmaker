<?xml version="1.0" encoding="utf-8"?>
<!--Autogenerated by CreoleToXMLSchema!-->
<database name="workflow">
  <table name="PLUGIN_DEMO">
    <vendor type="mysql">
      <parameter name="Name" value="PLUGIN_DEMO"/>
      <parameter name="Engine" value="MyISAM"/>
      <parameter name="Version" value="10"/>
      <parameter name="Row_format" value="Dynamic"/>
      <parameter name="Auto_increment" value=""/>
      <parameter name="Collation" value="utf8_general_ci"/>
      <parameter name="Create_options" value=""/>
      <parameter name="Comment" value="The plugin demo table for {className}"/>
    </vendor>
    <column name="PLG_UID" type="VARCHAR" size="32" required="true" primaryKey="true" default="">
      <vendor type="mysql">
        <parameter name="Field" value="PLG_UID"/>
        <parameter name="Type" value="varchar(32)"/>
        <parameter name="Null" value="NO"/>
        <parameter name="Key" value="PRI"/>
        <parameter name="Default" value=""/>
        <parameter name="Extra" value=""/>
      </vendor>
    </column>
    <column name="PLG_PARENT_ID" type="INTEGER" required="true" default="0">
      <vendor type="mysql">
        <parameter name="Field" value="PLG_PARENT_ID"/>
        <parameter name="Type" value="int(11)"/>
        <parameter name="Null" value="NO"/>
        <parameter name="Key" value=""/>
        <parameter name="Default" value="0"/>
        <parameter name="Extra" value=""/>
      </vendor>
    </column>
    <column name="PLG_NAME" type="VARCHAR" size="100" required="true" default="">
      <vendor type="mysql">
        <parameter name="Field" value="PLG_FOLDER_NAME"/>
        <parameter name="Type" value="varchar(100)"/>
        <parameter name="Null" value="NO"/>
        <parameter name="Key" value=""/>
        <parameter name="Default" value=""/>
        <parameter name="Extra" value=""/>
      </vendor>
    </column>
    <column name="PLG_FULL_PATH" type="VARCHAR" size="255" required="true" default="">
      <vendor type="mysql">
        <parameter name="Field" value="PLG_FULL_PATH"/>
        <parameter name="Type" value="varchar(255)"/>
        <parameter name="Null" value="NO"/>
        <parameter name="Key" value=""/>
        <parameter name="Default" value=""/>
        <parameter name="Extra" value=""/>
      </vendor>
    </column>
    <index name="indexApp">
      <index-column name="PLG_UID"/>
      <vendor type="mysql">
        <parameter name="Table" value="PLUGU"/>
        <parameter name="Non_unique" value="1"/>
        <parameter name="Key_name" value="indexApp"/>
        <parameter name="Seq_in_index" value="1"/>
        <parameter name="Column_name" value="PLUGIN_DEMO"/>
        <parameter name="Collation" value="A"/>
        <parameter name="Cardinality" value="6"/>
        <parameter name="Sub_part" value=""/>
        <parameter name="Packed" value=""/>
        <parameter name="Null" value=""/>
        <parameter name="Index_type" value="BTREE"/>
        <parameter name="Comment" value=""/>
      </vendor>
    </index>    
  </table>
</database>
