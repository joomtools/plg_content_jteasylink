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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Uri\Uri;

/**
 * Class plgContentJteasylink
 *
 * Insert and cache law information from easyrechtssicher.de
 *
 * @package      Joomla.Plugin
 * @subpackage   Content.jteasylink
 * @since        1.0.0
 */
class PlgContentJteasylink extends JPlugin
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

		$debug = $this->params->get('debug', 0) == '0' ? true : false;

		if ($debug)
		{
			Profiler::getInstance('JT - Easylink (' . $context . ')')->setStart($startTime);
		}

		$useCss = filter_var(
			$this->params->get('usecss', 1),
			FILTER_VALIDATE_BOOLEAN
		);

		$cachePath  = JPATH_CACHE . '/plg_content_jteasylink';
		$cacheTime  = (int) $this->params->get('cachetime', 60) * 60;
		$cacheOnOff = filter_var(
			$this->params->get('cache', 1),
			FILTER_VALIDATE_BOOLEAN
		);

		$methode     = $this->params->get('methode', 'html');
		$apiKey      = trim($this->params->get('apikey', ''));
		$defaultLang = $this->params->get('language', 'de');
		$activeLang  = strtolower(substr(Factory::getLanguage()->getTag(), 0, 2));
		$language    = in_array($activeLang, $this->supportedLangguages) ? $activeLang : $defaultLang;
		$domain      = Uri::getInstance()->getHost();

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
			// Pause prevent DB#1062 Duplicate entry database error (primary key)
			sleep(1);

			$callType = trim(strtolower($plgCalls[1][$key][0]));
			$fileName = $callType . '.html';

			if (!empty($plgCalls[1][$key][1]))
			{
				// Get preferred language from plugin call
				$callLang = trim(strtolower($plgCalls[1][$key][1]));

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

			$cacheFile = $cachePath . '/' . $language . '/' . $fileName;

			if (!Folder::exists(dirname($cacheFile)))
			{
				Folder::create(dirname($cacheFile));
			}

			if ($useCacheFile = File::exists($cacheFile))
			{
				$useCacheFile = $this->getFileTime($cacheFile, $cacheTime);
			}

			if($useCacheFile === false)
			{
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
					$buffer = $this->getJson($cacheFile, $easylawServerUrl);
				}

				if (!empty($buffer))
				{
					$this->setCache($cacheFile, $buffer);
				}
				else
				{
					if ($cacheOnOff === true)
					{
						$buffer = $this->getCache($cacheFile);
					}
				}

			}
			else
			{
				$buffer = $this->getCache($cacheFile);
			}

			$article->text = str_replace($plgCall, $buffer, $article->text);
		}

		if ($methode == 'json' && $useCss && !self::$cssSet)
		{
			$css = $this->params->get('css');

			Factory::getDocument()->addStyleDeclaration($css);

			self::$cssSet = true;
		}


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
					$params = trim($matches[3][$key]);
				}

				if (empty($matches[3][$key]) && !empty($matches[5][$key]))
				{
					$params = trim($matches[5][$key]);
				}

				$options[$key] = explode(',', $params);

				if (empty($options[$key][0]))
				{
					$options[$key][0] = 'dse';
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
	 * @param   string  $cacheFile         Cachefile with absolute path
	 * @param   string  $easylawServerUrl  EasyLaw Server-URL for API-Call
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	private function getHtml($cacheFile, $easylawServerUrl)
	{
		$http = JHttpFactory::getHttp();
		$data = $http->get($easylawServerUrl);

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
	 * @param   string  $cacheFile         Cachefile with absolute path
	 * @param   string  $easylawServerUrl  EasyLaw Server-URL for API-Call
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	private function getJson($cacheFile, $easylawServerUrl)
	{
		$error   = false;
		$message = [];

		$http   = JHttpFactory::getHttp();
		$data   = $http->get($easylawServerUrl);
		$result = json_decode($data->body);

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
//			file_put_contents($cacheFile . '.json', $data->body);

			$cTag = $this->params->get('ctag', 'section');
			$html = '<' . $cTag . ' class="jteasylink">';
			$html .= $this->formatRules($result->rules);
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
			);

			$ruleContent = str_replace($search, $replace, $html);
			$html = nl2br($ruleContent);

			return $html;
		}

		$this->setErrorMessage($cacheFile, (int) $data->code, $message);

		return '';
	}

	/**
	 * Create HTML from rules
	 *
	 * @param   array  $rules  Array of rules from $this->getJason()
	 *
	 * @return   string
	 * @since    1.0.0
	 */
	private function formatRules(array $rules)
	{
		$html      = '';
		$_hTag     = (int) $this->params->get('htag', '1');
		$container = $this->params->get('ctag', 'section');

		foreach ($rules as $rule)
		{
			$cTag  = ($rule->level > 2) ? 'div' : $container;
			$level = (int) $rule->level - 1;
			$level = ($level < 1) ? 1 : $level;
			$hTag  = $_hTag + (int) $rule->level - 2;
			$hTag  = ($hTag < 1) ? 1 : $hTag;
			$hTag  = ($hTag > 6) ? 6 : $hTag;

			$html .= '<' . $cTag . ' id="' . $rule->name
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
				$html .= $this->formatRules($rule->rules);
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
			return @file_get_contents($cacheFile);
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
		JFile::write($cacheFile, $html);
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
}
