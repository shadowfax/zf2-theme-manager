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

use Zend\Mvc\ApplicationInterface;

use Zend\EventManager\Event;

class ThemeEvent extends Event
{
	const EVENT_CHANGE_THEME = 'changeTheme';
	const EVENT_CHANGE_THEME_PATH = 'changeTheme.path';
	
	
	protected $theme;
	
	protected $basePath;
	
	protected $application;
	
	/**
     * Get application instance
     *
     * @return ApplicationInterface
     */
    public function getApplication()
    {
        return $this->application;
    }
    
	/**
     * Set application instance
     *
     * @param  ApplicationInterface $application
     * @return MvcEvent
     */
    public function setApplication(ApplicationInterface $application)
    {
        $this->setParam('application', $application);
        $this->application = $application;
        return $this;
    }

    
    
	public function getBasePath()
	{
		return $this->basePath;
	}
	
	public function setBasePath($path)
	{
		$this->basePath = $path;
		return $this;
	}
	
	public function getTheme()
	{
		return $this->theme;
	}
	
	public function setTheme( $theme )
	{
		$this->theme = $theme;
		return $this;
	}
}