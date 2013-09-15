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
namespace ThemeManager\ModuleManager\Feature;

interface DirectoryProviderInterface
{
	
	/**
	 * Return the path to the module.
	 *  
	 * @return string The Path to the module
	 */
	public function getDir();
} 