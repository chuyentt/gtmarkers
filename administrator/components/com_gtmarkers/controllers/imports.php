<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_GTMarkers
 * @author     Chuyen Trung Tran <chuyentt@gmail.com>
 * @copyright  2018 Chuyen Trung Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;

/**
 * Imports list controller class.
 *
 * @since  1.6
 */
class GtmarkersControllerImports extends JControllerAdmin
{
	/**
	 * Method to clone existing Imports
	 *
	 * @return void
	 */
	public function duplicate()
	{
		// Check for request forgeries
		Jsession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new Exception(JText::_('COM_GTMARKERS_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Jtext::_('COM_GTMARKERS_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_gtmarkers&view=imports');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function getModel($name = 'imports', $prefix = 'GtmarkersModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks   = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}
        
        /**
	 * Method to save the submitted imports values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveImports()
	{       
            // Get the input
            $input = JFactory::getApplication()->input;

            // Get the model
            $model = $this->getModel();

            // Kiểm tra người dùng về quyền cập nhật
            $user = JFactory::getUser();

            $data = [];
            
            $tmp_src = $_FILES["jsonfile"]["tmp_name"];
            
            switch (mime_content_type($_FILES["jsonfile"]["tmp_name"])) {
            case 'text/plain': // có thể là json, geojson
                $str = file_get_contents($tmp_src);
                $json = json_decode($str, true);
                $markers = $json['markers'];
                foreach($markers as $marker) {
                    if (intval($marker['id']) === 0 || intval($marker['created_by']) === 0) {
                        $item['created_by'] = $user->id;
                        $item['modified_by'] = $user->id;
                    } else {
                        $item['modified_by'] = $user->id;
                    }
                    $item['state'] = isset($marker['state']) ? intval($marker['state']) : 1;
                    
                    $item['title'] = $marker['properties']['name'];
                    $item['pos'] = $marker['geometry']['coordinates'][1].','.$marker['geometry']['coordinates'][0];
                    $item['alias'] = JFilterOutput::stringURLSafe(trim($item['title']));
                    //$item['properties'] = json_encode($marker['properties']);
                    array_push($data, $item);
                }
                break;
            case 'application/xml': // có thể là xml, gpx
                $xml = simplexml_load_file($tmp_src) or die("Error: Cannot create object");
                $wpts = $xml->wpt;
                foreach($wpts as $wpt) { 
                    if (intval($wpt['id']) === 0 || intval($wpt['created_by']) === 0) {
                        $item['created_by'] = $user->id;
                        $item['modified_by'] = $user->id;
                    } else {
                        $item['modified_by'] = $user->id;
                    }
                    $item['state'] = isset($wpt['state']) ? intval($wpt['state']) : 1;
                    $item['title'] = (string)$wpt->name;
                    $item['pos'] = (float)$wpt['lat'].','.(float)$wpt['lon'];
                    $item['alias'] = JFilterOutput::stringURLSafe(trim($item['title']));
                    //$item['properties'] = json_encode(array('desc'=>(string)$wpt->desc));
                    array_push($data, $item);
                }
                break;
            default:
                break;
            }
            // Save the imports
            $return = $model->importItems($data);//saveImports($data);

            if ($return)
            {
                $this->setMessage(Jtext::_('COM_GTMARKERS_ITEMS_SUCCESS_IMPORTS'));
            }
            $this->setRedirect('index.php?option=com_gtmarkers&view=imports');
            // Close the application
            //JFactory::getApplication()->close();
	}
}
