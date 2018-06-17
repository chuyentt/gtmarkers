<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gtmarkers
 * @author     Chuyen Trung Tran <chuyentt@gmail.com>
 * @copyright  2018 Chuyen Trung Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Gtmarkers', JPATH_COMPONENT);
JLoader::register('GtmarkersController', JPATH_COMPONENT . '/controller.php');


// Execute the task.
$controller = JControllerLegacy::getInstance('Gtmarkers');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
