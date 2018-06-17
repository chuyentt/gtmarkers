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
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gtmarkers') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'reviewform.xml');
$canEdit    = $user->authorise('core.edit', 'com_gtmarkers') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'reviewform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gtmarkers');
$canChange  = $user->authorise('core.edit.state', 'com_gtmarkers');
$canDelete  = $user->authorise('core.delete', 'com_gtmarkers');
?>

<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
	<table class="table table-striped" id="reviewList">
		<thead>
		<tr>
<!--			<?php if (isset($this->items[0]->state)): ?>
				<th width="5%">
	<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
</th>
			<?php endif; ?>
-->
							<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GTMARKERS_REVIEWS_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GTMARKERS_REVIEWS_M_ALIAS', 'a.m_alias', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GTMARKERS_REVIEWS_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GTMARKERS_REVIEWS_RATING', 'a.rating', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GTMARKERS_REVIEWS_COMMENT', 'a.comment', $listDirn, $listOrder); ?>
				</th>
                                <th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GTMARKERS_REVIEWS_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GTMARKERS_REVIEWS_TIMESTAMP', 'a.timestamp', $listDirn, $listOrder); ?>
				</th>

<!--
							<?php if ($canEdit || $canDelete): ?>
					<th class="center">
				<?php echo JText::_('COM_GTMARKERS_REVIEWS_ACTIONS'); ?>
				</th>
				<?php endif; ?>
-->
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<?php $canEdit = $user->authorise('core.edit', 'com_gtmarkers'); ?>

							<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gtmarkers')): ?>
					<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
				<?php endif; ?>

			<tr class="row<?php echo $i % 2; ?>">
<!--
				<?php if (isset($this->items[0]->state)) : ?>
					<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
					<td class="center">
	<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? JRoute::_('index.php?option=com_gtmarkers&task=review.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
	<?php if ($item->state == 1): ?>
		<i class="icon-publish"></i>
	<?php else: ?>
		<i class="icon-unpublish"></i>
	<?php endif; ?>
	</a>
</td>
				<?php endif; ?>
-->
								<td>

					<?php echo $item->id; ?>
				</td>
				<td>

					<?php echo $item->m_alias; ?>
				</td>
				<td>
				<?php if (isset($item->checked_out) && $item->checked_out) : ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'reviews.', $canCheckin); ?>
				<?php endif; ?>
				<a href="<?php echo JRoute::_('index.php?option=com_gtmarkers&view=review&id='.(int) $item->id); ?>">
				<?php echo $this->escape($item->title); ?></a>
				</td>
				<td>

                                    <div class="row" style="white-space: nowrap" id="<?php echo 'star-item-'.$item->id; ?>"><?php echo $item->rating; ?></span>
				</td>
				<td>

					<?php echo $item->comment; ?>
				</td>
                                <td>

					<?php echo $item->created_by_name; ?>
				</td>
				<td>

					<?php echo $item->timestamp; ?>
				</td>
                                

<!--
								<?php if ($canEdit || $canDelete): ?>
					<td class="center">
						<?php if ($canEdit): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_gtmarkers&task=reviewform.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_gtmarkers&task=reviewform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
						<?php endif; ?>
					</td>
				<?php endif; ?>
-->
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<!--
	<?php if ($canCreate) : ?>
		<a href="<?php echo JRoute::_('index.php?option=com_gtmarkers&task=reviewform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			<?php echo JText::_('COM_GTMARKERS_ADD_ITEM'); ?></a>
	<?php endif; ?>
-->
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script>
    jQuery(document).ready(function ($) {
        // Tìm tất cả các id='star-item-*'
        // để đọc giá trị trong đó và thay bằng star icon
        // <div id="star-item-1">4</div> => <div id="star-item-1">*****</div>
        $('*[id*=star-item-]:visible').each(function() {
            var r = isNaN(parseInt(this.innerHTML)) ? 0: parseInt(this.innerHTML);

            // Tạo star
            doStar(this, r);
        });
    });
    function doStar(el, r=0) {
        el.innerHTML = '';
        for (var i = 1; i<=r; i++) {
            var a = document.createElement("span");
            a.setAttribute("class", "fas fa-star text-warning");
            el.appendChild(a);
        }

        for (var i = r+1; i<=5; i++) {
            var a = document.createElement("span");
            a.setAttribute("class", "far fa-star text-muted");
            el.appendChild(a);
        }
    }
</script>

<?php if($canDelete) : ?>
<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
                // Tìm tất cả các id='star-item-*'
                jQuery('*[id*=star-item-2]').each(function() {
                    
                });
	});

	function deleteItem() {

		if (!confirm("<?php echo JText::_('COM_GTMARKERS_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
</script>
<?php endif; ?>
