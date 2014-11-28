<?php
/**
 * IStateSaver.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	StateSavers
 * @since		5.0
 *
 * @date		18.10.14
 */

namespace IPub\DataTables\StateSavers;

interface IStateSaver
{
	/**
	 * Store JSON data to database
	 *
	 * @param $name
	 * @param $data
	 *
	 * @return $this
	 */
	public function saveState($name, $data);

	/**
	 * Load JSON data from database
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function loadState($name);
}