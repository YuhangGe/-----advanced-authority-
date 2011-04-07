<?php
/**
 * @package Advanced Authority
 * @version 1.0
 */
/*
Plugin Name: 日志保护
Plugin URI: http://wordpress.org/extend/plugins/advance-authority/
Description: 日志保护插件，可以设置日志查看需要回答的问题（密码），问题答案可以有多个。同时可以设置全局口令可用于回答所有问题。
Author: Abraham
Version: 1.0
Author URI: http://www.yuhanghome.net
*/

//define the option_name of this plugin
define ( 'ADV_AUTH_KEY', 'adv-auth-keys' );
define ( 'ADV_AUTH_NAME', 'adv-auth-name' );
define ( 'ADV_AUTH_EMAIL', 'adv-auth-email' );
define ( 'ADV_AUTH_ANS','adv-auth-ans');
define ( 'ADV_AUTH_USER_ANS','adv-auth-user-ans');

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
	
	$adv_auth_on = $_POST ['adv-auth-on'];
	$adv_auth_ques = $_POST ['adv-auth-ques'];
	$adv_auth_ans = $_POST ['adv-auth-ans'];
	$adv_ans_arr = array ();
	if ($adv_auth_on == 'on') {
		if (trim ( $adv_auth_ques ) == "") {
			return $post_id;
		}
		//echo $adv_auth_ans;
		$ans_tmp = explode ( '|', $adv_auth_ans );
		//var_dump($ans_tmp);
		//exit();
		foreach ( $ans_tmp as $ans_e ) {
			if (trim ( $ans_e ) == "") {
				return $post_id;
			} else {
				$adv_ans_arr [] = trim ( $ans_e );
			}
		}
		update_post_meta ( $post_id, 'adv-auth-on', $adv_auth_on );
		update_post_meta ( $post_id, 'adv-auth-ques', $adv_auth_ques );
		update_post_meta ( $post_id, 'adv-auth-ans', $adv_ans_arr );
	
	} else {
		update_post_meta ( $post_id, 'adv-auth-on', 'off' );
	}
	
	return $post_id;
}

$adv_auth_passed = false;

add_filter ( 'comments_array', 'adv_auth_comments' );
function adv_auth_comments($arr) {
	global $adv_auth_passed;
	if ($adv_auth_passed)
		return $arr;
	else
		return array ();
}

add_action ( 'the_content', 'adv_auth_content' );

//hook the_content to vertify authority
function adv_auth_content($c) {
	global $post, $current_user, $adv_auth_passed;
	$adv_on = get_post_meta ( $post->ID, 'adv-auth-on', true );
	if ($adv_on == 'on') {
		
		//first, check if this post was written by current user
		get_currentuserinfo ();
		$a_id = $post->post_author;
		if ($current_user != null && $a_id == $current_user->ID) {
			return $c;
		}
		
		$question = get_post_meta ( $post->ID, 'adv-auth-ques', true );
		
		$u_ans_name=ADV_AUTH_USER_ANS.'-'.$post->ID;
		if (isset ( $_POST [$u_ans_name] )==true)  {
			$user_ans = $_POST [$u_ans_name];
	
			if(adv_is_correct($post->ID,$user_ans)==true){
				$adv_auth_passed = true;
				echo '<strong>set cookie</strong>';
				setcookie ( $u_ans_name, $user_ans, time () + 3600 * 24 * 2 );
				return $c;
			}
			
			//disable comment
			$post->comment_status = 'close';
			
			$author = adv_get_author ();
			//or return no authority message
			return "<h2 style='color:red;'>问题回答错误！</h2>
					<p>作者没有向所有人公开日志，如果你想阅读，请联系<a href='" . $author ['email'] . "'>" . $author ['name'] . "</a>获取答案。</p>";
		
		} else {
			if(isset($_COOKIE[$u_ans_name])==true){
				$user_ans=$_COOKIE[$u_ans_name];
				if(adv_is_correct($post->ID,$user_ans)==true){
					echo '<strong>cookie correct</strong>';
					$adv_auth_passed = true;
					return $c;
				}
			}
			//disable comment
			$post->comment_status = 'close';
			
			$author = adv_get_author ();
			return "<form id='adv-auth-form-".$post->ID."' method='post'> 
				<h3>这是一篇受保护的日志，你需要输入下面问题的答案才能查看</h3>
				<div>问题：" . $question . "</div>
				<div>答案：<input name='".$u_ans_name."' type='text'/></div>
				<div><input autocomplete='off' name='submit' value='提交' type='submit'/></div>
				</form>
			    <p>如果你不知道答案但希望阅读，请联系<a href='" . $author ['email'] . "'>" . $author ['name'] . "</a>获取答案。</p>";
		
		}
	} else {
		$adv_auth_passed = true;
		return $c;
	}
}
function adv_is_correct($p_id, $user_ans) {
	$ans_tmp = get_post_meta ( $p_id, ADV_AUTH_ANS );
	//var_dump($ans_tmp);
	$answers = $ans_tmp [0];
	//if the answer is correct, return true
	if (in_array ( $user_ans, $answers )) {
		return true;
	}
	
	$keys_tmp = get_option ( ADV_AUTH_KEY );
	$key_arr = explode ( '|', $keys_tmp );
	//if the answer is correct global key, return content
	if (in_array ( $user_ans, $key_arr )) {
		return true;
	}

}

function adv_get_author() {
	$author_name = get_option ( ADV_AUTH_NAME );
	$author_email = get_option ( ADV_AUTH_EMAIL );
	if (empty ( $author_name ))
		$author_name = "作者";
	if (empty ( $author_email ))
		$author_email = '#';
	else
		$author_email = 'mailto:' . $author_email;
	return array ('name' => $author_name, 'email' => $author_email );
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