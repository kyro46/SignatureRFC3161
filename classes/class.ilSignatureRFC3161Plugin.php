<?php

require_once 'Modules/Test/classes/class.ilTestSignaturePlugin.php';

/**
 * @author  Yves Annanias <yves.annanias@llz.uni-halle.de>
 */
class ilSignatureRFC3161Plugin extends ilTestSignaturePlugin
{
	/**
	 * Get Plugin Name. Must be same as in class name il<Name>Plugin
	 * and must correspond to plugins subdirectory name.	 
	 * @return    string    Plugin Name
	 */
	public function getPluginName()
	{
		return 'SignatureRFC3161';
	}

	/**    
	 * Passes the control to the plugin.	 	 
	 * @param string|null $cmd Optional command for the plugin
	 */
	public function invoke($cmd = null)
	{
		switch ($cmd)
		{
			case 'process_success':
				$this->handleProcessSuccessfulRequest();				
				break;

			case 'process_error':
				$this->handleProcessErrorRequest();				
				break;

			case 'invokeSignaturePlugin':
			default:
				$this->renderPlugin();
		}
	}

	/**
	 * Renders the plugin to the screen. 
	 */
	protected function renderPlugin()
	{
		global $ilUser, $ilDB;		
		// 1. pdf erzeugen
		$filename = $this->generatePDF();
		// 2. pdf signieren
		$basePfad = './Customizing/global/plugins/Modules/Test/Signature/SignatureRFC3161/resources/';
		$refId = $_GET['ref_id'];
		$fullName = $ilUser->getLogin();
		
		// get config from DB
		$result = $ilDB->query("SELECT * FROM tst_tsig_rfc3161_keys WHERE id=0");		
		$record = $ilDB->fetchAssoc($result);		
		
		$input = 'java '.$record['jvm'].' -jar '.$basePfad.'signPdf.jar'.' '.$filename.'  '.$record['key_alias'].' '
			.$record['certificate_chain_alias'].' '.$record['keystore_password'].' '.$record['private_key_password'].' '.$basePfad.$record['keystore_file'].' '.$record['tsa'].' 2>&1';
		$errorString =shell_exec($input);			 
		$len = strlen($errorString);				
		if ($len > 0 )
		{
			/*
			// TODO Besser darstellen, ueberhaupt notwendig?
			$template = new ilTemplate('Customizing/global/plugins/Modules/Test/Signature/SignatureRFC3161/templates/default/error_main.html', true, true, false, array(), true);		
			$template->setVariable('INPUT', "Testdaten wurden gespeichert. Pdf wurde erstellt. Signierung konnte nicht durchgeführt werden.");
			$template->setVariable('ERROR', $errorString);
			$template->parseCurrentBlock();
			$this->populatePluginCanvas( $template->get() );
			$this->showPluginCanvas();					 
			*/
			$this->handleProcessErrorRequest();			
		} else
		{	
							
			$signFileName = $this->handleFile($filename);
			$this->archivingFile($signFileName);
			
			$this->handleProcessSuccessfulRequest();						
		}		
	}

	protected function generatePdf()
	{
		global $ilUser; 
		$active = $this->getGUIObject()->getTest()->getActiveIdOfUser($ilUser->getId());
		require_once './Modules/Test/classes/class.ilTestEvaluationGUI.php';
		$testevaluationgui = new ilTestEvaluationGUI($this->getGUIObject()->getTest());
		// class.ilObjTest.php
		$pass = $this->getGUIObject()->getTest()->_getMaxPass($active);
		$results = $this->getGUIObject()->getTest()->getTestResult($active, $pass, true);		
		// folgendes findet in der Modules/Test/classes/class.ilTestServiceGUI.php statt:
		// getPassListOfAnswers($results, $active, $pass, $show_solutions = FALSE, $only_answered_questions = FALSE, $show_question_only = FALSE, 
		//						$show_reached_points = FALSE, $compare_solutions = FALSE)
		$results_output = $testevaluationgui->getPassListOfAnswers($results, $active, $pass, false, false, false, true, true);
		
		$base = ilUtil::getDataDir().'/temp/';
		if ( !is_dir($base) ) {
			ilUtil::makeDirParents($base);
		}		
		$userName = $ilUser->getLogin();
		$currDate = date("YmdHis");
		
		$filename = $base.$userName.$currDate.'.pdf';
		require_once './Modules/Test/classes/class.ilTestPDFGenerator.php';		
		ilTestPDFGenerator::generatePDF($results_output, 'F', $filename); // 'F' -> File,  write pdf to local file $filename
		
		return $filename;
	}
	
	protected function handleFile($orgFile)
	{
		global $ilUser;
		$refId = $_GET['ref_id'];
		$dir = ilUtil::getDataDir()."/files/".$refId.'/';  
		if ( !is_dir($dir) ) {
			ilUtil::makeDirParents($dir);
		}
		$signFile = $dir.$ilUser->getLogin().'SIGN.pdf';
		rename(str_replace('.pdf', 'SIGN.pdf', $orgFile), $signFile);
		return $signFile;
	}
	
	protected function archivingFile($filename)	
	{		
		global $ilUser;
		$active = $this->getGUIObject()->getTest()->getActiveIdOfUser($ilUser->getId());
		$pass = $this->getGUIObject()->getTest()->_getMaxPass($active);		
		$this->handInFileForArchiving($active, $pass, $ilUser->getLogin().'SIGN.pdf', $filename);
		
	}

	protected function handleProcessSuccessfulRequest()
	{				
		/*
		global $ilCtrl, $ilUser;
		$active = $this->getGUIObject()->getTest()->getActiveIdOfUser($ilUser->getId());
		$pass = $this->getGUIObject()->getTest()->_getMaxPass($active);		
		
		$key = 'signed_'. $active .'_'. $pass;		
		ilSession::set($key, true);
		$ilCtrl->redirectByClass("ilTestOutputGUI", "outUserResultsOverview");
		*/
		$this->getGUIObject()->redirectToTest(true); 
	}

	protected function handleProcessErrorRequest()
	{
		$this->getGUIObject()->redirectToTest(false); 
	}
}
