<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_GTMarkers
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
$canCreate  = $user->authorise('core.create', 'com_gtmarkers');
$canEdit    = $user->authorise('core.edit', 'com_gtmarkers');
$canCheckin = $user->authorise('core.manage', 'com_gtmarkers');
$canChange  = $user->authorise('core.edit.state', 'com_gtmarkers');
$canDelete  = $user->authorise('core.delete', 'com_gtmarkers');
?>

<div class="container">
<!-- Vị trí của hộp search -->
<!--<input id="pac-input" class="form-control" type="text" placeholder="Tìm ...">-->
<!--<input id="pac-input" class="form-control" type="text" onfocus="this.style.width='300px'" onblur="this.style.width='28px';this.value=''" style="-webkit-transition: width 0.30s linear 0s;-moz-transition: width 0.30s linear 0s;-o-transition: width 0.30s linear 0s;transition: width 0.30s linear 0s;width:28px;height:28px;borderRadius:2px;boxShadow:0 1px 4px rgba(0,0,0,0.3);margin:10px 14px;">-->

<!-- Vị trí dành cho Map -->
<div id="map" class="map"></div>
  <!-- Begin modal add and edit review -->
  <div class="modal fade" id="editReviewModal" tabindex="-1" role="dialog" aria-labelledby="editReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h2>Đánh giá: <span id="star"></span></h2>
        </div>
        <!-- Form -->
        <form data-async data-target="#target" id="form-review" action="/index.php?option=com_gtmarkers&amp;task=review.save" method="post" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" id="jform_id" name="jform[id]" value="">
            <input type="hidden" id="jform_ordering" name="jform[ordering]" value="">
            <input type="hidden" id="jform_state" name="jform[state]" value="1">
            <input type="hidden" id="jform_checked_out" name="jform[checked_out]" value="">
            <input type="hidden" id="jform_checked_out_time" name="jform[checked_out_time]" value="">
            <input type="hidden" id="jform_rating" name="jform[rating]" value="">
            <input type="hidden" id="jform_alias" name="jform[alias]" value="" />
            <input type="hidden" id="jform_m_alias" name="jform[m_alias]" value="">
            <input type="hidden" name="option" value="com_gtmarkers">
            <input type="hidden" name="task" value="reviewform.save">
            <?php echo JHtml::_('form.token'); ?>
            <input type="hidden" name="jform[ajax]" value="true">
            
            <div class="form-group">
              <label for="jform_title" class="control-label">Tiêu đề (ngắn gọn)</label>
              <input type="text" name="jform[title]" id="jform_title" value="" maxlength="20" class="form-control required" placeholder="Mốc dùng tốt | Mốc đã mất | Mốc đã hỏng | Độ chính xác thấp" required="required" aria-required="true" aria-invalid="false">
            </div>
            <div class="form-group">
              <label for="jform_comment" class="control-label">Mô tả chi tiết (nếu cần)</label>
              <textarea class="form-control" id="jform_comment" name="jform[comment]" placeholder="Mô tả chi tiết (nếu cần)"></textarea>
            </div>
            
            <div class="custom-file">
              <label for="jform_photo" class="custom-file-label">Hình ảnh (nếu có)</label>
              <input id="jform_photo" name="jform[photo][]" type="file" class="custom-file-input" multiple 
     data-msg-placeholder="Chọn các ảnh để upload...">
              <input id="jform_photo_hidden" type="hidden" name="jform[photo_hidden]" value="" />
              <div id="photos"></div>
            </div>
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
            <button type="submit" class="btn btn-primary">Gửi đánh giá &amp; nhận xét</button>
          </div>
        </form>
        <!-- End Form -->
      </div>
    </div>
    <!-- Begin modal photo preview -->
    <div id="photoModal" class="modal modal-child" data-backdrop-limit="1" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true" data-modal-parent="#editReviewModal">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button id="btnClosePhotoModal" type="button" class="close">&times;</button>
                    <h4 class="modal-title">Hình ảnh</h4>
                </div>
                <div class="modal-body">
                    <img id="photo" src="" class="imagepreview" style="width: 100%;" >
                </div>
                <div class="modal-footer">
                    <button id="btnCancelPhotoModal" class="btn btn-default">Đóng</button>
                </div>

            </div>
        </div>
    </div>
    <!-- End modal photo preview -->
  </div>
  <!-- End Modal -->
</div>

<?php
$document   = JFactory::getDocument();
$language   = JFactory::getLanguage();

/**
* Lấy tham số thiết lập của component
* Hàm này sẽ chọn một số tham số cần thiết cho hiển thị bản đồ
*
* @param   mảng  &$params là một mảng chứa các tham số của component
*
* @return  mảng  Một mảng chứa các tham số cần thiết
*
*/
function getMapParams($params) {
    $user = JFactory::getUser();
    $mapapi = $params->get('map_api_key');
    if ($mapapi != 'YOUR_API_KEY' && strlen($mapapi) == 39) {
        $params = array(
            'center' => $params->get('center'),
            'zoom' => $params->get('zoom'),
            'maptypeid' => $params->get('maptypeid'),
            'styles' => $params->get('styles'),
            'header_height' => $params->get('header_height'),
            'height' => $params->get('height'),
            'map_api_key' => $mapapi,
            'form_token' => JHtml::_('form.token'),
            'can_create' => $user->authorise('core.create', 'com_gtmarkers'),
            'can_edit' => $user->authorise('core.edit', 'com_gtmarkers'),
            'u_id' => JFactory::getUser()->id,
            'url' => JURI::root(),
            'firebase_config' => $params->get('firebase_config') // Nên kiểm tra điều kiện quyền để lấy giá trị này
        );
        return $params;
    } else {
            JError::raiseWarning( 100, 'No Google Maps API Key entered in your configuration' );
    }
}

/**
 * Thêm bản đồ vào view và làm việc với bản đồ
 * Hàm này sẽ lấy tham số thiết lập bản đồ từ hàm getMapParams để thiết lập
 *
 * @param   mảng  &$params là một mảng chứa các tham số cần thiết cho bản đồ
 * 
 * @return  mảng  Một mảng chứa các tham số cần thiết
 *
 */
function addMap($params) {
    $document = JFactory::getDocument();
    $assetUrl = JURI::root().'components/com_gtmarkers/assets/';

    // Lấy dữ liệu tham số bản đồ để chuyển qua mã JavaScript (JS)
    $mapParams = getMapParams($params);

    // Lưu trữ các tham số từ php chuyển qua sử dụng trong JS
    // phía JS sử dụng: const params = Joomla.getOptions('params');
    $document->addScriptOptions('params', $mapParams);
    
    // Thêm định nghĩa style
    $document->addStyleSheet($assetUrl.'css/mapme.css');

    // Thêm mã JS để hiển thị bản đồ làm việc với bản đồ.
    // Lưu ý: ?v1.1 ở cuối đường dẫn URL là báo cho phía client biết
    // có sự thay đổi mã nguồn từ phía server. Nếu không thì những thay đổi
    // mã từ phía server sẽ không có hiệu lực vì cơ chế cache từ client.
    // Các thay đổi mã JS phần toàn cục: biến, hằng, hàm thì nên thêm phiên
    // bản ví dụ: ?v1.2, ?v1.3,... những thay đổi mã trong nội bộ của một
    // hàm trước đó thì không cần sửa phiên bản.
    
    $document->addScript($assetUrl.'js/mapme.js');

    // Thêm Marker Clustering
    $document->addScript('//developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js');
    
    // Thêm Google Maps API
    $document->addScript('//maps.googleapis.com/maps/api/js?key='.$mapParams['map_api_key'].'&language=vi&libraries=places,geometry,visualization&callback=initMap', true, true, true);
    $document->addScript($assetUrl.'js/review.js');

}

// Chuyển items qua JavaScript để hiển thị markers
$document->addScriptOptions('items', $this->customItems);

// Gọi hàm để thực thi những thay đổi
addMap($this->params);
