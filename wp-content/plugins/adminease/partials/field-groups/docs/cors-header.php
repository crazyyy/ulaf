<?php
defined( 'ABSPATH' ) || exit;
?>
<h4><?php esc_html_e( 'How It Works', 'adminease' ); ?></h4>

<p>
	<?php
	printf(
	/* translators: %1$s and %2$s are example domain names */
		esc_html__( 'Websites often need to talk to each other. For example, a web app at %1$s might try to load data from %2$s.', 'adminease' ),
		'<strong>' . esc_html( 'website-a.com' ) . '</strong>',
		'<strong>' . esc_html( 'website-b.com' ) . '</strong>'
	);
	?>
</p>

<p>
	<?php esc_html_e( 'But browsers have a security feature that blocks this kind of request by default. It\'s called the same-origin policy — it only allows a website to request data from its own domain unless told otherwise.', 'adminease' ); ?>
</p>

<p>
	<?php
	echo wp_kses_post(
		sprintf(
		/* translators: %s is the Access-Control-Allow-Origin header name */
			__( 'To allow another website to access your data, your server must include a special instruction in its response headers: %s', 'adminease' ),
			'<strong>' . esc_html( 'Access-Control-Allow-Origin' ) . '</strong>'
		)
	);
	?>
</p>

<p>
	<?php esc_html_e( 'This header tells the browser: "It\'s okay to share this data with the other site."', 'adminease' ); ?>
</p>

<p>
	<?php
	printf(
	/* translators: %s is the acronym CORS */
		esc_html__( 'This setup is called Cross-Origin Resource Sharing, or %s for short. It\'s a way to safely let other websites use your data.', 'adminease' ),
		'<strong>' . esc_html( 'CORS' ) . '</strong>'
	);
	?>
</p>