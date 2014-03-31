<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class FOFFormFieldFrontendlink extends FOFFormFieldText
{
    /**
     * Get the rendering of this field type for a repeatable (grid) display,
     * e.g. in a view listing many item (typically a "browse" task).
     * We're going to check if the current user can edit an item or not, creating the correct link
     *
     * @since 2.0
     *
     * @return  string  The field HTML
     */
    public function getRepeatable()
    {
        $userid     = JFactory::getUser()->id;
        $itemid     = $this->item->todo_item_id;
        $created_by = $this->item->created_by;

        // If I can edit, or I am editing a record of mine (and I can do it) or I have
        // special permissions on that record, display the edit link
        if(
            (JAccess::check($userid, 'core.edit', 'COM_WS'))                                              ||
            ($created_by == $userid && JAccess::check($userid, 'core.edit.own', 'COM_WS'))                ||
            ($created_by == $userid && JAccess::check($userid, 'core.edit.own', 'COM_WS.item.'.$itemid))  ||
            (JAccess::check($userid, 'core.edit', 'COM_WS.item.'.$itemid))
        )
        {
            $url = 'index.php?option=COM_WS&view=item&task=edit&id=[ITEM:ID]';
        }
        // otherwise display the read one
        else
        {
            $url = 'index.php?option=COM_WS&view=item&id=[ITEM:ID]';
        }

        $this->element['url'] = $url;

        return parent::getRepeatable();
    }
}