/**
 * @version    CVS: 1.0.0
 * @package    Com_GTMarkers
 * @author     Chuyen Trung Tran <chuyentt@gmail.com>
 * @copyright  2018 Chuyen Trung Tran
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/*
 * Create a rating 5 stars
 * <!-- Font Awesome Icon Library -->
 * <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css">
 * <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js">
 * Params:
 *  bid - base id of the element to showing 5 stars. Eg. createStar("star", 3) for <div id="star"></div>
 *  r - rating, initial rating
 *  tid - target id of the element to showing rating.
 */
function createStar(bid, tid, r = 0) {
  var rating = parseInt(r);
  if ((document.getElementById(tid).tagName) === "INPUT") {
    document.getElementById(tid).value = rating;
  } else {
    document.getElementById(tid).innerHTML = rating;
  }
  document.getElementById(bid).innerHTML = "";
  for (var i = 1; i<=5; i++) {
    var a = document.createElement("span");
    a.setAttribute("class", "far fa-star");
    a.setAttribute("style", "color: orange;");
    var id = i+"-"+bid;
    a.setAttribute("id", id);
    document.getElementById(bid).appendChild(a);
    jQuery('#'+id).hover(function() {
      var _rating = parseInt(this.id.split("-")[0]);
      setStar(bid, _rating);
    }, function(){
      setStar(bid, rating);
    });

    jQuery('#'+id).click(function() {
      rating = parseInt(this.id.split("-")[0]);
      if ((document.getElementById(tid).tagName) === "INPUT") {
        document.getElementById(tid).value = rating;
      } else {
        document.getElementById(tid).innerHTML = rating;
      }
      setStar(bid, rating);
    });
  }
  setStar(bid,rating);
}

function setStar(bid, r) {
  for (var i = 1; i<=r; i++) {
    var id = i+"-"+bid;
    document.getElementById(id).setAttribute("class", "fas fa-star");
  }

  for (var i1 = r+1; i1<=5; i1++) {
    var id = i1+"-"+bid;
    document.getElementById(id).setAttribute("class", "far fa-star");
  }
}

function showReviewFormModal(bid, tid, item) {
    jQuery.noConflict();
    (function( $ ) {
        // Xóa hết form cũ
        createStar(bid, tid, 0);
        $('#jform_id').val('');
        $('#jform_title').val('');
        $('#jform_rating').val('');
        $('#jform_comment').val('');
        $('#jform_state').val('');
        $('#jform_m_alias').val(item.m_alias);
        $('#jform_alias').val('');
        $('#jform_ordering').val('');
        $('#jform_photo').val('');
        $('#jform_photo_hidden').val('');
        $('#photos').empty();

        if (item.id > 0) {
            createStar(bid, tid, parseInt(item.rating));
            $('#jform_id').val(item.id);
            $('#jform_title').val(item.title);
            $('#jform_rating').val(item.rating);
            $('#jform_comment').val(item.comment);
            $('#jform_state').val(item.state);
            $('#jform_m_alias').val(item.m_alias);
            $('#jform_alias').val(item.alias);
            $('#jform_ordering').val(item.ordering);
            $('#jform_photo').val('');
            if (item.photo.length > 0) {
                var photos = item.photo.split(',');
                for (var i = 0; i < photos.length; i++) {
                    var filePath = location.origin+'/images/com_gtmarkers/'+photos[i];
                    $('#photos').append('<a id="photoModalPreview" href="#" data-target="#photoModal" data-toggle="modal" class="pop"><img src="'+filePath+'"" style="width: 50px;"></a>');
                }
            }
            $('#jform_photo_hidden').val(item.photo);
        }
        $('#btnClosePhotoModal').click(function(){
            $('#photoModal').modal('toggle');
        });
        $('#btnCalcelPhotoModal').click(function(){
            $('#photoModal').modal('toggle');
        });
        $('#photoModalPreview').click(function(){
            $('#photo').attr('src',$('#photoModalPreview').find('img').attr('src'));
        });
    })(jQuery);
}
