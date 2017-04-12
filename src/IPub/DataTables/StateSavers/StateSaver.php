<?php
/**
 * StateSaver.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     StateSavers
 * @since          1.0.0
 *
 * @date           18.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\StateSavers;

use Nette;
use Nette\Http;
use Nette\Security as NS;

/**
 * DataTables state saver
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     StateSavers
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class StateSaver implements IStateSaver
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Http\SessionSection
	 */
	private $session;

	/**
	 * @var NS\User
	 */
	private $user;

	/**
	 * @param Http\Session $session
	 * @param NS\User $user
	 */
	public function __construct(
		Http\Session $session,
		NS\User $user
	) {
		$this->session = $session->getSection('DataTables');
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveState(string $name, $data)
	{
		// Generate unique session key
		$key = $this->generateKey($name);

		// Store settings into session
		$this->session->offsetSet($key, $data);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadState(string $name)
	{
		// Generate unique session key
		$key = $this->generateKey($name);

		return isset($this->session->$key) ? $this->session->offsetGet($key) : NULL;
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	private function generateKey(string $name) : string
	{
		return md5($name . '-' . $this->user->getId());
	}
}
