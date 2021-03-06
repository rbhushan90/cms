<div class="media">
	<span class="pull-left"><?php echo $this->Html->image($user->avatar(['s' => 150]), ['class' => 'media-object']); ?></span>
	<div class="media-body">
		<h2 class="media-heading"><?php echo __d('user', 'User Profile'); ?></h2>
		<p><strong><?php echo __d('user', 'Name'); ?>:</strong> <?php echo $user->name; ?></p>
		<p><strong><?php echo __d('user', 'Username'); ?>:</strong> @<?php echo $user->username; ?></p>
		<p><strong><?php echo __d('user', 'Registered on'); ?>:</strong> <?php echo $user->created->format(__d('user', 'Y-m-d H:i:s')); ?></p>
		<p><strong><?php echo __d('user', 'Last Connection'); ?>:</strong> <?php echo $user->last_login->format(__d('user', 'Y-m-d H:i:s')); ?></p>

		<?php if ($user->public_email): ?>
			<p><strong><?php echo __d('user', 'Email'); ?>:</strong> <?php echo $user->email; ?></p>
		<?php endif; ?>
	</div>
</div>
