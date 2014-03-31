<?php
/**
 * @package todo
 * @copyright Copyright (c)2012 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class TodoTableItem extends FOFTable
{
    public function getAssetParentId($table = null, $id = null)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
                    ->select($db->qn('id'))
                    ->from($db->qn('#__assets'))
                    ->where($db->qn('name').' = '.$db->q('COM_WS'))
                    ->where($db->qn('parent_id').' = 1')
                    ->where($db->qn('level').' = 1');
        $parent_id = $db->setQuery($query)->loadResult();

        if(!$parent_id)
        {
            return parent::getAssetParentId($table, $id);
        }
        else
        {
            return $parent_id;
        }
    }
}