<?php declare(strict_types = 1);
 
namespace Modules\NpServerSettings;
 
use APP;
use CController as CAction;
 
/**
 * Please see \Zabbix\Core\CModule class for additional reference.
 */
class Module extends \Zabbix\Core\CModule {
 
	/**
	 * Initialize module.
	 */
	public function init(): void {
		// Initialize main menu (CMenu class instance).
		APP::Component()->get('menu.main')
			->findOrAdd(_('Administration'))
				->getSubmenu()
					->insertAfter(_('General'),((new \CMenuItem(_('NetPing Server Settings')))
						->setAction('npserversettings.view'))
					);
	}
 
	/**
	 * Event handler, triggered before executing the action.
	 *
	 * @param CAction $action  Action instance responsible for current request.
	 */
	public function onBeforeAction(CAction $action): void {
	}
 
	/**
	 * Event handler, triggered on application exit.
	 *
	 * @param CAction $action  Action instance responsible for current request.
	 */
	public function onTerminate(CAction $action): void {
	}
}
