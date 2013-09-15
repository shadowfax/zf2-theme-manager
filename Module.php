<?php
/**
 * ZF2-ThemeManager
 * 
 * Theme Manager for Zend Framework 2
 * 
 * @author    Juan Pedro Gonzalez
 * @copyright Copyright (c) 2013 Juan Pedro Gonzalez
 * @link      http://github.com/shadowfax/zf2-theme-manager
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 */
namespace ThemeManager;

use ThemeManager\Mvc\AssetRouteListener;

use ThemeManager\ThemeManager\ThemeManager;

use Zend\Mvc\MvcEvent;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;

use Zend\ModuleManager\Feature\InitProviderInterface;

use ThemeManager\ModuleManager\Listener\ThemeListener;

use Zend\ModuleManager\ModuleEvent;

use Zend\ModuleManager\ModuleManagerInterface;

class Module implements 
	InitProviderInterface, 
	AutoloaderProviderInterface,
	ServiceProviderInterface
{

	public function init(ModuleManagerInterface $moduleManager)
	{
		$events = $moduleManager->getEventManager();
		//$events->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'onMergeConfig'));
		//$events->attach(ModuleEvent::EVENT_LOAD_MODULE, new ThemeListener());
		$events->attachAggregate(new ThemeListener());
		
		$sharedEvents  = $events->getSharedManager();
		
		// VIEW MANAGER IS TRIGGERED AT 10000
		$sharedEvents->attach('Zend\Mvc\Application', MvcEvent::EVENT_BOOTSTRAP, array($this, 'onPostBootstrap'), 9999);
		//$sharedEvents->attachAggregate(new ThemeManager());
	}
	
	public function onPostBootstrap(MvcEvent $e)
	{
		$serviceManager = $e->getApplication()->getServiceManager();

		$themeManager = $serviceManager->get('ThemeManager');
		$config = $serviceManager->get('Config');
		if (isset($config['theme_manager'])) {
			$config = $config['theme_manager'];
			$themeManager->setOptions($config);
		}
		
		// We want assets!
		$assetListener = new AssetRouteListener();
	}
	
	public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
	public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'ThemeManager\ModuleManager\Listener\ThemeListener' => 'ThemeManager\ModuleManager\Listener\ThemeListener',
            ),
            'factories' => array(
            	'ThemeManager' => 'ThemeManager\Mvc\Service\ThemeManagerFactory'
            )
        );
    }
	
}
