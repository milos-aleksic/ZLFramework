<?php
/**
* @package      ZL Framework
* @author       JOOlanders, SL http://www.zoolanders.com
* @copyright    Copyright (C) JOOlanders, SL
* @license      http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class ZLModelItem extends ZLModel
{
    /*
        Function: _buildQueryFrom
            Builds FROM tables list for the query
    */
    protected function _buildQueryFrom(&$query)
    {
        $query->from(ZOO_TABLE_ITEM.' AS a');
    }

    /*
        Function: _buildQueryJoins
            Builds JOINS clauses for the query
    */
    protected function _buildQueryJoins(&$query)
    {
        // categories
        if($this->getState('categories')){
            $query->join('LEFT', ZOO_TABLE_CATEGORY_ITEM." AS b ON a.id = b.item_id");
        }

        // elements
        if ($orderby = $this->getState('order_by'))
        {
            // get item ordering
            list($join, $order) = $this->_getItemOrder($orderby);

            // save order for order query
            $this->orderby = $order;
            
            // join
            if($join){ // don't use escape() here
                $query->leftJoin($join);
            }
        }
    }

    /*
        Function: _buildQueryWhere
            Bilds WHERE query
    */
    protected function _buildQueryWhere(&$query)
    {
        // Apply basic filters
        $this->basicFilters($query);
        
        // Apply general item filters (type, app, etc)
        $this->itemFilters($query);
    }

    /*
        Function: _buildQueryGroup
            Builds a GROUP BY clause for the query
    */
    protected function _buildQueryGroup(&$query)
    {
        if($group_by = $this->_db->escape( $this->getState('group_by') )){
            $query->group('a.' . $group_by);
        }
    }

    /*
        Function: _buildQueryOrder
            Bilds ORDER BY query
    */
    protected function _buildQueryOrder(&$query)
    {
        // custom order
        if ($this->getState('order_by') && isset($this->orderby))
        {
            $query->order( $this->orderby );
        }
    }


    /*
        Function: itemFilters
            Apply general item filters (type, app, etc)
    */
    protected function itemFilters(&$query)
    {   
        $pqry = ''; // partial query

        // application
        if ($apps_id = $this->getState('apps'))
        {    
            if(is_array($apps_id))
                $pqry = count($apps_id) > 1 ? 'IN ('.implode(',', $apps_id).')' : '= '.(int)array_shift($apps_id);
            else
                $pqry = '= '.(int)$apps_id;

            $query->where('a.application_id '.$pqry);
        }

        // type
        if ($types = $this->getState('types'))
        {
            if(is_array($types))
                $pqry = count($types) > 1 ? "IN ('". implode("', '", $types)."')" : 'LIKE '.$this->_db->Quote(array_shift($types));
            else
                $pqry = 'LIKE '.$this->_db->Quote($types);

            $query->where('a.type '.$pqry);
        }

        // categories
        if($cats_id = $this->getState('categories')){
            $query->where('b.category_id '.(is_array($cats_id) ? ' IN ('.implode(',', $cats_id).')' : ' = '.(int) $cats_id));
        }

        // published
        if ($this->getState('published')){
            $query->where('a.state = 1');
        }

        // accessible
        if ($user = $this->_db->escape( $this->getState('user') )){
            $user = $this->app->user->get($user);
            $query->where($this->app->user->getDBAccessString($user));
        }
    }

    /*
        Function: itemFilters
            Apply general filters like searchable, publicated, etc
    */
    protected function basicFilters(&$query)
    {
        // init vars
        $date = JFactory::getDate();
        $now  = $this->_db->Quote($date->toMySQL());
        $null = $this->_db->Quote($this->_db->getNullDate());

        // created/published/modified from
        if ($this->getState('created_from') || $this->getState('modified_from'))
        {
            $date = $this->getState('created_from') ? $this->getState('created_from') : $this->getState('modified_from');
            $date = $this->_db->Quote($this->_db->escape( $date ));

            $where = array();
            $where[] = 'a.publish_up > ' . $date;
            $where[] = 'a.created > ' . $date;
            $this->getState('modified_from') && $where[] = 'a.modified > ' . $date;
            $query->where('(' . implode(' OR ', $where) . ')');
        }
        else
        {
            // publication up
            $where = array();
            $where[] = 'a.publish_up = ' . $null;
            $where[] = 'a.publish_up <= ' . $now;
            $query->where('(' . implode(' OR ', $where) . ')');
        }

        // publication down
        $where = array();
        $where[] = 'a.publish_down = ' . $null;
        $where[] = 'a.publish_down >= ' . $now;
        $query->where('(' . implode(' OR ', $where) . ')');
    }

    /**
     * _getItemOrder - Returns ORDER query from an array of item order options
     *
     * @param array $order Array of order params
     * Example:array(0 => '_itemcreated', 1 => '_reversed', 2 => '_random')
     */
    protected function _getItemOrder($order)
    {
        // if string, try to convert ordering
        if (is_string($order)) {
            $order = $this->app->itemorder->convert($order);
        }

        $result = array(null, null);
        $order = (array) $order;

        // remove empty and duplicate values
        $order = array_unique(array_filter($order));

        // if random return immediately
        if (in_array('_random', $order)) {
            $result[1] = 'RAND()';
            return $result;
        }

        // get order dir
        if (($index = array_search('_reversed', $order)) !== false) {
            $reversed = 'DESC';
            unset($order[$index]);
        } else {
            $reversed = 'ASC';
        }

        // item priority
        if (($index = array_search('_priority', $order)) !== false) {
            $result[1] = "a.priority DESC, ";
            unset($order[$index]);
        }

        // set default ordering attribute
        if (empty($order)) {
            $order[] = '_itemname';
        }

        // if there is a none core element present, ordering will only take place for those elements
        if (count($order) > 1) {
            $order = array_filter($order, create_function('$a', 'return strpos($a, "_item") === false;'));
        }

        // order by core attribute
        foreach ($order as $element) {
            if (strpos($element, '_item') === 0) {
                $var = str_replace('_item', '', $element);
                $result[1] .= $reversed == 'ASC' ? "a.$var+0<>0 DESC, a.$var+0, a.$var" : "a.$var+0<>0, a.$var+0 DESC, a.$var DESC";
            }
        }

        // else order by elements
        if (!isset($result[1])) {
            $result[0] = ZOO_TABLE_SEARCH." AS s ON a.id = s.item_id AND s.element_id IN ('".implode("', '", $order)."')";
            $result[1] = $reversed == 'ASC' ? "ISNULL(s.value), s.value+0<>0 DESC, s.value+0, s.value" : "s.value+0<>0, s.value+0 DESC, s.value DESC";
        }

        return $result;
    }
}