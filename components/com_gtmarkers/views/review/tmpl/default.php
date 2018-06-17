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

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gtmarkers');

if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gtmarkers'))
{
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}
?>

<div class="item_fields">

	<table class="table">
		

		<tr>
			<th><?php echo JText::_('COM_GTMARKERS_FORM_LBL_REVIEW_M_ALIAS'); ?></th>
			<td><?php echo $this->item->m_alias; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_GTMARKERS_FORM_LBL_REVIEW_TITLE'); ?></th>
			<td><?php echo $this->item->title; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_GTMARKERS_FORM_LBL_REVIEW_RATING'); ?></th>
			<td><?php echo $this->item->rating; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_GTMARKERS_FORM_LBL_REVIEW_COMMENT'); ?></th>
			<td><?php echo nl2br($this->item->comment); ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_GTMARKERS_FORM_LBL_REVIEW_PHOTO'); ?></th>
			<td>
			<?php
			foreach ((array) $this->item->photo as $singleFile) : 
				if (!is_array($singleFile) && strlen($singleFile) > 0) : 
					$uploadPath = 'images/com_gtmarkers' . DIRECTORY_SEPARATOR . $singleFile;
                                        echo '<a href="#" class="pop"><img src="' . JRoute::_(JUri::root() . $uploadPath, false) . '" style="width: 50px;"></a>';
				endif;
			endforeach;
		?></td>
		</tr>

	</table>

</div>
<!--
<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn" href="<?php echo JRoute::_('index.php?option=com_gtmarkers&task=review.edit&id='.$this->item->id); ?>"><?php echo JText::_("COM_GTMARKERS_EDIT_ITEM"); ?></a>

<?php endif; ?>
-->
<?php if (JFactory::getUser()->authorise('core.delete','com_gtmarkers.review.'.$this->item->id)) : ?>

	<a class="btn btn-danger" href="#deleteModal" role="button" data-toggle="modal">
		<?php echo JText::_("COM_GTMARKERS_DELETE_ITEM"); ?>
	</a>

	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo JText::_('COM_GTMARKERS_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo JText::sprintf('COM_GTMARKERS_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">Close</button>
			<a href="<?php echo JRoute::_('index.php?option=com_gtmarkers&task=review.remove&id=' . $this->item->id, false, 2); ?>" class="btn btn-danger">
				<?php echo JText::_('COM_GTMARKERS_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>

<?php endif; ?>

<div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">              
      <div class="modal-body">
      	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <img src="" class="imagepreview" style="width: 100%;" >
      </div>
    </div>
  </div>
</div>

<script>
    jQuery(document).ready(function ($) {
        $('.pop').on('click', function() {
                $('.imagepreview').attr('src', $(this).find('img').attr('src'));
                $('#imagemodal').modal('show');   
        });		
    });
</script>