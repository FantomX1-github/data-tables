<?php
/**
 * UnknownActionException.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Exceptions
 * @since          1.0.0
 *
 * @date           24.11.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Exceptions;

class UnknownActionException extends InvalidArgumentException implements IException
{
}
