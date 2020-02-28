function kyc_logout() {
    var pass0 = {};
    pass0.op = 'logout';
    url1 = "http://localhost/inventory/api/inventoryApi.php";
        // url: "http://michael1.cp35.secserverpros.com/inventory/api/inventoryApi.php",
    $.ajax({
        url:url1,
        method: 'POST',
        data: pass0,
        beforeSend: function (xhr) {
            $('.loader').css('display', 'flex');
        },
        complete: function (xhr, status) {
            $('.loader').css('display', 'none');
        },
        success: function (response, xhr, status) {
            console.log(response);
            response = JSON.parse(response);
            status = response['responseStatus']; // if SUCCESS return content array
            console.log(response);
            if (status == 'OK') { // login SUCCESS
                location.href = 'login.php';
            } 
        },
        error: function (xhr, status) {
            // `status` will return 'error'
            console.log('xhr: ', xhr);
            $('.alert-text').text('* 系統出現非預期錯誤，請聯絡負責人員。');
            $('.alert-text').show();
        }
    });

    return false;
}

function disEnterKeyNoUse() {
  $(document).on("keypress", ":input:not(textarea)", function(event) {
      if (event.keyCode == 13) {
          event.preventDefault();
      }
  });
}


function disEnterKey() {
  $('body').on('keydown', 'input, select', function(e) {
      var self = $(this)
        , form = self.parents('form:eq(0)')
        , focusable
        , next
        ;
      if (e.keyCode == 13) {
          focusable = form.find('input,a,select,button').filter(':visible');
          next = focusable.eq(focusable.index(this)+1);
          if (next.length) {
              next.focus();
          } else {
              form.submit();
          }
          return false;
      }
  });
}

// 利用 bootbox.js show 錯誤訊息
function show_bootbox_msg(para1){
    bootbox.alert('<div class="text-center">' + para1 + '</div>');  
}

// 在查詢產品時(k10_inq_prod.tpl), 按下 [ - ] 鈕 ,  刪除 搜尋欄位 一列資料
function form_get_data_remove_condition(para1){
       var totalObj = $('.searchID');  
       if(totalObj.length <= 1){
         return false;
       }
       para1.remove();
}

// 在查詢產品時(k10_inq_prod.tpl), 按下 [ + ] 鈕 , 自動產生  搜尋欄位 一列資料
function form_get_data_add_condition(para1){
      $("#" + para1 + " .row > div:last-child").append(form_get_data_add_condition1());
    // $("#form_get_data .row > div:last-child").append(form_get_data_add_condition1());
}

function form_get_data_add_condition1() {
    var search = '<div  class="form-group searchID">';
    var tempOption = "";
    for (var key in conditionObj){
        tempOption += '<option value="' + key + '">' + conditionObj[key] + ' </option>'; 
    }

    search += '  <div class="col-sm-2">';
    search += '    <select class="selectbox indexText newoption" >';
    search += '      <option selected value=""> 查詢項目 </option>';
    search += tempOption;
    search += '    </select>';
    search += '  </div>';
    search += '  <div class="col-sm-2">';
    search += '    <select class="selectbox indexText" >';
    search += '      <option selected value="="> 等於 </option>';      
    search += '      <option value=">"> 大於 </option>';
    search += '      <option value="<"> 小於 </option>';
    search += '      <option value="!="> 不等於 </option>';
    search += '      <option value="LIKE"> 相似 </option>';
    search += '    </select>';
    search += '  </div>';
    search += '  <div class="col-sm-2" style="padding-left: 30px; padding-right: 0">';
    search += '     <h5 style="float: right;"> 關鍵字： </h5>';
    search += '  </div>';
    search += '  <div class="col-sm-2" style="padding-left: 0; padding-right: 30px">';
    search += '    <input type="text" class="form-control indexText searchStr">';
    search += '  </div>';
    search += '  <div class="col-sm-2">';
    search += '    <select class="selectbox indexText" >';
    search += '      <option selected  value="OR">  或者 </option>';
    search += '      <option value="AND"> 而且 </option>';
    search += '    </select>';
    search += '  </div>';
    search += '  <div class="col-sm-2">';
    search += '    <div class="conditionBtn addConditionBtn"><span> &#10133; </span></div>';
    search += '    <div class="conditionBtn delConditionBtn"><span> &#10134; </span></div>';
    search += '  </div>';
    search += '</div>';

    return search;
}
