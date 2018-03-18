<?php
/**
 * IButton.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 * @since          1.0.0
 *
 * @date           26.11.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Components\Actions;

use Nette\Utils;

/**
 * Global action column button control interface
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
	const ID = 'globalAction';

	/**
	 * Define button element type
	 */
	const TYPE_BUTTON = 'button';
	const TYPE_LINK = 'link';

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
	 * Get button formatted action for select box
	 *
	 * @return Utils\Html
	 */
	function getAction() : Utils\Html;

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
	 * @return mixed|Utils\Html
	 */
	function render();
}
