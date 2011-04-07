
<div class="wrap">
	<h2>日志保护插件配置</h2>
<?php 
	$opt_name=ADV_AUTH_OPT_NAME;//defined in advanced-authority.php
	if(isset($_POST[$opt_name])){
		$opt_val=$_POST[$opt_name];
		if(!empty($opt_val)){
			update_option($opt_name,$opt_val);
			echo '<p style=\'color:green;\'>设置已更新</p>';
		}
	}
	$opt_val=get_option($opt_name);
	$key_arr=explode('|',$opt_val);

?>

	<p>你可以在下方添加全局访问口令，这些口令可以用来回答所有问题，也就是用这些口令可以查看所有受保护的日志</p>
	<div>
		<style>
			table td{
				padding:5px;
			}
			thead{
				font-weight:bold;
			}
		</style>
		<table>
			<thead>
				<td>口令</td>
				<td>操作</td>
			</thead>
			<?php 
			$key_id=0;
			foreach($key_arr as $key){
				echo '<tr id="adv-key-p-'.$key_id.'">';
				echo "<td id='adv-key-$key_id'>$key</td>";
				echo "<td><a href='javascript:adv_auth_del($key_id,\"$key\");'>删除</a></td>";
				echo '</tr>';
				$key_id++;
			}
			?>
			<tr id='adv-key-cntl'>
				<td><input id='adv-key-add' type='text' style='width:100%;' /></td>
				<td><a href='javascript:adv_auth_add();'>添加</a></td>
			</tr>
		</table>
		
	</div>
	<div style='padding-left:5px;'>
		<form action="" method="post">
			<input id='adv-auth-keys' type='hidden' name='adv-auth-keys' value='<?php echo $opt_val;?>'/>
			<input type="submit" name="submit" value="保存设置"/>
		</form>
	</div>
</div>
<script type="text/javascript">
	AUTO_KEY_ID=<?php echo $key_id;?>;

	var adv_auth_reg=/[\(\)\^\$\\\/\*\+\.\[\]]/g;
	var no_accept=['|','\'','"'];
	function adv_auth_add(){
		var key=jQuery.trim(jQuery('#adv-key-add').attr('value'));
		if(key==''){
			alert('口令不能为空！');
			return;
		}else{
			for(var i=0;i<no_accept.length;i++)
			if(key.indexOf(no_accept[i])!==-1){
				alert('不能使用符号 '+no_accept[i]);
				return;
			}
			var r_key=key.replace(adv_auth_reg,
					function(m){
						return "\\"+m;
				});
			var adv_keys=jQuery('#adv-auth-keys').attr('value');
			var r1=new RegExp("^"+r_key+"\\|");
			var r2=new RegExp("\\|"+r_key+"\\|");
			var r3=new RegExp("\\|"+r_key+"$");
			if(r1.test(adv_keys)||r2.test(adv_keys)||r3.test(adv_keys)){
				alert('口令已经存在！');
				return;
			}
			if(jQuery.trim(adv_keys)!=''){
				adv_keys=adv_keys+"|"+key;
			}else{
				adv_keys=key;
			}
			jQuery("<tr>").attr('id','adv-key-p-'+AUTO_KEY_ID).html(
					"<td id='adv-key-"+AUTO_KEY_ID+"'>"
					+key+"</td><td><a href='javascript:adv-auth-del("+AUTO_KEY_ID+",\""+key+"\");'>删除</a></td>")
					.insertBefore('#adv-key-cntl');
			AUTO_KEY_ID++;
			jQuery('#adv-key-add').attr('value','');
			jQuery('#adv-auth-keys').attr('value',adv_keys);
		}
	}

	function adv_auth_del(key_id,key){
		var r_key=key.replace(adv_auth_reg,
				function(m){
					return "\\"+m;
				});
		var r1=new RegExp("^"+key+"\\|");
		var r2=new RegExp("\\|"+key+"\\|");
		var r3=new RegExp("\\|"+key+"$");
		var adv_keys=jQuery('#adv-auth-keys').attr('value');
		if(r1.test(adv_keys))
			adv_keys=adv_keys.replace(r1,'');
		else if(r2.test(adv_keys))
			adv_keys=adv_keys.replace(r2,'|');
		else if(r3.test(adv_keys))
			adv_keys=adv_keys.replace(r3,'');
		jQuery("#adv-auth-keys").attr('value',adv_keys);
		jQuery("#adv-key-p-"+key_id).remove();
			
	}	
</script>