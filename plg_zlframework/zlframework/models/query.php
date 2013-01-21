<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Query Element Class.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.6
 */
class ZlQueryElement extends JObject
{
	/** @var string The name of the element */
	protected $_name = null;
	
	/** @var array An array of elements */
	protected $_elements = null;
	
	/** @var string Glue piece */
	protected $_glue = null;

	/**
	 * Constructor.
	 * 
	 * @param	string	The name of the element.
	 * @param	mixed	String or array.
	 * @param	string	The glue for elements.
	 */
	public function __construct($name, $elements, $glue=',')
	{
		$this->_elements	= array();
		$this->_name		= $name;		
		$this->_glue		= $glue;
		
		$this->append($elements);
	}
	
	public function __toString()
	{
		return PHP_EOL.$this->_name.' '.implode($this->_glue, $this->_elements);
	}
	
	/**
	 * Appends element parts to the internal list.
	 * 
	 * @param	mixed	String or array.
	 */
	public function append($elements)
	{
		if (is_array($elements)) {
			$this->_elements = array_unique(array_merge($this->_elements, $elements));
		} else {
			$this->_elements = array_unique(array_merge($this->_elements, array($elements)));
		}
	}
}

/**
 * Query Building Class.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.6
 */
class ZlQuery extends JObject
{
	/** @var string The query type */
	protected $_type = '';
	
	/** @var object The select element */
	protected $_select = null;
	
	/** @var object The from element */
	protected $_from = null;
	
	/** @var object The join element */
	protected $_join = null;
	
	/** @var object The where element */
	protected $_where = null;
	
	/** @var object The where element */
	protected $_group = null;
	
	/** @var object The where element */
	protected $_having = null;
	
	/** @var object The where element */
	protected $_order = null;
	
	/** @var object The where element */
	protected $_limit = null;

	/**
	 * @param	mixed	A string or an array of field names
	 */
	public function select($columns)
	{
		$this->_type = 'select';
		if (is_null($this->_select)) {
			$this->_select = new ZLQueryElement('SELECT', $columns);
		} else {
			$this->_select->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of table names
	 */
	public function from($tables)
	{
		if (is_null($this->_from)) {
			$this->_from = new ZLQueryElement('FROM', $tables);
		} else {
			$this->_from->append($tables);
		}

		return $this;
	}

	/**
	 * @param	string
	 * @param	string
	 */
	public function join($type, $conditions)
	{
		if (is_null($this->_join)) {
			$this->_join = array();
		}
		$this->_join[] = new ZLQueryElement(strtoupper($type) . ' JOIN', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &innerJoin($conditions)
	{
		$this->join('INNER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &outerJoin($conditions)
	{
		$this->join('OUTER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &leftJoin($conditions)
	{
		$this->join('LEFT', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &rightJoin($conditions)
	{
		$this->join('RIGHT', $conditions);

		return $this;
	}

	/**
	 * @param	mixed	A string or array of where conditions
	 * @param	string
	 */
	public function where($conditions, $glue='AND')
	{
		if (is_null($this->_where)) {
			$glue = strtoupper($glue);
			$this->_where = new ZLQueryElement('WHERE', $conditions, "\n\t$glue ");
		} else {
			$this->_where->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function group($columns)
	{
		if (is_null($this->_group)) {
			$this->_group = new ZLQueryElement('GROUP BY', $columns);
		} else {
			$this->_group->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function having($conditions, $glue='AND')
	{
		if (is_null($this->_having)) {
			$glue = strtoupper($glue);
			$this->_having = new ZLQueryElement('HAVING', $conditions, "\n\t$glue ");
		} else {
			$this->_having->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function order($columns)
	{
		if (is_null($this->_order)) {
			$this->_order = new ZLQueryElement('ORDER BY', $columns);
		} else {
			$this->_order->append($columns);
		}

		return $this;
	}
	
	/**
	 * @param	mixed	A string for limit and/or offset
	 */
	public function limit($start, $count)
	{
		if(strlen($start) && strlen($count))
		{
			$this->_limit = ' LIMIT '.$start.', '.$count;
		}
		elseif(strlen($count))
		{
			$this->_limit = ' LIMIT '.$count;
		}
		elseif(strlen($start))
		{	// offset can't go alone, must be combined with LIMIT but as we don't wonna limit anything,
			// a workaround is to use the biggest limit possible, which is 18446744073709551615 (maximum of unsigned BIGINT)
			$this->_limit = ' LIMIT 18446744073709551615 OFFSET '.$start;
		}

		return $this;
	}

	/**
	 * @return	string	The completed query
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->_type)
		{
			case 'select':
				$query .= (string) $this->_select;
				$query .= (string) $this->_from;
				if ($this->_join) {
					// special case for joins
					foreach ($this->_join as $join) {
						$query .= (string) $join;
					}
				}
				if ($this->_where) {
					$query .= (string) $this->_where;
				}
				if ($this->_group) {
					$query .= (string) $this->_group;
				}
				if ($this->_having) {
					$query .= (string) $this->_having;
				}
				if ($this->_order) {
					$query .= (string) $this->_order;
				}
				if ($this->_limit) {
					$query .= (string) $this->_limit;
				}
				break;
		}

		return $query;
	}
}