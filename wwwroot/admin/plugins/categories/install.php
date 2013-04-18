<?php

    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

    /**
     * Install File
     *
     * This file does the install of the plug-in
     *
     * LICENSE:
     * This program is free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation; either version 2 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License along
     * with this program; if not, write to the Free Software Foundation, Inc.,
     * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
     * http://www.gnu.org/copyleft/gpl.html
     *
     * @package     Geeklog
     * @subpackage  Categories Plug-in
     * @author      Nicholas Dunnaway
     * @copyright   2007 php|uber.leet
     * @license     http://www.gnu.org/copyleft/gpl.html
     * @link        http://uber.leetphp.com
     * @version     CVS: $Id: install.php,v 1.6 2007/04/06 19:15:22 nkd Exp $
     * @since       File available since Release 1.03
     *
     */


    /**
     * This is the main entry point of GeekLog.
     *
     */
    require_once '../../../lib-common.php';

    /**
     * This plug-ins config file.
     *
     */
    require_once $_CONF['path'] . 'plugins/categories/config.php';

    /**
     * This plug-ins function file.
     *  @todo Try renaming this to functions.php
     */
    require_once $_CONF['path'] . 'plugins/categories/functions.inc';


    $pi_name = 'categories';                // Plugin name
    $pi_version = '1.10';                   // Plugin Version also found in admin/index.php
    $gl_version = '1.4.1';                  // GeekLog Version plug-in works on.
    $pi_url = 'http://uber.leetphp.com';    // Plugin Homepage

    // Define the new tables. They will be created later.
    $NEWTABLE = array();
    $NEWTABLE['categories_List'] = "CREATE TABLE `" . $_TABLES['categories_List'] . "` ("
            . "`categories_id` int(4) NOT NULL auto_increment,"
            . "`categories_name` varchar(250) NOT NULL default '',"
            . "`categories_description` varchar(250) NOT NULL default '',"
            . "`categories_imagename` varchar(250) NOT NULL default '',"
            . "`categories_access` char(7) NOT NULL default 'private',"
            . "`categories_isactive` varchar(5) NOT NULL default 'TRUE',"
            . "PRIMARY KEY  (`categories_id`)"
            . ") TYPE=MyISAM";

    $NEWTABLE['categories_LinkToUsers'] = "CREATE TABLE `" . $_TABLES['categories_LinkToUsers'] . "` ("
            . "`link_id` INT(12) NOT NULL AUTO_INCREMENT, "
            . "`categories_id` INT(4) NOT NULL, "
            . "`uid` MEDIUMINT(8) NOT NULL, "
            . "PRIMARY KEY (`link_id`) "
            . ") TYPE=MyISAM";


    // Here we are adding some default data to the database.
    $DEFVALUES = array();
    $DEFVALUES['categories_List'] = 'INSERT INTO `' . $_TABLES['categories_List'] .
               '` ( `categories_name` , `categories_description` , `categories_imagename` , `categories_isactive` ) ' .
               ' VALUES ( \'Unreal\', \'Unreal Version 1.2\', \'ut.gif\', \'TRUE\' );';


    /**
     * I think these are the new user groups to add to geeklog. This controls who
     * can do with with this plug-in.
     *
     * Note from UNPL.
     * Fill in your security features here
     * Note you must add these features to the uninstall routine in function.inc
     * so that they will be removed when the uninstall routine runs. You do not
     * have to use these particular features.  You can edit/add/delete them to
     * fit your plugins security model
     *
     */
    $NEWFEATURE = array();
    $NEWFEATURE['categories.admin'] = "Categories Admin";
    $NEWFEATURE['categories.edit']  = "Categories Editor";


    // Only let Root users access this page.
    if (!SEC_inGroup('Root')) {
        // Someone is trying to illegally access this page
        COM_errorLog("Someone has tried to illegally access the Categories install/uninstall page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR", 1);
        $display  = COM_siteHeader();
        $display .= COM_startBlock($LANG_PL00['access_denied']);
        $display .= $LANG_PL00['access_denied_msg'];
        $display .= COM_endBlock();
        $display .= COM_siteFooter(true);
        echo $display;
        exit;
    }


    /**
     * At this point the user is part of the root user group and is trying to
     * install the plug-in. We are going to let them install it.
     */
    $display = COM_siteHeader(); // Store the geeklog header.

    // Create a new template opject for my plugin.
    $T = new Template($_CONF['path'] . 'plugins/categories/templates');
    $T->set_file('install',         'install.thtml');
    $T->set_var('install_header',   $LANG_PL00['install_header']);
    $T->set_var('img',              $_CONF['site_url'] . '/categories/images/categories.gif');
    $T->set_var('cgiurl',           $_CONF['site_admin_url'] . '/plugins/categories/install.php');
    $T->set_var('admin_url',        $_CONF['site_admin_url'] . '/plugins/categories/index.php');

    // If our Action is install then we install. Else we uninstall.
    if ($_REQUEST['action'] == 'install')
    {
        // If the install works display the success message.
        if (plugin_install_categories())
        {
            $T->set_var('installmsg1', $LANG_PL00['install_success']);
        }
        else // The install failed. Display the failed message.
        {
            $T->set_var('installmsg1', $LANG_PL00['install_failed']);
        }
    }
    else if ($_REQUEST['action'] == "uninstall")
    {
       plugin_uninstall_categories('installed');
       $T->set_var('installmsg1', $LANG_PL00['uninstall_msg']);
    }

    // This is checking the database for our tables. If they are not there
    // Then it is displaying a messages and link to read me docs.
    if (DB_count($_TABLES['plugins'], 'pi_name', 'categories') == 0)
    {
        $T->set_var('installmsg2',  $LANG_PL00['uninstalled']);
        $T->set_var('readme',       $LANG_PL00['readme']);
        $T->set_var('installdoc',   $LANG_PL00['installdoc']);
        $T->set_var('btnmsg',       $LANG_PL00['install']);
        $T->set_var('action',       'install');
    }
    else
    {
        $T->set_var('installmsg2', $LANG_PL00['installed']);
        $T->set_var('btnmsg', $LANG_PL00['uninstall']);
        $T->set_var('action','uninstall');
    }

    $T->parse('output', 'install'); // I have no idea what this is doing.
    $display .= $T->finish($T->get_var('output'));
    $display .= COM_siteFooter(true);

    echo $display; // Print the HTML to the page.


    /**
     * Puts the datastructures for this plugin into the Geeklog database
     * Note: Corresponding uninstall routine is in functions.inc
     *
     * @return boolean True if successful False otherwise
     * @todo Move this function to another file. We include function.inc
     *       at the start why not move it there?
     */
    function plugin_install_categories()
    {
        // This is pulling global vars. Oooo BAD! We call this function ourself
        // why not pass all the data to it. It would trivial to so and would
        // be better coding practice.
        global $pi_name, $pi_version, $gl_version, $pi_url, $NEWTABLE, $DEFVALUES, $NEWFEATURE;
        global $_TABLES;

        COM_errorLog("Attempting to install the $pi_name Plugin", 1);

        // Create the Plugins Tables
        foreach ($NEWTABLE as $table => $sql)
        {
            COM_errorLog("Creating $table table", 1); // Logging
            DB_query($sql, 1);

            if (DB_error())
            {
                COM_errorLog("Error Creating $table table", 1); // Logging
                plugin_uninstall_categories();                  // Uninstall
                return false;                                   // We failed.
            }
            COM_errorLog("Success - Created $table table", 1); // Logging
        }

        // Create UNIQUE Index on Link Table.
        $gstrMySQLIndex = 'ALTER TABLE `' . $_TABLES['categories_LinkToUsers'] . '`
                           ADD UNIQUE ( `categories_id` , `uid` ) ';

        COM_errorLog('Creating UNIQUE index on ' . $_TABLES['categories_LinkToUsers'] . ' table.', 1); // Logging

        DB_query($gstrMySQLIndex, 1);
        if (DB_error())
        {
            COM_errorLog('Error Creating UNIQUE index on ' . $_TABLES['categories_LinkToUsers'] . ' table.', 1); // Logging
            plugin_uninstall_categories();  // Uninstall
            return false;                   // We failed.
        }
        COM_errorLog('Success - Created UNIQUE index on ' . $_TABLES['categories_LinkToUsers'] . ' table.', 1); // Logging

        // Insert Default Data
        foreach ($DEFVALUES as $table => $sql)
        {
            COM_errorLog("Inserting default data into $table table", 1); // Logging

            DB_query($sql, 1);
            if (DB_error())
            {
                COM_errorLog("Error inserting default data into $table table", 1); // Logging
                plugin_uninstall_categories();  // Uninstall
                return false;                   // We failed.
            }
            COM_errorLog("Success - inserting data into $table table", 1); // Logging
        }

        // 1.) Create the plugin admin security group
        // @todo (This needs to be cleaned up)
        COM_errorLog("Attempting to create $pi_name admin group", 1); // Logging
        DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr) "
            . "VALUES ('" . ucfirst($pi_name) . " Admin', 'Users in this group can administer the $pi_name plugin')", 1);
        if (DB_error()) {
            plugin_uninstall_categories();
            return false;
        }
        COM_errorLog('...success',1);
        $group_id = DB_insertId();

        // 1.) Save the grp id for later uninstall
        COM_errorLog('About to save group_id to vars table for use during uninstall',1);
        DB_query("INSERT INTO {$_TABLES['vars']} VALUES ('{$pi_name}_gid', $group_id)",1);
        if (DB_error()) {
            plugin_uninstall_categories();
            return false;
        }
        COM_errorLog('...success',1);

        // 1.) Add plugin Features
        foreach ($NEWFEATURE as $feature => $desc) {
            COM_errorLog("Adding $feature feature",1);
            DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) "
                . "VALUES ('$feature','$desc')",1);
            if (DB_error()) {
                COM_errorLog("Failure adding $feature feature",1);
                plugin_uninstall_categories();
                return false;
            }
            $feat_id = DB_insertId();
            COM_errorLog("Success",1);
            COM_errorLog("Adding $feature feature to admin group",1);
            DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, $group_id)");
            if (DB_error()) {
                COM_errorLog("Failure adding $feature feature to admin group",1);
                plugin_uninstall_categories();
                return false;
            }
            COM_errorLog("Success",1);
        }

        // OK, now give Root users access to this plugin now! NOTE: Root group should always be 1
        COM_errorLog("Attempting to give all users in Root group access to $pi_name admin group",1);
        DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES ($group_id, NULL, 1)");
        if (DB_error()) {
            plugin_uninstall_categories();
            return false;
        }

        // Register the plugin with Geeklog
        COM_errorLog("Registering $pi_name plugin with Geeklog", 1);
        DB_delete($_TABLES['plugins'],'pi_name','categories');
        DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) "
            . "VALUES ('$pi_name', '$pi_version', '$gl_version', '$pi_url', 1)");

        if (DB_error()) {
            plugin_uninstall_categories();
            return false;
        }

        COM_errorLog("Succesfully installed the $pi_name Plugin!",1);
        return true;
    }

?>
