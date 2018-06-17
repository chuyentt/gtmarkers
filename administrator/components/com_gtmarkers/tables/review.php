<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gtmarkers
 * @author     Chuyen Trung Tran <chuyentt@gmail.com>
 * @copyright  2018 Chuyen Trung Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;
/**
 * review Table class
 *
 * @since  1.6
 */
class GtmarkersTablereview extends JTable
{
	/**
	 * Check if a field is unique
	 *
	 * @param   string  $field  Name of the field
	 *
	 * @return bool True if unique
	 */
	private function isUnique ($field)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName($field))
			->from($db->quoteName($this->_tbl))
			->where($db->quoteName($field) . ' = ' . $db->quote($this->$field))
			->where($db->quoteName('id') . ' <> ' . (int) $this->{$this->_tbl_key});

		$db->setQuery($query);
		$db->execute();

		return ($db->getNumRows() == 0) ? true : false;
	}

	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'GtmarkersTablereview', array('typeAlias' => 'com_gtmarkers.review'));
		parent::__construct('#__gtmarkers_review', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 *
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable:bind
	 * @since   1.5
	 */
	public function bind($array, $ignore = '')
	{
	    $date = JFactory::getDate();
		$task = JFactory::getApplication()->input->get('task');
	    
		$input = JFactory::getApplication()->input;
		$task = $input->getString('task', '');

		if ($array['id'] == 0 && empty($array['created_by']))
		{
			$array['created_by'] = JFactory::getUser()->id;
		}

		if ($array['id'] == 0 && empty($array['modified_by']))
		{
			$array['modified_by'] = JFactory::getUser()->id;
		}

		if ($task == 'apply' || $task == 'save')
		{
			$array['modified_by'] = JFactory::getUser()->id;
		}

		// Support for alias field: alias
		if (empty($array['alias']))
		{
			if (empty($array['title']))
			{
				$array['alias'] = $this->stringURLSafe(date('Y-m-d H:i:s'));
			}
			else
			{
				if(JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$array['alias'] = JFilterOutput::stringURLUnicodeSlug(trim($array['title']));
				}
				else
				{
					$array['alias'] = $this->stringURLSafe(trim($array['title']));
				}
			}
		}


		// Support for multiple or not foreign key field: m_alias
			if(!empty($array['m_alias']))
			{
				if(is_array($array['m_alias'])){
					$array['m_alias'] = implode(',',$array['m_alias']);
				}
				else if(strrpos($array['m_alias'], ',') != false){
					$array['m_alias'] = explode(',',$array['m_alias']);
				}
			}
			else {
				$array['m_alias'] = '';
			}
		// Support for multi file field: photo
		if (!empty($array['photo']))
		{
			if (is_array($array['photo']))
			{
				$array['photo'] = implode(',', $array['photo']);
			}
			elseif (strpos($array['photo'], ',') != false)
			{
				$array['photo'] = explode(',', $array['photo']);
			}
		}
		else
		{
			$array['photo'] = '';
		}


		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (!JFactory::getUser()->authorise('core.admin', 'com_gtmarkers.review.' . $array['id']))
		{
			$actions         = JAccess::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_gtmarkers/access.xml',
				"/access/section[@name='review']/"
			);
			$default_actions = JAccess::getAssetRules('com_gtmarkers.review.' . $array['id'])->getData();
			$array_jaccess   = array();

			foreach ($actions as $action)
			{
                if (key_exists($action->name, $default_actions))
                {
                    $array_jaccess[$action->name] = $default_actions[$action->name];
                }
			}

			$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$this->setRules($array['rules']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * This function convert an array of JAccessRule objects into an rules array.
	 *
	 * @param   array  $jaccessrules  An array of JAccessRule objects.
	 *
	 * @return  array
	 */
	private function JAccessRulestoArray($jaccessrules)
	{
		$rules = array();

		foreach ($jaccessrules as $action => $jaccess)
		{
			$actions = array();

			if ($jaccess)
			{
				foreach ($jaccess->getData() as $group => $allow)
				{
					$actions[$group] = ((bool)$allow);
				}
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
			$this->ordering = self::getNextOrder();
		}
		
		// Check if alias is unique
		if (!$this->isUnique('alias'))
		{
			$this->alias .= '-' . $this->stringURLSafe(date('Y-m-d-H:i:s'));
		}
		
		// Support multi file field: photo
		$app = JFactory::getApplication();
		$files = $app->input->files->get('jform', array(), 'raw');
		$array = $app->input->get('jform', array(), 'ARRAY');

		if ($files['photo'][0]['size'] > 0)
		{
			// Deleting existing files
			$oldFiles = GtmarkersHelper::getFiles($this->id, $this->_tbl, 'photo');

			foreach ($oldFiles as $f)
			{
				$oldFile = JPATH_ROOT . '/images/com_gtmarkers/' . $f;

				if (file_exists($oldFile) && !is_dir($oldFile))
				{
					unlink($oldFile);
				}
			}

			$this->photo = "";

			foreach ($files['photo'] as $singleFile )
			{
				jimport('joomla.filesystem.file');

				// Check if the server found any error.
				$fileError = $singleFile['error'];
				$message = '';

				if ($fileError > 0 && $fileError != 4)
				{
					switch ($fileError)
					{
						case 1:
							$message = JText::_('File size exceeds allowed by the server');
							break;
						case 2:
							$message = JText::_('File size exceeds allowed by the html form');
							break;
						case 3:
							$message = JText::_('Partial upload error');
							break;
					}

					if ($message != '')
					{
						$app->enqueueMessage($message, 'warning');

						return false;
					}
				}
				elseif ($fileError == 4)
				{
					if (isset($array['photo']))
					{
						$this->photo = $array['photo'];
					}
				}
				else
				{
					// Check for filesize
					$fileSize = $singleFile['size'];

					if ($fileSize > 2097152)
					{
						$app->enqueueMessage('File bigger than 2MB', 'warning');

						return false;
					}

					// Check for filetype
					$okMIMETypes = 'image/png,image/jpeg,image/jpeg';
					$validMIMEArray = explode(',', $okMIMETypes);
					$fileMime = $singleFile['type'];

					if (!in_array($fileMime, $validMIMEArray))
					{
						$app->enqueueMessage('This filetype is not allowed', 'warning');

						return false;
					}

					// Replace any special characters in the filename
					jimport('joomla.filesystem.file');
                                        // Thêm ngày giờ vào trước file
					$filename = $this->stringURLSafe(date('Y-m-d H:i:s')).'_'.JFile::stripExt($singleFile['name']);
					$extension = JFile::getExt($singleFile['name']);
					$filename = preg_replace("/[^A-Za-z0-9]/i", "-", $filename);
					$filename = $filename . '.' . $extension;
					$uploadPath = JPATH_ROOT . '/images/com_gtmarkers/' . $filename;
					$fileTemp = $singleFile['tmp_name'];

					if (!JFile::exists($uploadPath))
					{
						if (!JFile::upload($fileTemp, $uploadPath))
						{
							$app->enqueueMessage('Error moving file', 'warning');

							return false;
						}
					}

					$this->photo .= (!empty($this->photo)) ? "," : "";
					$this->photo .= $filename;
				}
			}
		}
		else
		{
			$this->photo .= $array['photo_hidden'];
		}

		return parent::check();
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return   boolean  True on success.
	 *
	 * @since    1.0.4
	 *
	 * @throws Exception
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				throw new Exception(500, JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE `' . $this->_tbl . '`' .
			' SET `state` = ' . (int) $state .
			' WHERE (' . $where . ')' .
			$checkin
		);
		$this->_db->execute();

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin each row.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		return true;
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return string The asset name
	 *
	 * @see JTable::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_gtmarkers.review.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 *
	 * @param   JTable   $table  Table name
	 * @param   integer  $id     Id
	 *
	 * @see JTable::_getAssetParentId
	 *
	 * @return mixed The id on success, false on failure.
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = JTable::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_gtmarkers');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}

	/**
	 * Delete a record by id
	 *
	 * @param   mixed  $pk  Primary key value to delete. Optional
	 *
	 * @return bool
	 */
	public function delete($pk = null)
	{
		$this->load($pk);
		$result = parent::delete($pk);
		
		if ($result)
		{
			jimport('joomla.filesystem.file');
                        
                        // Fixed sửa lại để khi xóa bản ghi thì xóa file $photoFiles = explode(',',$this->photo);
                        $photoFiles = explode(',',$this->photo);
			foreach ($photoFiles as $photoFile)
			{
				JFile::delete(JPATH_ROOT . '/images/com_gtmarkers/' . $photoFile);
			}
		}

		return $result;
	}
        
	/**
         * Sửa để tạo alias thân thiện
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents", whitespaces are replaced by hyphens and the string is lowercase.
	 *
	 * @param   string  $string    String to process
	 * @param   string  $language  Language to transilterate to
	 *
	 * @return  string  Processed string
	 *
	 * @since   11.1
	 */
	public static function stringURLSafe($string, $language = '')
	{
                $trans = array(
                "đ"=>"d","ă"=>"a","â"=>"a","á"=>"a","à"=>"a",
                "ả"=>"a","ã"=>"a","ạ"=>"a",
                "ấ"=>"a","ầ"=>"a","ẩ"=>"a","ẫ"=>"a","ậ"=>"a",
                "ắ"=>"a","ằ"=>"a","ẳ"=>"a","ẵ"=>"a","ặ"=>"a",
                "é"=>"e","è"=>"e","ẻ"=>"e","ẽ"=>"e","ẹ"=>"e",
                "ế"=>"e","ề"=>"e","ể"=>"e","ễ"=>"e","ệ"=>"e",
                "í"=>"i","ì"=>"i","ỉ"=>"i","ĩ"=>"i","ị"=>"i",
                "ư"=>"u","ô"=>"o","ơ"=>"o","ê"=>"e",
                "Ư"=>"u","Ô"=>"o","Ơ"=>"o","Ê"=>"e",
                "ú"=>"u","ù"=>"u","ủ"=>"u","ũ"=>"u","ụ"=>"u",
                "ứ"=>"u","ừ"=>"u","ử"=>"u","ữ"=>"u","ự"=>"u",
                "ó"=>"o","ò"=>"o","ỏ"=>"o","õ"=>"o","ọ"=>"o",
                "ớ"=>"o","ờ"=>"o","ở"=>"o","ỡ"=>"o","ợ"=>"o",
                "ố"=>"o","ồ"=>"o","ổ"=>"o","ỗ"=>"o","ộ"=>"o",
                "ú"=>"u","ù"=>"u","ủ"=>"u","ũ"=>"u","ụ"=>"u",
                "ứ"=>"u","ừ"=>"u","ử"=>"u","ữ"=>"u","ự"=>"u",
                "ý"=>"y","ỳ"=>"y","ỷ"=>"y","ỹ"=>"y","ỵ"=>"y",
                "Ý"=>"Y","Ỳ"=>"Y","Ỷ"=>"Y","Ỹ"=>"Y","Ỵ"=>"Y",
                "Đ"=>"D","Ă"=>"A","Â"=>"A","Á"=>"A","À"=>"A",
                "Ả"=>"A","Ã"=>"A","Ạ"=>"A",
                "Ấ"=>"A","Ầ"=>"A","Ẩ"=>"A","Ẫ"=>"A","Ậ"=>"A",
                "Ắ"=>"A","Ằ"=>"A","Ẳ"=>"A","Ẵ"=>"A","Ặ"=>"A",
                "É"=>"E","È"=>"E","Ẻ"=>"E","Ẽ"=>"E","Ẹ"=>"E",
                "Ế"=>"E","Ề"=>"E","Ể"=>"E","Ễ"=>"E","Ệ"=>"E",
                "Í"=>"I","Ì"=>"I","Ỉ"=>"I","Ĩ"=>"I","Ị"=>"I",
                "Ư"=>"U","Ô"=>"O","Ơ"=>"O","Ê"=>"E",
                "Ư"=>"U","Ô"=>"O","Ơ"=>"O","Ê"=>"E",
                "Ú"=>"U","Ù"=>"U","Ủ"=>"U","Ũ"=>"U","Ụ"=>"U",
                "Ứ"=>"U","Ừ"=>"U","Ử"=>"U","Ữ"=>"U","Ự"=>"U",
                "Ó"=>"O","Ò"=>"O","Ỏ"=>"O","Õ"=>"O","Ọ"=>"O",
                "Ớ"=>"O","Ờ"=>"O","Ở"=>"O","Ỡ"=>"O","Ợ"=>"O",
                "Ố"=>"O","Ồ"=>"O","Ổ"=>"O","Ỗ"=>"O","Ộ"=>"O",
                "Ú"=>"U","Ù"=>"U","Ủ"=>"U","Ũ"=>"U","Ụ"=>"U",
                "Ứ"=>"U","Ừ"=>"U","Ử"=>"U","Ữ"=>"U","Ự"=>"U",);
		// Remove any '-' from the string since they will be used as concatenaters
		$str = str_replace('-', ' ', $string);
                
                $str = strtr($str, $trans);

		// Transliterate on the language requested (fallback to current language if not specified)
		$lang = $language == '' || $language == '*' ? \JFactory::getLanguage() : Language::getInstance($language);
		$str = $lang->transliterate($str);

		// Trim white spaces at beginning and end of alias and make lowercase
		$str = trim(StringHelper::strtolower($str));

		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);

		// Trim dashes at beginning and end of alias
		$str = trim($str, '-');

		return $str;
	}
}