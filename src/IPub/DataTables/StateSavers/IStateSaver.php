<?php
/**
 * IStateSaver.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     StateSavers
 * @since          1.0.0
 *
 * @date           18.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\StateSavers;

/**
 * DataTables state saver interface
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     StateSavers
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IStateSaver
{
	/**
	 * Store JSON data to database
	 *
	 * @param string $name
	 * @param $data
	 *
	 * @return void
	 */
	function saveState(string $name, $data) : void;

	/**
	 * Load JSON data from database
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	function loadState(string $name);
}
