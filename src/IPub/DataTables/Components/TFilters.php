<?php
/**
 * TFilters.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 * @since          1.0.0
 *
 * @date           13.04.17
 */

declare(strict_types=1);

namespace IPub\DataTables\Components;

use Nette\ComponentModel;

use IPub\DataTables\Exceptions;
use IPub\DataTables\DataSources;
use IPub\DataTables\Filters;
use Nette\Forms\Container;

/**
 * DataTables filters trait
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method ComponentModel\IComponent|NULL getComponent(string $name, bool $need = TRUE)
 * @method DataSources\IModel getModel()
 */
trait TFilters
{
	/**
	 * @var array
	 */
	private $defaultFilter = [];

	/**
	 * @var array
	 */
	private $filter = [];

	/**
	 * Sets default filtering
	 *
	 * @param array $filter
	 *
	 * @return void
	 */
	public function setDefaultFilter(array $filter)
	{
		$this->defaultFilter = array_merge($this->defaultFilter, $filter);
	}

	/**
	 * @param string $key
	 * @param mixed|NULL $value
	 *
	 * @return void
	 */
	public function setActualFilter(string $key, $value = NULL)
	{
		if ($value === NULL) {
			unset($this->filter[$key]);

		} else {
			$this->filter[$key] = $value;
		}
	}

	/**
	 * Returns actual filter values
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getActualFilter(string $key = NULL)
	{
		$filter = $this->filter ? $this->filter : $this->defaultFilter;

		return $key && isset($filter[$key]) ? $filter[$key] : $filter;
	}

	/**
	 * Get all filters components
	 *
	 * @return array
	 */
	public function getFilters() : array
	{
		if (!$this->hasFilters()) {
			return [];
		}

		/** @var ComponentModel\Container $filtersContainer */
		$filtersContainer = $this->getComponent(Filters\Filter::ID);

		return $filtersContainer->getComponents()->getArrayCopy();
	}

	/**
	 * Returns filter component by its name
	 *
	 * @param string $name
	 * @param bool $need
	 *
	 * @return Filters\IFilter|NULL
	 */
	public function getFilter(string $name, bool $need = TRUE)
	{
		if (!$this->hasFilters() && $need) {
			throw new Exceptions\UnknownFilterException(sprintf('Filter with name "%s" does not exist.', $name));
		}

		/** @var ComponentModel\Container $filtersContainer */
		$filtersContainer = $this->getComponent(Filters\Filter::ID);

		/** @var Filters\IFilter|NULL $filter */
		$filter = $filtersContainer->getComponent($name, FALSE);

		if ($filter === NULL && $need) {
			throw new Exceptions\UnknownFilterException(sprintf('Filter with name "%s" does not exist.', $name));
		}

		return $filter;
	}

	/**
	 * Check if some filter is registered
	 *
	 * @return bool
	 */
	public function hasFilters() : bool
	{
		/** @var ComponentModel\Container $filtersContainer */
		$filtersContainer = $this->getComponent(Filters\Filter::ID, FALSE);

		return $filtersContainer !== NULL && count($filtersContainer->getComponents()) > 0;
	}

	/**
	 * Apply column filtering to the model
	 *
	 * @return void
	 */
	protected function applyFiltering()
	{
		/** @var Filters\Condition[] $conditions */
		$conditions = [];

		if ($this->getActualFilter()) {
			/** @var Container $filterFormContainer */
			$filterFormContainer = $this['gridForm'][Filters\Filter::ID];
			$filterFormContainer->setDefaults($this->getActualFilter());

			foreach ($this->getActualFilter() as $column => $value) {
				/** @var Filters\IFilter|NULL $filter */
				$filter = $this->getFilter($column, FALSE);

				if ($filter === NULL) {
					throw new Exceptions\UnknownFilterException(sprintf('Filter with name "%s" does not exist.', $column));
				}

				$condition = $filter->__getCondition($value);

				if ($condition !== FALSE) {
					$conditions[] = $condition;
				}
			}
		}

		// Apply filter to the data model
		$this->getModel()->filter($conditions);
	}
}
