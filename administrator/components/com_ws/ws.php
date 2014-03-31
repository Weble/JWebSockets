<?php
/*
 * @package todo
 * @copyright Copyright (c)2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

// Load FOF
include_once JPATH_LIBRARIES.'/fof/include.php';
if(!defined('FOF_INCLUDED')) {
	JError::raiseError ('500', 'FOF is not installed');
	
	return;
}

FOFDispatcher::getTmpInstance('com_ws')->dispatch();
