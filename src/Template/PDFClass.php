<?php
// New DF Class to control headers and footers
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
namespace Wooc\WebtreesAddon\WoocAncestorsTableModule\Template;

use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\I18N;
use TCPDF;
use Wooc\WebtreesAddon\WoocAncestorsTableModule\TreeChartClass;
use Wooc\WebtreesAddon\WoocAncestorsTableModule;

// Extend the TCPDF class to create custom Header and Footer
class PDFClass extends TCPDF {
	//Page header
    public function Header() {
		global $WT_TREE;
        // Set font
        $this->SetFont('dejavusans', 'BI', 16);
        // Title
		$header = '<div style="width:100%;border-bottom:1px solid black;">' . $WT_TREE->getTitle() . '</div>';
		$this->writeHtml($header, false, false, false, false, 'C');
    }

    // Page footer
    public function Footer() {
		global $WT_TREE;
        // Position at 15 mm from bottom
        $this->SetY(-10);
        // Set font
        $this->SetFont('dejavusans', 'I', 8);
		$cal = I18N::defaultCalendar()->gedcomCalendarEscape();
		$date = new Date($cal);
		$cal_date = $date->minimumDate();
		// Fill in any missing bits with todays date
		$today = $cal_date->today();
		if ($cal_date->d === 0) {
			$cal_date->d = $today->d;
		}
		if ($cal_date->m === 0) {
			$cal_date->m = $today->m;
		}
		if ($cal_date->y === 0) {
			$cal_date->y = $today->y;
		}
		$footer = '<div style="width:100%;border-top:1px solid black;">' . $WT_TREE->getTitle() . ' ' . $date->display() . '</div>';
        // Page number
        $this->writeHtml($footer, false, false, false, false, 'L');
   }
}
