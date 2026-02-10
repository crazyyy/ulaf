<div class="render-hooks">
	<div class="filters">
		<p>Remove the following hooks from the list</p>
		<?php foreach ( $filter_groups as $key => $values ) : ?>
			<p><input type="checkbox" class="render-hooks-filter" value="<?php echo $key; ?>" /> <?php echo $key . ' (' . implode(', ', array_merge( $values['exact'], $values['regex'] ) ) , ')'; ?></p>
		<?php endforeach; ?>
        <p>
	        <label for="render-hooks-search">Search:</label> <input id="render-hooks-search" class="render-hooks-search" />
		</p>
		<p>
			<input type="button" class="expand-hooks" value="Expand All" />
			<input type="button" class="collapse-hooks" value="Collapse All" />
		</p>
	</div>
	<div class="hooks">
		<ul>
		<?php foreach ( $hooks as $hook_info ) : ?>
			<li class="hook <?php echo implode( $hook_info['groups'], ' ' ); ?>" data-hookname="<?php echo esc_attr( $hook_info['name'] ); ?>">
				<div class="hook-title"><?php echo $hook_info['name']; ?></div>
				<pre class="hook-args"><?php echo esc_html( print_r( $hook_info['args'], true ) ); ?></pre>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
</div>
