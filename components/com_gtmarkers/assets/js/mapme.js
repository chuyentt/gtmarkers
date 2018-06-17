/* global google, Joomla, createElement */

var map;
var markers = [];
var infoWindow;
var messageWindow;
var iconSize = 1;

// Lấy tham số đã lưu từ Joomla
const params = Joomla.getOptions('params');

// Lấy danh sách các bản ghi đã lưu từ Joomla
const items = Joomla.getOptions('items');

// Các phần tiếp theo làm theo các hướng dẫn trong tài liệu của Google Maps API

/**
* Data object
*/
var data = {
  title: null,
  lat: null,
  lng: null,
  properties: null
};

jQuery.noConflict();
jQuery(function($) {
    $('form[data-async]').on('submit', function(event) {
        event.preventDefault();
        var $form = $(this);
        var $target = $($form.attr('data-target'));
        
        // Lấy tất cả các trường dạng input values, không phải là files
        var params = $form.serializeArray();
        
        // Lấy files
        var files = $('#jform_photo')[0].files;
        
        // Tạo FormData
        var formData;
        
        if (files.length > 0) {
            formData = new FormData(this);
        } else {
            formData = new FormData();
            // Đưa tất cả các trường còn lại vào formData
            $(params).each(function(index, element) {
                formData.append(element.name, element.value);
            });
        }
        
        // Xác định rating mới, nếu chưa đánh giá thì hiện thông báo
        var _rating = params.find(function(element) {
            return element.name === 'jform[rating]';
        }).value;
        
        if (isNaN(parseInt(_rating))) {
            alert('Bạn chưa đánh giá, hãy đánh giá bằng click vào ngôi sao.');
            return false;
        }        
    
        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: formData,
            contentType: false,
            processData: false,
            async: false,
            success: function(data, status) {
                infoWindow.close();
                $('#editReviewModal').hide();
                alert(JSON.stringify(data));
                
                var _alias = params.find(function(element) {
                    return element.name === 'jform[m_alias]';
                }).value;
                
                // Xác định marker
                var marker = markers.find(function(marker) {
                    return marker.alias = _alias;
                });
                
                // Xác định id của review
                var _id = params.find(function(element) {
                    return element.name === 'jform[id]';
                }).value;
                
                var _item = items.find(function(item) {
                    return item.alias === String(_alias);
                });
                var ravg = parseInt(_rating);
                if (!isNaN(parseInt(_item.ravg))) {
                    if (parseInt(_id) !== 0) {
                        ravg = Math.round((parseInt(_item.ravg)+ravg)*0.5);
                    }
                }
                
                _item.ravg = ravg;
                updateEvent(marker, _item);
              //$target.html(data);
              //Joomla.renderMessages(status);
            },
            error: function (request, status, error) {
                infoWindow.close();
                $('#editReviewModal').hide();
                alert(JSON.stringify(request) + status + error);
              //$target.html(JSON.stringify(request) + status + error);
              //Joomla.renderMessages(status);
            }
        });
    });
});

var style = document.createElement("style");
var style_content = document.createTextNode('.bar-container {margin-left:4px;width: 100%;background-color: #f1f1f1;text-align: center;color: white;}'+
'.side {float: left; text-align:right;width: 15%;margin-top:2px;margin-left:8px;} .middle {margin-top:3px;margin-left:5px;float: left;width:70%;}'+
'.right {text-align: right;} .row:after {content: "";display: table;clear: both;}');
style.appendChild(style_content);
document.head.appendChild(style);

function createSearchButton() {
    var controlDiv = document.createElement('div');
    var firstChild = document.createElement('input');
    controlDiv.setAttribute('id','map-search-group');
    controlDiv.setAttribute('class','input-group');
    controlDiv.setAttribute('style','floar:right;');
    firstChild.setAttribute('id','pac-input');
    firstChild.setAttribute('class','form-control');
    firstChild.setAttribute('type','text');
    firstChild.setAttribute('onfocus','this.style.width="180px"');
    firstChild.setAttribute('onblur','this.style.width="115px";this.value=""');
    firstChild.setAttribute('style',
        '-webkit-transition: width 0.30s linear 0s;'+
        '-moz-transition: width 0.30s linear 0s;'+
        '-o-transition: width 0.30s linear 0s;'+
        'transition: width 0.30s linear 0s;'+
        'width:115px;height:35px;borderRadius:2px;boxShadow:0 1px 4px rgba(0,0,0,0.3);margin:10px 14px;');

    controlDiv.appendChild(firstChild);
    return controlDiv;
}

function addYourLocationButton(map, marker) {
    var controlDiv = document.createElement('div');
    var firstChild = document.createElement('button');
    firstChild.style.backgroundColor = '#fff';
    firstChild.style.border = 'none';
    firstChild.style.outline = 'none';
    firstChild.style.width = '28px';
    firstChild.style.height = '28px';
    firstChild.style.borderRadius = '2px';
    firstChild.style.boxShadow = '0 1px 4px rgba(0,0,0,0.3)';
    firstChild.style.cursor = 'pointer';
    firstChild.style.marginRight = '10px';
    firstChild.style.padding = '0px';
    firstChild.title = 'Your Location';
    controlDiv.appendChild(firstChild);

    var secondChild = document.createElement('div');
    secondChild.style.margin = '5px';
    secondChild.style.width = '18px';
    secondChild.style.height = '18px';
    secondChild.style.backgroundImage = 'url(https://maps.gstatic.com/tactile/mylocation/mylocation-sprite-1x.png)';
    secondChild.style.backgroundSize = '180px 18px';
    secondChild.style.backgroundPosition = '0px 0px';
    secondChild.style.backgroundRepeat = 'no-repeat';
    secondChild.id = 'you_location_img';
    firstChild.appendChild(secondChild);

    map.addListener("dragend", function() {
        jQuery('#you_location_img').css('background-position', '0px 0px');
    });

    firstChild.addEventListener('click', function() {
        var imgX = '0';
        var animationInterval = setInterval(function(){
            if(imgX === '-18') imgX = '0';
            else imgX = '-18';
            jQuery('#you_location_img').css('background-position', imgX+'px 0px');
        }, 500);

        if(navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                marker.setPosition(latlng);
                map.setCenter(latlng);
                clearInterval(animationInterval);
                jQuery('#you_location_img').css('background-position', '-144px 0px');
            });
        } else{
            clearInterval(animationInterval);
            jQuery('#you_location_img').css('background-position', '0px 0px');
        }
    });

    controlDiv.index = 1;
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(controlDiv);
}
/*
 * Khởi tạo bản đồ theo &callback=initMap ở mapme.php
 */
function initMap() {
    var header_height = params.header_height + 'px';
    params.width = '100%';
    params.height = params.height ==='auto' || params.height ==='100%' ? 'calc(100vh - '+header_height+')':params.height + 'px';
    
    // Tìm đến phần tử HTML có id='map' để thiết lập kích thước cửa sổ bản đồ
    document.getElementById('map').style.width=params.width;
    document.getElementById('map').style.height=params.height;
    
    // Gọi hàm tạo bản đồ
    createMap(params);
    
    /*
     * Tạo bản đồ và thêm các sự kiện tương tác
     * @param mảng params chứa các tham số thiết lập bản đồ
     * @returns {undefined}
     */
    function createMap(params) {
        coords = params.center.split(',');
        center = new google.maps.LatLng(coords[0],coords[1]);
        var options = {
            center: center,
            zoom: parseInt(params.zoom),
            mapTypeId: params.maptypeid,
            styles: JSON.parse(params.styles),
            scrollwheel: 1,
            panControl: 1,
            mapTypeControl: 1,
            scaleControl: 1,
            zoomControl: 1,
            gestureHandling: 'greedy'
        };

        map = new google.maps.Map(document.getElementById('map'), options);
        
        infoWindow = new google.maps.InfoWindow;

        messageWindow = new google.maps.InfoWindow;

        // Tạo một search box và liên kết tới giao diện theo id của HTML.


        var divSearchButton = createSearchButton();
        
        var input = divSearchButton.children[0];
        var searchBox = new google.maps.places.SearchBox((input));
        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(divSearchButton);

        // Kiểm soát sự kiện tầm nhìn bản đồ thay đổi: pan, zoom để giới hạn khu 
        // vực tìm kiến theo tầm nhìn hiện tại trên bản đồ.
        map.addListener("bounds_changed", function() {
            searchBox.setBounds(map.getBounds());
        });
        
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.

        // [START region_getplaces]
        // Listen for the event fired when the user selects an item from the
        // pick list. Retrieve the matching places for that item.
        searchBox.addListener("places_changed", function() {
            var places = searchBox.getPlaces();

            if (places.length === 0) {
                return;
            }

            // Clear out the old markers.
            markers.forEach(function(marker) {
                marker.setMap(null);
            });

            markers = [];

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();

            places.forEach(function(place) {
                if (!place.geometry) {
                    console.log("Returned place contains no geometry");
                    return;
                }
                var icon = {
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(25, 25)
                };
                
                // Create a marker for each place.
                markers.push(new google.maps.Marker({
                    map: map,
                    icon: icon,
                    title: place.name,
                    position: place.geometry.location
                }));

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
            });
            map.fitBounds(bounds);
        });
        // [END region_getplaces]
        
        var icon = {
            path: "M12 8c-2 0-4 2-4 4s2 4 4 4 4-2 4-4-2-4-4-4zm10 3c-1-5-4-8-9-9V0h-2v2c-5 1-8 4-9 9H0v2h2c1 5 4 8 9 9v2h2v-2c5-1 8-4 9-9h2v-2h-2zm-10 9c-4 0-8-4-8-8s4-8 8-8 8 4 8 8-4 8-8 8z",
            fillColor: '#1E90FF',
            fillOpacity: 1,
            strokeColor: '#FFFFFF',
            anchor: new google.maps.Point(12,12),
            strokeWeight: 0.8,
            labelOrigin: new google.maps.Point(40-24, 40)
        };

        var label = {
            color: '#2D2D2D',
            fontWeight: 'bold',
            text: 'Vị trí của bạn'
        };

        var myMarker = new google.maps.Marker({
            map: map,
            icon: icon,
            label: label,
            title: 'Vị trí của bạn',
            position: center
        });
        
        myMarker.addListener('click', function() {
            var latLng = myMarker.getPosition();
            infoWindow.setContent(
                    '<div style="user-select: text !important">' +
                    latLng.lat().toFixed(7) + ',' + latLng.lng().toFixed(7) +
                    '</div>'
            );
            infoWindow.open(map, myMarker);
        });
                        
        addYourLocationButton(map, myMarker);
        
        
        map.addListener('click', function(e) {
            infoWindow.close();
        });
        // Hiển thị các markers đã lưu trong CSDL
        items.forEach(function(item) {
            
            var icon_tg = {
                path: "M12 0l11 22H1z", // Tam giác
                fillColor: '#EA4335',
                fillOpacity: 1,
                strokeColor: '#FFFFFF',
                anchor: new google.maps.Point(12,12),
                strokeWeight: 1,
                labelOrigin: new google.maps.Point(40-24, 40)
            };

            var label = {
                color: '#2D2D2D',
                fontWeight: 'bold',
                text: item.title
            };
            var coords = item.pos.split(',');
            var pos = new google.maps.LatLng(coords[0],coords[1]);
            var marker = new google.maps.Marker({
                position: pos,
                icon: icon_tg,
                label: label,
                alias: item.alias
                //map: map
            });
            
            updateEvent(marker, item);
            
            markers.push(marker);
        });
        // Add a marker clusterer to manage the markers.
        var markerCluster = new MarkerClusterer(map, markers,
            {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});
    }
}

/**
 * Hàm tạo infoWindow
 * @param {type} data
 * @return {Boolean}
 */
function updateEvent(marker, item) {
    marker.addListener('click', function() {
        var ritem = JSON.stringify({id:"0", m_alias:item.alias, rating:"5"});
        // Tạo nút thêm review
        var span = document.createElement("span");
        span.setAttribute("onclick", "javascript:showReviewFormModal('star', 'jform_rating', "+ritem+")");
        span.setAttribute("data-toggle", "modal");
        span.setAttribute("data-target", "#editReviewModal");
        span.setAttribute("data-whatever", "@mdo");
        span.setAttribute("class", "label fa fa-edit pull-right btn btn-success");
        span.innerHTML = "Thêm";
        var div = document.createElement('div');
        div.appendChild(span);
        var createHtml = params.can_create ? div.innerHTML:'<span class="far fa-lock pull-right"></span>';
        div.innerHTML = '';
        // Tạo nút Xem chi tiết đánh giá ≫
        var _div = document.createElement('div');
        if (parseInt(item.ravg) > 0) {
            _div.innerHTML = 'Xem chi tiết đánh giá ≫';
            _div.setAttribute('class','far fa-file-text btn');
            _div.setAttribute('onclick','javascript:showReviews('+item.id+','+item.ravg+');');
        } else {
            if (params.can_create) {
                _div.innerHTML = 'Mốc này chưa có đánh giá. Nếu bạn biết,<br>hãy thêm một đánh giá và nhận xét!';
            } else {
                _div.innerHTML = 'Mốc này chưa có đánh giá. Hãy đăng nhập <br>để trở thành người đầu tiên đánh giá!';
            }
        }
        div.appendChild(_div);
        var showReviewHtml = div.innerHTML;
        div.innerHTML = '';
        var ravg = isNaN(parseInt(item.ravg)) ? 0: parseInt(item.ravg);
        infoWindow.setContent(
            '<div style="width: 295px;">'+
            '<h3>'+item.title+'</h3>'+
            '<ul class="list-group">'+
                '<li class="list-group-item"><strong>Tọa độ:&nbsp;&nbsp;</strong><span class="pull-right">'+item.pos+'</span></li>'+
                //'<li class="list-group-item">Độ cao <span class="badge">'+item.ravg+'</span></li>'+
            '</ul>'+
            '<h3 class="heading">Xếp hạng & nhận xét '+
                starRating(ravg)+createHtml+
            '</h3>'+
            '<p id="average_note"></p>'+
            '<hr style="border:1px solid #f1f1f1; margin-top:10px; margin-bottom:10px;">'+
            '<div id="rating_detail" class="row"></div>'+
            '<h3 id="reviews_label"></h3>'+
            '<div id="reviews"><div class="panel-group" id="accordion">'+showReviewHtml+
            //'<a href="javascript:showReviews('+item.id+','+item.ravg+');" class="list-group-item active">Xem chi tiết đánh giá</a>'+
            '</div></div>'+

            '</div>'
        );

        infoWindow.open(map, marker);
    });
}

/**
 * Xử lý dữ liệu click chuột
 * @param {đối tượng} data Dữ liệu theo cấu trúc được khai báo ở trên.
 * Nó chứa title, lat, lng và properties
 */
function addToDatabase(data) {
    // Test: Hiện thông báo tọa độ. Có thể bỏ dòng này khi test thành công
    // alert(data.lat.toString().concat(",",data.lng.toString()));
    
    // Gọi hàm saveData
    return true;
};

function showReviews(id, ravg = 0) {
    var average_note = document.getElementById('average_note');
    var rating_detail = document.getElementById('rating_detail');
    rating_detail.style.marginLeft = 0;
    rating_detail.style.marginRight = 0;

    // var reviews_label = document.getElementById('reviews_label');
    // reviews_label.innerHTML = 'Các đánh giá:';
    var reviews_div = document.getElementById('reviews');

    var request = {
        'option' : 'com_gtmarkers',
        'task'   : 'marker.getItemAjax',
        'id'     : id
    };
    jQuery.ajax({
        type   : 'POST',
        data   : request,
        success: function (response) {
            // Tạo message mặc định của Joomla
            // Joomla.renderMessages({"success":["Your item has been saved."]});
            // Chuyển kết quả của method vào phần tử html class status
            //$('.status').html(response);
            //infoWindow.close();
            
            // TODO: Cần phải cập nhật dữ liệu item
            var htmlreviews = '';//JSON.stringify(response.reviews);
            var ratings = Array(6).fill(0);
            response.reviews.forEach(function(item) {
                if (item.comment !== null) {
                    htmlreviews += accordionContent(item);
                }
                ratings[parseInt(item.rating)]++;
            });
            reviews_div.innerHTML = htmlreviews;
            var avg = ravg !== null ? ravg.toFixed(1):0;
            average_note.innerHTML = avg +'/5 trung bình dựa trên '+response.reviews.length+' xếp hạng.';
            rating_detail.innerHTML = barRating(0);

            document.getElementById('bar-5').style.width = ratings[5]*100/response.reviews.length+'%';
            document.getElementById('bar-4').style.width = ratings[4]*100/response.reviews.length+'%';
            document.getElementById('bar-3').style.width = ratings[3]*100/response.reviews.length+'%';
            document.getElementById('bar-2').style.width = ratings[2]*100/response.reviews.length+'%';
            document.getElementById('bar-1').style.width = ratings[1]*100/response.reviews.length+'%';

            document.getElementById('bar-5').style.height = '4px';
            document.getElementById('bar-4').style.height = '4px';
            document.getElementById('bar-3').style.height = '4px';
            document.getElementById('bar-2').style.height = '4px';
            document.getElementById('bar-1').style.height = '4px';
            
            document.getElementById('bar-5').style.backgroundColor = '#4CAF50';
            document.getElementById('bar-4').style.backgroundColor = '#2196F3';
            document.getElementById('bar-3').style.backgroundColor = '#00bcd4';
            document.getElementById('bar-2').style.backgroundColor = '#ff9800';
            document.getElementById('bar-1').style.backgroundColor = '#f44336';

            document.getElementById('bar-5-text').innerHTML = ratings[5];
            document.getElementById('bar-4-text').innerHTML = ratings[4];
            document.getElementById('bar-3-text').innerHTML = ratings[3];
            document.getElementById('bar-2-text').innerHTML = ratings[2];
            document.getElementById('bar-1-text').innerHTML = ratings[1];


        },
        error: function (request, status, error) {
            // Render the message
            //Joomla.renderMessages({"danger":[error]});
            //infoWindow.close();
            reviews_div.innerHTML = error;
        }
    });
}

function accordionContent(item) {
/*
 * s - Title
 * t - Time
 * r - Rating
 * u - Create by
 * c - Comment
 */

    var s = item.title;
    var t = item.timestamp;//timeDifference(new Date(), new Date(item.timestamp.replace(' ','T')));
    var r = parseInt(item.rating);
    var u = item.modified_by_name;
    var c = item.comment;
    var a = item.m_alias+item.id;
    var i = JSON.stringify(item);
    var canEdit = params.can_edit && (params.u_id === item.created_by);
    var span = document.createElement("span");
    span.setAttribute("onclick", "javascript:showReviewFormModal('star', 'jform_rating', "+i+")");
    span.setAttribute("data-toggle", "modal");
    span.setAttribute("data-toggle", "modal");
    span.setAttribute("data-target", "#editReviewModal");
    span.setAttribute("data-whatever", "@mdo");
    span.setAttribute("class", "label fa fa-edit pull-right btn btn-success");
    span.innerHTML = "Sửa nhận xét";
    var div = document.createElement('div');
    div.appendChild(span);
    var editHtml = canEdit ? div.innerHTML:'<span class="pull-right">'+u+'</span>';
    
    //var editHtml = canEdit ? '<span onclick="javascript:showReviewFormModal(\'star\', \'jform_rating\', \'item\');" data-toggle="modal" data-target="#editReviewModal" data-whatever="@mdo" class="label fa fa-edit pull-right btn btn-success">Sửa nhận xét</span>':'<span class="pull-right">'+u+'</span>';
    var html = 
    '<div class="panel panel-default">'+
      '<div class="panel-heading">'+
        '<div class="panel-title">'+
          '<a data-toggle="collapse" data-parent="#accordion" href="#'+a+'">'+s+'</a><span class="small pull-right"><small>'+t+'</small></span>'+
        '</div>'+
        '<div class="small">'+starRating(r)+editHtml+'</div>'+
      '</div>'+
      '<div id="'+a+'" class="panel-collapse collapse">'+
        '<div class="panel-body">'+c+'</div>'+
      '</div>'+
    '</div>';
    return html;
}

function starRating(ravg = 0) {
    var el = document.createElement("div");
    for (var i = 1; i<=ravg; i++) {
        var a = document.createElement("span");
        a.setAttribute("class", "fas fa-star text-warning");
        el.appendChild(a);
    }

    for (var i = ravg+1; i<=5; i++) {
        var a = document.createElement("span");
        a.setAttribute("class", "far fa-star text-muted");
        el.appendChild(a);
    }
    return el.innerHTML;
}

function barRating(argument) {
    var html = '<div style="width: 100%">';
    for (var i = 5; i >0; i--) {
        html += '<div class="row small" style="margin:0 auto;">'+
                '<div class="pull-left small">'+starRating(i)+'</div>'+
                '<div class="middle"><div class="bar-container"><div id="bar-'+i+'"></div></div></div>'+
                '<div class="pull-right"><span id="bar-'+i+'-text"></span></div>'+
                '</div>';
    }
    html += '</div>';

//    
//    var html = 
//      '<div class="side"><div>'+starRating(5)+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//        '</div></div><div class="middle">'+
//        '<div class="bar-container"><div id="bar-5"></div></div></div><div class="side right">'+
//        '<div id="bar-5-text">150</div></div>'+
//      '<div class="side"><div>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//        '</div></div><div class="middle">'+
//        '<div class="bar-container"><div id="bar-4"></div></div></div><div class="side right">'+
//        '<div id="bar-4-text">63</div></div>'+
//      '<div class="side"><div>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//        '</div></div><div class="middle">'+
//        '<div class="bar-container"><div id="bar-3"></div></div></div><div class="side right">'+
//        '<div id="bar-3-text">15</div></div>'+
//      '<div class="side"><div>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//        '</div></div><div class="middle">'+
//        '<div class="bar-container"><div id="bar-2"></div></div></div><div class="side right">'+
//        '<div id="bar-2-text">6</div></div>'+
//      '<div class="side"><div>'+
//          '<span class="fas fa-star" style="font-size:6px;color:orange;"></span>'+
//        '</div></div><div class="middle">'+
//        '<div class="bar-container"><div id="bar-1"></div></div></div><div class="side right">'+
//        '<div id="bar-1-text">20</div></div>';
    return html;
}

function saveData() {
    var divNode = document.getElementById('form');
    var inputNodes = divNode.getElementsByTagName('input');
    var input = {};
    for(var i = 0; i < inputNodes.length; i++) {
        inputNode = inputNodes[i];
        name = inputNode.name;
        value = inputNode.value;
        input[name]=value;
    }
    var textAreas = divNode.getElementsByTagName('textarea');
    input[textAreas[0].name] = textAreas[0].value;
    
    store(input);
}

function store(data) {
    var request = {
        'option' : 'com_gtmarkers',
        'task'   : 'review.save',
        'data'   : data
    };
    jQuery.ajax({
        type   : 'POST',
        data   : request,
        success: function (response) {
            // Tạo message mặc định của Joomla
            Joomla.renderMessages({"success":["Your item has been saved."]});
            // Chuyển kết quả của method vào phần tử html class status
            //$('.status').html(response);
            //infoWindow.close();
            
            // TODO: Cần phải cập nhật dữ liệu item
            
        },
    error: function (request, status, error) {
            // Render the message
            Joomla.renderMessages({"danger":[error]});
            //infoWindow.close();
        }
    });
}

function cancel() {
    infoWindow.close();
}

function timeDifference(current, previous) {
    
    var msPerMinute = 60 * 1000;
    var msPerHour = msPerMinute * 60;
    var msPerDay = msPerHour * 24;
    var msPerMonth = msPerDay * 30;
    var msPerYear = msPerDay * 365;
    
    var elapsed = current - previous;
    
    if (elapsed < msPerMinute) {
         return Math.round(elapsed/1000) + ' giây trước';   
    }
    
    else if (elapsed < msPerHour) {
         return Math.round(elapsed/msPerMinute) + ' phút trước';   
    }
    
    else if (elapsed < msPerDay ) {
         return Math.round(elapsed/msPerHour ) + ' giờ trước';   
    }

    else if (elapsed < msPerMonth) {
         return 'gần ' + Math.round(elapsed/msPerDay) + ' ngày trước';   
    }
    
    else if (elapsed < msPerYear) {
         return 'gần ' + Math.round(elapsed/msPerMonth) + ' tháng trước';   
    }
    
    else {
         return 'gần ' + Math.round(elapsed/msPerYear ) + ' năm trước';   
    }
}