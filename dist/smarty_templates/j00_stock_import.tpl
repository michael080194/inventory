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
    <h1 class="pt-4">匯入現有庫存檔</h1>
    <hr>
    <!-- <form action="j00_stock.php" method="post" enctype="multipart/form-data"> -->
    <form id="form-stock-excel" class="needs-validation" autocomplete="off" novalidate>
        <input type="hidden" name="op" value="stockImport">

        <div class="form-row">
            <div class="col-sm-6">
                <div class="form-group">
                    <input type="text" name="c_house" class="form-control" placeholder="倉庫別" title="倉庫別" autofocus required>
                    <div class="invalid-feedback">請輸入倉庫別</div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <div class="input-group date datetimepicker">
                        <input type="text" name="check_date" class="form-control" placeholder="庫存上傳日" title="庫存上傳日" autofocus required>
                        <div class="input-group-append">
                            <span class="input-group-addon">
                                <span class="far fa-calendar-alt"></span>
                            </span>
                        </div>
                    </div>
                    <div class="invalid-feedback">請輸入盤點日期</div>
                </div>
            </div>
        </div>

        <div class="custom-file mb-3">
            <input type="file" id="stock-excel" class="custom-file-input" name="excel_file" accept=".csv, .xls, .xlsx" autofocus required>
            <label class="custom-file-label" for="stock-excel">選擇檔案</label>
            <div class="invalid-feedback">請選擇檔案</div>
        </div>

        <div class="alert alert-text" role="alert"></div>

        <div class="form-group">
            <button type="submit" class="btn btn-main mt-3">匯入</button>
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
    $('.custom-file-input').change(function() {
        let filename = $('#stock-excel').val().split('\\').slice(-1)[0];
        // Prevent the value from null: it will be null when no file was selected
        filename = (filename == '') ? '選擇檔案' : filename;
        $('#stock-excel').next().text(filename);
    });

    $('.datetimepicker').datetimepicker({
        // date: new Date(),
        format: 'YYYY-MM-DD',
        locale: moment.locale('zh-tw'),
    });

    (function() {
        'use strict';
        window.addEventListener('load', function() {
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
                        check_stock_data_exist(form);
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    function check_stock_data_exist(form) {
        var url1 = "/inventory/api/inventoryApi.php";
        var pass0 = {};
        pass0.op = "checkStockDataExist";
        pass0.check_date = $('#form-stock-excel input[name="check_date"]').val();
        pass0.c_house = $('#form-stock-excel input[name="c_house"]').val();

        $.ajax({
            url: url1,
            method: "POST",
            data: pass0,
            beforeSend: function (xhr) {
                $('.loader').css('display', 'flex');
            },
            success: function (response, xhr, status) {
                response = JSON.parse(response);
                status = response["responseStatus"]; // if  SUCCESS return content array
                if (status == "notExist") { // 資料不重複
                    kyc_stock_import(form);
                }
                else {
                    $('.alert-text').addClass('alert-danger').text('這個時間的這個倉庫別的庫存已經存在了，所以無法匯入。');
                    $('.alert-text').show();
                }
            },
            complete: function (xhr, status) {
                $('.loader').css('display', 'none');
            },
            error: function (xhr, status) {
                // `status` will return 'error'
                console.log('xhr: ', xhr);
                $('.alert-text').addClass('alert-danger').text('系統出現非預期錯誤，請聯絡負責人員。');
                $('.alert-text').show();
            }
        })
    }

    function kyc_stock_import(form) {
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
                    let msg = response['responseMessage'];
                    let cssAlertColor = 'alert-danger';
                    if (status == 'OK') {
                        cssAlertColor = 'alert-success';
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
</script>
