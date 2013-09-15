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

use Zend\EventManager\EventManagerAwareInterface;

interface ThemeManagerInterface extends EventManagerAwareInterface
{

	/**
     * Get the base path for themes.
     *
     * @return string
     */
	public function getBasePath();
	
	/**
     * Set the base path for themes.
     *
     * @param String $path The path to the themes folder
     * @return ThemeManagerInterface
     */
	public function setBasePath($path);
	
	/**
	 * Get the default theme.
	 * 
	 * return string
	 */
	public function getDefaultTheme();
	
	/**
	 * Set the default theme
	 * 
	 * @param string $theme
	 */
	public function setDefaultTheme( $theme );
	
}