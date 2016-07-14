<#1>
<?php
$fields = array(
        'id' 						=> array('type' => 'integer', 'length' => 4, 'notnull' => true ),
        'key_alias' 				=> array('type' => 'text', 'length' => 100, 'fixed' => false, 'notnull' => true ),
        'certificate_chain_alias' 	=> array('type' => 'text', 'length' => 100, 'fixed' => false, 'notnull' => true ),
        'keystore_password' 		=> array('type' => 'text', 'length' => 100, 'fixed' => false, 'notnull' => true ),
        'private_key_password' 		=> array('type' => 'text', 'length' => 100, 'fixed' => false, 'notnull' => true ),
        'keystore_file'		 		=> array('type' => 'text', 'length' => 100, 'fixed' => false, 'notnull' => true )
);
$ilDB->createTable("tst_tsig_rfc3161_keys", $fields);
$ilDB->addPrimaryKey("tst_tsig_rfc3161_keys", array("id"));
$ilDB->manipulate('INSERT INTO tst_tsig_rfc3161_keys (id, key_alias, certificate_chain_alias, keystore_password, private_key_password, keystore_file) VALUES (1,"","","","","")');
?>
<#2>
<?php
	//Add TSA definded by admin
    if(!$ilDB->tableColumnExists('tst_tsig_rfc3161_keys', 'tsa'))
    {
        $ilDB->addTableColumn('tst_tsig_rfc3161_keys', 'tsa', array(
                'type' => 'text',
                'length' => 200,
                'notnull' => true,
        		'default' => "http://zeitstempel.dfn.de/"
            )
        );
    }
?>
<#3>
<?php
	//Add parameter for JVM - proxy etc.
    if(!$ilDB->tableColumnExists('tst_tsig_rfc3161_keys', 'jvm'))
    {
        $ilDB->addTableColumn('tst_tsig_rfc3161_keys', 'jvm', array(
                'type' => 'text',
                'length' => 200,
                'notnull' => false,
            )
        );
    }
?>