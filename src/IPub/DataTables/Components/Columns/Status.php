<?php
/**
 * Status.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Columns
 * @since		5.0
 *
 * @date		21.10.14
 */

namespace IPub\DataTables\Components\Columns;

class Status extends Settings
{
	/**
	 * {@inheritdoc}
	 */
	protected $sortable = FALSE;

	/**
	 * {@inheritdoc}
	 */
	protected $searchable = FALSE;

	/**
	 * {@inheritdoc}
	 */
	protected $type = 'string';
}