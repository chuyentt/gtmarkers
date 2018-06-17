<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_GTMarkers
 * @author     Chuyen Trung Tran <chuyentt@gmail.com>
 * @copyright  2018 Chuyen Trung Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of GTMarkers records.
 *
 * @since  1.6
 */
class GtmarkersModelImports extends JModelList
{
        
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

		// Load the parameters.
		$params = JComponentHelper::getParams('com_gtmarkers');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.title', 'asc');
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
		$db	= $this->getDbo();
		$query	= $db->getQuery(true);

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
                

		return $items;
	}

	/**
	 * update current message
	 */
	public function importItems($rows)
	{
            // quoted table and column names for sql commands
            $db	= $this->getDbo();
            $table = $db->quoteName('#__gtmarkers_marker');
            $columns = array("created_by", "modified_by", "state", "title", "pos", "alias");
            $ok = true;
            
            foreach ($rows as $row) {
                $query		= $db->getQuery(true);	//new query
                $query->insert($table);
                $query->columns($db->quoteName($columns));
                $query->values('"'.implode('","', $row).'"');
                $db->setQuery($query);
                $result	= $db->execute();
                $ok = $ok && $result;
            }
            return $ok;
	}
        
        public function saveImports($rows) {
            $db	= JFactory::getDbo();
            $table = $db->quoteName('#__marker');
            $colnames = array("id", "state", "created_by", "modified_by", "ordering", "title", "pos", "alias");
            $keyqt = $db->quoteName($colnames[0]);
            // get column header names from csv file
            $headers	= array_shift($rows);
//            print_r($keyqt);
//            JFactory::getApplication()->close();
            foreach ($rows as $item) {
                // first query if record with this id already exists
                // if so, $numRows will be > 0
                $query = $db->getQuery(true);	//new query
                $query->select( $keyqt )->from( $table )->where( $keyqt.'='.$key );
                $db->setQuery($query)->execute();
                $numRows = $db->getNumRows();
                // set new values for columns
                $query->clear();			//new query
                foreach($map as $colnum => $hdr) {
                        $query->set( $colqt[$colnum]."=".$db->quote( $row[$hdr]) );
                }
                if( $numRows > 0 ) {			// pk already exists so update record
                        $query->update( $table );
                        $query->where(  $keyqt.'='.$key );
                } else {				// new pk so insert new record
                        $query->insert($table);
                }
                $result	= $db->setQuery($query)->execute();
                $ok	= $ok && $result;
            }
            return true;
        }
        
	/**
	 * Get an instance of JTable class
	 *
	 * @param   string $type   Name of the JTable class to get an instance of.
	 * @param   string $prefix Prefix for the table class name. Optional.
	 * @param   array  $config Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	public function getTable($type = 'Marker', $prefix = 'GtmarkersTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_gtmarkers/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

}
