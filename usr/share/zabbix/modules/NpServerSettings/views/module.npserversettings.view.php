<?php

$page['title'] = _('NetPing Server Settings');

require_once './include/page_header.php';
$this->includeJsFile('validator/nearley.js.php');
$this->includeJsFile('validator/ipv4.js.php');
$this->includeJsFile('validator/ip_single.js.php');
$this->includeJsFile('helper.js.php');
$this->includeJsFile('styles.js.php');

exec("/etc/dksl-90-vars lan", $nmcli_out, $resCodeOnGet);
$connectionName = $nmcli_out[0];
//$connectionName = "enp0s3"; // you must change this value according to your server setting

$scriptFolder = '/usr/share/zabbix/modules/NpServerSettings/bash';

$nmcli_out = [];
$net = [];
$dhcp_mode = "auto";

$ipv4 = "";
$gateway = "";
$dns1 = "";
$dns2 = "";
$messages = [];
$resCodeOnGet = 0;
$resCodeOnSet = 0;

$netplanFile = '/etc/netplan/00-installer-config.yaml';

if (file_exists($netplanFile)) {
         $yamlContent = file_get_contents($netplanFile);
         $config = yaml_parse($yamlContent);
} else {
        $errorMsgs[] = "Файл конфигурации Netplan не найден: $netplanFile";
}

if (empty($_POST)) {
        if (isset($config['network']['ethernets'][$connectionName])) {
                $settings = $config['network']['ethernets'][$connectionName];

                $dhcp_mode = isset($settings['dhcp4']) && $settings['dhcp4'] ? "auto" : "manual";

                $ipv4 = $settings['addresses'][0] ?? "";
                $gateway = $settings['gateway4'] ?? "";

                if (isset($settings['nameservers']['addresses'])) {
                        $dns1 = $settings['nameservers']['addresses'][0] ?? "";
                        $dns2 = $settings['nameservers']['addresses'][1] ?? "";
                }
        } else {
                $errorMsgs[] = "Интерфейс $connectionName не найден в конфиге Netplan!";
        }
}
else {

    if ($_POST['dhcp_mode'] === "auto") {
        $config['network']['ethernets'][$connectionName]['dhcp4'] = true;
    	unset($config['network']['ethernets'][$connectionName]['addresses']);
    	unset($config['network']['ethernets'][$connectionName]['gateway4']);
    	// Также удаляем nameservers, если они есть
    	unset($config['network']['ethernets'][$connectionName]['nameservers']);
    } else {
        $config['network']['ethernets'][$connectionName]['dhcp4'] = false;
        $config['network']['ethernets'][$connectionName]['addresses'] = [$_POST['ipv4']];
        $config['network']['ethernets'][$connectionName]['gateway4'] = $_POST['gateway'];

        if (!empty($_POST['dns1']) || !empty($_POST['dns2'])) {
            $dnsServers = array_filter([$_POST['dns1'], $_POST['dns2']]);
            $config['network']['ethernets'][$connectionName]['nameservers']['addresses'] = $dnsServers;
        }
    }

    $yamlContent = yaml_emit($config);
    file_put_contents($netplanFile, $yamlContent);

    exec("sudo netplan apply", $output, $resCodeOnSet);
    $messages = array_merge($messages, $output);

}

?>


<header class="header-title"><nav class="sidebar-nav-toggle" role="navigation" aria-label="Sidebar control"><button type="button" id="sidebar-button-toggle" class="button-toggle" title="Show sidebar">Show sidebar</button></nav><div><h1 id="page-title-general">NetPing Server Settings</h1></div></header>


<?php if ($resCodeOnGet > 0) { ?>

		<output class="msg-bad" role="contentinfo" aria-label="Error message">
			<span>
				<h2>Error when reading the settings!</h2>
				<?php
					foreach($messages as $key=>$val) {
						echo "<p>#" . intval($key+1) . ": " . $val . "</p>";
					}
				?>
			</span>
			<button type="button" class="overlay-close-btn" onclick="jQuery(this).closest('.msg-bad').remove();" title="Close"></button>
		</output>

<?php } elseif ($resCodeOnSet > 0) { ?>

		<output class="msg-bad" role="contentinfo" aria-label="Error message">
			<span>
				<h2>Error when apply the settings!</h2>
				<?php
					foreach($messages as $key=>$val) {
						echo "<p>#" . intval($key+1) . ": " . $val . "</p>";
					}
				?>
			</span>
			<button type="button" class="overlay-close-btn" onclick="jQuery(this).closest('.msg-bad').remove();" title="Close"></button>
		</output>

<?php } elseif (sizeof($_POST) > 0) { ?>

		<output class="msg-good" role="contentinfo" aria-label="Success message">
			<span>
				<h2>Server settings were applied successfully!</h2>
				<dl class="attrs-line">
				<?php
					foreach($nmcli_out as $raw) {
						if(strpos($raw, "ipv4.") !== false) {
							list($key, $val) = preg_split("/:\s+/", $raw);
							echo "<dt>" . $key . " : </dt><dd>" . $val . "</dd>";
						}
					}
				?>
				</dl>
			</span>
			<button type="button" class="overlay-close-btn" onclick="jQuery(this).closest('.msg-good	').remove();" title="Close"></button>
		</output>';

		<?php foreach($messages as $msg) { ?>
			<output class="msg-good" role="contentinfo" aria-label="Success message">
				<span><?php echo $msg; ?></span>
				<button type="button" class="overlay-close-btn" onclick="jQuery(this).closest('.msg-good	').remove();" title="Close"></button>
			</output>';
		<?php } ?>

<?php } ?>




<br/>
<form method="post">
	<div id="tabs" class="table-forms-container ui-tabs ui-widget ui-widget-content ui-corner-all" style="visibility: visible;">
		<div id="maintenanceTab" aria-labelledby="tab_maintenanceTab" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="true" aria-hidden="false">
			<ul class="table-forms" id="maintenanceFormList">
				<li>
					<div class="table-forms-td-left">
						<label class="form-label-ipv4-type" for="time">IP v4 адрес </label>
					</div>
					<div class="table-forms-td-right">
						<select id="dhcp_mode" name="dhcp_mode" onchange="enableStatic(this);">
							<option value="auto" <?php if($dhcp_mode == 'auto') echo ' selected'; ?>>DHCP</option>
							<option value="manual" <?php if($dhcp_mode == 'manual') echo ' selected'; ?>>Статический</option>
						</select>
					</div>
				</li>
				<li>
					<div class="table-forms-td-left">
						<label for="IP v4 адрес">Статический </label>
					</div>
					<div class="table-forms-td-right">
						<input type="text" id="ipv4" name="ipv4" disabled="true" value="<?php echo $ipv4; ?>" maxlength="255" style="width: 270px;">
						<?php $exampleIpv4 = ((isset($ipv4) && $ipv4) != "") ? $ipv4 : "192.168.0.2/24"; ?>
						<div class="error" style="display: none; color: red;">Укажите корректный IP / маску, например: <a href="javascript: $('#ipv4').val('<?php echo $exampleIpv4; ?>'); validateIpV4('<?php echo $exampleIpv4; ?>');"><?php echo $exampleIpv4; ?></a></div>
					</div>
				</li>
				<li>
					<div class="table-forms-td-left">
						<label for="Адрес шлюза">Адрес шлюза </label>
					</div>
					<div class="table-forms-td-right">
						<input type="text" id="gateway" name="gateway" value="<?php echo $gateway; ?>" maxlength="255" style="width: 270px;">
						<?php $exampleGateway = (isset($gateway) && $gateway != "") ? $gateway : "192.168.0.1"; ?>
						<div class="error" style="display: none; color: red;">Укажите корректный IP, например: <a href="javascript: $('#gateway').val('<?php echo $exampleGateway; ?>'); validateIpGateway('<?php echo $exampleGateway; ?>');"><?php echo $exampleGateway; ?></a></div>
					</div>
				</li>
				<li>
					<div class="table-forms-td-left">
						<label for="Основной DNS сервер">Основной DNS сервер </label>
					</div>
					<div class="table-forms-td-right">
						<input type="text" id="dns1" name="dns1" value="<?php echo $dns1; ?>" maxlength="255" style="width: 270px;">
						<?php $exampleDns1 = (isset($dns1) && $dns1 != "") ? $dns1 : "8.8.8.8"; ?>
						<div class="error" style="display: none; color: red;">Укажите корректный DNS-адрес, например: <a href="javascript: $('#dns1').val('<?php echo $exampleDns1; ?>'); validateIpDns1('<?php echo $exampleDns1; ?>');"><?php echo $exampleDns1; ?></a></div>
					</div>
				</li>
				<li>
					<div class="table-forms-td-left">
						<label for="Дополнительный DNS сервер">Дополнительный DNS сервер </label>
					</div>
					<div class="table-forms-td-right">
						<input type="text" id="dns2" name="dns2" value="<?php echo $dns2; ?>" maxlength="255" style="width: 270px;">
						<?php $exampleDns2 = (isset($dns2) && $dns2 != "") ? $dns2 : "77.88.8.88"; ?>
						<div class="error" style="display: none; color: red;">Укажите корректные DNS-адреса, например: <a href="javascript: $('#dns2').val('<?php echo $exampleDns2; ?>'); validateIpDns2('<?php echo $exampleDns2; ?>');"><?php echo $exampleDns2; ?></a></div>
					</div>
				</li>
			</ul>
			<ul class="table-forms">
				<li>
					<div class="table-forms-td-left"></div>
					<div class="table-forms-td-right tfoot-buttons">
						<button type="submit" id="save_and_apply" value="SaveAndApply"/>Save and Apply</button>
					</div>
				</li>
			</ul>
		</div>
	</div>
</form>
<?php

require_once './include/page_footer.php';

?>
