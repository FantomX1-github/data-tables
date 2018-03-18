<?php
/**
 * Status.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           21.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;

/**
 * Status column control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Status extends Column
{
	/**
	 * Define column data type for DataTables
	 */
	const COLUMN_DATA_TYPE = 'string';

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 * @param string|NULL $insertBefore
	 */
	public function __construct(Components\Control $parent, string $name, string $label, string $insertBefore = NULL)
	{
		parent::__construct($parent, $name, $label, $insertBefore);

		$this->setType(self::COLUMN_DATA_TYPE);

		// Disable specific column functions
		$this->disableSortable();
		$this->disableSearchable();
	}

	/**
	 * {@inheritdoc}
	 */
	public function enableSortable()
	{
		throw new Exceptions\NotSupportedException('Sortable function is not allowed for action column.');
	}

	/**
	 * {@inheritdoc}
	 */
	public function enableSearchable()
	{
		throw new Exceptions\NotSupportedException('Searchable function is not allowed for action column.');
	}
}
