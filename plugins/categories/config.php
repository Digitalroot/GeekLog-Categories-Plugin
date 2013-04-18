<?php

    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

    /**
     * Config File
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
     * @version     CVS: $Id: config.php,v 1.3 2007/03/28 22:09:29 nkd Exp $
     * @since       File available since Release 1.03
     *
     */

    // Set Plugin Table Prefix the Same as Geeklogs
    $_ST_table_prefix = $_DB_table_prefix;

    // Add Stats Plugin tables to $_TABLES array
    $_TABLES['categories_List']         = $_ST_table_prefix . 'categories_list';
    $_TABLES['categories_LinkToUsers']  = $_ST_table_prefix . 'categories_linktousers';

?>
