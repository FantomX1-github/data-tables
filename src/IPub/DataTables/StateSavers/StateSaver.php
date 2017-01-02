<?php
/**
 * StateSaver.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	StateSavers
 * @since		5.0
 *
 * @date		18.10.14
 */

namespace IPub\DataTables\StateSavers;

use Nette;
use Nette\Http;
use Nette\Security as NS;

class StateSaver extends Nette\Object implements IStateSaver
{
	/**
	 * @var Http\SessionSection
	 */
	protected $session;

	/**
	 * @var NS\User
	 */
	protected $user;

	/**
	 * @param Http\Session $session
	 * @param NS\User $user
	 */
	public function __construct(
		Http\Session $session,
		NS\User $user
	) {
		$this->session	= $session->getSection('DataTables');
		$this->user		= $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveState($name, $data)
	{
		// Generate unique session key
		$key = $this->generateKey($name);

		// Store settings into session
		$this->session->$key = $data;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadState($name)
	{
		// Generate unique session key
		$key = $this->generateKey($name);

		return isset($this->session->$key) ? $this->session->$key : [];
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	protected function generateKey($name)
	{
		return md5($name .'-'. $this->user->getId());
	}
}
