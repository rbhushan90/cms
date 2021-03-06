<?php echo $this->Flash->render('register'); ?>

<?php if (!$registered): ?>
	<?php echo $this->Form->create($user, ['id' => 'user-form']); ?>
		<fieldset>
			<legend><?php echo __d('user', 'Creating New Account'); ?></legend>
			<?php echo $this->Form->input('name', ['label' => __d('user', 'Real name')]); ?>
			<em class="help-block"><?php echo __d('user', 'Your real name, e.g. John Locke'); ?></em>

			<?php echo $this->Form->input('username', ['label' => __d('user', 'User Name')]); ?>
			<em class="help-block"><?php echo __d('user', 'Alphanumeric characters only and "_" symbol. e.g. My_user_Email'); ?></em>

			<?php echo $this->Form->input('email', ['label' => __d('user', 'e-Mail')]); ?>
			<em class="help-block"><?php echo __d('user', 'Must be unique.'); ?></em>

			<?php echo $this->Form->input('password', ['type' => 'password', 'label' => __d('user', 'Password')]); ?>
			<em class="help-block"><?php echo __d('user', 'At least six characters long.'); ?></em>
			<?php echo $this->Form->input('password2', ['type' => 'password', 'label' => __d('user', 'Confirm Password')]); ?>

			<?php echo $this->Form->submit(__d('user', 'Register')); ?>
		</fieldset>
	<?php echo $this->Form->end(); ?>
<?php endif; ?>