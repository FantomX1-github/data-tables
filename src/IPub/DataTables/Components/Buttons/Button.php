<?php
/**
 * Button.php
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

namespace IPub\DataTables\Components\Buttons;

use Nette\Application\UI;
use Nette\ComponentModel;
use Nette\Forms;
use Nette\Utils;
use Nette\Localization;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;

/**
 * Action column button control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @property-read UI\Control $parent
 */
class Button extends UI\Control implements IButton
{
	/**
	 * @var string
	 */
	private $type = self::TYPE_BUTTON;

	/**
	 * Button label
	 *
	 * @var callable|string
	 */
	private $label;

	/**
	 * Title attribute
	 *
	 * @var callable|string
	 */
	private $title;

	/**
	 * Additional style class
	 *
	 * @var callable|string
	 */
	private $class;

	/**
	 * @var callable|array
	 */
	private $attributes = [];

	/**
	 * @var bool
	 */
	private $ajax = TRUE;

	/**
	 * @var callable|NULL
	 */
	private $callback;

	/**
	 * @var callable|string
	 */
	private $link;

	/**
	 * @var callable|string
	 */
	private $renderer;

	/**
	 * @var Localization\ITranslator
	 */
	private $translator;

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string|callable $label
	 */
	public function __construct(Components\Control $parent, string $name, $label)
	{
		parent::__construct();

		$this->addComponentToGrid($parent, $name);

		$this->setLabel($label);

		$buttonsFormContainer = $this->getButtonsFormContainer();
		$buttonsFormContainer->addSubmit($name, $label)
			->setValidationScope(FALSE);

		$this->ajax = $parent->hasEnabledAjax();

		// Get translator
		$this->translator = $parent->getTranslator();
	}

	/**
	 * {@inheritdoc}
	 */
	public function showAsButton()
	{
		$this->type = self::TYPE_BUTTON;
	}

	/**
	 * {@inheritdoc}
	 */
	public function showAsLink()
	{
		$this->type = self::TYPE_LINK;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTitle($title)
	{
		if (!is_string($title) && !is_callable($title)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided value is not valid. Only string or callable types are allowed. %s provided instead', gettype($title)));
		}

		$this->title = $title;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setClass($class)
	{
		if (!is_string($class) && !is_callable($class)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided value is not valid. Only string or callable types are allowed. %s provided instead', gettype($class)));
		}

		$this->class = $class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setAttributes($attributes)
	{
		if (!is_array($attributes) && !is_callable($attributes)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided value is not valid. Only array or callable types are allowed. %s provided instead', gettype($attributes)));
		}

		$this->attributes = $attributes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCallback(callable $callback)
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCallback()
	{
		if ($this->callback === NULL) {
			throw new Exceptions\UnknownButtonCallbackException(sprintf('Button "%s" doesn\'t have callback.', $this->name));
		}

		return $this->callback;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLink($link)
	{
		if (!is_string($link) && !is_callable($link)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided value is not valid. Only string or callable types are allowed. %s provided instead', gettype($link)));
		}

		$this->link = $link;
	}

	/**
	 * {@inheritdoc}
	 */
	public function enableAjax()
	{
		$this->ajax = TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disableAjax()
	{
		$this->ajax = FALSE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasEnabledAjax() : bool
	{
		return $this->ajax;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRenderer(callable $renderer)
	{
		$this->renderer = $renderer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($data)
	{
		if ($this->getCallback() === NULL && $this->getLink($data) === NULL) {
			throw new Exceptions\InvalidStateException('Row button have to have action callback or link defined.');
		}

		if ($this->getLink($data) !== NULL && $this->type === self::TYPE_BUTTON) {
			throw new Exceptions\InvalidStateException('Link could be used only for button type link. Please call $button->showAsLink() in button definition.');
		}

		if (is_callable($this->renderer)) {
			echo call_user_func($this->renderer, $data, $this);

		} else {
			/** @var Forms\Controls\SubmitButton $button */
			$button = $this->getButtonsFormContainer()->getComponent($this->name);

			if ($this->type === self::TYPE_LINK) {
				$element = Utils\Html::el('a');

				if ($this->getLink($data) === NULL) {
					$element->setAttribute('href', '#');

					// Set element attributes for JS
					$element->data('action-name', $button->getHtmlName());
					$element->data('action-value', $button->caption);

				} else {
					$element->setAttribute('href', $this->getLink($data));
				}

			} else {
				$element = $button->getControl();
			}

			$element->addAttributes($this->getAttributes($data));
			$element->setText($this->getLabel($data));

			if ($this->getLink($data) === NULL) {
				$element->appendAttribute('class', 'js-data-grid-row-button');
			}

			$additionalStyleClass = $this->getClass($data);

			if ($additionalStyleClass !== NULL) {
				$element->appendAttribute('class', $additionalStyleClass);
			}

			$element->setAttribute('title', $this->getTitle($data));

			// Check if ajax request is enabled
			if ($this->hasEnabledAjax()) {
				$element->appendAttribute('class', 'js-data-grid-ajax');
			}

			echo $element->render();
		}
	}

	/**
	 * Get button title
	 *
	 * @param mixed $data
	 *
	 * @return string|NULL
	 */
	private function getTitle($data)
	{
		if (is_callable($this->title)) {
			$title = (string) call_user_func($this->title, $data);

		} else {
			$title = $this->title;
		}

		return $this->translator ? $this->translator->translate($title) : $title;
	}

	/**
	 * Set button element label
	 *
	 * @param callable|string $label
	 *
	 * @return void
	 */
	private function setLabel($label)
	{
		if (!is_string($label) && !is_callable($label)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided value is not valid. Only string or callable types are allowed. %s provided instead', gettype($label)));
		}

		$this->label = $label;
	}

	/**
	 * Get button element label
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	private function getLabel($data) : string
	{
		if (is_callable($this->label)) {
			return (string) call_user_func($this->label, $data);
		}

		return $this->label;
	}

	/**
	 * Get button link only for link type
	 *
	 * @param mixed $data
	 *
	 * @return string|NULL
	 */
	private function getLink($data)
	{
		if (is_callable($this->link)) {
			return (string) call_user_func($this->link, $data);
		}

		return $this->link;
	}

	/**
	 * Get button element class
	 *
	 * @param mixed $data
	 *
	 * @return string|NULL
	 */
	private function getClass($data)
	{
		if (is_callable($this->class)) {
			return (string) call_user_func($this->class, $data);
		}

		return $this->class;
	}

	/**
	 * @param mixed $data
	 *
	 * @return array
	 */
	private function getAttributes($data) : array
	{
		if (is_callable($this->attributes)) {
			return (array) call_user_func($this->attributes, $data);
		}

		return $this->attributes;
	}

	/**
	 * @return Forms\Container
	 */
	private function getButtonsFormContainer() : Forms\Container
	{
		/** @var Components\Control $gridControl */
		$gridControl = $this->lookup(Components\Control::class);

		return $gridControl->getComponent('gridForm')->getComponent(self::ID);
	}

	/**
	 * @param Components\Control $grid
	 * @param string $name
	 *
	 * @return void
	 */
	private function addComponentToGrid(Components\Control $grid, string $name)
	{
		/** @var ComponentModel\Container $container */
		$container = $grid->getComponent(self::ID, FALSE);

		// Check container exist
		if (!$container) {
			$grid->addComponent(new ComponentModel\Container, self::ID);

			$container = $grid->getComponent(self::ID);
		}

		$container->addComponent($this, $name);
	}
}
