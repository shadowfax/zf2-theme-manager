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
namespace ThemeManager\Mvc\Service;


use ThemeManager\ThemeManager\ThemeManager;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class ThemeManagerFactory implements FactoryInterface
{
	
	/**
     * Create and return a theme manager based on detected environment
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ConsoleViewManager|HttpViewManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
    	$config = $serviceLocator->get('Config');
    	$config = isset($config['theme_manager']) && (is_array($config['theme_manager']) || $config['theme_manager'] instanceof ArrayAccess)
	              ? $config['theme_manager']
	              : array();
	                        
        $themeManager = new ThemeManager($serviceLocator, $config);
        return $themeManager;
    }
    
}