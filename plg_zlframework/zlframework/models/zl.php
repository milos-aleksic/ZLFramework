<?php
/**
* @package      ZL Framework
* @author       JOOlanders, SL http://www.zoolanders.com
* @copyright    Copyright (C) JOOlanders, SL
* @license      http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );

/**
 *  j3.0 workaround
 */
if(!class_exists('ZLWorksAroundJoomlaToGetAModel')) {
    if(interface_exists('JModel')) {
        abstract class ZLWorksAroundJoomlaToGetAModel extends JModelLegacy {}
    } else {
        class ZLWorksAroundJoomlaToGetAModel extends JModel {}
    }
}

class ZLModel extends ZLWorksAroundJoomlaToGetAModel
{
    protected $app = null;
    
    function __construct($config = array())
    {
        parent::__construct($config);
        
        $this->app = App::getInstance('zoo');
    }
    
    /**
     * Empties the state
     *
     * @return unknown_type
     */
    public function emptyState()
    {
        $state = JArrayHelper::fromObject( $this->getState() );
        foreach ($state as $key=>$value)
        {
            if (substr($key, '0', '1') != '_')
            {
                $this->setState( $key, '' );
            }
        }
        return $this;
    }

    /**
     * Gets the model's query, building it if it doesn't exist
     * @return valid query object
     */
    public function getQuery()
    {
        if (empty( $this->_query ) )
        {
            $this->_query = $this->_buildQuery();
        }
        return $this->_query;
    }

    /**
     * Sets the model's query
     * @param $query    A valid query object
     * @return valid query object
     */
    public function setQuery( $query )
    {
        $this->_query = $query;
        return $this;
    }

    /**
     * Gets the model's query, building it if it doesn't exist
     * @return valid query object
     */
    public function getResultQuery( $refresh=false )
    {
        if (empty( $this->_resultQuery ) || $refresh )
        {
            $this->_resultQuery = $this->_buildResultQuery();
        }
        return $this->_resultQuery;
    }

    /**
     * Sets the model's query
     * @param $query    A valid query object
     * @return valid query object
     */
    public function setResultQuery( $query )
    {
        $this->_resultQuery = $query;
        return $this;
    }

    /**
     * Retrieves the data for a paginated list
     * @return array Array of objects containing the data from the database
     */
    function getList()
    {
        //echo str_replace('#__', 'oo1dl_', $this->getQuery());die();
        
        if (empty( $this->_list ))
        {
            $query = $this->getQuery();
            
            // Limits
            $offset = $this->_db->escape( $this->getState('offset'));
            $limitstart = $this->_db->escape( $this->getState('limitstart') );
            $limit  = $this->_db->escape( $this->getState('limit') );

            if (strlen($limitstart)) {
                $offset = $limitstart;
            }

            
            $this->_db->setQuery($query, $offset, $limit);
            $result = $this->_db->execute();
    
            // fetch objects and execute init callback
            $objects = array();
            while ($object = $this->app->database->fetchObject($result, 'Item')) 
            {
                $objects[$object->id] = $this->initObject($object);
            }
    
            $this->app->database->freeResult($result);
            $this->_list = $objects;
        }
        
        return $this->_list;
    }
    
    protected function initObject($object)
    {
        // add reference to related app instance
        if (property_exists($object, 'app')) {
            $object->app = $this->app;
        }
        
        // workaround for php bug, which calls constructor before filling values
        if (is_string($object->params) || is_null($object->params)) {
            // decorate data as object
            $object->params = $this->app->parameter->create($object->params);
        }

        if (is_string($object->elements) || is_null($object->elements)) {
            // decorate data as object
            $object->elements = $this->app->data->create($object->elements);
        }

        // trigger init event
        $this->app->event->dispatcher->notify($this->app->event->create($object, 'item:init'));

        return $object;
    }

    /**
     * Retrieves the data for an un-paginated list
     * @return array Array of objects containing the data from the database
     */
    function getAll()
    {
        if (empty( $this->_all ))
        {
            $query = $this->getQuery();
            $this->_all = $this->_getList( (string) $query, 0, 0 );
        }
        return $this->_all;
    }

    /**
     * Retrieves the count
     * @return array Array of objects containing the data from the database
     */
    function getTotal()
    {
        if (empty($this->_total))
        {
            $query = $this->getQuery();
            $this->_total = $this->_getListCount( (string) $query);
        }
        return $this->_total;
    }
    
    /**
     * Retrieves the result from the query
     * Useful on SUM and COUNT queries
     * 
     * @return array Array of objects containing the data from the database
     */
    function getResult( $refresh=false )
    {
        if (empty($this->_result) || $refresh)
        {
            $query = $this->getResultQuery( $refresh );
            $this->_db->setQuery( (string) $query );
            $this->_result = $this->_db->loadResult();
        }
        return $this->_result;
    }
    
    /**
     * Builds a generic SELECT query
     *
     * @return  string  SELECT query
     */
    protected function _buildQuery()
    {
        if (!empty($this->_query))
        {
            return $this->_query;
        }

        // Use joomla query builder
        $query = $this->_db->getQuery(true);

        $this->_buildQueryFields($query);
        $this->_buildQueryFrom($query);
        $this->_buildQueryWhere($query);
        $this->_buildQueryJoins($query);
        $this->_buildQueryGroup($query);
        $this->_buildQueryHaving($query);
        $this->_buildQueryOrder($query);

        return $query;
    }

    /**
     * Builds a generic SELECT COUNT(*) query
     */
    protected function _buildResultQuery()
    {
        // Use joomla query builder
        $query = $this->_db->getQuery(true);
        $query->select( 'COUNT( DISTINCT a.id )');

        $this->_buildQueryFrom($query);
        $this->_buildQueryWhere($query);
        $this->_buildQueryJoins($query);
        $this->_buildQueryGroup($query);
        $this->_buildQueryHaving($query);

        return $query;
    }

    /**
     * Builds SELECT fields list for the query
     */
    protected function _buildQueryFields(&$query)
    {
        $query->select( $this->getState( 'select', 'a.*' ) );
    }

    /**
     * Builds FROM tables list for the query
     */
    protected function _buildQueryFrom(&$query)
    {
    }

    /**
     * Builds JOINS clauses for the query
     */
    protected function _buildQueryJoins(&$query)
    {
    }

    /**
     * Builds WHERE clause for the query
     */
    protected function _buildQueryWhere(&$query)
    {
    }

    /**
     * Builds a GROUP BY clause for the query
     */
    protected function _buildQueryGroup(&$query)
    {
    }

    /**
     * Builds a HAVING clause for the query
     */
    protected function _buildQueryHaving(&$query)
    {
    }

    /**
     * Builds a generic ORDER BY clasue based on the model's state
     */
    protected function _buildQueryOrder(&$query)
    {
        $order_by = $this->_db->escape( $this->getState('order_by') );
        if ($order_by){
            $query->order($order_by);
        }
    }
}