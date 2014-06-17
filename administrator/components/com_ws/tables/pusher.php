<?php
/**
 * @package todo
 * @copyright Copyright (c)2012 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class WsTablePusher extends FOFTable
{
    public function __construct($table, $key, &$db, $config = array()) {
        parent::__construct('#__ws_items', 'ws_item_id', $db, $config);
    }
}