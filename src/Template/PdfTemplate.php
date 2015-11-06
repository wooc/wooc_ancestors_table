<?php
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

use Fisharebest\Webtrees\File;
use Fisharebest\Webtrees\Filter;
use TCPDF;
use Wooc\WebtreesAddon\WoocAncestorsTableModule\TreeChartClass;
use Wooc\WebtreesAddon\WoocAncestorsTableModule\Template\PDFClass;

class PdfTemplate extends TreeChartClass {
	var $pdf;
	var $tmpFile;
	var $filename;

	public function pageBody($format) {
		global $WT_TREE;
		$this->tmpFile = 'wat_tmp.txt';
		$this->filename = WT_DATA_DIR . $this->tmpFile;
		if (is_dir(WT_DATA_DIR) && is_readable($this->filename)) {
			$html = '<head>
				<link rel="stylesheet" type="text/css" href="' . $this->directory . '/css/style.css">
				<link rel="stylesheet" type="text/css" href="' . $this->directory . '/css/style-pdf.css">
				</head>';
			$html .= file_get_contents($this->filename);

			$PDF_PAGE_FORMAT = $format;
			$PDF_UNIT = 'mm';
			$PDF_PAGE_ORIENTATION = 'L';

			$this->pdf = new PDFClass($PDF_PAGE_ORIENTATION, $PDF_UNIT, $PDF_PAGE_FORMAT, true, 'UTF-8', false);
			// set font
			$this->pdf->SetFont('dejavusans', 'BI', 10);
			// set document information
			$this->pdf->SetCreator(PDF_CREATOR);
			$this->pdf->SetAuthor('Łukasz Wileński');
			$this->pdf->SetTitle($this->getTitle());
			$this->pdf->SetSubject('Webtrees');
			$this->pdf->SetKeywords('PDF, Webtrees, tree');
			// set header and footer fonts
			$this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			// set default monospaced font
			$this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			// set margins
			$this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$this->pdf->SetTopMargin('15');
			// set auto page breaks
			$this->pdf->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);
			// set image scale factor
			$this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			// add a page
			$this->pdf->AddPage();
			$this->pdf->writeHTML($html, true, false, true, false, '');
			$this->pdf->Output(Filter::get('title') . '.pdf', 'I');
			// remove the temporary files
			File::delete($this->tmpFile);
			foreach (glob(WT_DATA_DIR . 'wat*.*') as $image) {
				File::delete($image);
			}
		}
	}

	public function pageData($page) {
		$this->tmpFile = 'wat_tmp.txt';
		$this->filename = WT_DATA_DIR . $this->tmpFile;
		//$filename = WT_DATA_DIR . '/wat_tmp.txt';
		//$content = Filter::post('pdfContent');
		$content = $page;
		// make our datafile if it does not exist.
		if (!file_exists($this->filename)) {
			$handle = fopen($this->filename, 'w');
			fclose($handle);
			chmod($this->filename, 0644);
		}
		// Let's make sure the file exists and is writable first.
		if (is_writable($this->filename)) {
			if (!$handle = @fopen($this->filename, 'w')) {
				exit;
			}
			// Write the pdfContent to our txt file.
			if (fwrite($handle, $content) === false) {
				exit;
			}
			fclose($handle);
		}
	}
}