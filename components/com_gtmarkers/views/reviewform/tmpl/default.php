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

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_gtmarkers', JPATH_SITE);
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/media/com_gtmarkers/js/form.js');

$user    = JFactory::getUser();
$canEdit = GtmarkersHelpersGtmarkers::canUserEdit($this->item, $user);


?>

<div class="review-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new Exception(JText::_('COM_GTMARKERS_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo JText::sprintf('COM_GTMARKERS_EDIT_ITEM_TITLE', $this->item->id); ?></h1>
		<?php else: ?>
			<h1><?php echo JText::_('COM_GTMARKERS_ADD_ITEM_TITLE'); ?></h1>
		<?php endif; ?>

		<form id="form-review"
			  action="<?php echo JRoute::_('index.php?option=com_gtmarkers&task=review.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />

	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />

	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />

	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />

	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
        
        <input type="hidden" name="jform[alias]" value="<?php echo $this->item->alias; ?>" />
        
<!--        <input type="hidden" name="jform[ajax]" value="true" />-->

				<?php echo $this->form->getInput('created_by'); ?>
				<?php echo $this->form->getInput('modified_by'); ?>
	<?php echo $this->form->renderField('m_alias'); ?>

	<?php foreach((array)$this->item->m_alias as $value): ?>
		<?php if(!is_array($value)): ?>
			<input type="hidden" class="m_alias" name="jform[m_aliashidden][<?php echo $value; ?>]" value="<?php echo $value; ?>" />
		<?php endif; ?>
	<?php endforeach; ?>
	<?php echo $this->form->renderField('title'); ?>

	<?php echo $this->form->renderField('rating'); ?>

	<?php echo $this->form->renderField('comment'); ?>

	<?php echo $this->form->renderField('photo'); ?>

				<?php if (!empty($this->item->photo)) : ?>
					<?php $photoFiles = array(); ?>
					<?php foreach ((array)$this->item->photo as $fileSingle) : ?>
						<?php if (!is_array($fileSingle)) : ?>
							<a href="<?php echo JRoute::_(JUri::root() . 'images/com_gtmarkers' . DIRECTORY_SEPARATOR . $fileSingle, false);?>"><?php echo $fileSingle; ?></a> | 
							<?php $photoFiles[] = $fileSingle; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
				<input type="hidden" name="jform[photo_hidden]" id="jform_photo_hidden" value="<?php echo implode(',', $photoFiles); ?>" />
	<input type="hidden" name="jform[timestamp]" value="<?php echo $this->item->timestamp; ?>" />

			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<?php echo JText::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn"
					   href="<?php echo JRoute::_('index.php?option=com_gtmarkers&task=reviewform.cancel'); ?>"
					   title="<?php echo JText::_('JCANCEL'); ?>">
						<?php echo JText::_('JCANCEL'); ?>
					</a>
				</div>
			</div>
        
			<input type="hidden" name="option" value="com_gtmarkers"/>
			<input type="hidden" name="task"
				   value="reviewform.save"/>
			<?php echo JHtml::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
