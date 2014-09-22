<?php
 
include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
 * Plugin configuration class
 * @author  Yves Annanias 
 */
class ilSignatureRFC3161ConfigGUI extends ilPluginConfigGUI
{
        /**
        * Handles all commmands, 
        * $cmd = functionName()
        */
        function performCommand($cmd)
        { 
			switch ($cmd)
			{
			case "configure":
			case "save":
			case "check":
				$this->$cmd();
				break;
			}
        }
     
     /**
      * Check-Action
      * sign a test file to check input.
      */
     function check()
     {
		global $ilDB, $tpl, $ilCtrl;
		
		$result = $ilDB->query("SELECT * FROM tst_tsig_rfc3161_keys WHERE id=0");		
		$record = $ilDB->fetchAssoc($result);	
		$basePfad = './Customizing/global/plugins/Modules/Test/Signature/SignatureRFC3161/resources/';
		$filename = './Customizing/global/plugins/Modules/Test/Signature/SignatureRFC3161/resources/test.pdf';
		
		$input = 'java -jar "'.$basePfad."signPdf.jar".'" "'.$filename.'"  "'.$record['key_alias'].'" "'
			.$record['certificate_chain_alias'].'" "'.$record['keystore_password'].'" "'.$record['private_key_password'].'" "'.$basePfad.$record['keystore_file'].'" 2>&1';
		$errorString =shell_exec($input);			 
		$len = strlen($errorString);				
		if ($len > 0 )
		{	
			$template = new ilTemplate('error_main.html', true, true, "Customizing/global/plugins/Modules/Test/Signature/SignatureRFC3161");		
			$template->setCurrentBlock("errText");
			//$template->setVariable('INPUT', $input);
			$template->setVariable('ERROR', $errorString);
			$template->parseCurrentBlock();
			$form = $this->initConfigurationForm();
			$tpl->setContent($template->get().$form->getHTML());			
			ilUtil::sendFailure("Das Signieren ist fehlgeschalgen, bitte überprüfen Sie die Eingaben.", true);		
		} 
		else
		{
			ilUtil::sendSuccess("Eingaben erfolgreich getestet.", true);
			$this->configure();
		}
	 }
 
	/**
	 * Save-Action
	 * update values in DB,
	 * upload keystore
	 */ 
	function save()
	{
		global $ilCtrl, $tpl, $ilDB;
		$form = $this->initConfigurationForm();
		// input ok? length<=max, not null, ...
		if ($form->checkInput())
		{			
			// get Values
			$keyAlias = $form->getInput("keyAliasValue");
			$certificateChainAlias = $form->getInput("certificateChainAliasValue");
			$keystorePassword = $form->getInput("keystorePasswordValue");
			$privateKeyPassword = $form->getInput("privateKeyPasswordValue");
			// upload file
			$fileinfo = pathinfo($_FILES["keystore"]["name"]);
			$extract_file = "./Customizing/global/plugins/Modules/Test/Signature/SignatureRFC3161/resources/".$fileinfo["basename"];
			ilUtil::sendSuccess($_FILES["keystore"]["name"], true);
			copy($_FILES["keystore"]["tmp_name"], $extract_file);
			// store values		
			$ilDB->manipulate("DELETE FROM tst_tsig_rfc3161_keys");			
			$ilDB->insert("tst_tsig_rfc3161_keys", 
					array(					
					"key_alias" 				=>	array("text", $keyAlias),
					"certificate_chain_alias" 	=>  array("text", $certificateChainAlias),
					"keystore_password" 		=>  array("text", $keystorePassword),
					"private_key_password" 		=>  array("text", $privateKeyPassword),
					"keystore_file"				=>	array("text", $fileinfo["basename"])
					)/*,
					array(
						"id" =>        array("integer", 1)						
					)	*/
			);					
			// message and redirect
			//ilUtil::sendSuccess("Saving Successful", true);
			$ilCtrl->redirect($this, "configure");			
		} else
		{
			// input not ok, then
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}
 
	/**
	 * Configure screen
	 */
	function configure()
	{		
		global $tpl;
		$form = $this->initConfigurationForm();
		$tpl->setContent($form->getHTML());
		
	}
        
    /**
	 * Init configuration form.
	 *
	 * @return object form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl, $ilDB;			
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		// get saved values from DB		
		$result = $ilDB->query("SELECT * FROM tst_tsig_rfc3161_keys WHERE id=0");		
		$record = $ilDB->fetchAssoc($result);		
		// 								String -> Gui, access-name
		$keyAlias = new ilTextInputGUI("keyAlias", "keyAliasValue");
		//$keyAlias->setInfo('Info:');
		$keyAlias->setRequired(true);
		$keyAlias->setMaxLength(100);
		$keyAlias->setSize(60);
		$keyAlias->setValue($record['key_alias']);
		$form->addItem($keyAlias);		
		
		$certificateChainAlias = new ilTextInputGUI("CertificateChainAlias", "certificateChainAliasValue");
		$certificateChainAlias->setRequired(true);
		$certificateChainAlias->setMaxLength(100);
		$certificateChainAlias->setSize(60);
		$certificateChainAlias->setValue($record['certificate_chain_alias']);
		$form->addItem($certificateChainAlias);		
		
		$keystorePassword = new ilPasswordInputGUI("KeystorePassword", "keystorePasswordValue");
		$keystorePassword->setRetype(false);
		$keystorePassword->setSkipSyntaxCheck(true);
		$keystorePassword->setRequired(true);
		$keystorePassword->setMaxLength(100);
		$keystorePassword->setSize(60);
		$keystorePassword->setValue($record['keystore_password']);
		$form->addItem($keystorePassword);		
		
		$privateKeyPassword = new ilPasswordInputGUI("PrivateKeyPassword", "privateKeyPasswordValue");
		$privateKeyPassword->setRetype(false);
		$privateKeyPassword->setSkipSyntaxCheck(true);
		$privateKeyPassword->setRequired(true);
		$privateKeyPassword->setMaxLength(100);
		$privateKeyPassword->setSize(60);
		$privateKeyPassword->setValue($record['private_key_password']);
		$form->addItem($privateKeyPassword);		
		
		$fi = new ilFileInputGUI("file", "keystore");
		$fi->setRequired(true);
		$fi->setSuffixes(array("keystore"));
		$fi->setSize(30);
		$fi->setInfo("Current Keystore-File: ".$record['keystore_file']); 
		$form->addItem($fi);
		
		if ($ilDB->numRows($result) > 0)
			$form->addCommandButton("check", "check");
		$form->addCommandButton("save", "save");
	                
		$form->setTitle("Signature Plugin Configuration");
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		return $form;
	}
 
}
?>
