<?php
/*
 *  webpa-lti - WebPA module to add LTI support
 *  Copyright (C) 2019  Stephen P Vickers
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 *  Contact: stephen@spvsoftwareproducts.com
 */

###
###  Update WebPA menu when accessing an LTI module
###

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\ToolConsumer;
use ceLTIc\LTI\ResourceLink;

require_once('vendor/autoload.php');
require_once('setting.php');

global $DB, $dataconnector, $consumer, $user_resource_link;

#
### Check if this is an LTI connection
#
$DB->open();
$dataconnector = DataConnector::getDataConnector($DB->getConnection(), APP__DB_TABLE_PREFIX);
if ($_source_id) {
    $consumer = new ToolConsumer($_SESSION['_user_source_id'], $dataconnector);
    $user_resource_link = ResourceLink::fromConsumer($consumer, $_SESSION['_user_context_id']);
    if ($this->_user->is_staff() && $_source_id) {
#
### Update upload option if Memberships service is available
#
        $menu = $this->get_menu('Admin');
        if ($user_resource_link->hasMembershipsService()) {
            $menu['sync data'] = APP__WWW . "/mod/$mod/admin/manage/";
        }
        unset($menu['upload data']);
#
### Add upload option if Outcomes service is available
#
        if ($user_resource_link->hasOutcomesService()) {
            $menu['transfer grades'] = APP__WWW . "/mod/$mod/admin/grade/";
        }
#
### Add sharing option if enabled
#
        if (ALLOW_SHARING && ($_source_id == $_SESSION['_user_source_id'])) {
            $menu['sharing'] = APP__WWW . "/mod/$mod/admin/share/";
        }
        $this->set_menu('Admin', $menu);
    }
}

#
### Add sources menu for administrators
#
if ($this->_user->is_admin()) {
    $this->set_menu('LTI Admin',
        array('lti sources' => APP__WWW . "/mod/$mod/admin/source/",
            'change source' => APP__WWW . "/mod/$mod/admin/source.php"));
} else {
#
### Add message to logout option
#
    $menu = $this->get_menu(' ');
    if (isset($_SESSION['logout_url'])) {
        $text = 'return to VLE';
        if (isset($_SESSION['branding_return_menu_text'])) {
            $text = $_SESSION['branding_return_menu_text'];
        }
        $menu[$text] = APP__WWW . '/logout.php';
        unset($menu['logout']);
    } else {
        $menu['logout'] = APP__WWW . '/logout.php?lti_msg=' . urlencode('You have been logged out of ' . APP__NAME);
    }
    $this->set_menu(' ', $menu);
}
?>