<?php
/**
 * Licensed under The GPL-3.0 License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since	 2.0.0
 * @author	 Christopher Castro <chris@quickapps.es>
 * @link	 http://www.quickappscms.org
 * @license	 http://opensource.org/licenses/gpl-3.0.html GPL-3.0 License
 */
?>

<div class="panel panel-default">
	<div class="panel-heading"><?php echo __d('node', 'Search'); ?></div>
	<div class="panel-body">
		<?php echo $this->Form->create(null, ['type' => 'get', 'role' => 'form', 'onsubmit' => 'doSearch(); return false;']); ?>
			<div class="input-group">
				<?php echo $this->Form->input('criteria', ['label' => false, 'required']); ?>
				<span class="input-group-btn">
					<?php echo $this->Form->submit(__d('node', 'Go!')); ?>
				</span>
			</div>

			<p>
				<hr />
				<strong><?php echo __d('node', 'Advanced Search Options'); ?>:</strong>
				<ul>
					<li><code>created:</code> <?php echo __d('node', 'to specify when the contents were created. e.g., created:2013..2014'); ?></li>
					<li><code>type:</code> <?php echo __d('node', "to specify the type contents (content type's machine-name). e.g., type:article"); ?></li>
					<li><code>promote:</code> <?php echo __d('node', 'filter contents that were (or were not) promoted to front page. e.g., promote:true, promote:false'); ?></li>
					<li><code>author:</code> <?php echo __d('node', 'filter contents matching a given author name. e.g., author:admin'); ?></li>
					<li><code>language:</code> <?php echo __d('node', 'filter contents matching the given languages. e.g., language:es,en-us'); ?></li>
					<li><code>limit:</code> <?php echo __d('node', 'limits the number of items of search result. e.g., limit:10'); ?></li>
				</ul>
			</p>
		<?php echo $this->Form->end(); ?>
	</div>
</div>

<script type="text/javascript">
	function doSearch () {
		if ($('#criteria').val()) {
			$(location).attr('href',
				'<?php echo $this->Url->build('/find/'); ?>' + decodeURIComponent($('#criteria').val())
			);
		}
	}
</script>