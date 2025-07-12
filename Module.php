<?php
namespace Modules\NagiosView;
use Zabbix\Core\CModule;
use APP;
use CMenuItem;
class Module extends CModule 
{
    public function init(): void {
        APP::Component()->get('menu.main')
            ->findOrAdd(_('Monitoring'))
                ->getSubmenu()
                    ->insertAfter(_('Discovery'),((new CMenuItem(_('Nagios View')))
                        ->setAction('nagios.view'))
                    );
    }
}
