<?php
/**
 * IControl.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Components
 * @since		5.0
 *
 * @date		12.03.14
 */

namespace IPub\DataTables\Components;

interface IControl
{
	/**
	 * @param NULL $templateFile
	 *
	 * @return Control
	 */
	public function create($templateFile = NULL);
}