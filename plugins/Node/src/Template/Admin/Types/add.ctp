<?php echo $this->Form->create($type); ?>
	<fieldset>
		<legend><?php echo __d('node', 'Content Type Information'); ?></legend>
		<?php echo $this->Form->input('name', ['label' => __d('node', 'Name')]); ?>
		<em class="help-block"><?php echo __d('node', 'This text will be displayed as part of the list on the "Add New Content" page.'); ?></em>

		<?php echo $this->Form->input('slug', ['label' => __d('node', 'Machine name')]); ?>
		<em class="help-block">
			<?php echo __d('node', 'A unique name for this content type, accepted characters are lowercase letters, numbers and "-" symbol (a-z, 0-9, -). Leave empty to automatically generate one from content name. This value can not be changed after content type is created.'); ?>
		</em>


		<?php echo $this->Form->input('title_label', ['label' => __d('node', 'Title field label')]); ?>
		<em class="help-block"><?php echo __d('node', 'Label name for the "Title" field. e.g. "Product name", "Author name", etc.'); ?></em>

		<?php echo $this->Form->input('description', ['label' => __d('node', 'Description'), 'type' => 'textarea']); ?>
		<em class="help-block"><?php echo __d('node', 'Describe this content type. The text will be displayed on the Add new content page.'); ?></em>
	</fieldset>

	<hr />

	<fieldset>
		<legend><?php echo __d('node', 'Content Type Settings'); ?></legend>

		<?php echo $this->Form->input('defaults.status', ['type' => 'checkbox', 'label' => __d('node', 'Mark as published')]); ?>
		<?php echo $this->Form->input('defaults.promote', ['type' => 'checkbox', 'label' => __d('node', 'Promoted to front page')]); ?>
		<?php echo $this->Form->input('defaults.sticky', ['type' => 'checkbox', 'label' => __d('node', 'Sticky at top of lists')]); ?>

		<hr />

		<?php echo $this->Form->input('defaults.author_name', ['type' => 'checkbox', 'label' => __d('node', "Show author's name")]); ?>
		<em class="help-block"><?php echo __d('node', "Author's username will be displayed."); ?></em>

		<?php echo $this->Form->input('defaults.show_date', ['type' => 'checkbox', 'label' => __d('node', 'Show date')]); ?>
		<em class="help-block"><?php echo __d('node', 'Publish date will be displayed'); ?></em>

		<?php
			echo $this->Form->input('defaults.comment_status', [
				'type' => 'select',
				'label' => __d('node', 'Comments default status'),
				'options' => [
					0 => __d('node', 'Closed'),
					1 => __d('node', 'Open'),
					2 => __d('node', 'Read only'),
				],
			]);
		?>
		<em class="help-block"><?php echo __d('node', 'Default comment setting for new content.'); ?></em>

		<?php
			echo $this->Form->input('defaults.language', [
				'type' => 'select',
				'label' => __d('node', 'Language'),
				'options' => $languages,
				'empty' => __d('node', '-- ANY --'),
			]);
		?>
		<em class="help-block"><?php echo __d('node', 'Default language for new contents.'); ?></em>
	</fieldset>

	<?php echo $this->Form->submit(__d('node', 'Create and continue »')); ?>
<?php echo $this->Form->end(); ?>