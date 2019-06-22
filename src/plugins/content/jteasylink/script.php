<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jteasylink
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2019 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

defined('_JEXEC') or die;

/**
 * Script file of Joomla CMS
 *
 * @since  1.0.0
 */
class PlgContentJteasylinkInstallerScript
{
	/**
	 * Extension script constructor.
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		// Define the minumum versions to be supported.
		$this->minimumJoomla = '3.9';
		$this->minimumPhp    = '5.6';
	}

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string      $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller  $installer  The class calling this method
	 *
	 * @return   boolean  True on success
	 * @since    1.0.0
	 * @throws   \Exception
	 */
	public function preflight($action, $installer)
	{
		$app = JFactory::getApplication();
		JFactory::getLanguage()->load('plg_content_jteasylink', dirname(__FILE__));

		if (version_compare(PHP_VERSION, $this->minimumPhp, 'lt'))
		{
			$app->enqueueMessage(JText::_('PLG_CONTENT_JTEASYLINK_MINPHPVERSION'), 'error');

			return false;
		}

		if (version_compare(JVERSION, $this->minimumJoomla, 'lt'))
		{
			$app->enqueueMessage(JText::_('PLG_CONTENT_JTEASYLINK_MINJVERSION'), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string      $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller  $installer  The class calling this method
	 *
	 * @return   boolean  True on success
	 * @since    1.0.3
	 */
	public function postflight($action, $installer)
	{
		$cache = JFactory::getCache();
		$cache->cache->clean('plg_content_jteasylink');
		$cache->cache->clean('_jteasylink');

		return true;
	}
}
