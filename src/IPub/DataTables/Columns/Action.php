<?php
/**
 * Action.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           21.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

use Nette\ComponentModel;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;

/**
 * Action button column control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Action extends Column
{
	/**
	 * Define column data type for DataTables
	 */
	const COLUMN_DATA_TYPE = 'string';

	/**
	 * @var Components\Buttons\IButton[]
	 */
	private $buttons = [];

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

		/** @var ComponentModel\Container $buttonsContainer */
		$buttonsContainer = $this->getGrid()->getComponent(Components\Buttons\Button::ID, FALSE);

		if ($buttonsContainer === NULL) {
			// Create container for buttons
			$this->getGrid()->addComponent(new ComponentModel\Container, Components\Buttons\Button::ID);

		} elseif (!$buttonsContainer instanceof ComponentModel\Container) {
			throw new Exceptions\InvalidStateException('Reserved component name for buttons is occupied with another component.');
		}
	}


	/**
	 * @param string $name
	 * @param string|NULL $label
	 *
	 * @return Components\Buttons\IButton
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateRowButtonException
	 */
	public function addButton(string $name, string $label = NULL) : Components\Buttons\IButton
	{
		/** @var ComponentModel\Container $buttonsContainer */
		$buttonsContainer = $this->getGrid()->getComponent(Components\Buttons\Button::ID);

		if ($buttonsContainer->getComponent($name, FALSE) !== NULL) {
			throw new Exceptions\DuplicateRowButtonException(sprintf('Row button "%s" already exists.', $name));
		}

		$this->buttons[$name] = new Components\Buttons\Button($this->getGrid(), $name, $label);

		return $this->buttons[$name];
	}

	/**
	 * @return Components\Buttons\IButton[]
	 */
	public function getButtons() : array
	{
		return $this->buttons;
	}

	/**
	 * @param string $name
	 *
	 * @return Components\Buttons\IButton
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function getButton(string $name) : Components\Buttons\IButton
	{
		/** @var ComponentModel\Container $buttonsContainer */
		$buttonsContainer = $this->getGrid()->getComponent(Components\Buttons\Button::ID, FALSE);

		if ($buttonsContainer === FALSE || $buttonsContainer->getComponent($name, FALSE) === NULL) {
			throw new Exceptions\InvalidArgumentException(sprintf('Row button "%s" doesn\'t exists.', $name));
		}

		/** @var Components\Buttons\IButton $button */
		$button = $buttonsContainer->getComponent($name);

		return $button;
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

	/**
	 * {@inheritdoc}
	 */
	public function setRenderer(callable $renderer)
	{
		throw new Exceptions\NotSupportedException('Setting renderer for action column is not supported. Use addButton instead.');
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($data)
	{
		foreach ($this->buttons as $button) {
			echo $button->render($data);
		}
	}
}
