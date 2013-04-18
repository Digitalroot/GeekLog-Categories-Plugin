<?php

    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

    /**
     * Roster File
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
     * @version     CVS: $Id: roster.inc.php,v 1.6 2007/04/06 19:13:20 nkd Exp $
     * @since       File available since Release 1.03
     *
     */

    class Categories
    {
        /**
         * @return array
         * @desc Get all active Categories from Database.
         */
        function buildActiveCategoriesArray()
        {
            $sql = "SELECT categories_id, categories_name, categories_imagename " .
                   "FROM {$GLOBALS['_TABLES']['categories_List']} " .
                   "WHERE categories_isactive = 'TRUE'";

            $fresMySQLResult = DB_query($sql);

            while ($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult))
            {
                foreach ($faryMySQLResult as $fstrKey => $fstrValue)
                {
                    // Build an array with all the details for each active category.
                    $faryDataReturned[$faryMySQLResult['categories_id']][$fstrKey] = $fstrValue;
                }
            }
            return $faryDataReturned;
        }

        /**
         * @return array
         * @desc Get all the items in the link table to link active Categories to users.
         */
        function buildCategoriesLinkToUsersArray()
        {
            $fresMySQLResult = DB_query('SELECT categories_id, uid FROM ' .
                                            $GLOBALS['_TABLES']['categories_LinkToUsers']);

            while ($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult))
            {
                $faryDataReturned[] = 'UID:' . $faryMySQLResult['uid'] .
                                      ' CategoriesID:' . $faryMySQLResult['categories_id'];
            }
            return $faryDataReturned;
        }

        /**
         * @return string
         * @param integer $fintUserID
         * @desc Returns a string with the html code to display the icons for Categories.
         */
        function display($fintUserID)
        {
            static $faryActiveCategories;
            static $faryCategoriesLinkToUsersArray;
            if (!isset($faryActiveCategories) || !isset($faryCategoriesLinkToUsersArray))
            {
                $faryActiveCategories = Categories::buildActiveCategoriesArray();
                $faryCategoriesLinkToUsersArray = Categories::buildCategoriesLinkToUsersArray();
            }

            $fstrDataReturned = null; // Clean
            foreach ($faryActiveCategories as $fintKey => $faryUnused)
            {
                if (isset($faryCategoriesLinkToUsersArray) && array_search('UID:' . $fintUserID . ' CategoriesID:' . $fintKey, $faryCategoriesLinkToUsersArray) !== FALSE)
                {
                    $fstrDataReturned .= '<img src="' . $GLOBALS['_CONF']['site_url'] . '/categories/images/' . $faryActiveCategories[$fintKey]['categories_imagename'] . '" />&nbsp';
                }
            }
            return $fstrDataReturned;
        }

        /**
         * @return string
         * @desc Get everyone who is a =MF= member.
         */
        function processMembers()
        {
            $sql = 'SELECT uid, username, fullname, photo ' .
                   'FROM ' . $GLOBALS['_TABLES']['users'] . ' ' .
                   'WHERE username LIKE \'=MF=%\' ' . // Ignore Non =MF= Members
                   'ORDER BY username';

            $fresMySQLResult = DB_query($sql);
            $fstrDataReturned = null; // Clean

            while ($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult))
            {
                    if (!empty($faryMySQLResult['photo']))
                    {
                        $faryMySQLResult['photo'] = '<img border="0" src="' . $GLOBALS['_CONF']['layout_url'] . '/images/smallcamera.gif">';
                    }

                    $fstrDataReturned .= '
                        <tr>
                            <td>
                                <a href="' . $GLOBALS['_CONF']['site_url'] . '/users.php?mode=profile&uid='
                                  . $faryMySQLResult['uid'] . '">'
                                  . $faryMySQLResult['username'] . '
                                </a>
                           </td>
                            <td>
                                ' . $faryMySQLResult['fullname'] . '
                           </td>
                            <td>
                                ' . $faryMySQLResult['photo'] . '
                           </td>
                            <td>&nbsp;
                                ' . Categories::display($faryMySQLResult['uid']) . '
                           </td>
                        </tr>';

            }
            return $fstrDataReturned;
        }

        /**
        * @return string
        * @desc Get Everyone else who is not a MF member.
        */
        function processNonMembers()
        {
            $sql = 'SELECT uid, username, fullname, photo ' .
                   'FROM ' . $GLOBALS['_TABLES']['users'] . ' ' .
                   'WHERE uid != 1 && '  . // Ignore Anonymous account
                         'uid != 2 && '  . // Ignore Admin account
                         'uid != 3 && '  . // Ignore Moderator account
                         'username NOT LIKE \'=MF=%\' ' . // Ignore =MF= Members
                   'ORDER BY username';

            $fresMySQLResult = DB_query($sql);
            $fstrDataReturned = null; // Clean

            while ($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult))
            {
                    if (!empty($faryMySQLResult['photo']))
                    {
                        $faryMySQLResult['photo'] = '<img border="0" src="' . $GLOBALS['_CONF']['layout_url'] . '/images/smallcamera.gif">';
                    }

                    $fstrDataReturned .= '
                        <tr>
                            <td>
                                <a href="' . $GLOBALS['_CONF']['site_url'] . '/users.php?mode=profile&uid='
                                  . $faryMySQLResult['uid'] . '">'
                                  . $faryMySQLResult['username'] . '
                                </a>
                           </td>
                            <td>
                                ' . $faryMySQLResult['fullname'] . '
                           </td>
                            <td>
                                ' . $faryMySQLResult['photo'] . '
                           </td>
                            <td>&nbsp;
                                ' . Categories::display($faryMySQLResult['uid']) . '
                           </td>
                        </tr>';

            }
            return $fstrDataReturned;
        }

    }


    /**
     * Display output.
     */
    $display .= '
        <table border="0" cellpadding="5" cellspacing="0" width="100%">
            <tr>
                <td colspan="4" align="center">
                    <b>Names with the =MF= tag are clan members, the others are either
                        applying for membership or friends of Midnight Force.</b>
                </td>
            </tr>
            <tr>
                <td>
                    Username
                </td>
                <td>
                    Fullname
                </td>
                <td>
                    Photo
                </td>
                <td>
                    Games
                </td>
            </tr>';

    $display .= Categories::processMembers();    // Display =MF= Members.
    $display .= Categories::processNonMembers(); // Display Non =MF= Members.
    $display .= '</table>';

    echo $display;

?>
