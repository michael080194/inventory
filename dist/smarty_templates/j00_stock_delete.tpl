<{include file='header.tpl' }>

<!-- Bootstrap datetimepicker 4.17.47 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">

<style>
    .input-group-addon {
        display: flex;
        align-items: center;
        padding: .375rem .75rem;
        margin-bottom: 0;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        text-align: center;
        white-space: nowrap;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        border-top-right-radius: .25rem;
        border-bottom-right-radius: .25rem;
    }
</style>

<{include file='loader.tpl' }>

<div class="container">
    <h1 class="pt-4">刪除上傳庫存檔及其所有盤點明細資料</h1>
    <hr>
    <!-- <form action="j00_stock.php" method="post" enctype="multipart/form-data"> -->
    <form id="form-stock-excel" class="needs-validation" autocomplete="off" novalidate>
        <input type="hidden" name="op" value="deleteStockData">

        <div class="form-row">
            <div class="col-sm-12">
                <div class="form-group">
                    <select class="form-control list-check-data-summary" onchange="load_data_to_hidden_input()" autofocus required>
                        <option value="" disabled selected>請選擇倉別和庫存上傳日</option>
                    </select>
                    <div class="invalid-feedback">請選擇倉別和庫存上傳日</div>
                </div>
            </div>
            <input type="hidden" name="c_house" class="form-control" placeholder="倉庫別" title="倉庫別" autofocus required>
            <input type="hidden" name="check_date" class="form-control" placeholder="盤點日期" title="盤點日期" autofocus required>
        </div>

        <div class="alert alert-text" role="alert"></div>

        <div class="form-group">
            <button type="submit" class="btn btn-main mt-3">刪除</button>
        </div>
    </form>
</div>

<{include file='footer.tpl' }>

<!-- Moment.js (must be imported before bs-datetimepicker) -->
<script src="https://cdn.bootcss.com/moment.js/2.18.1/moment-with-locales.min.js"></script>

<!-- Bootstrap datetimepicker 4.17.47 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<!-- Custom form validations -->
<script>
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // 回傳 盤點庫存檔(inv_stock) 公司別+倉庫別+盤點檔上傳日期 的摘要給使用者挑選
            list_check_data_summary();

            // Fetch all forms we want to apply custom validation styles
            let forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            let validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        event.preventDefault();
                        check_password_delete(prompt('請輸入密碼：'), form);
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    function check_password_delete(pwd, form) {
		var url1 = "/inventory/api/inventoryApi.php";
        var pass0 = {};
        pass0.op = "getCompId";

        $.ajax({
            url: url1,
            method: 'POST',
            data: pass0,
            success: function(response, xhr, status) {
                let PWD = '';
                if (response != '') {
                    let currentDatetime = new Date().toLocaleString('zh-TW', {timeZone: 'Asia/Taipei'});
                    let d = currentDatetime.split(' ')[0];
                    let year = d.split('/')[0];
                    let month = (d.split('/')[1] < 10) ? ('0' + d.split('/')[1]) : (d.split('/')[1]);
                    let day = (d.split('/')[2] < 10) ? ('0' + d.split('/')[2]) : (d.split('/')[2]);
                    PWD = response + year + month + day;
                }
                if (pwd == PWD) kyc_stock_delete(form);
                else alert('密碼錯誤。');
            },
            error: function(xhr, status) {
                // `status` will return 'error'
                console.log('xhr: ', xhr);
            }
        });
    }

    function kyc_stock_delete(form) {
		var url1 = "/inventory/api/inventoryApi.php";
        $.ajax({
            url: url1,
            method: 'POST',
            data: new FormData(form),
            contentType: false,
            processData: false,
			beforeSend: function (xhr) {
				$('.loader').css('display', 'flex');
			},
			complete: function (xhr, status) {
                setTimeout(function () {
                    $('.loader').css('display', 'none');
                }, 1000);
			},
            success: function(response, xhr, status) {
                setTimeout(function () {
                    response = JSON.parse(response);
                    status = response['responseStatus'];
                    let msg = '系統出現非預期錯誤，請聯絡負責人員。';
                    let cssAlertColor = 'alert-danger';
                    if (status == 'OK') {
                        cssAlertColor = 'alert-success';
                        msg = '刪除成功。';
                    }
                    $('.alert-text').addClass(cssAlertColor).text(msg);
                    $('.alert-text').show();
                }, 1000);
            },
            error: function(xhr, status) {
                setTimeout(function () {
                    // `status` will return 'error'
                    console.log('xhr: ', xhr);
                    $('.alert-text').addClass('alert-danger').text('系統出現非預期錯誤，請聯絡負責人員。');
                    $('.alert-text').show();
                }, 1000);
            }
        });
    }

    function list_check_data_summary() {
        var url1 = "/inventory/api/inventoryApi.php";
        var pass0 = {};
        // pass0.op = "listCheckDataSummary";
        pass0.op = "listStockDataSummary";

        $.ajax({
            url: url1,
            method: "POST",
            data: pass0,
            beforeSend: function (xhr) {
                $('.loader').css('display', 'flex');
            },
            success: function (response, xhr, status) {
                response = JSON.parse(response);
                msg = response["responseStatus"]; // if SUCCESS return content array

                if (msg == "OK") {
                    // render `c_house` && `check_date` to select options
                    product = response["responseArray"];
                    let selectInput = $('.list-check-data-summary');
                    $.each(product, function (key, value) {
                        let c_house    = value["c_house"];
                        let check_date = value["check_date"];
                        check_date = check_date.split(' ')[0];
                        let details = `倉別：${c_house}，庫存上傳日：${check_date}`;
                        let option = `<option value="${c_house}:${check_date}">${details}</option>`;
                        selectInput.append(option);
                    });
                }
                $('.loader').css('display', 'none');
            },
            error: function (xhr, status) {
                // `status` will return 'error'
                console.log('xhr: ', xhr);
                $('.loader').css('display', 'none');
            }
        })
    }

    function load_data_to_hidden_input() {
        let value = $('.list-check-data-summary').val();
        let c_house = value.split(':')[0];
        let check_date = value.split(':')[1];
        $('#form-stock-excel input[name="c_house"]').val(c_house);
        $('#form-stock-excel input[name="check_date"]').val(check_date);
    }
</script>
