<?php
/**
 * IButton.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 * @since          1.0.0
 *
 * @date           26.11.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Components\Buttons;

use Nette\Utils;

use IPub\DataTables;

/**
 * Action column button control interface
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IButton
{
	/**
	 * Components group ID in data grid
	 */
	const ID = 'rowAction';

	/**
	 * Define button element type
	 */
	const TYPE_BUTTON = 'button';
	const TYPE_LINK = 'link';

	/**
	 * @return string
	 */
	function getName();

	/**
	 * Set button type to button element
	 *
	 * @return void
	 */
	function showAsButton();

	/**
	 * Set button type to link element
	 *
	 * @return void
	 */
	function showAsLink();

	/**
	 * Set button title
	 *
	 * @param callable|string $title
	 *
	 * @return void
	 */
	function setTitle($title);

	/**
	 * Set button element class
	 *
	 * @param callable|string $class
	 *
	 * @return void
	 */
	function setClass($class);

	/**
	 * Button element attributes
	 *
	 * @param callable|array $attributes
	 *
	 * @return void
	 */
	function setAttributes($attributes);

	/**
	 * Set button callable
	 *
	 * @param callable $callable
	 *
	 * @return void
	 */
	function setCallback(callable $callable);

	/**
	 * Get button callable
	 *
	 * @return callable|NULL
	 */
	function getCallback();

	/**
	 * Set button link
	 *
	 * @param callable|string $link
	 *
	 * @return void
	 */
	function setLink($link);

	/**
	 * Enable ajax for button
	 *
	 * @return void
	 */
	function enableAjax();

	/**
	 * Disable ajax for button
	 *
	 * @return void
	 */
	function disableAjax();

	/**
	 * Check if ajax is for this button enabled
	 *
	 * @return bool
	 */
	function hasEnabledAjax() : bool;

	/**
	 * Set button renderer
	 *
	 * @param callable $renderer
	 *
	 * @return void
	 */
	function setRenderer(callable $renderer);

	/**
	 * Render row button
	 *
	 * @param mixed $data
	 *
	 * @return mixed|Utils\Html
	 */
	function render($data);
}
