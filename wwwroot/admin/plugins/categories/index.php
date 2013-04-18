<?php

    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

    /**
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
     * @version     CVS: $Id: index.php,v 1.6 2007/04/06 19:15:22 nkd Exp $
     * @since       File available since Release 1.03
     *
     */

    require_once '../../../lib-common.php';
    $gstrPluginVersion = '1.10'; // Plugin Version also found in install.php


    /**
     *  Only let admin users access this page
     */
    if (!SEC_hasRights('categories.admin'))
    {
        // Someone is trying to illegally access this page
        COM_errorLog("Someone has tried to illegally access the Categories Admin page.  User id: {$GLOBALS['_USER']['uid']}, Username: {$GLOBALS['_USER']['username']}, IP: $REMOTE_ADDR",1);
        $display  = COM_siteHeader();
        $display .= COM_startBlock($GLOBALS['LANG_PL00']['access_denied']);
        $display .= $GLOBALS['LANG_PL00']['access_denied_msg'];
        $display .= COM_endBlock();
        $display .= COM_siteFooter(true);
        echo $display;
        exit;
    }

    /**
     *  @return void
     *  @param string $fstrPluginVersion
     *  @desc This checks to see if an older version of the plugin is installed.
     */
    function CategoriesPluginVersionCheck($fstrPluginVersion)
    {
        $fresMySQLResult = DB_query('SELECT pi_version FROM `' .
                                    $GLOBALS['_TABLES']['plugins'] . '` ' .
                                    'WHERE pi_name = \'categories\';');

        while ($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult))
        {
            $fstrCurrentlyInstalledVersion = $faryMySQLResult['pi_version'];
        }

        if ($fstrCurrentlyInstalledVersion < $fstrPluginVersion)
        {
            $display  = COM_siteHeader();
            $display .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.upgradetitle']);
            $display .= $GLOBALS['LANG_PL00']['adminpage.upgrade_msg'];
            $display .= '<br />';
            $display .= '<form action="' . //<-
                        $GLOBALS['_CONF']['site_url'] . //<-
                        '/admin/plugins/categories/index.php" method="POST" enctype="multipart/form-data">
                        <input type="submit" name="frmbtnSubmit" value="' . //<-
                        $GLOBALS['LANG_PL00']['adminpage.upgradebtn'] . //<-
                        '">
                        </form>';
            $display .= COM_endBlock();
            $display .= COM_siteFooter(true);
            echo $display;
            exit;
        }
    }

    /**
     * Upgrades categories plugin
     *
     * @param string $fstrPluginVersion
     */
    function UpgradeCategoriesPlugin($fstrPluginVersion)
    {
            // Update the plugin verion number.
            COM_errorLog($GLOBALS['LANG_PL00']['adminpage.upgrade_err_msg_01'], 1);

            // Version Upgrade
            DB_query('UPDATE `' . $GLOBALS['_TABLES']['plugins'] . //<-
                     '` SET `pi_version` = \'' . $fstrPluginVersion . //<-
                     '\' WHERE `pi_name` = \'categories\' LIMIT 1 ;');
            COM_errorLog($GLOBALS['LANG_PL00']['adminpage.upgrade_err_msg_02'], 1);

            return;
    }

    // If no post data check if plugin is upto date.
    if (!isset($_POST['frmbtnSubmit']))
    {
        CategoriesPluginVersionCheck($gstrPluginVersion); // This checks if the plugin is uptodate.
    }

    /**
     * This deals with events to change data.
     * This should be a switch statement.
     */
    if (isset($_POST['frmbtnSubmit']))
    {
        switch ($_POST['frmbtnSubmit'])
        {

            case $GLOBALS['LANG_PL00']['adminpage.addnew']:

                // Add New button selected.
                $gstrCategoriesAdminActionMessageOutput = null; // Clean

                // Check for blank entrys
                if (empty($_POST['frmtxtName']) || empty($_POST['frmtxtImageName']))
                {
                    $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.errortitle']);
                    $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.errormessage'];
                    $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                }
                else // Add data to database.
                {
                    DB_query('INSERT INTO `' . $GLOBALS['_TABLES']['categories_List'] .
                             '` ( `categories_name` , `categories_description`, `categories_imagename` , `categories_isactive` ) ' .
                             'VALUES (\'' . //<-
                             str_replace('"', '\'', $_POST['frmtxtName']) . '\', \'' .
                             str_replace('"', '\'', $_POST['frmtxtDescription']) . '\', \'' .
                             str_replace('"', '\'', $_POST['frmtxtImageName']) .
                             '\', \'TRUE\');');

                    $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.addnew']);
                    $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.addnew_msg'];
                    $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                }
                break;

            case $GLOBALS['LANG_PL00']['adminpage.upgradebtn']:

                // Upgrade Plugin button selected.
                UpgradeCategoriesPlugin($gstrPluginVersion);
                $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.upgradetitle']);
                $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.upgrade_err_msg_02'];
                $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                break;

            case $GLOBALS['LANG_PL00']['adminpage.savechanges']:

                // Save Changes button selected. This is when editing a catagory.
                DB_query('UPDATE `' . $GLOBALS['_TABLES']['categories_List'] . '` ' .
                    'SET
                    `categories_name` = \'' . str_replace('"', '\'', $_POST['frmtxtName']) . '\',
                    `categories_description` = \'' . str_replace('"', '\'', $_POST['frmtxtDescription']) . '\',
                    `categories_imagename`  = \'' . str_replace('"', '\'', $_POST['frmtxtImageName']) . '\'
                    WHERE `categories_id` = \'' . $_POST['frmhdnCategoriesID'] . '\' LIMIT 1 ;');
                $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.savetitle']);
                $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.savechangemsg'];
                $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                break;

            case $GLOBALS['LANG_PL00']['adminpage.search']:

                // Search button selected
                // Check if anything was selected for search.
                if (!isset($_POST['frmchkCategoriesToSearch']))
                {
                    $gstrCategoriesAdminActionMessageOutput  = COM_siteHeader();
                    $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.search']);
                    $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.searcherror_msg'];
                    $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                    $gstrCategoriesAdminActionMessageOutput .= COM_siteFooter(true);
                    echo $gstrCategoriesAdminActionMessageOutput;
                    exit;
                }

                $gstrBuildMailTo = array(); // Clean
                $gstrSearchHtml = implode(' ' . $_POST['frmlstLoginOperator'] . ' ', str_replace('categories_id = ', '', $_POST['frmchkCategoriesToSearch'])) ;

                if (isset($_POST['frmchkCategoriesToSearch']))
                {
                    $gstrBuildMySqlSearch = 'SELECT DISTINCT `username`, `fullname`, `email`
                                             FROM `gl_users`
                                             LEFT JOIN `gl_categories_LinkToUsers` USING (`uid`)
                                             WHERE ' . implode(' ' . $_POST['frmlstLoginOperator'] . ' ', $_POST['frmchkCategoriesToSearch']);
                }

                $gresMySQLResult = DB_query($gstrBuildMySqlSearch);
                $gstrSearchHtml .= "\n" . '<br /><br /><table border="0" cellpadding="5" cellspacing="0" width="100%">';
                while ($garyMySQLResult = mysql_fetch_assoc($gresMySQLResult))
                {
                    if (empty($garyMySQLResult['fullname']))
                    {
                        $garyMySQLResult['fullname'] = $garyMySQLResult['username'];
                    }

                    $gstrSearchHtml .= '
                                        <tr>
                                            <td align="left">&nbsp;&nbsp;&nbsp;&nbsp;
                                               ' . $garyMySQLResult['fullname'] . '
                                           </td>
                                           <td align="left">
                                               ' . $garyMySQLResult['email'] . '
                                           </td>
                                       </tr>
                                    ';
                    $garyBuildMailTo[] = $garyMySQLResult['fullname'] . ' <' . $garyMySQLResult['email'] . '>' ;
                }
                if (isset($garyBuildMailTo))
                {
                    $gstrMailToList = implode('; ', $garyBuildMailTo);
                }
                $gstrSearchHtml .= '<tr>
                                        <td colspan="2">
                                            <form action="mailto:' .
                                                $GLOBALS['LANG_PL00']['adminpage.mailtoaddress'] .
                                                '?bcc=' . $gstrMailToList . '">
                                                <input type="submit" value="Send Email">
                                            </form>
                                        </td>
                                    </tr>
                                </table>';

                $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.search']);
                $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.search_msg_02'] . $gstrSearchHtml;
                $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                break;

        }
    }

    if (isset($_GET['frmbtnSubmit']))
    {
        switch ($_GET['frmbtnSubmit'])
        {
            case $GLOBALS['LANG_PL00']['adminpage.activate']:

                // Activate button selected.
                DB_query('UPDATE `' . $GLOBALS['_TABLES']['categories_List'] . '` ' .
                    'SET `categories_isactive` = \'TRUE\' ' . //<-
                    'WHERE `categories_id` = \'' . $_GET['categories_id'] . '\' LIMIT 1 ;');

                $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.activate']);
                $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.activate_msg'];
                $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                break;

            case $GLOBALS['LANG_PL00']['adminpage.deactivate']:

                // Deactivate button selected.
                DB_query('UPDATE `' . $GLOBALS['_TABLES']['categories_List'] . '` ' .
                    'SET `categories_isactive` = \'FALSE\' ' . //<-
                    'WHERE `categories_id` = \'' . $_GET['categories_id'] . '\' LIMIT 1 ;');
                $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.deactivate']);
                $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.deactivate_msg'];
                $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                break;

            case $GLOBALS['LANG_PL00']['adminpage.delete']:

                // Delete button selected.
                // Remove category from category list table.
                DB_query('DELETE FROM `' . $GLOBALS['_TABLES']['categories_List'] . '` ' .
                         'WHERE `categories_id` = \'' . $_GET['categories_id'] . '\' LIMIT 1 ;');

                // Remove all links for thie category from link table.
                DB_query('DELETE FROM `' . $GLOBALS['_TABLES']['categories_LinkToUsers'] . '` ' .
                         'WHERE `categories_id` = ' . $_GET['categories_id'] . ';');

                $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.delete']);
                $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.delete_msg'];
                $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                break;

            case 'ChangeAccess':
                // Change access level selected.
                if ($_GET['NewValue'] != 'public' && $_GET['NewValue'] != 'private')
                {
                    COM_errorLog("Someone has tried to hack the Categories Admin page by sending bad data into the URL.  User id: {$GLOBALS['_USER']['uid']}, Username: {$GLOBALS['_USER']['username']}, IP: $REMOTE_ADDR",1);
                    $display  = COM_siteHeader();
                    $display .= COM_startBlock($GLOBALS['LANG_PL00']['access_denied']);
                    $display .= $GLOBALS['LANG_PL00']['access_denied_msg'];
                    $display .= COM_endBlock();
                    $display .= COM_siteFooter(true);
                    echo $display;
                    exit;
                }

                DB_query('UPDATE `' . $GLOBALS['_TABLES']['categories_List'] . '` ' .
                    'SET `categories_access` = \'' . $_GET['NewValue'] . '\' ' .
                    'WHERE `categories_id` = \'' . $_GET['categories_id'] . '\' LIMIT 1 ;');

                $gstrCategoriesAdminActionMessageOutput .= COM_startBlock($GLOBALS['LANG_PL00']['adminpage.access_msg_01']);
                $gstrCategoriesAdminActionMessageOutput .= $GLOBALS['LANG_PL00']['adminpage.access_msg_01'];
                $gstrCategoriesAdminActionMessageOutput .= COM_endBlock();
                break;

        }
    }

    // Get all Categories from Database.
    $bresMySQLResult = DB_query('SELECT * FROM ' . $GLOBALS['_TABLES']['categories_List']);

    // Geeklog's database layer does not have an fetch assoc function.
    $gstrCategoriesAdminHtmlOutput = '
                                    <form action="' . $GLOBALS['_CONF']['site_url'] .
                                        '/admin/plugins/categories/index.php" method="POST" enctype="multipart/form-data">
                                    <table border="0" cellpadding="5" cellspacing="0" width="100%">
                                        <tr>
                                            <td>
                                               ' . $GLOBALS['LANG_PL00']['adminpage.id'] . '
                                            </td>
                                            <td>
                                                ' . $GLOBALS['LANG_PL00']['adminpage.name'] . '
                                            </td>
                                            <td>
                                                ' . $GLOBALS['LANG_PL00']['adminpage.icon'] . '
                                            </td>
                                            <td>
                                                &nbsp;
                                            </td>
                                            <td>
                                                &nbsp;
                                            </td>
                                            <td>
                                                &nbsp;
                                            </td>
                                            <td>
                                                &nbsp;
                                            </td>
                                        </tr>';

    while ($baryMySQLResult = mysql_fetch_assoc($bresMySQLResult))
    {
        // Check if category is active or not.
        if ($baryMySQLResult['categories_isactive'] == 'TRUE')
        {
            $bstrSubmitButtonValue = $GLOBALS['LANG_PL00']['adminpage.deactivate'];
        }
        else
        {
            $bstrSubmitButtonValue = $GLOBALS['LANG_PL00']['adminpage.activate'];
        }

        // Check if category is Public or Private.
        if ($baryMySQLResult['categories_access'] == 'public')
        {
            $bstrAccessValue = $GLOBALS['LANG_PL00']['adminpage.public'];
            $bstrNewValue = 'private';
        }
        else
        {
            $bstrAccessValue = $GLOBALS['LANG_PL00']['adminpage.private'];
            $bstrNewValue = 'public';
        }

        $gstrCategoriesAdminHtmlOutput .= '
            <tr>
                <td>
                    ' . $baryMySQLResult['categories_id'] . '
                    &nbsp;<input type="checkbox" name="frmchkCategoriesToSearch[]" value="categories_id = ' .
                    $baryMySQLResult['categories_id'] . '" >
                </td>
                <td>
                    ' . $baryMySQLResult['categories_name'] . '<br />
                    <small>' . $baryMySQLResult['categories_description'] . '</small>
                </td>
                <td>
                    <img src="' . //<-
                        $GLOBALS['_CONF']['site_url'] .
                        '/categories/images/' .
                        $baryMySQLResult['categories_imagename'] . '">
                </td>
                <td>
                    <a href="?categories_id=' .
                        $baryMySQLResult['categories_id'] .
                        '&frmbtnSubmit=ChangeAccess&NewValue=' . $bstrNewValue .
                        '" style="text-decoration: none;">' .
                        $bstrAccessValue .
                        '</a>
                </td>
                <td>
                    <a href="?categories_id=' .
                        $baryMySQLResult['categories_id'] .
                        '&frmbtnSubmit=' . $bstrSubmitButtonValue .
                        '" style="text-decoration: none;">' .
                        $bstrSubmitButtonValue .
                        '</a>
                </td>
                <td>
                    <a href="
                         ?categories_id=' . $baryMySQLResult['categories_id'] .
                        '&frmbtnSubmit='  . $GLOBALS['LANG_PL00']['adminpage.edit'] .
                        '&frmhdnCategoriesName='  . $baryMySQLResult['categories_name'] .
                        '&frmhdnCategoriesDescription='  . $baryMySQLResult['categories_description'] .
                        '&frmhdnCategoriesImageName='  . $baryMySQLResult['categories_imagename'] .
                        '" style="text-decoration: none;">' .
                        $GLOBALS['LANG_PL00']['adminpage.edit'] .
                        '</a>
                </td>
                <td>
                    <a href="
                         ?categories_id=' . $baryMySQLResult['categories_id'] .
                        '&frmbtnSubmit='  . $GLOBALS['LANG_PL00']['adminpage.delete'] .
                        '" style="text-decoration: none;" onClick="return confirm(\'Delete this Category?\')">' .
                        $GLOBALS['LANG_PL00']['adminpage.delete'] .
                        '</a>
                </td>
            </tr>
        ';
    }

    $gstrCategoriesAdminHtmlOutput .= '
        <tr>
            <td colspan="4">
                <input type="submit" name="frmbtnSubmit" value="' .
                    $GLOBALS['LANG_PL00']['adminpage.search'] .
                    '" />&nbsp;&nbsp;&nbsp;'
                    . $GLOBALS['LANG_PL00']['adminpage.search_msg_01'] . '
            </td>
            <td colspan="3">&nbsp;
                <input type="hidden" name="frmlstLoginOperator" value="or">
                <!-- select name="frmlstLoginOperator">
                    <option value="or">OR</option>
                    <option value="and">AND</option>
                </select -->
                </form>
            </td>

        </tr>
        <tr>
            <td colspan="4">
                    <a href="./edituser.php">' . $GLOBALS['LANG_PL00']['adminpage.editusers'] . '</a>
            </td>
        </tr>
        ';

    // Edit a Category
    if (isset($_GET['frmbtnSubmit']) && $_GET['frmbtnSubmit'] == $GLOBALS['LANG_PL00']['adminpage.edit'])
    {
        $gstrCategoriesAdminHtmlOutput .= '
        <tr>
            <td colspan="7">
        <hr>
            <form action="' .
                            $GLOBALS['_CONF']['site_url'] .
                            '/admin/plugins/categories/index.php" method="POST" enctype="multipart/form-data">
                            <center>
                            <table border="0" cellspacing="0" cellpadding="5">
                                <tr>
                                    <td>
                                        ' . $GLOBALS['LANG_PL00']['adminpage.name'] .
                                        ': <input maxlength="250" type="text" name="frmtxtName" value="' .
                                        stripslashes($_GET['frmhdnCategoriesName']) . '">
                                    </td>
                                    <td>
                                        ' . $GLOBALS['LANG_PL00']['adminpage.description'] . //<-
                                        ': <input maxlength="250" type="text" name="frmtxtDescription" value="' .
                                        stripslashes($_GET['frmhdnCategoriesDescription']) . '">
                                    </td>
                                    <td>
                                        ' . $GLOBALS['LANG_PL00']['adminpage.imagename'] .
                                        ': <input maxlength="250" type="text" name="frmtxtImageName" value="' .
                                        stripslashes($_GET['frmhdnCategoriesImageName']) . '">
                                    </td>
                                </tr>
                            </table>
                            </center>
                            <br /><br />
                            <input type="hidden" name="frmhdnCategoriesID" value="' .
                                $_GET['categories_id'] . '">
                            <input type="submit" name="frmbtnSubmit" value="' .
                                $GLOBALS['LANG_PL00']['adminpage.savechanges'] . '">
            </form>
        </td></tr>';
    }

    // Add a new Category
    $gstrCategoriesAdminHtmlOutput .= '
        <tr><td colspan="7">
        <hr>
            <form action="' .
                            $GLOBALS['_CONF']['site_url'] .
                            '/admin/plugins/categories/index.php" method="POST" enctype="multipart/form-data">
                            <center>
                            <table border="0" cellspacing="0" cellpadding="5">
                                <tr>
                                    <td>
                                        ' . $GLOBALS['LANG_PL00']['adminpage.name'] . ' <input maxlength="250" type="text" name="frmtxtName" value="Example Name" onfocus="value=\'\'">
                                    </td>
                                    <td>
                                        ' . $GLOBALS['LANG_PL00']['adminpage.description'] . ' <input maxlength="250" type="text" name="frmtxtDescription" value="Example Description" onfocus="value=\'\'">
                                    </td>
                                    <td>
                                        ' . $GLOBALS['LANG_PL00']['adminpage.imagename'] . ' <input maxlength="250" type="text" name="frmtxtImageName" value="Example.gif" onfocus="value=\'\'">
                                    </td>
                                </tr>
                            </table>
                            </center>
                            <br /><br />
                            <input type="submit" name="frmbtnSubmit" value="' . $GLOBALS['LANG_PL00']['adminpage.addnew'] . '">
            </form>
            <hr>
            ' . $GLOBALS['LANG_PL00']['adminpage.lowermessage'] . '
        </td></tr>';
        $gstrCategoriesAdminHtmlOutput .= '</table>';

    /**
     * Main
     * This part of the plugin uses a template file. admin.thtml
     */
    $display = COM_siteHeader();
    $T = new Template($GLOBALS['_CONF']['path'] . 'plugins/categories/templates');
    $T->set_file('admin', 'admin.thtml');
    $T->set_var('site_url', $GLOBALS['_CONF']['site_url']);
    $T->set_var('site_admin_url', $GLOBALS['_CONF']['site_admin_url']);
    $T->set_var('header', $GLOBALS['LANG_PL00']['admin']);
    $T->set_var('plugin','categories');
    $T->set_var('tstrStartBlock', COM_startBlock('Categories'));
    $T->set_var('tstrActionMessageBlock', $gstrCategoriesAdminActionMessageOutput);
    $T->set_var('tstrMidBlock', $gstrCategoriesAdminHtmlOutput);
    $T->set_var('tstrEndBlock', COM_endBlock());
    $T->parse('output','admin');
    $display .= $T->finish($T->get_var('output'));
    $display .= COM_siteFooter(true);

    echo $display;
?>
