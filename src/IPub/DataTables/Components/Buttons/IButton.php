<?php
/**
 * IButton.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Components
 * @since		5.0
 *
 * @date		26.11.14
 */

namespace IPub\DataTables\Components\Buttons;

use Nette;
use Nette\Application\UI;
use Nette\Utils;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Exceptions;

interface IButton
{
	/**
	 * Components group ID in data grid
	 */
	const ID = 'rowAction';

	/**
	 * Define button element type
	 */
	const TYPE_BUTTON	= 'button';
	const TYPE_LINK		= 'link';

	/**
	 * Set button type to button element
	 * @return $this
	 */
	public function showAsButton();

	/**
	 * Set button type to link element
	 *
	 * @return $this
	 */
	public function showAsLink();

	/**
	 * Set button title
	 *
	 * @param callback|string $title
	 *
	 * @return $this
	 */
	public function setTitle($title);

	/**
	 * Get button title
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	public function getTitle($data);

	/**
	 * Set button element label
	 *
	 * @param callback|string $label
	 *
	 * @return $this
	 */
	public function setLabel($label);

	/**
	 * Get button element label
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	public function getLabel($data);

	/**
	 * Set button element class
	 *
	 * @param callback|string $class
	 *
	 * @return $this
	 */
	public function setClass($class);

	/**
	 * Get button element class
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	public function getClass($data);

	/**
	 * Set button callback
	 *
	 * @param callback $callback
	 *
	 * @return $this
	 */
	public function setCallback($callback);

	/**
	 * Get button callback
	 *
	 * @return callable
	 *
	 * @throws Exceptions\UnknownButtonCallbackException
	 */
	public function getCallback();

	/**
	 * Set button link
	 *
	 * @param callback|string $link
	 *
	 * @return $this
	 */
	public function setLink($link);

	/**
	 * Get button link only for link type
	 *
	 * @param mixed $data
	 *
	 * @return string
	 */
	public function getLink($data);

	/**
	 * Set button renderer
	 *
	 * @param callback $renderer
	 *
	 * @return $this
	 *
	 * @throws Exceptions\ButtonRendererNotCallableException
	 */
	public function setRenderer($renderer);

	/**
	 * Set ajax for button
	 *
	 * @param bool $ajax
	 *
	 * @return $this
	 */
	public function setAjax($ajax = TRUE);

	/**
	 * Enable ajax for button
	 *
	 * @return $this
	 */
	public function enableAjax();

	/**
	 * Disable ajax for button
	 *
	 * @return $this
	 */
	public function disableAjax();

	/**
	 * Check if ajax is for this button enabled
	 *
	 * @return bool
	 */
	public function hasEnabledAjax();

	/**
	 * Get row action form
	 *
	 * @return UI\Form
	 */
	public function getForm();

	/**
	 * Render row button
	 *
	 * @param mixed|null $data
	 *
	 * @return mixed|Utils\Html
	 */
	public function render($data = NULL);
}