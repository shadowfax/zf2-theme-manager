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
namespace ThemeManager\ThemeManager;

use Zend\Stdlib\SplStack;

use ThemeManager\Mvc\View\Http\InjectTemplateListener;

use Zend\Mvc\MvcEvent;

use Zend\EventManager\EventManagerInterface;

use Zend\EventManager\EventManager;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ThemeManager implements 
	ThemeManagerInterface, 
	ServiceManagerAwareInterface
{
	
	/**
	 * ServiceManager instance 
	 * 
	 * @var ServiceManager
	 */
	protected $services;
	
	/**
     * The used EventManager if any
     *
     * @var null|EventManagerInterface
     */
    protected $events = null;
    
	/**
	 * Base path to the centralized theme repository
	 * 
	 * If only module-specific themes exist this should be null.
	 * 
	 * @var string|null
	 */
	protected $basePath;
	
	/**
	 * The default theme.
	 * 
	 * @var string
	 */
	protected $defaultTheme = 'default';
	
	/**
	 * The current theme
	 * 
	 * @var string
	 */
	protected $theme;
	
	protected $themeConfiguration;
	
	//protected $config = array();
	
	public function __construct(ServiceManager $serviceManager, $config)
	{
		$this->services = $serviceManager;
		
		$this->setOptions($config);
	}
	
	/**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     * @return ThemeManagerInterface
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
    	$this->services = $serviceManager;
    	return $this;
    }
    
    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
    	if (!$this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }
    
	/**
     * Set the event manager instance used by this module manager.
     *
     * @param  EventManagerInterface $events
     * @return ModuleManager
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this),
            'theme_manager',
        ));
        $this->events = $events;
        $this->attachDefaultListeners();
        return $this;
    }
    
    
    public function setOptions($options)
    {
    	if (isset($options['base_path'])) {
			$this->setBasePath($options['base_path']);
			unset($options['base_path']);		
		}
		
		if (isset($options['default_theme'])) {
			$this->setDefaultTheme($options['default_theme']);
			unset($options['default_theme']);		
		} else {
			$this->setDefaultTheme('default');
		}
		
		if (isset($options['theme'])) {
			$this->setTheme($options['theme']);
			unset($options['theme']);		
		} else {
			$this->setTheme($this->defaultTheme);
		}
		
		//// Save the rest of the configuration
		//$this->config = array_filter($options);
    }
    
    /**
     * Get the base path for themes.
     *
     * @return string
     */
	public function getBasePath()
	{
		return $this->basePath;
	}
	
	/**
     * Set the base path for themes.
     *
     * @param String $path The path to the themes folder
     * @return ThemeManagerInterface
     */
	public function setBasePath($path)
	{
		// The path could be null if only module-specific themes are in use
		if (!is_dir($path) && !is_null($path)) {
			throw new Exception\DirectoryNotFoundException($path);
		}
		
		$realpath = realpath($path);
		if (is_null($realpath)) {
			throw new Exception\DirectoryNotFoundException($path);
		}
		$path = $realpath;
		
		// Set the path
		if ($this->basePath !== $path) {
			$this->basePath = $path;
			
			// Create the event
			$event = new ThemeEvent();
			$event->setTheme($this->theme);
			$event->setBasePath($path);
			$event->setApplication($this->services->get('Application'));
			
			// Trigger the event
			$this->getEventManager()->trigger(ThemeEvent::EVENT_CHANGE_THEME_PATH, $this, $event);
		}
		return $this;
	}
	
	/**
	 * Get the default theme.
	 * 
	 * return string
	 */
	public function getDefaultTheme()
	{
		return $this->defaultTheme;
	}
	
	/**
	 * Set the default theme
	 * 
	 * @param string $theme
	 */
	public function setDefaultTheme( $theme )
	{
		if (!is_string($theme)) {
			throw new Exception\InvalidArgumentException(sprintf(
                '%s: expected a string; received "%s"', 
                __METHOD__,
                (is_object($listener) ? get_class($listener) : gettype($listener))
            ));
		} elseif (empty($theme)) {
			throw new Exception\InvalidArgumentException(sprintf(
                '%s: expected a non-empty string', 
                __METHOD__
            ));
		}
		
		if ($this->defaultTheme !== $theme) {
			// Are we using the default theme?
			if (!empty($this->theme)) {
				if ($this->theme === $this->defaultTheme) {
					// Change to the new default theme
					$this->setTheme($theme);		
				}
			}
			
			// Set the default theme
			$this->defaultTheme = $theme;
		}

		return $this;
	}
	
	/**
	 * Get the current theme
	 * 
	 * @return string
	 */
	public function getTheme()
	{
		return $this->theme;
	}

	/**
	 * Set the current theme
	 * 
	 * @param string $theme The current theme
	 */
	public function setTheme( $theme )
	{
		if (!is_string($theme)) {
			throw new Exception\InvalidArgumentException(sprintf(
                '%s: expected a string; received "%s"', 
                __METHOD__,
                (is_object($listener) ? get_class($listener) : gettype($listener))
            ));
		} elseif (empty($theme)) {
			throw new Exception\InvalidArgumentException(sprintf(
                '%s: expected a non-empty string', 
                __METHOD__
            ));
		}
		
		if ($this->theme !== $theme) {
			$this->theme = $theme;
			
			// Create the event
			$event = new ThemeEvent();
			$event->setTheme($theme);
			$event->setBasePath($this->basePath);
			$event->setApplication($this->services->get('Application'));
			
			// Trigger the event
			$this->getEventManager()->trigger(ThemeEvent::EVENT_CHANGE_THEME, $this, $event);
		}
		return $this;
	}
	
	protected function attachDefaultListeners()
	{
		$events = $this->getEventManager();
        $events->attach(ThemeEvent::EVENT_CHANGE_THEME, array($this, 'onThemeChange'), 10000);
        $events->attach(ThemeEvent::EVENT_CHANGE_THEME_PATH, array($this, 'onThemeChange'), 10000);
	}
	
	public function onThemeChange(ThemeEvent $event)
	{
		// Reset theme configuration
		$this->themeConfiguration = array();
		
		$theme = $event->getTheme();
		if (empty($theme)) return;
		
		$serviceManager = $event->getApplication()->getServiceManager();
		
		$templatePathStack = array();
		
		// Add the ViewManager template paths as a global fallback
		$config = $serviceManager->get('Config');
        $config   = isset($config['view_manager']) && (is_array($config['view_manager']) || $config['view_manager'] instanceof ArrayAccess)
                     ? $config['view_manager']
                     : array();
        $config = isset($config['template_path_stack']) && (is_array($config['template_path_stack']) || $config['template_path_stack'] instanceof ArrayAccess)
                     ? $config['template_path_stack']
                     : array();
        
        foreach ($config as $key => $value) {
        	$templatePathStack[] = $value;
        }
        
        // From here on themes are loaded
        $defaultTheme = $this->getDefaultTheme();
        
		// Add the ThemeManager template paths as a fallback
		$config = $serviceManager->get('Config');
        $config   = isset($config['theme_manager']) && (is_array($config['theme_manager']) || $config['theme_manager'] instanceof ArrayAccess)
                     ? $config['theme_manager']
                     : array();
        $config = isset($config['template_path_stack']) && (is_array($config['template_path_stack']) || $config['template_path_stack'] instanceof ArrayAccess)
                     ? $config['template_path_stack']
                     : array();
        
        foreach ($config as $key => $value) {
        	// Add the default theme as fallback
			if ($defaultTheme !== $theme) {
				$themePath = $value . DIRECTORY_SEPARATOR . $defaultTheme;
				if (is_dir($themePath)) {
					$templatePathStack[] = $themePath;
				}
			}
			
			$themePath = $value . DIRECTORY_SEPARATOR . $theme;
			if (is_dir($themePath)) {
	        	$templatePathStack[] = $themePath;
	        	
	        	// check for a config file
	        	if (file_exists($themePath . DIRECTORY_SEPARATOR . 'theme.config.php')) {
	        		$themeConfig = include($themePath . DIRECTORY_SEPARATOR . 'theme.config.php');
	        		$this->themeConfiguration = array_merge_recursive($this->themeConfiguration, $themeConfig);
	        	}
			}
        	
        }
        
		// Do we have a central repository?
		$basePath = $this->getBasePath();
		if (!empty($basePath)) {
			// Add the default theme as fallback
			if ($defaultTheme !== $theme) {
				// Add default theme as Fallback
				$themePath = $basePath . DIRECTORY_SEPARATOR . $defaultTheme;
				if (is_dir($themePath . 'module')) {
					$templatePathStack[] = $themePath . DIRECTORY_SEPARATOR . 'module';
				}	
			}
			
			// Add the current theme
			$themePath = $basePath . DIRECTORY_SEPARATOR . $theme;
			if (is_dir($themePath)) {
				// Maybe no templates are present and we are using it to hold theme configuration
				if (is_dir($themePath . DIRECTORY_SEPARATOR . 'module')) {
					$templatePathStack[] = $themePath . DIRECTORY_SEPARATOR . 'module';
				}
				
				// check for a config file
		        if (file_exists($themePath . DIRECTORY_SEPARATOR . 'theme.config.php')) {
		        	$themeConfig = include($themePath . DIRECTORY_SEPARATOR . 'theme.config.php');
		        	$this->themeConfiguration = array_merge_recursive($themeConfig, $this->themeConfiguration);
		        }
			}
			
			unset($basePath);
		}
        
		// Set the template path stack
		$templatePathResolver = $serviceManager->get('ViewTemplatePathStack');   
		$templatePathResolver->setPaths($templatePathStack);

		// We must now load the asset paths for the theme!!
		$assetManager   = $serviceManager->get('AssetManager');
		$assetPathStack = $serviceManager->get('AssetPathStack');
		
		$assetPathStack->clearPaths();
		if (isset($this->themeConfiguration['assets'])) {
			if (isset($this->themeConfiguration['assets']['paths'])) {
				$assetPathStack->addPaths($this->themeConfiguration['assets']['paths']);
			}
		}
		
		
	}
	
	
}