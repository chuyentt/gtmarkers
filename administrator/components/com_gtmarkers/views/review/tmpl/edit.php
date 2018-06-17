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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'media/com_gtmarkers/css/form.css');
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {
		
	js('input:hidden.m_alias').each(function(){
		var name = js(this).attr('name');
		if(name.indexOf('m_aliashidden')){
			js('#jform_m_alias option[value="'+js(this).val()+'"]').attr('selected',true);
		}
	});
	js("#jform_m_alias").trigger("liszt:updated");
	});

	Joomla.submitbutton = function (task) {
		if (task == 'review.cancel') {
			Joomla.submitform(task, document.getElementById('review-form'));
		}
		else {
			
			if (task != 'review.cancel' && document.formvalidator.isValid(document.id('review-form'))) {
				
				Joomla.submitform(task, document.getElementById('review-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_gtmarkers&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="review-form" class="form-validate">

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_GTMARKERS_TITLE_REVIEW', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">

									<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
				<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
				<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
				<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
                                <input type="hidden" name="jform[alias]" value="<?php echo $this->item->alias; ?>" />

				<?php echo $this->form->renderField('created_by'); ?>
				<?php echo $this->form->renderField('modified_by'); ?>
                                
				<?php echo $this->form->renderField('m_alias'); ?>

			<?php
//				foreach((array)$this->item->m_alias as $value): 
//					if(!is_array($value)):
//						echo '<input type="hidden" class="m_alias" name="jform[m_aliashidden]['.$value.']" value="'.$value.'" />';
//					endif;
//				endforeach;
			?>				<?php echo $this->form->renderField('title'); ?>
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
					<input type="hidden" name="jform[photo_hidden]" id="jform_photo_hidden" value="<?php echo implode(',', $photoFiles); ?>" />
				<?php endif; ?>
                                        <input type="hidden" name="jform[timestamp]" value="<?php echo $this->item->timestamp; ?>" />


					<?php if ($this->state->params->get('save_history', 1)) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
					</div>
					<?php endif; ?>
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
