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
 * @var   string  $type      Type of call
 * @var   array   $rules     Array of rules.
 * @var   object  $renderer  Array of rules.
 */

$counter = 0;
$sumRules = count($rules);

foreach ($rules as $rule) :
	$parent = '';
	$counter++;

	if ($counter == 1) : ?>
	<ul class="nav-child">
	<?php endif;

	if (!empty($rule->header)) :

		if (!empty($rule->rules)) :
			$parent = ' parent deeper';
		endif; ?>

		<li class="level<?php echo $rule->level - 2 . ' ' . $rule->name . $parent; ?>">
			<a class="anchor" href="#<?php echo $type . '_' . $rule->name; ?>">
				<?php echo strip_tags($rule->header); ?>
			</a>
			<?php if (!empty($rule->rules)) :
				$this->setLayoutId('skiplinks');

				echo $this->sublayout(
					'child',
					array(
						'type'     => $type,
						'renderer' => $renderer,
						'rules'    => $rule->rules,
					)
				);
			endif; ?>
		</li>

	<?php endif;

	if ($counter == $sumRules) : ?>
	</ul>
	<?php endif;
endforeach; ?>
