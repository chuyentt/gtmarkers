<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gtmarkers
 * @author     Chuyen Trung Tran <chuyentt@gmail.com>
 * @copyright  2018 Chuyen Trung Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Gtmarkers records.
 *
 * @since  1.6
 */
class GtmarkersModelReviews extends JModelList
{
    
        
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.`id`',
				'ordering', 'a.`ordering`',
				'state', 'a.`state`',
				'created_by', 'a.`created_by`',
				'modified_by', 'a.`modified_by`',
				'm_alias', 'a.`m_alias`',
				'title', 'a.`title`',
				'alias', 'a.alias',
				'rating', 'a.`rating`',
				'comment', 'a.`comment`',
				'photo', 'a.`photo`',
				'timestamp', 'a.`timestamp`',
			);
		}

		parent::__construct($config);
	}

    
        
    
        
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
		// Filtering m_alias
		$this->setState('filter.m_alias', $app->getUserStateFromRequest($this->context.'.filter.m_alias', 'filter_m_alias', '', 'string'));

		// Filtering rating
		$this->setState('filter.rating', $app->getUserStateFromRequest($this->context.'.filter.rating', 'filter_rating', '', 'string'));


		// Load the parameters.
		$params = JComponentHelper::getParams('com_gtmarkers');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.timestamp', 'DESC');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

                
                    return parent::getStoreId($id);
                
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__gtmarkers_review` AS a');
                
		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');
		// Join over the foreign key 'm_alias'
		$query->select('`#__gtmarkers_marker_3017502`.`title` AS #__gtmarkers_marker_fk_value_3017502');
		$query->join('LEFT', '#__gtmarkers_marker AS #__gtmarkers_marker_3017502 ON #__gtmarkers_marker_3017502.`alias` = a.`m_alias`');
                

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(#__gtmarkers_marker_3017502.title LIKE ' . $search . '  OR  a.title LIKE ' . $search . '  OR  a.rating LIKE ' . $search . ' )');
			}
		}
                

		// Filtering m_alias
		$filter_m_alias = $this->state->get("filter.m_alias");

		if ($filter_m_alias !== null && !empty($filter_m_alias))
		{
			$query->where("a.`m_alias` = '".$db->escape($filter_m_alias)."'");
		}

		// Filtering rating
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
                //var_dump($orderCol,$orderDirn);
                //JFactory::getApplication()->close();
		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
                
		foreach ($items as $oneItem)
		{

			if (isset($oneItem->m_alias))
			{
				$values    = explode(',', $oneItem->m_alias);
				$textValue = array();

				foreach ($values as $value)
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query
						->select('`#__gtmarkers_marker_3017502`.`title`')
						->from($db->quoteName('#__gtmarkers_marker', '#__gtmarkers_marker_3017502'))
						->where($db->quoteName('alias') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->title;
					}
				}

				$oneItem->m_alias = !empty($textValue) ? implode(', ', $textValue) : $oneItem->m_alias;
			}
		}

		return $items;
	}
}
