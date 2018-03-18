<?php
/**
 * IControl.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 * @since          1.0.0
 *
 * @date           12.03.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Components;

/**
 * DataTables control factory
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.com>
 */
interface IControl
{
	/**
	 * @return Control
	 */
	function create() : Control;
}
