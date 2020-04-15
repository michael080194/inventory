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
            <button type="submit" class="btn btn-main mt-3 mr-3">匯出</button>
            <span id="btn-download-stock" style="display: none;"></span>
        </div>

        <div id="export-data-table" class="table-responsive" style="display: none">
            <table class="table table-striped">
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
        </div>

        <div class="download-stock-file d-none"></div>
    </form>
</div>

<{include file='footer.tpl' }>

<!-- Moment.js (must be imported before bs-datetimepicker) -->
<script src="https://cdn.bootcss.com/moment.js/2.18.1/moment-with-locales.min.js"></script>

<!-- Bootstrap datetimepicker 4.17.47 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<!-- Table CSV Exporter -->
<!-- Reference: https://www.youtube.com/watch?v=cpHCv3gbPuk -->
<script src="/inventory/dist/js/table-csv-exporter.js"></script>

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
        $('#export-data-table').hide();
        $('table tbody').html('');

		let form = $('#form-stock-excel');
		var url1 = "/inventory/api/inventoryApi.php";
		var pass0 = {};
		pass0.op = form.find('input[name="op"]').val(); // `stockExport`
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
                    $('#export-data-table').show();
                    $('.loader').css('display', 'none');

                    $('#btn-download-stock').html(`<button type="button" class="btn btn-main mt-3 mr-3" onclick="downloadStockData('${pass0.c_house}', '${pass0.check_date}')">下載</button>`)
                    $('#btn-download-stock').show();
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

    function downloadStockData (c_house, check_date) {
        const dataTable = document.getElementsByClassName('table table-striped')[0];
        const exporter = new TableCSVExporter(dataTable);
        const csvOutput = '\uFEFF' + exporter.convertToCSV(); // '\uFEFF' prevent chinese strings from wrong words
        const csvBlob = new Blob([csvOutput], { type: 'text/csv; charset=utf-8' });
        const blobUrl = URL.createObjectURL(csvBlob);
        const anchorElement = document.createElement('a');

        anchorElement.href = blobUrl;
        anchorElement.download = `${c_house}_${check_date}_盤點結果明細表.csv`;
        anchorElement.click();

        setTimeout(() => {
            URL.revokeObjectURL(blobUrl);
        }, 500);
    }

</script>
