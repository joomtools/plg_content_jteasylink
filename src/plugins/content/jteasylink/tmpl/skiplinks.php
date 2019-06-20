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

extract($displayData);

/**
 * Layout variables
 * ---------------------
 * @var   string  $type       Type of call
 * @var   string  $container  Container tag for skiplinks.
 * @var   array   $rules      Array of rules.
 */

?>
<<?php echo $container; ?> class="sidebar-nav jteasylink-skiplinks" role="navigation">
<ul class="nav">
	<?php foreach ($rules as $rule) :
		$parent = '';

		if (!empty($rule->rules)) :
			$parent = ' parent deeper';
		endif; ?>
		<li class="level1 <?php echo $rule->name . $parent; ?>">
			<a class="anchor" href="#<?php echo $type . '_' . $rule->name; ?>">
				<?php echo strip_tags($rule->header); ?>
			</a>
			<?php if (!empty($rule->rules)) :
				$childData = array(
					'type'     => $type,
					'renderer' => $this,
					'rules'    => $rule->rules,
				);
				echo $this->sublayout('child', $childData);
			endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
</<?php echo $container; ?>>
