<div class="wrap">
	<h2>这是个一测试页面</h2>
<?php 
	$opt_name=OPT_PRE."key";
	$opt_val=get_option($opt_name);
	if(isset($_POST[$opt_name])){
		$opt_val=$_POST[$opt_name];
		if(empty($opt_val)){
			echo "<div style='color:red;'>不能为空</div>";
		}else{
			update_option($opt_name,$opt_val);
			echo "<div style='color:green;'>设置更新成功</div>";
		}
	}
?>

	<p>测试Wordpress插件开发的options保存和读取</p>
	<div>
		<form action="" method="post">
			<div>
				<label>Key:</label>
				<input autocomplete="off" type="text" name="<?php echo $opt_name;?>" value="<?php echo $opt_val;?>"/>
			</div>
			<div>
				<input type="submit" name="submit" value="提交"/>
			</div>
		</form>
	</div>
</div>
