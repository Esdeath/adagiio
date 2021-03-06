<?php

// 定义语言

add_action('after_setup_theme', 'my_theme_setup');
function my_theme_setup() {
	load_theme_textdomain('dpt', get_template_directory() . '/lang');
}

// 定义导航

register_nav_menus(array(
	'main' => __( 'Main Nav','dpt' ),
));

// 定义侧边栏

if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'name' => __( 'Sidebar', 'dpt' ),
		'id' => 'dpt',
		'description' => 'Sidebar',
		'class' => '',
		'before_widget' => '<section class="widgets">',
		'after_widget' => '</section>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	)
);

// 定义特色图片

add_theme_support( 'post-thumbnails' );
set_post_thumbnail_size( 800, 410, true );


function autoset_featured() {

global $post;
$already_has_thumb = has_post_thumbnail($post->ID);

if (!$already_has_thumb)  {
	$attached_image = get_children( "post_parent=$post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );
	if ($attached_image) {
		foreach ($attached_image as $attachment_id => $attachment) {
			set_post_thumbnail($post->ID, $attachment_id);
		}
	}
}

}

add_action('new_to_publish', 'autoset_featured');

// 检查更新，需要一个服务器存放 info.json 和主题安装包。请参见 func 目录

if (get_option('dpt_upck') != 'no' ) {
	require_once(TEMPLATEPATH . '/func/theme-update-checker.php'); 
	$wpdaxue_update_checker = new ThemeUpdateChecker(
		'Adagiio',
		'http://work.dimpurr.com/theme/adagiio/update/info.json'
	);
}

// 主题使用统计，如果需要。

function dpt_count() {

// Ajax 统计函数

function dpt_tjaj() { ?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		// 修改地址为服务器的 theme_tj.php 页面。请参见 func 目录
		jQuery.get("http://work.dimpurr.com/theme/theme_tj.php?theme_name=Adagiio&blog_url=<?=get_bloginfo('url')?>&t=" + Math.random());
	});
	</script>
<?php };

// 统计筛选条件

$dpt_fitj = get_option('dpt_fitj');
$dpt_dayv = get_option('dpt_dayv');
$dpt_date = date('d'); 

if ($dpt_fitj == true) { 
	if($dpt_date == '01') {
		if ($dpt_dayv != true) {
			dpt_tjaj();
			update_option( 'dpt_dayv', true );
		};
	} elseif ($dpt_date != '01') {
		update_option( 'dpt_dayv', false );
	};
} else {
	dpt_tjaj();
	update_option( 'dpt_fitj', true );
};

};

// 获取博客标题

function dpt_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	$title .= get_bloginfo( 'name' );

	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( '页面 %s', 'dpt' ), max( $paged, $page ) );

	return $title;
}

add_filter( 'wp_title', 'dpt_title', 10, 2 );

// 随机头图

function dpt_banner() {
	$parray = glob(get_stylesheet_directory() . "/banner/*.*");
	echo get_template_directory_uri() . "/banner/" . basename($parray[array_rand($parray)]);
}

// 显示摘要

function get_post_excerpt($post, $excerpt_length=360){
    if(!$post) $post = get_post();

    $post_excerpt = $post->post_excerpt;
    if($post_excerpt == ''){
        $post_content = $post->post_content;
        $post_content = do_shortcode($post_content);
        $post_content = wp_strip_all_tags( $post_content );

        $post_excerpt = mb_strimwidth($post_content,0,$excerpt_length,'…','utf-8');
    }

    $post_excerpt = wp_strip_all_tags( $post_excerpt );
    $post_excerpt = trim( preg_replace( "/[\n\r\t ]+/", ' ', $post_excerpt ), ' ' );

    return $post_excerpt;
}

// 页面导航

function dpt_pagenavi () {
	global $wp_query, $wp_rewrite;
	$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;

	$pagination = array(
		'base' => @add_query_arg('paged','%#%'),
		'format' => '',
		'total' => $wp_query->max_num_pages,
		'current' => $current,
		'show_all' => false,
		'type' => 'plain',
		'end_size'=>'0',
		'mid_size'=>'5',
		'prev_text' => __('←','dpt'),
		'next_text' => __('→','dpt')
	);

	if( $wp_rewrite->using_permalinks() )
		$pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg('s',get_pagenum_link(1) ) ) . 'page/%#%/', 'paged');

	if( !empty($wp_query->query_vars['s']) )
		$pagination['add_args'] = array('s'=>get_query_var('s'));

	echo paginate_links($pagination);
}

// 评论附加函数

function delete_comment_link( $id ) {
	if (current_user_can('level_5')) {
		echo '<a class="comment-delete-link" href="'.admin_url("comment.php?action=cdc&c=$id").'">删除</a> ';
	}
}

// 加载评论

if ( ! function_exists( 'dpt_comment' ) ) :
function dpt_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php echo 'Pingback '; ?> <?php comment_author_link(); ?> <aside class="comment-link"><?php edit_comment_link( '编辑', '<span class="comment-edit-link">', '</span>' ); ?></aside></p>
	<?php
			break;
		default :
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<?php
				echo '<div class="avatar">' . get_avatar( $comment, 44 ) . '</div><div class="cmt_r">';
				printf( '<span class="cmt_meta_head">%1$s</span>',
					get_comment_author_link() );
				printf( '<span class="cmt_meta_time"><a href="%1$s"><time datetime="%2$s">%3$s</time></a></span>',
					esc_url( get_comment_link( $comment->comment_ID ) ),
					get_comment_time( 'c' ),
					sprintf( '%1$s %2$s' , get_comment_date(), get_comment_time() )
				);
			?>
				
			<aside class="comment-link">
				<?php edit_comment_link( __('編輯','dpt'), '', '' ); ?>
				<?php delete_comment_link(get_comment_ID()); ?>
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __('回复','dpt'), 'after' => '', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</aside>

			<div class="cmt_con"><?php comment_text(); ?></div>

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e('审核中','dpt'); ?></p>
			<?php endif; ?>
			</div>
		</article>
	<?php
		break;
	endswitch;
}
endif;

// 设置页单选按钮

function dpt_va($option) {
	if ( get_option($option) == "yes" ) { echo 'checked="true"'; }
}

function dpt_vb($option) {
	if ( get_option($option) == "no" ) { echo 'checked="true"'; }
}

// 谷歌字体移除

function remove_open_sans() {
	wp_deregister_style('open-sans');
	wp_register_style('open-sans',false);
	wp_enqueue_style('open-sans','');
}
if ( get_option('dpt_rmgf') == "yes" ) {
	add_action('init','remove_open_sans');
}

// 后台设置页面

function dpt_menu_func(){   
	add_theme_page(
		__('Adagiio Config','dpt'),
		__('Adagiio Config','dpt'),
		'administrator',
		'dpt_menu',
		'dpt_config');
}

add_action('admin_menu', 'dpt_menu_func');

function dpt_config(){ dpt_count(); ?>

<form method="post" name="dpt_form" id="dpt_form">

<h1><?php _e('主题设置'); ?></h1>
<hr width="600" align="left" color="#DDD" />
<h3>随机头图</h3>
<p>将会随机输出 /bannner/ 下的文件作为随机头图，请确保有至少一个文件且全部为有效图片</p>

<br>
<h3><?php _e('自定义样式'); ?></h3>
<textarea name="dpt_style" rows="10" cols="60" placeholder="<?php _e('输入 CSS 代码，以便更新时不会被覆盖'); ?>" style="font-size: 14px; font-family: Consolas, monospace, sans-serif, sans"><?php echo get_option('dpt_style'); ?></textarea><br>


<br>
<h3><?php _e('统计代码'); ?></h3>
<textarea name="dpt_tongji" rows="10" cols="60" placeholder="<?php _e('贴入统计工具提供的网站统计代码','dpt'); ?>" style="font-size: 14px; font-family: Consolas, monospace, sans-serif, sans"><?php echo get_option('dpt_tongji'); ?></textarea><br>

<br>
<h3><?php _e('检查更新'); ?></h3>
<p><?php _e('可以应对服务器设置导致的无限提示更新问题。需要更新时请手动打开此开关'); ?></p>
<input type="radio" name="dpt_upck" value="yes" required="required" <?php dpt_va("dpt_upck"); ?> /><?php _e('启用'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="dpt_upck" value="no" required="required" <?php dpt_vb("dpt_upck"); ?> /><?php _e('关闭'); ?><br>

<br>
<h3><?php _e('禁用 Google Font'); ?></h3>
<p><?php _e('解决由 OpenSans 网络字体引用带来的加载缓慢问题'); ?></p>
<input type="radio" name="dpt_rmgf" value="yes" required="required" <?php dpt_va("dpt_rmgf"); ?> /><?php _e('启用'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="dpt_rmgf" value="no" required="required" <?php dpt_vb("dpt_rmgf"); ?> /><?php _e('关闭'); ?><br>

<br>
<input type="submit" name="option_save" value="<?php _e('保存设置','dpt'); ?>" />

<?php wp_enqueue_script('thickbox'); wp_enqueue_style('thickbox'); ?>
	<script type="text/javascript">
	// 导入 WordPress 媒体上传组件
jQuery(document).ready(function() {
	// 选择按钮
	jQuery('#upbottom').click(function() {
		// 选择目标文本框
		targetfield = jQuery(this).prev('#dpt_example');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
	window.send_to_editor = function(html) {
		imgurl = jQuery('img',html).attr('src');
		jQuery(targetfield).val(imgurl);
		tb_remove();
	}	
});
	</script>

<?php wp_nonce_field('update-options'); ?>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="dpt_copy_right" />

</form>

<?php }

// 提交设置

if(isset($_POST['option_save'])){

	$dpt_banner_n = stripslashes($_POST['dpt_banner_n']);
	update_option( 'dpt_banner_n', $dpt_banner_n );

	$dpt_tongji = stripslashes($_POST['dpt_tongji']);
	update_option( 'dpt_tongji', $dpt_tongji );

	$dpt_upck = stripslashes($_POST['dpt_upck']);
	update_option( 'dpt_upck', $dpt_upck );

	$dpt_rmgf = stripslashes($_POST['dpt_rmgf']);
	update_option( 'dpt_rmgf', $dpt_rmgf );

	$dpt_style = stripslashes($_POST['dpt_style']);
	update_option( 'dpt_style', $dpt_style );

}

?>