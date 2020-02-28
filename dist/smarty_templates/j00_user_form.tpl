
<{include file='header.tpl'}>

<div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">使用者建立--<{$row.form_title}></h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" action="j00_user.php" method="post" role="form">
            <input type="hidden" name="id" id="id" value="{$row.id}">
            <div class="form-group">
                <label class="col-sm-3 control-label" for="user">使用者帳號</label>
                <div class="col-sm-9">
                    <input type="text" name="user" id="user" class="form-control" placeholder="請輸入使用者帳號"  value="<{$row.user}>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="pass">使用者密碼</label>
                <div class="col-sm-9">
                    <input type="text" name="pass" id="pass" class="form-control" placeholder="請輸入使用者密碼" >
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="name">姓名</label>
                <div class="col-sm-9">
                    <input type="text" name="name" id="name" class="form-control" placeholder="請輸入姓名"  value="<{$row.name}>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="email">Email</label>
                <div class="col-sm-9">
                    <input type="text" name="email" id="email" class="form-control" placeholder="請輸入Email"  value="<{$row.email}>">
                </div>
            </div>

            <label class="col-md-1" style="text-align: right; padding-right: 0;">管理者</label>
            <div class="col-md-2">
              <input type='radio' name='isAdmin' id='isAdmin_1' value='1' <{if $row.isAdmin==1}>checked<{/if}>>
                <label for='isAdmin_1'>是</label>&nbsp;&nbsp;
              <input type='radio' name='isAdmin' id='isAdmin_2' value='0' <{if $row.isAdmin==0}>checked<{/if}>>
                <label for='isAdmin_2'>否</label>
            </div>

            <div class="text-center">
                <button type="submit" name="op" value="save_user" class="btn btn-primary">
                    <{$row.form_title}> 存檔</button>
            </div>
        </form>
    </div>
</div>

<{include file='footer.tpl'}>
<script src="../../plugin/bootbox.min.js"></script>
<script src="../js/kyc_cm_fun.js"></script>
<script src="../js/kyc_cm_fun.js"></script>  
<script>
 $(document).ready(function() {
    disEnterKey(); 
    $('form').submit(function () {
        if ($.trim($('#user').val())  === '') {
            $('.kyc_modal-body').text('使用者帳號不能空白');
            $('#formMsgModal').modal('show');
            return false;
        }

        if ($.trim($('#pass').val())  === '') {
            $('.kyc_modal-body').text('使用者密碼不能空白');
            $('#formMsgModal').modal('show');
            return false;
        }        

        if ($.trim($('#name').val())  === '') {
            $('.kyc_modal-body').text('姓名不能空白');
            $('#formMsgModal').modal('show');
            return false;
        }     
    });     
 });   
</script>
