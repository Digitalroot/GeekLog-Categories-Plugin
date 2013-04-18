<?php

    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

    /**
     * Edit User
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
     * @version     CVS: $Id: edituser.php,v 1.6 2007/04/06 19:13:20 nkd Exp $
     * @since       File available since Release 1.03
     *
     */

    require_once('../../../lib-common.php');

    /**
     *  Only let admin users access this page
     */
    if (!SEC_hasRights('categories.admin'))
    {
        // Someone is trying to illegally access this page
        COM_errorLog("Someone has tried to illegally access the Categories Admin page.  User id: {$GLOBALS['_USER']['uid']}, Username: {$GLOBALS['_USER']['username']}, IP: $REMOTE_ADDR",1);
        $display  = COM_siteHeader();
        $display .= COM_startBlock($GLOBALS['LANG_PL00']['access_denied']);
        $display .= $GLOBALS['LANG_PL00']['hackattempt'];
        $display .= COM_endBlock();
        $display .= COM_siteFooter(true);
        echo $display;
        exit;
    }

    function GetUserList()
    {
        $fstrThisReturns = null; // Clean
        $fresMySQLResult = DB_query('SELECT uid, username FROM `' . $GLOBALS['_TABLES']['users']
                                                                  . '` ORDER BY `username`');

            while ($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult))
            {
                $fstrThisReturns .= '&nbsp;<a border="2" href="./edituser.php?editid='
                                 . $faryMySQLResult['uid'] .'">'
                                 . $faryMySQLResult['username']
                                 . '</a><br /><br />' . "\n";
            }

        return $fstrThisReturns;
    }

    function GetCategories($fintUserId)
    {
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
         *  working.
         */
        while ($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult))
        {
            $faryCurrentCategories[] = $faryMySQLResult['categories_id'];
        }

        // Get all active Categories from Database.
        $fresMySQLResult = DB_query("SELECT categories_id, categories_name,
                                            categories_description, categories_imagename " .
                                    "FROM {$GLOBALS['_TABLES']['categories_List']} " .
                                    "WHERE `categories_isactive` = 'TRUE'");

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

        $fstrHtmlOutput .= '
            <center>
                        <table border="0" cellpadding="10" cellspacing="0">
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
                                    <form action="./edituser.php" method="POST" enctype="multipart/form-data">
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
            </center>';

        return $fstrHtmlOutput;
    }

    function SaveCategories()
    {
        // First check to see if the form being submited is for the categories plugin.
        if ($_POST['frmbtnSubmit'] != $GLOBALS['LANG_PL00']['userprofile.save'])
        {
            return false;
        }

        // Next drop everything the user currently has.
        DB_query('DELETE FROM `' . $GLOBALS['_TABLES']['categories_LinkToUsers'] . '`
                  WHERE `uid` = \'' . $_POST['frmhdnUserID'] . '\';');

        // Next we add the categories the user checked from the profile screen.
        if (isset($_POST['frmchkCategories']))
        {
            foreach ($_POST['frmchkCategories'] as $bstrTempVar)
            {
                DB_query('INSERT INTO `' . $GLOBALS['_TABLES']['categories_LinkToUsers']
                                         . '` ( `categories_id` , `uid` ) '
                                         . ' VALUES (\'' . $bstrTempVar . '\', \''
                                         . $_POST['frmhdnUserID'] . '\' );');
            }
        }
    }

    if ($_POST['frmbtnSubmit'] == $GLOBALS['LANG_PL00']['userprofile.save'])
    {
        SaveCategories();
        $gintEditId = $_POST['frmhdnUserID'];
    }

    if (isset($_GET['editid']))
    {
        $gintEditId = $_GET['editid'];
    }

    $gstrPassToDisplay = null; // clean
    if (isset($gintEditId))
    {
        $gstrPassToDisplay .= COM_startBlock($GLOBALS['LANG_PL00']['edituser.edituser']);
        $gstrPassToDisplay .= GetCategories($gintEditId);
        $gstrPassToDisplay .= COM_endBlock();
    }

    $display  = COM_siteHeader();
    $display .= $gstrPassToDisplay;
    $display .= COM_startBlock($GLOBALS['LANG_PL00']['edituser.selectuser']);
    $display .= $GLOBALS['LANG_PL00']['edituser.selectuser_msg'] . '<br /><br />';
    $display .= GetUserList();
    $display .= COM_endBlock();
    $display .= COM_siteFooter();
    echo $display;
?>
