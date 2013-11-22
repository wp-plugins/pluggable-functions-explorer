<?php
/*
Plugin Name: Pluggable Functions Explorer
Plugin URI: http://wordpress.org/plugins/pluggable-functions-explorer/
Description: Check which <strong>Pluggable Functions</strong> have been overriden (reassigned), and in which PHP file. Activate the plugin and visit <strong>Tools&nbsp;&rsaquo;&nbsp;Pluggable&nbsp;Functions</strong>
Author: wpCure
Author URI: http://wpcure.com/
Version: 1.0.0
*/

class WPCure_Pluggable_Functions {

	private static $instance = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public function admin_menu() {
		add_management_page( 'Pluggable Functions', 'Pluggable Functions', 'manage_options', 'wpcure-pluggable-functions', array( $this, 'admin_page' ) );
	}

	public function admin_page() {
		echo '<h1>Pluggable Functions Explorer</h1>';
		if ( ! class_exists( 'ReflectionClass' ) || ! class_exists( 'ReflectionFunction' ) ) {
			echo '<div id="message" class="error"><strong>Error: Reflection package does NOT exist. Thus, this plugin is NOT compatible with your PHP environment.</strong></div>';
			return;
		}
		$items = $this->get_pluggable_items();
		echo '<p>The information in the following table sheds light on the <a href="http://codex.wordpress.org/Pluggable_Functions" target="_blank">Pluggable Functions</a> conditionally declared by WordPress Core.<br />In case a function has been overridden (reassigned) by another module, such as a plugin, the PHP file containing the <em>effective declaration</em> is shown.</p>';
		echo '<table class="widefat">';
		echo '<tr><th>Name</th><th>Type</th><th>Status</th><th>Effectively declared in File</th></tr>';
		foreach ( $items as $item ) {
			$filename = str_replace( ABSPATH, '', $item['effective_filename'] );
			if ( $item['wpcore_filename'] == $item['effective_filename'] ) {
				echo '<tr>';
				echo '<td>' . $item['name'] . '</td>';
				echo '<td>' . $item['type'] . '</td>';
				echo '<td>not overridden</td>';
				echo '<td>' . $filename . '</td>';
				echo '</tr>';
			} else {
				echo '<tr>';
				echo '<td><span style="color:red"><strong>' . $item['name'] . '</strong></span></td>';
				echo '<td><span style="color:red"><strong>' . $item['type'] . '</strong></span></td>';
				echo '<td><span style="color:red"><strong>overridden</strong></span></td>';
				echo '<td><span style="color:red"><strong>' . $filename . '</strong></span></td>';
				echo '</tr>';
			}
		}
		echo '</table>';
	}

	private function get_pluggable_items() {
		$pluggable_items = array();
		foreach ( array( 'pluggable.php', 'pluggable-deprecated.php' ) as $basename ) {
			$filename = ABSPATH . 'wp-includes' . DIRECTORY_SEPARATOR . $basename;
			$fcontent = file_get_contents( $filename );
			if ( false !== $fcontent ) {
				// classes
				preg_match_all( '/class[\s\n]+(\S+)[\s\n]*\{/', $fcontent, $results );
				if ( isset( $results[1] ) && is_array( $results[1] ) ) {
					foreach ( $results[1] as $class_name ) {
						$key = 'class-' . $class_name;
						$pluggable_items[ $key ] = array( 'type' => 'class', 'name' => $class_name, 'wpcore_filename' => $filename, 'effective_filename' => $filename );
					}
				}
				// functions
				preg_match_all( '/function[\s\n]+(\S+)[\s\n]*\(/', $fcontent, $results );
				if ( isset( $results[1] ) && is_array( $results[1] ) ) {
					foreach ( $results[1] as $function_name ) {
						$key = 'function-' . $function_name;
						$pluggable_items[ $key ] = array( 'type' => 'function', 'name' => $function_name, 'wpcore_filename' => $filename, 'effective_filename' => $filename );
					}
				}
			}
		}
		ksort( $pluggable_items );
		foreach ( $pluggable_items as $id => $pluggable_item ) {
			if ( 'class' == $pluggable_item['type'] ) {
				try {
					$reflection = new ReflectionClass( $pluggable_item['name'] );
					$pluggable_item['effective_filename'] = $reflection->getFileName();
					$pluggable_items[ $id ] = $pluggable_item;
				} catch ( Exception $e ) {
					unset( $pluggable_items[ $id ] );
					continue;
				}
			} elseif ( 'function' == $pluggable_item['type'] ) {
				try {
					$reflection = new ReflectionFunction( $pluggable_item['name'] );
					$pluggable_item['effective_filename'] = $reflection->getFileName();
					$pluggable_items[ $id ] = $pluggable_item;
				} catch ( Exception $e ) {
					unset( $pluggable_items[ $id ] );
					continue;
				}
			}
		}
		return $pluggable_items;
	}

}

WPCure_Pluggable_Functions::get_instance();