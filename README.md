GeekLog-Categories-Plugin
=========================

I wrote a plug-in for Geeklog a while back for some friends over at www.midnightforce.com  This is for version for Geeklog 1.4.1 

Things still left to do: 
    Search page needs cleaning up. Make it look better and display some more info. 
    Upload graphics.

Changes 1.09
    Fixed URL for Roster page.
    Changed display order on Admin edutuser page.
	
Changes 1.08
    Fixed CVS errors. 
    Updated plug-in to work with geeklog 1.4.1

Changes 1.07
    Changed Send Email button on admin page. Eamil address are auto loaded into the BCC field. 
        Due to email requirements the default TO: email address is set in the language file 
        under "adminpage.mailtoaddress".
    Changed database. Added new column for public or private categories.
    Added code to make change in the update script.
    Added public and private categories. User is unable to add/remove himself from a private category.
    Categories are private by default.
    New link on admin page. "Edit Users Categories" this page allows for editing another users categories.
        You may add users to both public and private categories from this page. Category must be active to
        appear on this page.
    Update to language file english.php

Changes 1.06
    Fix to roster page now include using: 
        return require_once $_CONF['path_html'] . 'categories/roster.inc.php';

Changes 1.05
    Roster can now be included in static pages by using: 
        require_once $_CONF['path_html'] . 'categories/roster.inc.php';
    Include page for roster/static pages. 

Changes 1.04
    Working Search 
    Mail to Button (If user does not have a Real Name their username is used.) 
    Admin page add new success message. 
    Admin page delete success message. 
    Admin page deactivate success message. 
    Admin page activate success message. 
    Admin page search success message. 
    More text added to language file. 
    Database fields for category Name and Description have been increased to 250. 
    Forms are now validated. Double quotes are now replaced with single quotes and then escaped for the database. 
    Buttons were changed to links to allow the search feature to work. 
    Html has been allowed in both the Name and Description text boxes when adding/editing a category. 
    Max length has been added to add/edit forums for all three text boxes. 
 
Changes 1.03
    Edit now works. 
    All text written to an HTML output is stored in the language file. 
    Added upgrade script to update geek-log with changes. 
    Added Example Text for new Categories 