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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

// Add Stylesheets
//JHtml::_('stylesheet', 'font-awesome.min.css', array('version' => 'auto', 'relative' => true));
$doc = JFactory::getDocument();
$doc->addStyleSheet('https://use.fontawesome.com/releases/v5.0.10/css/all.css',$type = 'text/css');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'administrator/components/com_gtmarkers/assets/css/gtmarkers.css');
$document->addStyleSheet(JUri::root() . 'media/com_gtmarkers/css/list.css');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_gtmarkers');
$saveOrder = $listOrder == 'a.`ordering`';

$config   = JFactory::getConfig();
$tmp_dest = $config->get('tmp_path');
//$tmp_src  = $userfile['tmp_name'];
//echo $tmp_dest;
//// Move uploaded file.
//jimport('joomla.filesystem.file');
//JFile::upload($tmp_src, $tmp_dest, false, true);
//echo JRoute::_(JUri::root() . 'upload' . DIRECTORY_SEPARATOR . $fileSingle, false);

$target_file = $tmp_dest . DIRECTORY_SEPARATOR . basename($_FILES["jsonfile"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
?>
<form action="" method="POST" role="form" enctype="multipart/form-data">
    <?php if (!empty($this->sidebar)): ?>
        <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif; ?>
            <legend>Upload</legend>
            <div class="form-group">
            <label for="file"><span class="icon-copy" aria-hidden="true"></span>JSON file input</label>
            <input id="jsonfile" type="file" class="btn btn-success" name="jsonfile" required="" />
            </div>
            <div class="form-group">
                <input class="form-control" type="submit" value="Upload & Process" name="submit">
            </div>
        </div>
    <input type="hidden" name="option" value="com_gtmarkers"/>
    <input type="hidden" name="task" value="imports.saveImports"/>
</form>
