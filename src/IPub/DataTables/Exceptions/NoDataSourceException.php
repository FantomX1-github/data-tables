<?php
/**
 * NoDataSourceException.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Exceptions
 * @since          1.0.0
 *
 * @date           21.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Exceptions;

class NoDataSourceException extends InvalidStateException implements IException
{
}
