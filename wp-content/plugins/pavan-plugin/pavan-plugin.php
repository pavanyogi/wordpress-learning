<?php
//https://www.youtube.com/watch?v=Z7QfH-s-15s

/**
 * @package PavanPlugin
 */

/*
Plugin Name: Pavan Plugin
Plugin URI: http://example.com
Description: This is my first attempt on writing a custom Plugin for this amazing tutorial series.
version: 1.0.0
Author: Pavan Yogi
Author URI: http://example.com
Liscense: GPLv2 or later
Text Domain: pavan-plugin
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/

if(! defined('ABSPATH')){
    die(__FILE__.' - '.__LINE__);
}

class PavanPlugin
{
    function activate()
    {
        echo 'The plugin was activated';
    }

    function deactivate()
    {

    }

    function uninstall()
    {

    }
}

if(class_exists('PavanPlugin'))
{
    $pavanPlugin = new PavanPlugin();
}

// activation
register_activation_hook(__FILE__, array($pavanPlugin, 'activate'));
// deactivation
register_activation_hook(__FILE__, array($pavanPlugin, 'deactivate'));
//uninstall



