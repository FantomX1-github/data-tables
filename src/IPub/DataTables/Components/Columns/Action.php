<?php
/**
 * Action.php
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

use Nette;
use Nette\ComponentModel;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;

class Action extends Settings
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

	/**
	 * @var array
	 */
	protected $buttons = [];

	/**
	 * @param string $name
	 * @param null|string $label
	 *
	 * @return Components\Buttons\Button
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateRowButtonException
	 */
	public function addButton($name, $label = NULL)
	{
		if (($buttons = $this->parent->getComponent(Components\Buttons\Button::ID, FALSE)) AND $buttons->getComponent($name, FALSE)) {
			throw new Exceptions\DuplicateRowButtonException("Row button $name already exists.");
		}

		$this->buttons[$name] = new Components\Buttons\Button($this->parent, $name, $label);

		return $this->buttons[$name];
	}

	/**
	 * @return array
	 */
	public function getButtons()
	{
		return $this->buttons;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRenderer($renderer)
	{
		throw new Exceptions\NotSupportedException("Setting renderer for action column is not supported. Use addButton instead.");

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($data)
	{
		if ($this->parent->getComponent(Components\Buttons\Button::ID, FALSE)) {
			foreach ($this->parent->getComponent(Components\Buttons\Button::ID, FALSE)->getComponents() as $button) {
				if (array_key_exists($button->getName(), $this->buttons)) {
					echo $button->render($data);
				}
			}
		}
	}
}