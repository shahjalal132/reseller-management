<?php
/**
 * Floating live chat widget (Messenger, WhatsApp, Call).
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$channels = \BOILERPLATE\Inc\Reseller_Helper::get_live_chat_channels();
if ( empty( $channels ) ) {
	return;
}

$icons = [
	'messenger' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.36 2 2 6.13 2 11.7c0 2.91 1.19 5.44 3.14 7.17V22l2.86-1.57c1.25.35 2.58.53 3.99.53 5.64 0 10.01-4.13 10.01-9.7C22 6.13 17.64 2 12 2zm1.03 13.08-2.55-2.72-4.98 2.72 5.48-5.82 2.61 2.72 4.92-2.72-5.48 5.82z"/></svg>',
	'whatsapp'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.47 14.38c-.29-.14-1.7-.84-1.96-.93-.26-.1-.45-.15-.64.14-.19.29-.74.93-.9 1.12-.17.19-.33.21-.62.07-.29-.14-1.22-.45-2.32-1.43-.86-.77-1.44-1.71-1.61-2-.17-.29-.02-.45.13-.59.13-.13.29-.33.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.64-1.54-.88-2.11-.23-.55-.47-.48-.64-.48h-.55c-.19 0-.5.07-.76.36-.26.29-1 1-1 2.43s1.02 2.82 1.17 3.01c.14.19 2 3.05 4.85 4.28.68.29 1.21.46 1.62.59.68.22 1.3.19 1.79.11.55-.08 1.7-.69 1.94-1.36.24-.67.24-1.24.17-1.36-.07-.11-.26-.18-.55-.32zM12.05 21.8h-.01a9.8 9.8 0 0 1-4.99-1.37l-.36-.21-3.71.97 1-3.62-.24-.37a9.76 9.76 0 0 1-1.5-5.21c0-5.4 4.4-9.79 9.82-9.79a9.73 9.73 0 0 1 6.93 2.87 9.73 9.73 0 0 1 2.87 6.92c0 5.4-4.4 9.8-9.81 9.8zm8.41-18.21A11.78 11.78 0 0 0 12.04 0C5.46 0 .1 5.35.1 11.92c0 2.1.55 4.15 1.6 5.96L0 24l6.3-1.65a11.9 11.9 0 0 0 5.73 1.46h.01c6.58 0 11.94-5.35 11.94-11.92 0-3.19-1.24-6.18-3.5-8.43z"/></svg>',
	'call'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.15 3.4 2 2 0 0 1 3.12 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16l.92.92z"/></svg>',
];
?>
<div class="rm-live-chat" id="rm-live-chat">
	<div class="rm-live-chat-menu" id="rm-live-chat-menu" hidden>
		<?php foreach ( $channels as $channel ) : ?>
			<a
				href="<?php echo esc_url( $channel['url'] ); ?>"
				class="rm-live-chat-item <?php echo esc_attr( $channel['class'] ); ?>"
				<?php echo 'call' !== $channel['id'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
				aria-label="<?php echo esc_attr( $channel['label'] ); ?>"
			>
				<span class="rm-live-chat-icon">
					<?php echo $icons[ $channel['id'] ] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
				</span>
				<span class="rm-live-chat-label"><?php echo esc_html( $channel['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<button type="button" class="rm-live-chat-toggle" id="rm-live-chat-toggle" aria-expanded="false" aria-controls="rm-live-chat-menu" aria-label="<?php esc_attr_e( 'Open live chat', 'reseller-management' ); ?>">
		<span class="rm-live-chat-toggle-open" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
		</span>
		<span class="rm-live-chat-toggle-close" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</span>
	</button>
</div>
<script>
(function () {
	var root = document.getElementById('rm-live-chat');
	var toggle = document.getElementById('rm-live-chat-toggle');
	var menu = document.getElementById('rm-live-chat-menu');
	if (!root || !toggle || !menu) return;

	toggle.addEventListener('click', function () {
		var open = root.classList.toggle('is-open');
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		if (open) {
			menu.removeAttribute('hidden');
		} else {
			menu.setAttribute('hidden', '');
		}
	});

	document.addEventListener('click', function (e) {
		if (!root.contains(e.target) && root.classList.contains('is-open')) {
			root.classList.remove('is-open');
			toggle.setAttribute('aria-expanded', 'false');
			menu.setAttribute('hidden', '');
		}
	});
})();
</script>
