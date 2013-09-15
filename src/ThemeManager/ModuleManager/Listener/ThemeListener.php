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
namespace ThemeManager\ModuleManager\Listener;

use Zend\ServiceManager\ServiceManagerAwareInterface;

use Zend\Stdlib\ArrayUtils;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

use ThemeManager\ModuleManager\Feature\DirectoryProviderInterface;

use Zend\EventManager\EventManagerInterface;

use Zend\ModuleManager\ModuleEvent;

use Zend\EventManager\ListenerAggregateInterface;

class ThemeListener implements ListenerAggregateInterface
{
	/**
     * @var array
     */
	protected $paths = array();
	
	
	protected $services;
	
	/**
     * @var array
     */
    protected $callbacks = array();
    
	/**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
    	$this->callbacks[] = $events->attach(
            ModuleEvent::EVENT_LOAD_MODULE,
            array($this, 'onLoadModule')
        );
      
    }

    /**
     * Detach all previously attached listeners
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
    	foreach ($this->callbacks as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->callbacks[$index]);
            }
        }
    }
    
    
    public function onLoadModule(ModuleEvent $e)
    {
    	$module = $e->getModule();
    	
    	if (!$module instanceof DirectoryProviderInterface
            && !method_exists($module, 'getDir')
        ) {
            return;
        }
        
        $moduleDirectory = $module->getDir();
        if (!is_dir($moduleDirectory)) {
        	return;
        }
        
        // Store the module path
        $moduleName = $e->getModuleName();
        $this->paths[$moduleName] = array(
        	'path'                        => $moduleDirectory, 
        	'template_path_stack'         => null,
        	'default_template_path_stack' => null
        );
        
        // Check for especific module-dependant themes
        if ($module instanceof ConfigProviderInterface || is_callable(array($this, 'getConfig'))) {
        	$config = $module->getConfig();
        	
	        if ($config instanceof Traversable) {
	            $config = ArrayUtils::iteratorToArray($config);
	        }
	        
	        if (!is_array($config)) {
	            throw new Exception\InvalidArgumentException(
	                sprintf('Config being merged must be an array, '
	                . 'implement the Traversable interface, or be an '
	                . 'instance of Zend\Config\Config. %s given.', gettype($config))
	            );
	        }
	        
        	if (isset($config['theme_manager'])) {
        		if (is_array($config['theme_manager'])) {
        			if (isset($config['theme_manager']['template_path_stack'])) {
        				$this->paths[$moduleName]['template_path_stack'] = $config['theme_manager']['template_path_stack'];
        			}
        		}
        	}
        	
        	if (isset($config['view_manager'])) {
        		if (is_array($config['view_manager'])) {
        			if (isset($config['view_manager']['template_path_stack'])) {
        				$this->paths[$moduleName]['default_template_path_stack'] = $config['view_manager']['template_path_stack'];
        			}
        		}
        	}
        }
    }
    
}