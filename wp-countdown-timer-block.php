<?php
/**
 * Plugin Name:       Countdown Timer
 * Plugin URI:        https://zubeidhendricks.dev/wp-plugins/countdown-timer-block
 * Description:        Drop a live countdown timer anywhere with a shortcode — for launches, sales and events. Lightweight, no jQuery.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Zubeid Hendricks
 * Author URI:        https://zubeidhendricks.dev
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       countdown-timer-block
 *
 * @package CountdownTimerBlock
 */

defined( 'ABSPATH' ) || exit;

define( 'COUNTDOWN_TIMER_BLOCK_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/factory-core.php';

/**
 * Countdown Timer.
 */
final class CountdownTimerBlock extends ZubFactory_Plugin {

	private $rendered = false;

	protected function configure() {
		$this->slug    = 'countdown-timer-block';
		$this->title   = 'Countdown Timer';
		$this->version = COUNTDOWN_TIMER_BLOCK_VERSION;
	}

	protected function settings_fields() {
		return array(
			'accent' => array(
				'label'   => __( 'Accent colour', 'countdown-timer-block' ),
				'type'    => 'color',
				'default' => '#2271b1',
			),
			'expiry' => array(
				'label'   => __( 'Default expiry text', 'countdown-timer-block' ),
				'type'    => 'text',
				'default' => 'This offer has ended.',
			),
			'evergreen' => array(
				'label'    => __( 'Evergreen timers', 'countdown-timer-block' ),
				'type'     => 'checkbox',
				'cb_label' => __( 'Per-visitor countdowns that restart for each user', 'countdown-timer-block' ),
				'pro'      => true,
			),
		);
	}

	protected function hooks() {
		add_shortcode( 'countdown', array( $this, 'shortcode' ) );
	}

	/**
	 * [countdown date="2026-12-31 23:59" expired="Sale over!"]
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'date'    => '',
				'expired' => $this->option( 'expiry', 'This offer has ended.' ),
			),
			$atts,
			'countdown'
		);

		$ts = $atts['date'] ? strtotime( $atts['date'] ) : 0;
		if ( ! $ts ) {
			return '';
		}

		$accent = $this->option( 'accent', '#2271b1' ) ?: '#2271b1';
		$id     = 'zct-' . wp_rand( 1000, 9999 );

		ob_start();
		if ( ! $this->rendered ) {
			$this->rendered = true;
			$this->styles( $accent );
		}
		?>
		<div class="zct" id="<?php echo esc_attr( $id ); ?>"
			data-deadline="<?php echo esc_attr( $ts * 1000 ); ?>"
			data-expired="<?php echo esc_attr( $atts['expired'] ); ?>">
			<div class="zct-unit"><span class="zct-n" data-u="d">0</span><span class="zct-l"><?php esc_html_e( 'Days', 'countdown-timer-block' ); ?></span></div>
			<div class="zct-unit"><span class="zct-n" data-u="h">0</span><span class="zct-l"><?php esc_html_e( 'Hours', 'countdown-timer-block' ); ?></span></div>
			<div class="zct-unit"><span class="zct-n" data-u="m">0</span><span class="zct-l"><?php esc_html_e( 'Mins', 'countdown-timer-block' ); ?></span></div>
			<div class="zct-unit"><span class="zct-n" data-u="s">0</span><span class="zct-l"><?php esc_html_e( 'Secs', 'countdown-timer-block' ); ?></span></div>
		</div>
		<script>
		(function(){
			var el=document.getElementById('<?php echo esc_js( $id ); ?>');
			if(!el)return;
			var end=parseInt(el.getAttribute('data-deadline'),10);
			function pad(n){return n<10?'0'+n:''+n;}
			function tick(){
				var diff=end-Date.now();
				if(diff<=0){el.innerHTML='<div class="zct-done">'+el.getAttribute('data-expired')+'</div>';clearInterval(t);return;}
				var s=Math.floor(diff/1000);
				var q=function(u){return el.querySelector('[data-u="'+u+'"]');};
				q('d').textContent=Math.floor(s/86400);
				q('h').textContent=pad(Math.floor(s%86400/3600));
				q('m').textContent=pad(Math.floor(s%3600/60));
				q('s').textContent=pad(s%60);
			}
			tick();var t=setInterval(tick,1000);
		})();
		</script>
		<?php
		return ob_get_clean();
	}

	private function styles( $accent ) {
		?>
		<style>
			.zct{display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin:16px 0;font-family:inherit}
			.zct-unit{min-width:64px;background:<?php echo esc_attr( $accent ); ?>;color:#fff;
				border-radius:10px;padding:12px 8px;text-align:center;line-height:1.1}
			.zct-n{display:block;font-size:30px;font-weight:700;font-variant-numeric:tabular-nums}
			.zct-l{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.6px;opacity:.85;margin-top:4px}
			.zct-done{font-size:18px;font-weight:600;text-align:center;padding:12px}
		</style>
		<?php
	}
}

add_action(
	'plugins_loaded',
	function () {
		( new CountdownTimerBlock( __FILE__ ) )->boot();
	}
);
