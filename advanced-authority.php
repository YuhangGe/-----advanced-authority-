<?php
/**
 * @package Advanced Authority
 * @version 1.0
 */
/*
Plugin Name: 日志保护
Plugin URI: http://wordpress.org/extend/plugins/advance-authority/
Description: 高级日志权限管理，可以设置日志查看密码，查看回答问题等。
Author: Abraham
Version: 1.0
Author URI: http://www.yuhanghome.net
*/

add_action ( 'add_meta_boxes', 'adv_auth_add_box' );
function adv_auth_add_box() {
	add_meta_box ( 'advanced-authority', '日志保护', 'adv_auth_inner_box', 'post' );

}
function adv_auth_inner_box() {
	global $post;
	if (! current_user_can ( 'manage_options' )) {
		wp_die ( __ ( 'You do not have sufficient permissions to access this page.' ) );
	}
	include_once dirname ( __FILE__ ) . "/adv-auth-box.php";
}

add_action ( 'save_post', 'adv_auth_save_post' );
function adv_auth_save_post($post_id) {
	if (! current_user_can ( 'edit_post', $post_id ))
		return $post_id;
	
		// verify if this is an auto save routine. 
	// If it is our form has not been submitted, so we dont want to do anything
	if (defined ( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
		return $post_id;

	$adv_auth_on=$_POST['adv-auth-on'];
	$adv_auth_ques=$_POST['adv-auth-ques'];
	$adv_auth_ans=$_POST['adv-auth-ans'];
	$adv_ans_arr=array();
	if($adv_auth_on=='on'){
		if(trim($adv_auth_ques)=="" ){
			return $post_id;
		}
		//echo $adv_auth_ans;
		$ans_tmp=explode('|',$adv_auth_ans);
		//var_dump($ans_tmp);
		//exit();
		foreach($ans_tmp as $ans_e){
			if(trim($ans_e)==""){
				return $post_id;
			}else{
				$adv_ans_arr[]=trim($ans_e);
			}
		}
		update_post_meta($post_id,'adv-auth-on',$adv_auth_on);
		update_post_meta($post_id,'adv-auth-ques',$adv_auth_ques);
		update_post_meta($post_id,'adv-auth-ans',$adv_ans_arr);
			
	}else{
		update_post_meta($post_id,'adv-auth-on','off');
	}
	
	//var_dump($adv_auth_on);
	//var_dump($adv_auth_ques);
	//var_dump($adv_auth_ans);
		// $mydata = $_POST['myplugin_new_field'];
	

	// Do something with $mydata 
	// probably using add_post_meta(), update_post_meta(), or 
	// a custom table (see Further Reading section below)
	

	return $post_id;
}

add_action ( 'the_content', 'adv_auth_content' );
function adv_auth_content($c) {
	global $post;
	$adv_on=get_post_meta($post->ID,'adv-auth-on',true);
	if($adv_on=='on'){
		$question=get_post_meta($post->ID,'adv-auth-ques',true);
		$ans_tmp=get_post_meta($post->ID,'adv-auth-ans');	
		$answers=$ans_tmp[0];
		if(isset($_POST['adv-auth-user-ans'])){
			$user_ans=$_POST['adv-auth-user-ans'];
			if(in_array($user_ans,$answers)){
				return $c;
			}else{
				return "<h2 style='color:red;'>问题回答错误！</h2>
					<p>作者没有向所有人公开日志，请联系<a href='mailto:abraham1@163.com'>Abraham1@163.com</a>获取答案。</p>";
			}
			
		}else{
			return "<form id='adv-auth-form' method='post'> 
				<h3>这是一篇受保护的日志，你需要输入下面问题的答案才能查看</h3>
				<div>问题：".$question."</div>
				<div>答案：<input name='adv-auth-user-ans' type='text'/></div>
				<div><input name='submit' value='提交' type='submit'/></div>
				</form>
			    <p>如果你不知道答案但希望阅读，请联系作者<a href='mailto:abraham1@163.com'>Abraham1@163.com</a>获取答案。</p>";

		}
	}else{
		return $c;	
	}
}

add_action ( 'admin_menu', 'my_plugin_menu' );

define ( "OPT_PRE", "adv-auth-" );

function my_plugin_menu() {
	if (! current_user_can ( 'manage_options' )) {
		wp_die ( __ ( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	add_options_page ( '日志保护设置', '日志保护', 'manage_options', 'adv-auth-unique-identifier', 'adv_auth_options' );

		//add_submenu_page("plugins.php","My Plugin Options","Abraham","manage_options",'submenu-unique-id','my_plugin_options');
}

function adv_auth_options() {
	if (! current_user_can ( 'manage_options' )) {
		wp_die ( __ ( 'You do not have sufficient permissions to access this page.' ) );
	}
	include_once dirname ( __FILE__ ) . "/adv-auth-options.php";

}
?>