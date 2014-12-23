<?php

class pkg_zooInstallerScript extends ZooInstallerScript {}

class ZooInstallerScript {

	public function install($parent) {}

	public function uninstall($parent) {}

	public function update($parent) {}

	public function preflight($type, $parent) {}

	public function postflight($type, $parent, $results) {
		if (class_exists('AppRequirements')) {
			$requirements = new AppRequirements();
			$requirements->checkRequirements();
			$requirements->displayResults();
		}

		if (class_exists('App')) {
			// get zoo instance
			$app = App::getInstance('zoo');

			$app->module->enable('mod_zooquickicon', 'icon');
			$app->plugin->enable('zooshortcode');
			$app->plugin->enable('zoosmartsearch');
			$app->plugin->enable('zoosearch');
			$app->plugin->enable('zooevent');
		}

		// updateservers url update workaround
		if ('update' == $type) {

			JFactory::getDBO()->setQuery(
				"DELETE a, b, c FROM `#__update_sites_extensions` a" .
				" LEFT JOIN `#__update_sites` b ON b.update_site_id = a.update_site_id" .
				" LEFT JOIN `#__updates` c ON c.update_site_id = a.update_site_id" .
				" WHERE a.extension_id = (SELECT `extension_id` FROM `#__extensions` WHERE `type` = 'package' AND `element` = 'pkg_zoo')"
			)->execute();
		}

		$extensions = array();
		foreach($results as $result) {
			$extensions[] = (object) array('name' => $result['name'] == 'com_zoo' ? 'ZOO extension' : $result['name'], 'status' => $result['result'], 'message' => $result['result'] ? ($type == 'update' ? 'Updated' : 'Installed').' successfully' : 'NOT Installed');
		}

		// display extension installation results
		self::displayResults($extensions, 'Extensions', 'Extension');
	}

	protected function displayResults($result, $name, $type) { ?>

		<h3><?php echo JText::_($name); ?></h3>
		<table class="adminlist table table-bordered table-striped" width="100%">
			<thead>
				<tr>
					<th class="title"><?php echo JText::_($type); ?></th>
					<th width="60%"><?php echo JText::_('Status'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					foreach ($result as $i => $ext) : ?>
					<tr class="row<?php echo $i++ % 2; ?>">
						<td class="key"><?php echo $ext->name; ?></td>
						<td>
							<?php $style = $ext->status ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
							<span style="<?php echo $style; ?>"><?php echo JText::_($ext->message); ?></span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

<?php }

}