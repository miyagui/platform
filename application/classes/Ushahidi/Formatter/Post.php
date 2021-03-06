<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi API Formatter for Post
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

use Ushahidi\Core\Tool\Formatter;

class Ushahidi_Formatter_Post extends Ushahidi_Formatter_API
{
	protected function get_field_name($field)
	{
		$remap = [
			'form_id' => 'form',
			];

		if (isset($remap[$field])) {
			return $remap[$field];
		}

		return parent::get_field_name($field);
	}

	protected function format_form_id($form_id)
	{
		return $this->get_relation('forms', $form_id);
	}

	protected function format_tags($tags)
	{
		$output = [];
		foreach ($tags as $tagid)
		{
			$output[] = $this->get_relation('tags', $tagid);
		}

		return $output;
	}
}
