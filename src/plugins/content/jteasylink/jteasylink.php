<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jteasylink
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2019 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Class plgContentJteasylink
 *
 * Insert and cache law information from easyrechtssicher.de
 *
 * @package      Joomla.Plugin
 * @subpackage   Content.jteasylink
 * @since        1.0.0
 */
class PlgContentJteasylink extends CMSPlugin
{
	/**
	 * Set css once
	 *
	 * @var     boolean
	 * @since   1.0.0
	 */
	static $cssSet = false;
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var     boolean
	 * @since   1.0.0
	 */
	protected $autoloadLanguage = true;
	/**
	 * Global application object
	 *
	 * @var     JApplication
	 * @since   1.0.0
	 */
	protected $app;
	/**
	 * Supported languages from Easyrechtssicher
	 *
	 * @var     array
	 * @since   1.0.0
	 */
	private $supportedLangguages = [
		'de',
		'en',
	];
	/**
	 * Collection point for error messages
	 *
	 * @var     array
	 * @since   1.0.0
	 */
	private $message = [];
	/**
	 * Document calls
	 *
	 * @var     array
	 * @since   1.0.0
	 */
	private $documentCalls = [
		'dse' => 'PLG_CONTENT_JTEASYLINK_CALL_DSE_LABEL',
		'imp' => 'PLG_CONTENT_JTEASYLINK_CALL_IMP_LABEL',
		'agb' => 'PLG_CONTENT_JTEASYLINK_CALL_AGB_LABEL',
	];
	/**
	 * Options for HttpFactory request
	 *
	 * @var     array
	 * @since   1.0.1
	 */
	private $options = [
		'userAgent' => 'JT-Easylink Joomla Plugin!',
	];


	/**
	 * onContentPrepare
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @param   mixed    $params   The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return   void
	 * @since    1.0.0
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run in administration Panel or when the content is being indexed
		if (strpos($article->text, '{jteasylink') === false
			|| $this->app->isClient('administrator') === true
			|| $context == 'com_finder.indexer'
			|| $this->app->input->getCmd('layout') == 'edit')
		{
			return;
		}
		// Set starttime for process total time
		$startTime = microtime(1);

		$debug  = $this->params->get('debug', 0) == '0' ? true : false;
		$useCss = filter_var(
			$this->params->get('usecss', 1),
			FILTER_VALIDATE_BOOLEAN
		);

		$cacheOnOff = filter_var(
			$this->params->get('cache', 1),
			FILTER_VALIDATE_BOOLEAN
		);

		$cachePath   = JPATH_CACHE . '/_jteasylink';
		$cacheTime   = (int) $this->params->get('cachetime', 60) * 60;
		$methode     = $this->params->get('methode', 'html');
		$apiKey      = trim($this->params->get('apikey', ''));
		$defaultLang = $this->params->get('language', 'de');
		$activeLang  = strtolower(substr(Factory::getLanguage()->getTag(), 0, 2));
		$language    = in_array($activeLang, $this->supportedLangguages) ? $activeLang : $defaultLang;
		$domain      = Uri::getInstance()->getHost();

		if ($debug)
		{
			Profiler::getInstance('JT - Easylink (' . $context . ')')->setStart($startTime);

			$this->app->enqueueMessage(
				Text::_('PLG_CONTENT_JTEASYLINK_WARNING_DEBUG_IS_ON'),
				'warning'
			);
		}

		if (empty($apiKey))
		{
			$this->app->enqueueMessage(
				Text::_('PLG_CONTENT_JTEASYLINK_WARNING_NO_APIKEY'),
				'error'
			);

			return;
		}

		if(strlen($apiKey) == 26)
		{
			$apiKey = substr($apiKey, 0, -1);
		}

		// Add identifier for usage of Joomla!
		$apiKey .= 'J';

		if(strlen($apiKey) != 26)
		{
			$this->app->enqueueMessage(
				Text::_('PLG_CONTENT_JTEASYLINK_WRONG_APIKEY'),
				'error'
			);

			return;
		}

		if ($cacheTime < 3600)
		{
			$cacheTime = 3600;
		}

		if ($cacheOnOff === false)
		{
			$cacheTime = 0;
		}

		$plgCalls = $this->getPlgCalls($article->text);

		foreach ($plgCalls[0] as $key => $plgCall)
		{
			$skiplinks = false;
			$fileName = '';

			if (trim($plgCalls[1][$key][0]) == 'skiplinks')
			{
				$skiplinks = true;
				$fileName = 'skiplinks_';
				array_shift($plgCalls[1][$key]);
			}

			$callType = trim($plgCalls[1][$key][0]);
			$fileName .= $callType . '.html';

			if (!empty($plgCalls[1][$key][1]))
			{
				// Get preferred language from plugin call
				$callLang = trim($plgCalls[1][$key][1]);

				// Validate if language is supported
				$supportedLangguages = in_array($callLang, $this->supportedLangguages) ? true : false;

				// Set site language or default language if preferred language is not supported
				$language = $supportedLangguages ? $callLang : $language;

				// Define error message if language is not supported
				if ($supportedLangguages === false)
				{
					$this->message['warning'][] = Text::sprintf(
						'PLG_CONTENT_JTEASYLINK_WARNING_LANGUAGE',
						$callLang,
						$language
					);
				}
			}

			$keyName   = $language . '_' . File::stripExt($fileName);
			$key       = md5(serialize($keyName));
			$cacheFile = $cachePath . '/' . $key . '-cache-' . $keyName . '.php';

			if (!Folder::exists(dirname($cacheFile)))
			{
				Folder::create(dirname($cacheFile));
			}

			if ($useCacheFile = File::exists($cacheFile))
			{
				$useCacheFile = $this->getFileTime($cacheFile, $cacheTime);
			}

			if ($debug)
			{
				$useCacheFile = false;
			}

			if($useCacheFile === false)
			{
				// Pause prevent DB#1062 Duplicate entry database error (primary key)
				sleep(1);

				$easylawServerUrl = 'https://er' . $callType . '.net/'
					. $apiKey . '/'
					. $language . '/'
					. $domain . '.'
					. $methode;

				if ($methode == 'html')
				{
					$buffer = $this->getHtml($cacheFile, $easylawServerUrl);
				}
				else
				{
					$buffer = $this->getJson($cacheFile, $easylawServerUrl, $skiplinks);
				}

				if (!empty($buffer) && $debug === false)
				{
					$this->setCache($cacheFile, $buffer);
				}
			}
			else
			{
				$buffer = $this->getCache($cacheFile);
			}

			$article->text = str_replace($plgCall, $buffer, $article->text);
		}

		if ($methode == 'json' && $useCss && self::$cssSet === false)
		{
			HTMLHelper::_('stylesheet', 'plg_content_jteasylink/jteasylink.min.css', array('version' => 'auto', 'relative' => true));

			self::$cssSet = true;
		}

		$this->removeJoomlaCache($context);

		if ($debug)
		{
			if (!empty($this->message))
			{
				foreach ($this->message as $type => $msgs)
				{
					if ($type == 'error')
					{
						$msgs[] = Text::_('PLG_CONTENT_JTEASYLINK_ERROR_CHECKLIST');
					}

					$msg = implode('<br />', $msgs);
					$this->app->enqueueMessage($msg, $type);
				}
			}

			$this->app->enqueueMessage(
				Profiler::getInstance('JT - Easylink (' . $context . ')')->mark('Verarbeitungszeit'),
				'info'
			);
		}
	}

	/**
	 * Find all plugin call's in $text and return them as array
	 *
	 * @param   string  $text  Text with plugin call's
	 *
	 * @return   array  All matches found in $text
	 * @since    1.0.0
	 */
	private function getPlgCalls($text)
	{
		$regex = '@(<(\w*+)[^>]*>)\s?{jteasylink([^}].*)?}.*(</\2>)|{jteasylink([^}]?.*)?}@iU';
		$p1    = preg_match_all($regex, $text, $matches);

		if ($p1)
		{
			// Exclude <code/> and <pre/> matches
			$code = array_keys($matches[1], '<code>');
			$pre  = array_keys($matches[1], '<pre>');

			if (!empty($code) || !empty($pre))
			{
				array_walk($matches,
					function (&$array, $key, $tags) {
						foreach ($tags as $tag)
						{
							if ($tag !== null && $tag !== false)
							{
								unset($array[$tag]);
							}
						}
					}, array_merge($code, $pre)
				);
			}

			$options = [];

			foreach ($matches[0] as $key => $value)
			{
				$params = 'dse';

				if (!empty($matches[3][$key]))
				{
					$params = trim(strtolower($matches[3][$key]));
				}

				if (empty($matches[3][$key]) && !empty($matches[5][$key]))
				{
					$params = trim(strtolower($matches[5][$key]));
				}

				$options[$key] = explode(',', $params);

				if (empty($options[$key][0]))
				{
					$options[$key][0] = 'dse';
				}

				if (empty($options[$key][1]) && $options[$key][0] == 'skiplinks')
				{
					$options[$key][1] = 'dse';
				}
			}

			return array(
				$matches[0],
				$options,
			);
		}

		return array();
	}

	/**
	 * Check to see if the cache file is up to date
	 *
	 * @param   string  $file       Cachefile with absolute path
	 * @param   int     $cacheTime  Cachetime setup in params
	 *
	 * @return   bool  true if cached file is up to date
	 * @since    1.0.0
	 */
	private function getFileTime($file, $cacheTime)
	{
		$time      = time();
		$fileTime  = filemtime($file);

		$control = $time - $fileTime;

		if ($control >= $cacheTime)
		{
			return false;
		}

		return true;
	}

	/**
	 * Load HTML file from Server or get cached file
	 *
	 * @param   string  $cacheFile          Cachefile with absolute path
	 * @param   string  $easylinkServerUrl  EasyLaw Server-URL for API-Call
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	private function getHtml($cacheFile, $easylinkServerUrl)
	{
		$options = new Registry($this->options);
		$http    = HttpFactory::getHttp($options);
		$data    = $http->get($easylinkServerUrl);

		if ($data->code >= 200 && $data->code < 400)
		{
			$error = !preg_match('@<body[^>]*>(.*?)<\/body>@is' ,$data->body, $matches);

			if ($error === false)
			{
				$cTag = $this->params->get('ctag', 'section');
				$html = '<' . $cTag . ' class="jteasylink">';
				$html .= $matches[1];
				$html .= '</' . $cTag . '>';

				return $html;
			}
		}

		$this->setErrorMessage($cacheFile, (int) $data->code, array($data->body));

		return '';
	}

	/**
	 * Load JSON file from Server or get cached file
	 *
	 * @param   string  $cacheFile          Cachefile with absolute path
	 * @param   string  $easylinkServerUrl  EasyLaw Server-URL for API-Call
	 * @param   bool    $skiplinks
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	private function getJson($cacheFile, $easylinkServerUrl, $skiplinks = false)
	{
		$error   = false;
		$message = [];
		$options = new Registry($this->options);
		$http    = HttpFactory::getHttp($options);
		$data    = $http->get($easylinkServerUrl);
		$result  = json_decode($data->body);

		if ($result === null)
		{
			$result = $data->body;
		}

		if (!is_object($result)
			|| (is_object($result) && empty($result->ok) && $result->ok !== 0))
		{
			$message[] = $result;
			$error     = true;
		}

		if (is_object($result) && $result->ok === 0)
		{
			$message[] = $result->errMsg;
			$error     = true;
		}

		if ($error === false)
		{
			if ($skiplinks)
			{
				return $this->createSkiplinksContent($result);
			}

			return $this->createJsonContent($result);
		}

		$this->setErrorMessage($cacheFile, (int) $data->code, $message);

		return '';
	}

	/**
	 * Create HTML from rules
	 *
	 * @param   string  $type       Type of call
	 * @param   array   $rules      Array of rules
	 * @param   int     $header     Header-Tag numner
	 * @param   string  $container  Container-Tag
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	public function formatRules($type, $rules, $header, $container)
	{
		$html = '';

		foreach ($rules as $rule)
		{
			$cTag  = ($rule->level > 2) ? 'div' : $container;
			$level = (int) $rule->level - 1;
			$level = ($level < 1) ? 1 : $level;
			$hTag  = $header + (int) $rule->level - 2;
			$hTag  = ($hTag < 1) ? 1 : $hTag;
			$hTag  = ($hTag > 6) ? 6 : $hTag;

			$html .= '<' . $cTag . ' id="' . $type . '_' . $rule->name
				. '" class="' . $rule->name . ' level' . $level . '">';

			if (!empty($rule->header))
			{
				$html .= '<h' . $hTag . '>'
					. strip_tags($rule->header)
					. '</h' . $hTag . '>';
			}

			$html .= '<p>' . $rule->content . '</p>';

			if (!empty($rule->rules) && is_array($rule->rules))
			{
				$html .= $this->formatRules($type, $rule->rules, $header, $container);
			}

			$html .= '</' . $cTag . '>';
		}

		return $html;
	}

	/**
	 * Get content from cache file
	 *
	 * @param   string  $cacheFile  Path to cache file
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	private function getCache($cacheFile)
	{
		if (file_exists($cacheFile))
		{
			$cache = @file_get_contents($cacheFile);

			return str_replace('<?php die("Access Denied"); ?>', '', $cache);
		}

		return '';
	}

	/**
	 * Write content to cache file
	 *
	 * @param   string  $cacheFile  Cachefile with absolute path
	 * @param   string  $html       Content to write to cache file
	 *
	 * @return   void
	 * @since    1.0.0
	 */
	private function setCache($cacheFile, $html)
	{
		JFile::delete($cacheFile);
		$cache = '<?php die("Access Denied"); ?>' . $html;
		JFile::write($cacheFile, $cache);
	}

	/**
	 * Set message to error buffer
	 *
	 * @param   string  $cacheFile   Cachefile with absolute path
	 * @param   int     $statusCode  Response status code
	 * @param   array   $message     Error message
	 *
	 * @return   void
	 * @since    1.0.0
	 */
	private function setErrorMessage($cacheFile, $statusCode, array $message)
	{
		$fileName     = basename($cacheFile);
		$documentCall = File::stripExt($fileName);

		if (!empty($this->documentCalls[$documentCall]))
		{
			$documentCall = Text::_($this->documentCalls[$documentCall]);
		}

		$this->message['error'][] = Text::sprintf(
			'PLG_CONTENT_JTEASYLINK_ERROR_NO_CACHE_SERVER',
			$documentCall,
			$statusCode,
			implode('<br />', $message)
		);
	}

	/**
	 * Create content from Json request
	 *
	 * @param   object  $json  Request from easyrechtssicher.de
	 *
	 * @return   string
	 * @since    1.0.3
	 */
	private function createJsonContent($json)
	{
		// file_put_contents($cacheFile . '.json', $data->body);

		$cTag = $this->params->get('ctag', 'section');
		$htag = (int) $this->params->get('htag', '1');
		$html = '<' . $cTag . ' class="jteasylink ' . $json->object . '">';
		$html .= $this->formatRules($json->object, $json->rules, $htag, $cTag);
		$html .= '</' . $cTag . '>';

		$search  = array(
			"\r\n \r\n",
			"\r\n\r\n",
			"\r\n <ul",
			"\r\n<ul",
			"\r\n    <li>",
			"\r\n </ul>",
			"\r\n<li>",
			"\r\n</ul>",
			"<p>\r\n",
			"<p><div",
			"</div></p>",
			"<p></p>",
			"<br /><br />",
		);
		$replace = array(
			'</p><p>',
			'</p><p>',
			'</p><ul',
			'</p><ul',
			'<li>',
			'</ul><p>',
			'<li>',
			'</ul><p>',
			'<p>',
			'<div',
			'</div>',
			'',
			'</p><p>',
		);

		$ruleContent = str_replace($search, $replace, $html);
		$html        = nl2br($ruleContent);

		return $html;
	}

	/**
	 * Create skiplinks from Json request
	 *
	 * @param   object  $json  Request from easyrechtssicher.de
	 *
	 * @return   string
	 * @since    1.0.3
	 * @throws   \Exception
	 */
	private function createSkiplinksContent($json)
	{
		$theme             = Factory::getApplication()->getTemplate();
		$themeOverridePath = JPATH_THEMES . '/' . $theme . '/html/plg_' . $this->_type . '_' . $this->_name;
		$layoutBasePath    = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/tmpl';

		$renderer = new FileLayout('skiplinks', $layoutBasePath, array('component' => 'none'));
		$renderer->addIncludePath($themeOverridePath);

		$displayData = array(
			'type'      => $json->object,
			'container' => $this->params->get('skiplinksCtag', 'nav'),
			'rules'     => $json->rules,
		);

		$content = $renderer->render($displayData);
		// file_put_contents($cacheFile . '.json', $data->body);

		return $content;
	}

	/**
	 * Remove caching if plugin is called
	 *
	 * @param   string  $context
	 *
	 * @return   void
	 * @since    1.0.3
	 */
	private function removeJoomlaCache($context)
	{
		$cachePagePlugin = PluginHelper::isEnabled('system', 'cache');
		$cacheIsActive   = Factory::getConfig()->get('caching', 0) != 0
			? true
			: false;

		if (!$cacheIsActive && !$cachePagePlugin)
		{
			return;
		}

		$key         = (array) JUri::getInstance()->toString();
		$key         = md5(serialize($key));
		$group       = strstr($context, '.', true);
		$cacheGroups = array();

		if($cacheIsActive)
		{
			$cacheGroups = array(
				$group        => 'callback',
				'com_modules' => '',
				'com_content' => 'view',
			);
		}

		if($cachePagePlugin)
		{
			$cacheGroups['page'] = 'callback';
		}

		foreach ($cacheGroups as $group => $handler)
		{
			$cache = JFactory::getCache($group, $handler);
			$cache->cache->remove($key);
			$cache->cache->setCaching(false);
		}
	}
}
