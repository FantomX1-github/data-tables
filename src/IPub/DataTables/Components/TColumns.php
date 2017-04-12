<?php
/**
 * TColumns.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 * @since          1.0.0
 *
 * @date           18.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Components;

use Nette\ComponentModel;

use IPub\DataTables\Columns;
use IPub\DataTables\Exceptions;

/**
 * DataTables columns trait
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method ComponentModel\IComponent|NULL getComponent(string $name, bool $need = TRUE)
 */
trait TColumns
{
	/**
	 * Create column component
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @param string|NULL $width
	 * @param string|NULL $insertBefore
	 *
	 * @return Columns\IColumn
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateColumnException
	 */
	public function addColumn(string $type, string $name, string $label, string $width = NULL, string $insertBefore = NULL) : Columns\IColumn
	{
		if (!in_array($type, [
			Columns\IColumn::TYPE_ACTION,
			Columns\IColumn::TYPE_DATE,
			Columns\IColumn::TYPE_IMAGE,
			Columns\IColumn::TYPE_NUMBER,
			Columns\IColumn::TYPE_STATUS,
			Columns\IColumn::TYPE_EMAIL,
			Columns\IColumn::TYPE_LINK,
			Columns\IColumn::TYPE_TEXT,
		], TRUE)
		) {
			throw new Exceptions\InvalidArgumentException('Invalid column type given.');
		}

		if ($this->columnExists($name)) {
			throw new Exceptions\DuplicateColumnException(sprintf('Column "%s" already exists.', $name));
		}

		// Create column class name
		$type = '\\IPub\\DataTables\\Columns\\' . $type;

		if (!class_exists($type)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Invalid column type. Class %s for column not found.', $type));
		}

		/** @var Columns\IColumn $column */
		$column = new $type($this, $name, $label, $insertBefore);

		if ($width !== NULL) {
			$column->setWidth($width);
		}

		return $column;
	}

	/**
	 * @param string $name
	 * @param string|NULL $label
	 * @param string|NULL $width
	 * @param int|NULL $truncate
	 * @param string|NULL $insertBefore
	 *
	 * @return Columns\Text
	 */
	public function addColumnText(string $name, string $label = NULL, string $width = NULL, int $truncate = NULL, string $insertBefore = NULL) : Columns\Text
	{
		/** @var Columns\Text $column */
		$column = $this->addColumn(Columns\IColumn::TYPE_TEXT, $name, $label, $width, $insertBefore);

		if ($truncate !== NULL) {
			$column->setTruncate($truncate);
		}

		return $column;
	}

	/**
	 * @param string $name
	 * @param string|NULL $label
	 * @param string|NULL $width
	 * @param string|NULL $insertBefore
	 *
	 * @return Columns\Email
	 */
	public function addColumnEmail(string $name, string $label = NULL, string $width = NULL, string $insertBefore = NULL) : Columns\Email
	{
		/** @var Columns\Email $column */
		$column = $this->addColumn(Columns\IColumn::TYPE_EMAIL, $name, $label, $width, $insertBefore);

		return $column;
	}

	/**
	 * @param string $name
	 * @param string|NULL $label
	 * @param string|NULL $width
	 * @param string|NULL $insertBefore
	 *
	 * @return Columns\Link
	 */
	public function addColumnLink(string $name, string $label = NULL, string $width = NULL, string $insertBefore = NULL) : Columns\Link
	{
		/** @var Columns\Link $column */
		$column = $this->addColumn(Columns\IColumn::TYPE_LINK, $name, $label, $width, $insertBefore);

		return $column;
	}

	/**
	 * @param string $name
	 * @param string|NULL $label
	 * @param string|NULL $width
	 * @param string|NULL $insertBefore
	 *
	 * @return Columns\Date
	 */
	public function addColumnDate(string $name, string $label = NULL, string $width = NULL, string $insertBefore = NULL) : Columns\Date
	{
		/** @var Columns\Date $column */
		$column = $this->addColumn(Columns\IColumn::TYPE_DATE, $name, $label, $width, $insertBefore);

		return $column;
	}

	/**
	 * @param string $name
	 * @param string|NULL $label
	 * @param string|NULL $width
	 * @param string|NULL $insertBefore
	 *
	 * @return Columns\Number
	 */
	public function addColumnNumber(string $name, string $label = NULL, string $width = NULL, string $insertBefore = NULL) : Columns\Number
	{
		/** @var Columns\Number $column */
		$column = $this->addColumn(Columns\IColumn::TYPE_NUMBER, $name, $label, $width, $insertBefore);

		return $column;
	}

	/**
	 * @param string $name
	 * @param string|NULL $label
	 * @param string|NULL $width
	 * @param string|NULL $insertBefore
	 *
	 * @return Columns\Image
	 */
	public function addColumnImage(string $name, string $label = NULL, string $width = NULL, string $insertBefore = NULL) : Columns\Image
	{
		/** @var Columns\Image $column */
		$column = $this->addColumn(Columns\IColumn::TYPE_IMAGE, $name, $label, $width, $insertBefore);

		return $column;
	}

	/**
	 * @param string $name
	 * @param string|NULL $label
	 * @param string|NULL $width
	 * @param string|NULL $insertBefore
	 *
	 * @return Columns\Action
	 */
	public function addColumnAction(string $name, string $label = NULL, string $width = NULL, string $insertBefore = NULL) : Columns\Action
	{
		/** @var Columns\Action $column */
		$column = $this->addColumn(Columns\IColumn::TYPE_ACTION, $name, $label, $width, $insertBefore);

		return $column;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getColumns() : array
	{
		/** @var ComponentModel\Container $columnsContainer */
		$columnsContainer = $this->getComponent(Columns\IColumn::ID, FALSE);

		return $columnsContainer !== NULL ? $columnsContainer->getComponents()->getArrayCopy() : [];
	}

	/**
	 * @param string $name
	 * @param bool $need
	 *
	 * @return Columns\IColumn|NULL
	 */
	public function getColumn(string $name, bool $need = TRUE)
	{
		/** @var ComponentModel\Container $columnsContainer */
		$columnsContainer = $this->getComponent(Columns\IColumn::ID, FALSE);

		return $columnsContainer !== NULL && $this->hasColumns()
			? $columnsContainer->getComponent($name, $need)
			: NULL;
	}

	/**
	 * @return int $count
	 */
	public function getColumnsCount() : int
	{
		$count = count($this->getColumns());

		if ($this->hasGlobalButtons() || $this->hasRowButtons()) {
			// Checkbox column
			$count++;
		}

		return $count;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasColumns() : bool
	{
		/** @var ComponentModel\Container $columnsContainer */
		$columnsContainer = $this->getComponent(Columns\IColumn::ID, FALSE);

		return ($columnsContainer !== NULL && count($columnsContainer->getComponents()) > 0) ? TRUE : FALSE;
	}

	/**
	 * @param string $columnName
	 *
	 * @return bool
	 */
	public function columnExists(string $columnName) : bool
	{
		/** @var ComponentModel\Container $columnsContainer */
		$columnsContainer = $this->getComponent(Columns\IColumn::ID, FALSE);

		return $columnsContainer !== NULL && $columnsContainer->getComponent($columnName, FALSE) ? TRUE : FALSE;
	}

	/**
	 * @return bool
	 */
	abstract public function hasRowButtons() : bool;

	/**
	 * @return bool
	 */
	abstract public function hasGlobalButtons() : bool;
}
