<?php namespace JFusion\Plugins\mediawiki;
/**
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mediawiki
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

use JFusion\Factory;
use JFusion\Framework;

use JFusion\User\Groups;
use Joomla\Language\Text;

use Psr\Log\LogLevel;

use Exception;
use stdClass;

/**
 * JFusion Admin Class for mediawiki 1.1.x
 * For detailed descriptions on these functions please check Plugin_Admin
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mediawiki
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class Admin extends \JFusion\Plugin\Admin
{
	/**
	 * @var $helper Helper
	 */
	var $helper;

    /**
     * @return string
     */
    function getTablename()
    {
        return 'user';
    }

    /**
     * @param string $softwarePath
     *
     * @return array
     */
    function setupFromPath($softwarePath)
    {
        $myfile = $softwarePath . 'LocalSettings.php';
        $params = array();
         //try to open the file
        if ( !file_exists($myfile) ) {
            Framework::raise(LogLevel::WARNING, Text::_('WIZARD_FAILURE') . ': ' . $myfile . ' ' . Text::_('WIZARD_MANUAL'), $this->getJname());
	        return false;
        } else {
            $wgDBserver = $wgDBtype = $wgDBname = $wgDBuser = $wgDBpassword = $wgDBprefix = '';

            $paths = $this->helper->includeFramework($softwarePath);
            $IP = $softwarePath;
            foreach($paths as $path) {
                include($path);
            }

            $params['database_host'] = $wgDBserver;
            $params['database_type'] = $wgDBtype;
            $params['database_name'] = $wgDBname;
            $params['database_user'] = $wgDBuser;
            $params['database_password'] = $wgDBpassword;
            $params['database_prefix'] = $wgDBprefix;
            $params['source_path'] = $softwarePath;

            if (!empty($wgCookiePrefix)) {
                $params['cookie_name'] = $wgCookiePrefix;
            }
        }
        return $params;
    }

    /**
     * Returns the a list of users of the integrated software
     *
     * @param int $limitstart start at
     * @param int $limit number of results
     *
     * @return array
     */
    function getUserList($limitstart = 0, $limit = 0)
    {
	    try {
		    // initialise some objects
		    $db = Factory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->select('user_name as username, user_email as email')
			    ->from('#__user');

		    $db->setQuery($query, $limitstart, $limit);
		    $userlist = $db->loadObjectList();

		    return $userlist;
	    } catch (Exception $e) {
		    Framework::raise(LogLevel::ERROR, $e, $this->getJname());
		    $userlist = array();
	    }
	    return $userlist;
    }

    /**
     * @return int
     */
    function getUserCount()
    {
	    try {
		    //getting the connection to the db
		    $db = Factory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->select('count(*)')
			    ->from('#__user');

		    $db->setQuery($query);

		    //getting the results
		    return $db->loadResult();
	    } catch (Exception $e) {
		    Framework::raise(LogLevel::ERROR, $e, $this->getJname());
		    return 0;
	    }
    }

    /**
     * @return array
     */
    function getUsergroupList()
    {
        $wgGroupPermissions = $this->helper->getConfig('wgGroupPermissions');

        $usergrouplist = array();
        foreach($wgGroupPermissions as $key => $value) {
        	if ($key != '*') {
 				$group = new stdClass;
        		$group->id = $key;
        		$group->name = $key;
        		$usergrouplist[] = $group;
        	}
        }
        return $usergrouplist;
    }

    /**
     * @return string|array
     */
    function getDefaultUsergroup()
    {
	    $usergroups = Groups::get($this->getJname(), true);
	    if ($usergroups !== null) {
		    return $usergroups;
	    } else {
		    return '';
	    }
    }

    /**
     * @return bool
     */
    function allowRegistration()
    {
		$wgGroupPermissions = $this->helper->getConfig('wgGroupPermissions');
        if (is_array($wgGroupPermissions) && $wgGroupPermissions['*']['createaccount'] == true) {
            return true;
        } else {
            return false;
        }
    }
   
	/*
	 * do plugin support multi usergroups
	 * return UNKNOWN for unknown
	 * return JNO for NO
	 * return JYES for YES
	 * return ... ??
	 */
    /**
     * @return string
     */
    function requireFileAccess()
	{
		return 'JYES';
	}

	/**
	 * @return bool do the plugin support multi instance
	 */
	function multiInstance()
	{
		return false;
	}

	/**
	 * do plugin support multi usergroups
	 *
	 * @return bool
	 */
	function isMultiGroup()
	{
		return true;
	}
}

