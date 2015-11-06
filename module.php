<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2015 Łukasz Wileński.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
namespace Wooc\WebtreesAddon\WoocAncestorsTableModule;

use Composer\Autoload\ClassLoader;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\BaseController;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions;
use Fisharebest\Webtrees\Functions\FunctionsPrint;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleMenuInterface;
use Fisharebest\Webtrees\Module\ModuleTabInterface;

use Wooc\WebtreesAddon\WoocAncestorsTableModule\Template\PdfTemplate;

class WoocAncestorsTableModule extends AbstractModule implements ModuleTabInterface {
	var $NumberOfGenerations;
	var $directory;
	var $action;

	public function __construct() {
		parent::__construct('wooc_ancestors_table');

		$this->NumberOfGenerations = 5;
		$this->directory = WT_MODULES_DIR . $this->getName();
		$this->action = Filter::get('mod_action');

		// register the namespaces
		$loader = new ClassLoader();
		$loader->addPsr4('Wooc\\WebtreesAddon\\WoocAncestorsTableModule\\', $this->directory . '/src');
		$loader->register();
	}

	/**
	 * Get the module class.
	 * Class functions are called with $this inside the source directory.
	 */
	private function module() {
		return new TreeChartClass;
	}

	// Extend Module
	public function getTitle() {
		return I18N::translate('Wooc Ancestors Table');
	}

	public function getTabTitle() {
		return /* I18N: Title used in the tab panel */ I18N::translate('Ancestors table');
	}

	// Extend Module
	public function getDescription() {
		return I18N::translate('A tab showing the table of individual’s ancestors.');
	}

	// Extend Module
	public function defaultTabOrder() {
		return 20;
	}

	/** {@inheritdoc} */
	public function hasTabContent() {
		return true;
	}

	/** {@inheritdoc} */
	public function isGrayedOut() {
		return '';
	}

	/** {@inheritdoc} */
	public function getPreLoadContent() {
		global $controller;

		$controller->addInlineJavascript('jQuery("a[href$=' . $this->getName() . ']").text("' . $this->getTabTitle() . '");
			if (document.createStyleSheet) {
				document.createStyleSheet("' . $this->css() . '"); //For Internet Explorer
			} else {
				var newSheet=document.createElement("link");
    		newSheet.setAttribute("rel","stylesheet");
    		newSheet.setAttribute("type","text/css");
   			newSheet.setAttribute("href","' . $this->css() . '");
		    document.getElementsByTagName("head")[0].appendChild(newSheet);
			}');
	}

	/** {@inheritdoc} */
	public function canLoadAjax() {
		return true;
	}

	/** {@inheritdoc} */
	public function getTabContent() {
		global $controller;
		$js = 'var ModuleDir	= "' . $this->directory . '";'.'
				var ModuleName	= "' . $this->getName() . '";
				var root 		= "' . Filter::get('pid') . '";';
		$root = Filter::get('pid');
		$max = '5';
		return '<div id="ancestors_table-script"><script>' . $js . '</script>
				<script src="' . $this->directory . '/js/tab.js"></script><div>
				<div id="contener">' . '<body>' . $this->module()->getTableAncestors($max, $root) . '</body></div>';
	}

	/** {@inheritdoc} */
	public function getTabAjaxContent(){
		return true;
	}

	/** {@inheritdoc} */
	public function modAction($mod_action) {
		switch ($mod_action) {
		case 'show_pdf' :
			$format = Filter::get('format');
			$template = new PdfTemplate();
			echo $template->pageBody($format);
			break;
		case 'pdf_direct' :
			$format = Filter::get('format');
			$root = Filter::get('root');
			$gen = Filter::get('gen');
			echo $this->module()->printPDF($root, $gen, $format);
			break;
		default:
			http_response_code(404);
			break;
		}
	}

	/**
	 * @return string
	 */
	public function css() {
		return WT_STATIC_URL . WT_MODULES_DIR . $this->getName() . '/css/style.css';
	}

	/**
	 * @return string
	 */
	public function js() {
		return WT_STATIC_URL . WT_MODULES_DIR . $this->getName() . '/js/';
	}
}

return new WoocAncestorsTableModule;