<?php

    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

    /**
     * Function Include file.
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
     * @version     CVS: $Id: functions.inc,v 1.4 2007/04/06 19:13:20 nkd Exp $
     * @since       File available since Release 1.03
     *
     */


    /**
     * Language file Include
     */
    define('gCONPath', $GLOBALS['_CONF']['path']);
    define('gCONLangFile', gCONPath . 'plugins/categories/language/' . $GLOBALS['_CONF']['language'] . '.php');

    if (file_exists(gCONLangFile))
    {
        include_once gCONLangFile;
    }
    else
    {
        include_once gCONPath . 'plugins/categories/language/english.php';
    }

    /**
     * Include categories config file
     */
    require_once gCONPath . 'plugins/categories/config.php';

    /**
     * +---------------------------------------------------------------------------+
     * | Geeklog Plugin API Implementations                                        |
     * +---------------------------------------------------------------------------+
     */

    /**
     * This will put an option for the plugin in the command and control block on moderation.php
     *
     * @return array Array containing (plugin name, admin url, url of plugin icon)
     */
    function plugin_cclabel_categories()
    {
        return array('Categories', $GLOBALS['_CONF']['site_admin_url'] .
                     '/plugins/categories/index.php', $GLOBALS['_CONF']['site_url'] .
                     '/categories/images/categories.gif');
    }


    /**
     * Check to see if this Plugin uses comments
     *
     * Geeklog is checking to see if this plugin supports comments, tell it no!
     * NOTE: to support comments you must use the same date/time based ID for your
     * widget. In other words, to make primary keys for your plugin you should call
     * makesid(). Comments are keyed off of that...it is a limitation on how geeklog
     * does comments.
     *
     * @return  boolean true or false
     */
    function plugin_commentsupport_categories()
    {
        return false;
    }


    /**
     * Adds the plugin to the Admin menu
     *
     * Returns the administrative option for this plugin
     *
     * @return array Array containing (plugin name, plugin admin url, # of items in plugin or '')
     */
    function plugin_getadminoption_categories()
    {
        if (SEC_hasRights('categories.admin'))
        {
            return array('Categories', $GLOBALS['_CONF']['site_admin_url'] .
                         '/plugins/categories/index.php', DB_count($GLOBALS['_TABLES']['categories_List']));
        }
        return null;
    }


    /**
     * Returns the items for this plugin that should appear on the main menu
     *
     * NOTE: this MUST return the url/value pairs in the following format
     * $<arrayname>[<label>] = <url>
     *
     * @return array Associative array of menu items to be added to menu
     */
    function plugin_getmenuitems_categories()
    {
        $menulabel = $GLOBALS['LANG_PL00']['menu.name']; // Menu label
        $menurul   = $GLOBALS['_CONF']['site_url'] . '/categories/index.php'; // Menu url
        $menuitems = array();
        $menuitems["$menulabel"] = $menurul;
        return $menuitems;
    }


    /**
     * Code used to add categories block to users profile.
     *
     * @param integer $fintUserId
     * @return string
     */
    function plugin_profileblocksedit_categories($fintUserId)
    {
        // This is Geek Logs way to get the users data from the database.
        $fresMySQLResult = DB_query("SELECT fullname, cookietimeout, email, homepage, sig, emailstories, about, pgpkey, photo
                                     FROM  {$GLOBALS['_TABLES']['users']}, {$GLOBALS['_TABLES']['userprefs']}, {$GLOBALS['_TABLES']['userinfo']}
                                     WHERE {$GLOBALS['_TABLES']['users']}.uid = {$GLOBALS['_USER']['uid']} &&
                                           {$GLOBALS['_TABLES']['userprefs']}.uid = {$GLOBALS['_USER']['uid']} &&
                                           {$GLOBALS['_TABLES']['userinfo']}.uid = {$GLOBALS['_USER']['uid']}");

        $faryUsersCurrentInfo = DB_fetchArray($fresMySQLResult);

        // This sets a default cookie value if one is not already set.
        if ($faryUsersCurrentInfo['cookietimeout'] == 0)
        {
            $faryUsersCurrentInfo['cookietimeout'] = $GLOBALS['_CONF']['default_perm_cookie_timeout'];
        }

        $faryCurrentCategories  = array(); // Clean
        $fstrCategoriesListHtml = null;    // Clean

        // Get users current Categories from database.
        $fresMySQLResult = DB_query("SELECT `categories_id` " .
                                    "FROM {$GLOBALS['_TABLES']['categories_LinkToUsers']} " .
                                    "WHERE uid = '$fintUserId'");

        /**
         *  Geeklog's database layer does not have an fetch assoc function.
         *  I hard coded the mysql_fetch_assoc function. In the future if
         *  geeklog starts supporting more then mysql. This plugin will stop
         *  working. NKD 12.02.2004
         */
        while ($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult))
        {
                    $faryCurrentCategories[] = $faryMySQLResult['categories_id'];
        }

        $sql = "SELECT categories_id, categories_name, categories_description, categories_imagename " .
               "FROM {$GLOBALS['_TABLES']['categories_List']} " .
               "WHERE `categories_isactive` = 'TRUE' and `categories_access` = 'public'";

        // Get all active Categories from Database.
        $fresMySQLResult = DB_query($sql);

        /**
         *  Geeklog's database layer does not have an fetch assoc function.
         *  I hard coded the mysql_fetch_assoc function. In the future if
         *  geeklog starts supporting more then mysql. This plugin will stop
         *  working.
         */
        while ($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult))
        {

            /**
             *  Check if user already has Categories selected. If so check html box.
             *
             *  NOTE: How it works.
             *  The database has a "link" table with the "categories_id" linked to the "user_id"
             *  a few steps up we SELECT everything in that table for that user and leave it in
             *  the array "$faryCurrentCategories". Next we SELECT all the active categories
             *  from the "list" table. We then search the "$faryCurrentCategories" array with
             *  the active categories list. If a match is found we print that category checkbox
             *  checked.
             *
             */
            $fstrChecked = null;
            if (array_search($faryMySQLResult['categories_id'], $faryCurrentCategories) !== false)
            {
                $fstrChecked = 'checked';
            }

            $fstrCategoriesListHtml .= '
                <tr>
                    <td>
                        ' . $faryMySQLResult['categories_name'] . '
                        <br /><small>
                        ' . $faryMySQLResult['categories_description'] . '

                    </td>
                    <td>
                        <input type="checkbox" name="frmchkCategories[]" value="' . //<-
                        $faryMySQLResult['categories_id'] . '" ' . $fstrChecked . '>
                    </td>
                    <td>
                        <img src="' . $GLOBALS['_CONF']['site_url'] . //<-
                        '/categories/images/' . //<-
                        $faryMySQLResult['categories_imagename'] . '">
                    </td>
                </tr>
                ';

        }

        // Title of Block in Profile
        $fstrHtmlOutput = COM_startBlock();

        $fstrHtmlOutput .= '
            <div id="pe_namepass">
                <fieldset style="margin-top:10px;"><legend>' . $GLOBALS['LANG_PL00']['userprofile.title'] . '</legend>
                    <center>
                            <table border="0" cellpadding="10" cellspacing="0" class="inputTable">
                                <tr>
                                    <td align="center" colspan="2">
                                        ' . $GLOBALS['LANG_PL00']['userprofile.note'] . '

                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="40%" valign="top">
                                        <br><p>
                                        ' . $GLOBALS['LANG_PL00']['userprofile.name'] . '
                                        <br>
                                        ' . $GLOBALS['LANG_PL00']['userprofile.description'] . '
                                        </p>
                                    </td>
                                    <td align="left" valign="top">
                                        <p>
                                        <form action="'
                                            . $GLOBALS['_CONF']['site_url'] .
                                            '/usersettings.php" method="POST" enctype="multipart/form-data">
                                        <table border="0" cellpadding="5" cellspacing="0">'
                                            . $fstrCategoriesListHtml .
                                        '</table>
                                            <input type="hidden" name="mode" value="saveuser">
                                            <input type="hidden" name="frmhdnUserID" value="' . $fintUserId . '">
                                            <input type="hidden" name="email" value="'        . $faryUsersCurrentInfo['email'] . '">
                                            <input type="hidden" name="fullname" value="'     . $faryUsersCurrentInfo['fullname'] . '">
                                            <input type="hidden" name="homepage" value="'     . $faryUsersCurrentInfo['homepage'] . '">
                                            <input type="hidden" name="cooktime" value="'     . $faryUsersCurrentInfo['cookietimeout'] . '">
                                            <input type="hidden" name="sig" value="'          . $faryUsersCurrentInfo['sig'] . '">
                                            <input type="hidden" name="about" value="'        . $faryUsersCurrentInfo['about'] . '">
                                            <input type="hidden" name="pgpkey" value="'       . $faryUsersCurrentInfo['pgpkey'] . '">
                                            <input type="submit" name="frmbtnSubmit" value="' . $GLOBALS['LANG_PL00']['userprofile.save'] . '">
                                        </form>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                    </center>
                </fieldset>
            </div>';

        $fstrHtmlOutput .= COM_endBlock();
        return $fstrHtmlOutput;

    }


    /**
     * Saves data to database when a user update their profile.
     *
     */
    function plugin_profileextrassave_categories()
    {
        // First check to see if the form being submited is for the categories plugin.
        if ($_POST['frmbtnSubmit'] != $GLOBALS['LANG_PL00']['userprofile.save'])
        {
            return;
        }

        // Only let logged in users access this page.
        if (!SEC_inGroup('Logged-in Users'))
        {

            // Someone is trying to illegally access this page
            COM_errorLog("Someone has tried to hack the Categories post page. " .
                         "User id: {$GLOBALS['_USER']['uid']}, Username: " .
                         "{$GLOBALS['_USER']['username']}, IP: {$_SERVER['REMOTE_ADDR']}", 1);

            $display = COM_siteHeader();
            $display .= COM_startBlock($GLOBALS['LANG_PL00']['access_denied']);
            $display .= $GLOBALS['LANG_PL00']['hackattempt'];
            $display .= COM_endBlock();
            $display .= COM_siteFooter(true);
            echo $display;
            exit;
        }

        // Next check to make sure the user is submiting data for himself only.
        if ($GLOBALS['_USER']['uid'] != $_POST['frmhdnUserID'])
        {

            // Someone is trying to illegally access this page
            COM_errorLog("Someone has tried to hack the Categories post page. " .
                         "User id: {$GLOBALS['_USER']['uid']}, Username: ".
                         "{$GLOBALS['_USER']['username']}, IP: {$_SERVER['REMOTE_ADDR']}", 1);

            $display  = COM_siteHeader();
            $display .= COM_startBlock($GLOBALS['LANG_PL00']['access_denied']);
            $display .= $GLOBALS['LANG_PL00']['hackattempt'];
            $display .= COM_endBlock();
            $display .= COM_siteFooter(true);
            echo $display;
            exit;
        }

        /**
         *  GL's SEC_hasRights function does not work I as would have expected.
         *  It requires that the string passed not have any spaces in it after the comma.
         *  i.e. Does not work:
         *  SEC_hasRights('categories.admin, categories.view, categories.edit', 'OR')
         *  Works: SEC_hasRights('categories.admin,categories.view,categories.edit', 'OR')
         */

        // First build list of private categories user has assigned to them.
        $fresMySQLResult = DB_query('SELECT ' . $GLOBALS['_TABLES']['categories_LinkToUsers']
                                              . '.categories_id
                                     FROM '   . $GLOBALS['_TABLES']['categories_LinkToUsers'] . ' ' .
                                    'LEFT JOIN `' . $GLOBALS['_TABLES']['categories_List'] . '`
                                     USING (`categories_id`) ' .
                                    'WHERE uid = \'' . $_POST['frmhdnUserID']
                                              . '\' AND categories_access = \'private\';');

        while ($faryMySQLResult = mysql_fetch_array($fresMySQLResult))
        {
            $faryAssignedPrivateCategories[] = $faryMySQLResult['categories_id'];
        }

        // Next drop everything the user currently has.
        DB_query('DELETE FROM `' . $GLOBALS['_TABLES']['categories_LinkToUsers'] .'`
                  WHERE `uid` = \'' . $_POST['frmhdnUserID'] . '\';');

        // Next we add the categories the user checked from the profile screen.
        if (isset($_POST['frmchkCategories']))
        {
            foreach ($_POST['frmchkCategories'] as $bstrTempVar)
            {
                DB_query('INSERT INTO `' . $GLOBALS['_TABLES']['categories_LinkToUsers'] .
                         '` ( `categories_id` , `uid` ) ' .
                         ' VALUES (\'' . $bstrTempVar . '\', \'' . $_POST['frmhdnUserID'] . '\' );');
            }
        }

        // Last add users assigned private categories.
        if (isset($faryAssignedPrivateCategories))
        {
            foreach ($faryAssignedPrivateCategories as $bstrTempVar)
            {
                DB_query('INSERT INTO `' . $GLOBALS['_TABLES']['categories_LinkToUsers'] .
                         '` ( `categories_id` , `uid` ) ' .
                         ' VALUES (\'' . $bstrTempVar . '\', \'' . $_POST['frmhdnUserID'] . '\' );');
            }
        }
    }

    /**
     * @return boolean True if successful false otherwise
     * @param string $installCheck Defult is blank but if set, check if plugin is disabled first
     * @desc Removes the datastructures for this plugin from the Geeklog database.
     *
     * This routine will get called from the Plugin install program if user select
     * De-Install or if Delete is used in the Plugin Editor. The Plugin Installer
     * will also call this routine upon and install error to remove anything it
     * has created. The Plugin installer will pass the optional parameter which
     * will then double check that plugin has first been disabled. For this plugin,
     * this routine will also remove the Block definition. Returns True if all
     * Plugin related data is removed without error
     *
     */
    function plugin_uninstall_categories($installCheck = '')
    {
        $pi_name  = 'categories'; // Name of Plugin
        $FEATURES = array ('categories.admin', 'categories.view', 'categories.edit');
        $TABLES   = array ('categories_List', 'categories_LinkToUsers');

        // Check and see if plugin is still enabled - if so display warning and exit
        if ($installCheck != '' && DB_getItem($GLOBALS['_TABLES']['plugins'], 'pi_enabled', 'pi_name = "' . $pi_name . '"')) {
            COM_errorLog('Plugin is installed and enabled. Disable first if you want to de-install it', 1);
            $display .= COM_startBlock($GLOBALS['LANG_PL00']['warning']);
            $display .= $GLOBALS['LANG_PL00']['enabled'];
            $display .= COM_endBlock();
            echo $display;
            return false;
        }

        // Ok to proceed and delete plugin

        // Unregister the plugin with Geeklog
        COM_errorLog('Attempting to unregister the Categories plugin from Geeklog', 1);
        DB_query('DELETE FROM ' . $GLOBALS['_TABLES']['plugins'] . ' WHERE pi_name = \'categories\'', 1);

        // Drop Categories tables
        foreach($TABLES as $table)
        {
            $t = $GLOBALS['_TABLES'][$table];
            COM_errorLog('Removing Table ' . $t , 1);
            DB_query('DROP TABLE ' . $t, 1);
        }

        // Remove Security for this plugin
        $grp_id = DB_getItem($GLOBALS['_TABLES']['vars'], 'value', 'name = \'' . $pi_name . '_gid\'');

        COM_errorLog('Removing ' . $pi_name . ' Admin Group', 1);
        DB_query('DELETE FROM ' . $GLOBALS['_TABLES']['groups'] . ' WHERE grp_id = ' . $grp_id, 1);
        DB_query('DELETE FROM ' . $GLOBALS['_TABLES']['vars'] . ' WHERE name = \'' . $pi_name . '_gid\'');
        COM_errorLog('Removing root users from admin of ' . $pi_name);
        DB_query('DELETE FROM ' . $GLOBALS['_TABLES']['group_assignments'] . ' WHERE ug_main_grp_id = ' . $grp_id, 1);

        // Remove all the associated features -- access rights
        foreach ($FEATURES as $feature)
        {
            COM_errorLog('Removing ' . $feature . ' feature and rights to it', 1);
            $feat_id = DB_getItem($GLOBALS['_TABLES']['features'], 'ft_id', 'ft_name = \'' . $feature . '\'');
            DB_query('DELETE FROM ' . $GLOBALS['_TABLES']['access'] . ' WHERE acc_ft_id = ' . $feat_id, 1);
            DB_query('DELETE FROM ' . $GLOBALS['_TABLES']['features'] . ' WHERE ft_id = ' . $feat_id, 1);
        }

        COM_errorLog('...success', 1);
        return true;

    }


    /**
     * Delete user's entries in the categories link database when the user is deleted.
     *
     * @param integer $fintUserId
     */
    function plugin_user_delete_categories($fintUserId)
    {
        // Delete everything the user currently has.
        DB_query('DELETE FROM `' . $GLOBALS['_TABLES']['categories_LinkToUsers'] .'`
                  WHERE `uid` = \'' . $fintUserId . '\';');
    }

?>
