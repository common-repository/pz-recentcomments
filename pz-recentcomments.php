<?php
/*
Plugin Name: Pz-RecentComments
Plugin URI: https://popozure.info/pz-recentcomments
Description: Recent comments widget.
Version: 1.3.2
Author: poporon
Author URI: https://popozure.info
License: GPLv2 or later
*/

class PzRecentCommentsWidget extends WP_Widget {
	public	$slug;
	function __construct() {
		$this->slug = basename(dirname(__FILE__));
		load_plugin_textdomain($this->slug, false, $this->slug.'/languages');
		parent::__construct( 'pz-recentcomments', __( 'Pz Recent Comments', $this->slug ), array('description' => __('Recent comments widget.', $this->slug ) ) );
		add_action('wp_enqueue_scripts', array($this, 'enqueue'));
	}

	function enqueue() {
		wp_enqueue_style	('pz-recentcomments', plugin_dir_url (__FILE__).'style.css');
		wp_enqueue_style	('wp-color-picker');
		wp_enqueue_script	('colorpicker-script', plugins_url('color-picker.js', __FILE__), array('wp-color-picker'), false, true);
	}
	
	// ウィジェット
	function widget($args, $instance ) {
		if (!isset($args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}
		
		$output		= '';
		$title		= isset($instance['title']) ? $instance['title'] : __('Recent Comments', $this->slug );
		$number		= isset($instance['number']) ? absint($instance['number'] ) : 5 ;
			
		// $mouseover	= (!empty($instance['mouseover']) ? $instance['mouseover'] : '' );
		$mouseover = (wp_is_mobile() ? 0 : 1 );
		
		$comments = get_comments( apply_filters( 'widget_comments_args',
			array(
				'number'		=> $number,
				'status'		=> 'approve',
				'type'			=> 'comment',
				'post_status'	=> 'publish'
			) ) );
		
		$output		= $args['before_widget'];
		if ( $title ) {
			$output	.= $args['before_title'].$title.$args['after_title'];
		}
		
		if ( is_array($comments ) && $comments ) {
			foreach ( $comments as $comment ) {
				
				$output		.=	'<span class="pz-rcs-user">';
//				if ( !empty($comment->comment_author_url ) ) {
//					$output	.=	'<span class="pz-rcs-author-link"><a href="'.esc_url( $comment->comment_author_url ).'" target="_blank">';
//				}
				$output		.=	'<span class="pz-rcs-avatar">'.get_avatar( $comment, 40, null ).'</span>';
				$output		.=	'<span class="pz-rcs-author">'.$comment->comment_author.'</span>';
//				if ( !empty($comment->comment_author_url ) ) {
//					$output	.=	'</a></span>';
//				}
				$output		.=	'<span class="pz-rcs-date">'.get_comment_date( get_option( 'date_format' ) , $comment->comment_ID).'</span>';
				$output		.=	'</span>';

				if ( $comment->user_id > 0 ) {
					$color	= isset($instance['color'])			? ' style="background-color: '.$instance['color'].';"'	: '' ;
				} else {
					$color	= isset($instance['color_guest'])	? ' style="background-color: '.$instance['color_guest'].';"'		: '' ;
				}
				$output		.=	'<span class="pz-rcs-content-link"><A href="'.get_comment_link( $comment->comment_ID ).'"><span class="pz-rcs-content"'.( $mouseover ? ' title="'.esc_html($comment->comment_content).'"' : '' ).$color.'>'.get_comment_excerpt( $comment->comment_ID ).'</span></a></span>';
				$output		.=	'<span class="pz-rcs-title"><A href="'.get_permalink( $comment->comment_post_ID ).'">'.get_the_title( $comment->comment_post_ID ).'</a></span>';
			}
		}
		$output		.= $args['after_widget'];
		
		echo	$output;
	}
	
	public function update( $new_instance, $old_instance ) {
		$instance				=	$old_instance;
		$instance['title']		=	sanitize_text_field( $new_instance['title'] );
		$instance['number']		=	absint( $new_instance['number'] );
		$new_color				=	sanitize_text_field( $new_instance['color'] );
		if ( $new_color == '' ) {
			$new_color			=	'#ffffff';
		}
		if (preg_match( '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $new_color )) {
			$instance['color']	=	$new_color;
		}
		$new_color				=	sanitize_text_field( $new_instance['color_guest'] );
		if ( $new_color == '' ) {
			$new_color			=	'#eeeeee';
		}
		if (preg_match( '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $new_color )) {
			$instance['color_guest']		=	$new_color;
		}
		return	$instance;
	}

	// 外観設定
	public function form( $instance ) {
		$title		= !empty( $instance['title']		) ? $instance['title'] : '';
		$number		= !empty( $instance['number']		) ? absint( $instance['number'] ) : 5;
		$color		= isset( $instance['color_guest']	) ? $instance['color_guest'] : '#ffffff' ;
		$color_user	= isset( $instance['color']			) ? $instance['color'] : '#eeeeee' ;
		$mouseover	= isset( $instance['mouseover']		) ? $instance['mouseover'] : 0;

		echo '<p><label for="'.$this->get_field_id( 'title' ).'">'.__( 'Title:', $this->slug ).'</label>';
		echo '<input class="widefat" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" type="text" value="'.esc_attr( $title ).'" /></p>';

		echo '<p><label for="'.$this->get_field_id( 'number' ).'">'.__( 'Number of comments to show:', $this->slug ).'</label>';
		echo '<input class="tiny-text" id="'.$this->get_field_id( 'number' ).'" name="'.$this->get_field_name( 'number' ).'" type="number" step="1" min="1" max="999" value="'.$number.'" size="3" /></p>';

		echo '<p><label for="'.$this->get_field_id( 'color' ).'">'.__( 'Your balloon color', $this->slug ).' ('.__( 'color code', $this->slug ).'):</label><br>';
		echo '<input class="color-picker" id="'.$this->get_field_id( 'color' ).'" name="'.$this->get_field_name( 'color' ).'" type="text" value="'.esc_attr( $color_user ).'" /></p>';

		echo '<p><label for="'.$this->get_field_id( 'color_guest' ).'">'.__( 'Guest balloon color', $this->slug ).' ('.__( 'color code', $this->slug ).'):</label><br>';
		echo '<input class="color-picker" id="'.$this->get_field_id( 'color_guest' ).'" name="'.$this->get_field_name( 'color_guest' ).'" type="text" value="'.esc_attr( $color ).'" /></p>';
	}
}
add_action('widgets_init', create_function('', 'return register_widget("PzRecentCommentsWidget");'));