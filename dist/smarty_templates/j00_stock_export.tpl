<{include file='header.tpl' }>

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
    <h1 class="pt-4">匯出盤點明細表</h1>
    <hr>
    <!-- <form action="j00_stock_export.php" method="post" enctype="multipart/form-data"> -->
    <form id="form-stock-excel" class="needs-validation" autocomplete="off" novalidate>
        <input type="hidden" name="op" value="stockExport">

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
                        <input type="text" name="check_date" class="form-control" placeholder="盤點日期" title="盤點日期" autofocus required>
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

        <div class="alert alert-text" role="alert"></div>

        <div class="form-group">
            <button type="submit" class="btn btn-main mt-3">匯出</button>
        </div>

        <table class="table table-striped" style="display: none">
            <thead>
                <tr>
                    <th scope="col">機種編號</th>
                    <th scope="col">條碼編號</th>
                    <th scope="col">產品名稱</th>
                    <th scope="col">單位</th>
                    <th scope="col">現有庫存</th>
                    <th scope="col">盤點條碼</th>
                    <th scope="col">盤點數量</th>
                    <th scope="col">差額</th>
                    <th scope="col">盤點說明</th>
                    <th scope="col">盤點人員</th>
                    <th scope="col">註記</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <div class="download-stock-file d-none"></div>
    </form>
</div>

<{include file='footer.tpl' }>

<!-- Moment.js (must be imported before bs-datetimepicker) -->
<script src="https://cdn.bootcss.com/moment.js/2.18.1/moment-with-locales.min.js"></script>

<!-- Bootstrap datetimepicker 4.17.47 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<!-- Custom form validations -->
<script>
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
                        kyc_stock_export();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    function kyc_stock_export() {
		let form = $('#form-stock-excel');
		var url1 = "/inventory/api/inventoryApi.php";
		var pass0 = {};
		pass0.op = form.find('input[name="op"]').val();
		pass0.c_house = form.find('input[name="c_house"]').val();
		pass0.check_date = form.find('input[name="check_date"]').val();

        $.ajax({
            url: url1,
            method: 'POST',
            data: pass0,
			beforeSend: function (xhr) {
				$('.loader').css('display', 'flex');
			},
            success: function(response, xhr, status) {
                setTimeout(function () {
                    response = JSON.parse(response);
                    let stockDataArray = response['responseResult'];
                    let content = '';
                    $.each(stockDataArray, function (index, stockDataRow) {
                        content += '<tr>';
                        $.each(stockDataRow, function (index, stockData) {
                            content += '<td>' + stockData + '</td>';
                        });
                        content += '</td>';
                    });
                    $('table tbody').html(content);
                    $('table').show();
                    $('.loader').css('display', 'none');
                    return;

                    let cssAlertColor = 'alert-danger';
                    let msg = '系統出現非預期錯誤，請聯絡負責人員。';
                    if (response == '[]') {
                        // 若成功產生盤點結果明細表，則自動下載後，刪除該檔案，再停止loader；
                        form.find('button[type="submit"]').addClass('disabled');
                        cssAlertColor = 'alert-success';
                        msg = '匯出成功，等待下載......';
                        kyc_stock_download_exported_data(pass0.c_house, pass0.check_date);
                    }
                    else {
                        // 若失敗則直接停止loader
                        $('.loader').css('display', 'none');
                    }
                    $('.alert-text').addClass(cssAlertColor).text(msg);
                    $('.alert-text').show();
                }, 1000);
            },
            error: function(xhr, status) {
                setTimeout(function () {
                    // `status` will return 'error'
                    console.log('xhr: ', xhr);
                    $('.loader').css('display', 'none');
                    $('.alert-text').addClass('alert-danger').text('系統出現非預期錯誤，請聯絡負責人員。');
                    $('.alert-text').show();
                }, 1000);
            }
        });
    }

    function kyc_stock_download_exported_data(c_house, check_date) {
        let filename = `${check_date}_${c_house}_盤點結果明細表.csv`;
        setTimeout(function () {
            let link = document.createElement('a');
            link.href = filename;
            link.download = filename;
            document.querySelector('.download-stock-file').appendChild(link);
            link.click();

            var url1 = "/inventory/api/inventoryApi.php";
            var pass0 = {};
            pass0.op = 'stockExportFileDelete';
            pass0.filename = filename;
            $.ajax({
                url: url1,
                method: 'POST',
                data: pass0,
                complete: function (xhr, status) {
                    setTimeout(function () {
                        $('.loader').css('display', 'none');
                    }, 1000);
                },
                success: function(response, xhr, status) {
                    response = JSON.parse(response);
                    status = response['responseStatus'];
                    if (status == 'OK') {
                        console.log(`File delete success in backend: ${filename}`);
                        let form = $('#form-stock-excel');
                        form.find('button[type="submit"]').removeClass('disabled');
                    }
                    else {
                        console.log(`File delete failed in backend: ${filename}`);
                    }
                },
                error: function(xhr, status) {
                    // `status` will return 'error'
                    console.log('xhr: ', xhr);
                    console.log('Something went wrong......');
                }
            });
        }, 1500);
    }
</script>
