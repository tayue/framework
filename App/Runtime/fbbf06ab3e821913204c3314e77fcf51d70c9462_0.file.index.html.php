<?php
/* Smarty version 3.1.33, created on 2018-11-28 10:25:20
  from '/home/wwwroot/default/framework/App/View/Home/Index/index.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5bfdfc90e01a16_21982985',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'fbbf06ab3e821913204c3314e77fcf51d70c9462' => 
    array (
      0 => '/home/wwwroot/default/framework/App/View/Home/Index/index.html',
      1 => 1543370922,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bfdfc90e01a16_21982985 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $_smarty_tpl->tpl_vars['name']->value;?>
</title>
    <?php echo '<script'; ?>
 src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"><?php echo '</script'; ?>
>
</head>
<body>
<div>
    <h2 id="dd"><?php echo $_smarty_tpl->tpl_vars['name']->value;?>
</h2>
</div>

<form action="" method="post" autocomplete="on" id="from">
    First name: <input type="text" name="fname" /><br />
    Last name: <input type="text" name="lname" /><br />
    E-mail: <input type="email" name="email" autocomplete="off" /><br />
    <input type="button" onclick="submitform()" />
</form>
</body>
</html>


<?php echo '<script'; ?>
>
    $(function () {

    });

    function submitform() {
        var url="\\home\\Index\\indexs";
        $.ajax({
            type:'post',
            url:url,
            data:$('#from').serializeArray(),
            dataType:'json',
            success:function (data) {
                console.log(data);
            }
        });
    }

<?php echo '</script'; ?>
><?php }
}
