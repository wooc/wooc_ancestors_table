<?php
// Classes for module system
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

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Controller\BaseController;
use Fisharebest\Webtrees\Controller\IndividualController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use PDO;
use Wooc\WebtreesAddon\WoocAncestorsTableModule\Template\PdfTemplate;

class TreeChartClass extends WoocAncestorsTableModule {

	/* creates a table with all the sosa numbers over 23 generations
	 * the id in position 1 is the root person. The other positions are filled according to the following algorithm
	 * if an individual is at position $i then individual $i's father position ($i*2) and $i's mother ($i*2)+1
	 */
	protected function ancestryArray($ged_id, $rootid, $maxgen) {
		global $WT_TREE;
		$ancestors_table = array(array());
		if ($maxgen == 0) {
			$maxgen = 23;
		}
		$tree_size = pow(2, ($maxgen));
		$tree_array = array();
		$L2 = log10(2);
		$tree_array[0] = '';
		$tree_array[1] = $rootid;
		$sosa = 1;
		$gen = 1;

		$ancestors_table[] = array(
			'id' => $rootid,
			'ged_id' => $ged_id,
			'sosa' => $sosa,
			'gen' => $gen
		);

		for ($i = 1; $i < ($tree_size / 2); $i++) {
			$tree_array[($i * 2) ] = false; // -- father
			$tree_array[($i * 2) + 1] = false; // -- mother
			if (!empty($tree_array[$i]) && ($tree_array[$i] != '?')) {
				$person = Individual::getInstance($tree_array[$i], $WT_TREE);
				$family = $person->getPrimaryChildFamily();
				if ($family) {
					$gen = log10($i * 2) / $L2;
					$gen = (int)$gen + 1;
					if ($family->getHusband()) {
						$tree_array[$i * 2] = $family->getHusband()->getXref();
						$personid = $i * 2;
						$ancestors_table[] = array(
							'id' => $family->getHusband()->getXref(),
							'ged_id' => $ged_id,
							'sosa' => $i * 2,
							'gen' => $gen
						);
					} else {
						$tree_array[$i * 2] = '?';
						$ancestors_table[] = array(
							'id' => '?',
							'ged_id' => $ged_id,
							'sosa' => $i * 2,
							'gen' => $gen
						);
					}
					if ($family->getWife()) {
						$tree_array[$i * 2 + 1] = $family->getWife()->getXref();
						$personid = $i * 2 + 1;
						$ancestors_table[] = array(
							'id' => $family->getWife()->getXref(),
							'ged_id' => $ged_id,
							'sosa' => $i * 2 + 1,
							'gen' => $gen
						);
					} else {
						$tree_array[$i * 2 + 1] = '?';
						$ancestors_table[] = array(
							'id' => '?',
							'ged_id' => $ged_id,
							'sosa' => $i * 2 + 1,
							'gen' => $gen
						);
					}
				}
				else {
					$gen = log10($i * 2) / $L2;
					$gen = (int)$gen + 1;
					$tree_array[$i * 2] = '?';
					$ancestors_table[] = array(
						'id' => '?',
						'ged_id' => $ged_id,
						'sosa' => $i * 2,
						'gen' => $gen
					);
					$tree_array[$i * 2 + 1] = '?';
					$ancestors_table[] = array(
						'id' => '?',
						'ged_id' => $ged_id,
						'sosa' => $i * 2 + 1,
						'gen' => $gen
					);
				}
			} else {
				if ($i % 2 == 0){
					$gen = log10($i * 2) / $L2;
					$gen = (int)$gen + 1;
					$tree_array[$i * 2] = '?';
					$ancestors_table[] = array(
						'id' => '?',
						'ged_id' => $ged_id,
						'sosa' => $i * 2,
						'gen' => $gen
					);
					$tree_array[$i * 2 + 1] = '?';
					$ancestors_table[] = array(
						'id' => '?',
						'ged_id' => $ged_id,
						'sosa' => $i * 2 + 1,
						'gen' => $gen
					);
				} else {
					$gen = log10($i * 2) / $L2;
					$gen = (int)$gen + 1;
					$tree_array[$i * 2] = '?';
					$ancestors_table[] = array(
						'id' => '?',
						'ged_id' => $ged_id,
						'sosa' => $i * 2,
						'gen' => $gen
					);
					$tree_array[$i * 2 + 1] = '?';
					$ancestors_table[] = array(
						'id' => '?',
						'ged_id' => $ged_id,
						'sosa' => $i * 2 + 1,
						'gen' => $gen
					);
				}
			}
		}
		return $ancestors_table;
	}

	private function createTableOfGenerations($max, $pid) {
		global $controller, $WT_TREE;
		$ancestors_table = $this->ancestryArray($WT_TREE->getTreeId(), $pid, $max);
		$gen = 1;
		foreach ($ancestors_table as $generation) {
			if (!empty($generation)) {
				$gen = $generation['gen'];
				$asc[$gen][] = $generation;
			}
		}
		return $asc;
	}

	private function getTabHeader($pid) {
		global $WT_TREE;
		$html = '<p style="padding-left:15px;">';
		if (Auth::accessLevel($WT_TREE) < 2){
			$html .= '<div id="enter">
				<div id="dialog-confirm" title="' . I18N::translate('Generate a PDF') . '" style="display:none">
					<form id="#ancestors_table-generation">
						<select id="#ancestors_table-output">
							<option value="0">' . I18N::translate('&lt;select&gt;') . '</option>
							<option value="5">' . I18N::translate('5 generations - A4') . '</option>
							<option value="6">' . I18N::translate('5 generations - A3') . '</option>
							<option value="7">' . I18N::translate('6 generations - A3') . '</option>
							<option value="8">' . I18N::translate('7 generations - A3') . '</option>
						</select>
					</form>
				</div>';
			$html .= '<a id="ancestors_table-pdf" href="#" title="' . I18N::translate('Creating the PDF file') . '">';
			$html .= '<i class="icon-mime-application-pdf"></i></a>';
		} 
		$html .= '<span class="title-ancestors_table">' . I18N::translate('Ancestors table of %s', $this->getPerson($pid)->getFullName() . ' ' . $this->getPerson($pid)->getLifeSpan()) . '</span></div></p>';
		return $html;
	}

	protected function getTableAncestors($max, $pid) {
		global $controller, $WT_TREE;

		if ($max == '') {
			$maxgen = $this->NumberOfGenerations;
		} else {
			$maxgen = $max;
		}
		$largeurMini = 940; // px 
		//$root = Filter::get('pid');
		$root = $pid;
		$person = $this->getPerson($root);
		$ancestors_table = $this->createTableOfGenerations($maxgen, $root);
		$html = '';
		$gen = $maxgen;
		if ($max == 5) {
			$html = $this->getTabHeader($root);
		} else {
			$html .= '<p><span class="title-ancestors_table"><h1>' . I18N::translate('Ancestors table of %s', $this->getPerson($root)->getFullName() . ' ' . $this->getPerson($pid)->getLifeSpan()) . '</h1></span></p>';
		}

		$colspan = 1;
		$html .= '<table cellspacing="3" cellpadding="3">';
		for ($i = $gen; $i > 0; $i--) {
			$cell = pow(2, $i -1);
			$largeur = (int)($largeurMini) / $cell;
			$colspan = pow(2, $gen - $i);
			$html .= '<tr>';
			foreach ($ancestors_table[$i] as $generation) {
				$sosa = $generation['sosa'];
				if ($generation['id'] != '?') {
					$person = $this->getPerson($generation['id']);
					$color = $this->getBackgroundColor($person);
					$label = $this->getLabel($person);
				} else {
					$color = 'white';
					$label = '<p>' . I18N::translate('unknown') . '</p>';
				}
				if ($i <= 4) {
					$html .= '<td colspan="' . $colspan . '" style="max-width:' . $largeur . 'px;background-color:' . $color . '" class="sosa">' . $sosa . ':' . $label . '</td>';
				} else if ($i <= 6) {
					$html .= '<td colspan="' . $colspan . '" style="max-width:' . $largeur . 'px;font-size:50%;background-color:' . $color . '"class="sosa">' . $sosa . ':' . $label . '</td>';
				} else {
					$html .= '<td colspan="' . $colspan . '" style="max-width:' . $largeur . 'px;font-size:15%;background-color:' . $color . '"class="sosa">' . $sosa . ':' . $label . '</td>';
				}
			}
			$html .= '</tr>';
			$html .= '<tr><td class="separation" style="border:0 solid white;"></td></tr>';
		}
		$html .= '</table>';
		return $html;
	}

	public function printPDF($root, $gen, $format) {
		global $controller;

		$max  = $gen;
		$out = '<div id="contener"><body>' . $this->getTableAncestors($max, $root) . '</body></div>';
		$template = new PDFTemplate();
		$template->pageData($out);
		//return $template->pageBody($format);
		return '';
	}

	private function getLabel($person) {
		if ($person->canShow()){
			$html = '<p><a href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</a> ' . $person->getLifeSpan() . '</p>';
		} else {
			$html = I18N::translate('Private');
		}
		return $html;
	}

	private function getPerson($pid) {
		global $WT_TREE;
		return Individual::getInstance($pid, $WT_TREE);
	}

	private function getBackgroundColor($person) {
		switch ($person->getSex()) {
		case 'M':
			$color = 'lightblue';
			break;
		case 'F':
			$color = 'lightpink';
			break;
		case 'U':
			$color = 'lightgrey';
			break;
		default:
		    $color = 'white';
			break;
		}
		return $color;
	}
}
