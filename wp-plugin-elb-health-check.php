<?php
/*
Plugin Name: ELB Health Check
Version: 0.1-alpha
Description: 本番環境でELBのヘルスチェックに利用するプラグインです。Cloud Design PatternのDeep Health Checkパターンを参考に動作プロセスの確認を行います。
*/

/**
 * CodeSnifferのセットアップ
 * $ composer global require 'squizlabs/php_codesniffer=*'
 * $ git clone https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git ~/.composer/vendor/squizlabs/php_codesniffer/CodeSniffer/Standards/WordPress
 * $ ~/.composer/vendor/bin/phpcs --config-set installed_paths ~/.composer/vendor/squizlabs/php_codesniffer/CodeSniffer/Standards/WordPress
 *
 * CodeSnifferの実行
 * Validate
 * $ ~/.composer/vendor/bin/phpcs -p -s -v --standard=WordPress-Core *.php
 *
 * CodeSnifferによる自動修正
 * $ ~/.composer/vendor/bin/phpcbf -p -s -v --standard=WordPress-Core *.php
 */

define( 'ELB_HEALTH_CHECK__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ELB_HEALTH_CHECK__PLUGIN_VER', '0.1' );

class ElbHealthCheck {

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'create_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_assets' ] );
	}

	/**
	 * 管理画面メニュー生成
	 */
	function create_admin_menu() {
		add_options_page(
			'ELBヘルスチェック',
			'ELBヘルスチェック',
			'manage_options',
			'elb-health-check-options',
			[ &$this, 'my_option_page' ]
		);
	}

	/**
	 * CSS/JSの読み込み
	 */
	function load_assets() {

		wp_register_style( 'style.css', ELB_HEALTH_CHECK__PLUGIN_URL . 'assets/css/style.css', [], ELB_HEALTH_CHECK__PLUGIN_VER );
		wp_enqueue_style( 'style.css' );

		wp_register_script( 'admin.js', ELB_HEALTH_CHECK__PLUGIN_URL . 'assets/js/admin.js', [ 'jquery' ], ELB_HEALTH_CHECK__PLUGIN_VER, $in_footer = true );
		wp_enqueue_script( 'admin.js' );
	}

	/**
	 * 管理画面表示
	 */
	function my_option_page() {
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$this->save_my_options();
		}
		$processes = get_option( 'elb-health-check-processes' );
		?>
		<div class="wrap">
			<h2>ELBヘルスチェックの設定</h2>
			<p>ELBのヘルスチェックのURLに下記を設定して利用します。</p>
			<p>/wp-content/plugins/wp-plugin-elb-health-check/health.php</p>
			<form id="elb-health-check-form" method="post" action="">
				<?php
				// nonceは12時間毎に変化する
				wp_nonce_field( 'elb-health-check-nonce-key', 'elb-health-check-nonce-name' );
				// nonceの出力例
				// <input type="hidden" id="cookie-sso-nonce-name" name="cookie-sso-nonce-name" value="3f86788d8e">
				// <input type="hidden" name="_wp_http_referer" value="/wp-admin/options-general.php?page=cookie-sso-options">
				?>

				<h3>動作確認対象のプロセス名</h3>
				<div class="elb-health-check__process--to-add">
					<input id="text-to-add" type="text" name="process--to-add" value="" />
					<a id="btn-to-add-process" class="button button-small">追加</a>
				</div>
				<h3>設定済みの確認対象プロセス</h3>
				<ul class="elb-health-check__processes">
				<?php
				$i = 0;
				foreach ( $processes as $process ) {
				?>

				<li id="<?php echo $i; ?>" class="elb-health-check__process--to-display">
					<input type="text" name="process-to-add-<?php echo $i; ?>" value="<?php echo esc_attr( $process ); ?>"/>
					<a href="#" onClick="test(event)">削除</a>
				</li>

				<?php
					$i ++;
				}
				?>
				</ul>
				<p><input type="submit" value="保存" class="button button-primary button-large" /></p>
			</form>
		</div>
		<?php
	}
	/**
	 * 管理画面保存時の処理
	 */
	function save_my_options() {
		if ( ! isset( $_POST['elb-health-check-nonce-name'] ) || ! $_POST['elb-health-check-nonce-name'] ) {
			return;
		}
		if ( ! check_admin_referer( 'elb-health-check-nonce-key', 'elb-health-check-nonce-name' ) ) {
			return;
		}

		$processes = [];
		foreach ( $_POST as $key => $val ) {
			if ( preg_match( '/process-to-add-[0-9]*/', $key ) ) {
				$is_not_empty = ! empty( trim( $val ) );
				if ( $is_not_empty ) {
					$processes[] = $val;
				}
			}
		}

		update_option( 'elb-health-check-processes', $processes );

	}
}
new ElbHealthCheck();
?>
