<?php 
	$adv_auth_on=true;
	$question="";
	$answer="";
	if($post!=null){
		$on=get_post_meta($post->ID,'adv-auth-on',true);
		if($on=='on'){
			$question=get_post_meta($post->ID,'adv-auth-ques',true);
			$ans_tmp=get_post_meta($post->ID,'adv-auth-ans');
			if($ans_tmp!=null)
				$ans_tmp=$ans_tmp[0];
			//var_dump($ans_tmp);
			$c=count($ans_tmp);
			for($i=0;$i<$c-1;$i++){
				$answer=$answer.$ans_tmp[$i]."|";
			}
			if($c>0){
				$answer=$answer.$ans_tmp[$c-1];
			}
			$adv_auth_on=true;
		}else{
			$adv_auth_on=false;
		}
	}else{
		$adv_auth_on=false;
	}
	//echo $adv_auth_on==true?'TRUE':'FALSE';
?>
<style type="text/css">
	#adv-auth-edit{
		display:<?php echo ($adv_auth_on==true?'block':'none');?>;
		padding-left:15px;	
		padding-right:15px;	
		margin-top:5px;
		margin-bottom:5px;
	}
	.adv-auth-row{
		margin-top:5px;
		font-size:11px;
			
	}
	#adv-auth-ans-div{
		padding-left:10px;
		margin:8px;
	}
</style>
<script type="text/javascript">
	function set_adv_auth_on(on){
		var e=document.getElementById('adv-auth-edit');
		if(on==true){
			e.style.display='block';
		}else{
			e.style.display='none';
		}
	}
</script>
<div>
	<input id='adv-auth-on' onclick='set_adv_auth_on(this.checked);' type='checkbox' name='adv-auth-on' value='on' <?php echo ($adv_auth_on==true?'checked':'unchecked');?>/>启用高级日志保护
</div>
<div id='adv-auth-edit' >
	<div class='adv-auth-row'>
		<span  >输入问题：</span>
		<input style='width:80%;'  type='text' id='adv-auth-ques' name='adv-auth-ques' value='<?php echo $question;?>'/>
	</div>
	<div class='adv-auth-row'>
		<span  >输入答案：</span>
		<input style='width:80%;' type='text' name='adv-auth-ans' id='adv-auth-ans' value='<?php echo $answer;?>'/>
	</div>
</div>