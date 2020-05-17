<{include file='header.tpl'}>

 <{if isset($users) }>
<div class="container pt-4">
  <div class="row">
     <div class="col-sm-6">
       <h2>資料列表<small>（共 <{$total}> 筆資料）</small></h2>
     </div>
     <div class="col-sm-6">
         <div class="text-right">
             <a href="j00_user.php?op=user_form" class="btn btn-primary">新增使用者</a>
         </div>
     </div>
  </div>
  <table class="table table-bordered table-hover table-striped">
    <thead>
      <tr>
        <th>序號</th>
        <th>公司別</th>
        <th>使用者</th>
        <th>使者姓名</th>
        <th>Email</th>
        <th>管理者</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
        <{foreach $users as $user}>
          <tr>
            <td><{$user@iteration + (($g2p-1)*$every_page)}></td>
            <td><{$user.comp_id}></td>
            <td><{$user.user}></td>
            <td><{$user.name}></td>
            <td><{$user.email}></td>
            <td>
              <{if $user.isAdmin == 1}>
                <span class="label label-success">管理者</span>
              <{/if}>
            </td>
            <td>
               <a href="j00_user.php?op=user_form&id=<{$user.id}>" class="btn btn-warning btn-xs">編輯</a>
               <a href="javascript:void(0)" class="del_user btn btn-danger btn-xs" smsg="<{$user.user}>" sn="<{$user.id}>">刪除</a>
            </td>
          </tr>
        <{/foreach}>
    </tbody>
  </table>
  <{$bar}>
</div>
<{/if}>
<{include file='footer.tpl'}>
<script src="../../plugin/bootbox.min.js"></script>
<script src="../js/kyc_cm_fun.js"></script>

<script>
  var sn = "" , smsg="";
  var obj1 = "";
  $('.del_user').click(function () {
      sn = $(this).attr("sn");
      smsg = $(this).attr("smsg");
      obj1 = $(this).parents('tr');
      bootbox.confirm({
          message: "你確定要刪除使用編號 " + smsg +"?",
          buttons: {
              confirm: {
                  label: 'Yes',
                  className: 'btn-success'
              },
              cancel: {
                  label: 'No',
                  className: 'btn-danger'
              }
          },
          callback: function (result) {
              if(result){
                delete1();
              }
          }
      });
  });

  function delete1() {
      $.ajax({ //调用jquery的ajax方法
          type: "POST",
          url: "j00_user.php",
          data: "op=delete_user&id=" + sn,
          success: function (msg) {
          },
          error: function (jqXHR, exception) {
              return "連線錯誤";
          },
          beforeSend: function () {
              // $(".loading img").css("display", "block");
          },
          complete: function () {
              // $(".loading img").css("display", "none");
              obj1.remove();
          }
      });
  }

</script>
