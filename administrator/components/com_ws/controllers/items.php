<?php

class WsControllerItems extends FOFController {
	public function ws(){

		$this->loadAkeeba();

		$dispatcher = JEventDispatcher::getInstance();
		
		$model = FOFModel::getAnInstance('Backups', 'AkeebaModel');

		$model->setState('profile',		$this->input->get('profileid', -10, 'int'));
		$model->setState('ajax',		$this->input->get('ajax', '', 'cmd'));
		$model->setState('description',	$this->input->get('description', '', 'string'));
		$model->setState('comment',		$this->input->get('comment', '','default', 'string', 4));
		$model->setState('jpskey',		$this->input->get('jpskey', '', 'raw', 2));
		$model->setState('angiekey',	$this->input->get('angiekey', '', 'raw', 2));

		// System Restore Point backup state variables
		$model->setState('tag',			$this->input->get('tag', 'backend', 'cmd'));
		$model->setState('type',		strtolower($this->input->get('type', '', 'cmd')));
		$model->setState('name',		strtolower($this->input->get('name', '', 'cmd')));
		$model->setState('group',		strtolower($this->input->get('group', '', 'cmd')));

		if($this->input instanceof FOFInput)
        {
			$model->setState('customdirs',	$this->input->get('customdirs', array(),'array' ,2));
			$model->setState('customfiles',	$this->input->get('customfiles', array(),'array' ,2));
			$model->setState('extraprefixes',$this->input->get('extraprefixes', array(),'array' ,2));
			$model->setState('customtables',$this->input->get('customtables', array(),'array' ,2));
			$model->setState('skiptables',	$this->input->get('skiptables', array(),'array' ,2));
			$model->setState('langfiles',	$this->input->get('langfiles', array(),'array' ,2));
			$model->setState('xmlname',		$this->input->getString('xmlname', ''));
		}
        else
        {
			$model->setState('customdirs',	$this->input->get('customdirs', array(), 'array', 2));
			$model->setState('customfiles',	$this->input->get('customfiles', array(), 'array', 2));
			$model->setState('extraprefixes',$this->input->get('extraprefixes', array(), 'array', 2));
			$model->setState('customtables',$this->input->get('customtables', array(), 'array', 2));
			$model->setState('skiptables',	$this->input->get('skiptables', array(), 'array', 2));
			$model->setState('langfiles',	$this->input->get('langfiles', array(), 'array', 2));
			$model->setState('xmlname',		$this->input->get('xmlname', '', 'string'));
		}

		echo '7';

		try {
			$data = $model->runBackup();
		} catch(Exception $e) {
			echo $e;
		}

		var_dump($data);

		$connections = WsApp::getConnections();
		$activeConnection = WsApp::getActiveConnection();
		
		foreach ($connections as $conn) {
			$conn->send(json_encode($data));
		}
	}

	protected function loadAkeeba() {
		if(!class_exists('AkeebaControllerDefault'))
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_akeeba/controllers/default.php';
		}

		ECHO '1';

		// Merge the language overrides
		$paths = array(JPATH_ROOT, JPATH_ADMINISTRATOR);
		$jlang = JFactory::getLanguage();
		$jlang->load($this->component, $paths[0], 'en-GB', true);
		$jlang->load($this->component, $paths[0], null, true);
		$jlang->load($this->component, $paths[1], 'en-GB', true);
		$jlang->load($this->component, $paths[1], null, true);

		$jlang->load($this->component.'.override', $paths[0], 'en-GB', true);
		$jlang->load($this->component.'.override', $paths[0], null, true);
		$jlang->load($this->component.'.override', $paths[1], 'en-GB', true);
		$jlang->load($this->component.'.override', $paths[1], null, true);

		FOFInflector::addWord('alice', 'alices');

		ECHO '2';

		// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
		if(function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set')) {
			if(function_exists('error_reporting')) {
				$oldLevel = error_reporting(0);
			}
			$serverTimezone = @date_default_timezone_get();
			if(empty($serverTimezone) || !is_string($serverTimezone)) $serverTimezone = 'UTC';
			if(function_exists('error_reporting')) {
				error_reporting($oldLevel);
			}
			@date_default_timezone_set( $serverTimezone);
		}

		$path_akeeba = JPATH_SITE . '/administrator/components/com_akeeba/';

		ECHO '3';
		// Necessary defines for Akeeba Engine
		if(!defined('AKEEBAENGINE')) {
			define('AKEEBAENGINE', 1); // Required for accessing Akeeba Engine's factory class
			define('AKEEBAROOT', $path_akeeba.'/akeeba');
			define('ALICEROOT', $path_akeeba.'/alice');
		}

		// Setup Akeeba's ACLs, honoring laxed permissions in component's parameters, if set
		// Access check, Joomla! 1.6 style.
		/*$user = JFactory::getUser();
		if (!$user->authorise('core.manage', 'com_akeeba')) {
			return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Make sure we have a profile set throughout the component's lifetime
		$session = JFactory::getSession();
		$profile_id = $session->get('profile', null, 'akeeba');
		if(is_null($profile_id))
		{
			// No profile is set in the session; use default profile
			$session->set('profile', 1, 'akeeba');
		}*/

		
		// Load the factory
		require_once $path_akeeba.'/akeeba/factory.php';
		@include_once $path_akeeba.'/alice/factory.php';

		// Load the Akeeba Backup configuration and check user access permission
		$aeconfig = AEFactory::getConfiguration();
		AEPlatform::getInstance()->load_configuration();
		unset($aeconfig);

		// Preload helpers
		require_once $path_akeeba.'/helpers/includes.php';
		require_once $path_akeeba.'/helpers/escape.php';

		// Load the utils helper library
		AEPlatform::getInstance()->load_version_defines();

		// Create a versioning tag for our static files
		$staticFilesVersioningTag = md5(AKEEBA_VERSION.AKEEBA_DATE);
		define('AKEEBAMEDIATAG', $staticFilesVersioningTag);

		// If JSON functions don't exist, load our compatibility layer
		if( (!function_exists('json_encode')) || (!function_exists('json_decode')) )
		{
			require_once $path_akeeba.'/helpers/jsonlib.php';
		}

		JFactory::$application = WsApp::getApplication();

		ECHO '5';
	}
}