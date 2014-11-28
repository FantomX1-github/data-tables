<?php
/**
 * GlobalButton.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Components
 * @since		5.0
 *
 * @date		21.10.14
 */

namespace IPub\DataTables\Components\Actions;

use Nette;
use Nette\Application\UI;
use Nette\Forms;
use Nette\Utils;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;

class Button extends UI\Control implements IButton
{
	/**
	 * @var callback|string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var callback|string
	 */
	protected $title;

	/**
	 * @var callback|string
	 */
	protected $class;

	/**
	 * @var bool
	 */
	protected $ajax = TRUE;

	/**
	 * @var callback
	 */
	protected $callback;

	/**
	 * @var callback|string
	 */
	protected $link;

	/**
	 * @var callback|string
	 */
	protected $renderer;

	/**
	 * @var Components\Control
	 */
	protected $parent;

	/**
	 * @var UI\Form
	 */
	protected $form;

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 */
	public function __construct(Components\Control $parent, $name, $label)
	{
		$this->addComponentToGrid($parent, $name);

		$this->label	= $label;
		$this->type		= get_class($this);

		$form = $this->getForm();

		$buttons = $form->getComponent($this::ID, FALSE);

		if ($buttons === NULL) {
			$buttons = $form->addContainer($this::ID);
		}

		$buttons->addSubmit($name, $label)
			->setValidationScope(FALSE);

		$this->ajax = $parent->hasEnabledAjax();
	}

	/**
	 * {@inheritdoc}
	 */
	public function showAsButton()
	{
		$this->type = $this::TYPE_BUTTON;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function showAsLink()
	{
		$this->type = $this::TYPE_LINK;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTitle()
	{
		if (is_callable($this->title)){
			return call_user_func($this->title);
		}

		return $this->title;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLabel($label)
	{
		$this->label = $label;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLabel()
	{
		if (is_callable($this->label)){
			return call_user_func($this->label);
		}

		return $this->label;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setClass($class)
	{
		$this->class = $class;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClass()
	{
		if (is_callable($this->class)){
			return call_user_func($this->class);
		}

		return $this->class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCallback($callback)
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCallback()
	{
		if ($this->callback === NULL){
			throw new Exceptions\UnknownActionCallbackException("Action $this->name doesn't have callback.");
		}

		return $this->callback;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLink($link)
	{
		$this->link = $link;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLink()
	{
		if (is_callable($this->link)){
			return call_user_func($this->link);
		}

		return $this->link;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAction()
	{
		if ($this->callback === NULL){
			throw new Exceptions\UnknownActionCallbackException("Action $this->name doesn't have callback.");
		}

		$option = Utils\Html::el('option')
			->setValue($this->getName())
			->setText($this->getLabel());

		// Check if ajax request is enabled
		if ($this->ajax) {
			$option->addClass('js-data-grid-ajax');
		}

		return $option;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRenderer($renderer)
	{
		if (is_callable($renderer)){
			throw new Exceptions\ButtonRendererNotCallableException("Renderer for button $this->name is not callable.");
		}

		$this->renderer = $renderer;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setAjax($ajax = TRUE)
	{
		$this->ajax = $ajax;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function enableAjax()
	{
		$this->ajax = TRUE;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disableAjax()
	{
		$this->ajax = FALSE;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasEnabledAjax()
	{
		return $this->ajax === TRUE;
	}

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 *
	 * @return Nette\ComponentModel\Container
	 */
	protected function addComponentToGrid(Components\Control $parent, $name)
	{
		$this->parent = $parent;

		// Check container exist
		$container = $this->parent->getComponent($this::ID, FALSE);
		if (!$container) {
			$this->parent->addComponent(new Nette\ComponentModel\Container, $this::ID);
			$container = $this->parent->getComponent($this::ID);
		}

		return $container->addComponent($this, $name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getForm()
	{
		if ($this->form === NULL) {
			$this->form = $this->parent->getComponent('dataGridForm');
		}

		return $this->form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($data = NULL)
	{
		if (is_callable($this->renderer)){
			return call_user_func($this->renderer, $data, $this);
		}

		if ($this->type == $this::TYPE_LINK) {
			$button = Utils\Html::el('a');
			$button
				->setHref($this->getLink($data));

			// Set element attributes for JS
			$button->data['action-name']	= $this->getForm()->getComponent($this::ID, FALSE)->getComponent($this->name)->getHtmlName();
			$button->data['action-value']	= $this->getForm()->getComponent($this::ID, FALSE)->getComponent($this->name)->caption;

		} else {
			$button = $this->getForm()->getComponent($this::ID, FALSE)->getComponent($this->name)->getControl();
		}

		$button
			->setText($this->getLabel($data))
			->addClass('js-data-grid-global-button')
			->addClass($this->getClass($data))
			->setTitle($this->getTitle($data));

		// Check if ajax request is enabled
		if ($this->ajax) {
			$button->addClass('js-data-grid-ajax');
		}

		echo $button;
	}
}