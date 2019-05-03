<?php

namespace AfipWS\Invoice;

/**********************************
* 						          					*
* PDF INVOICE GENERATOR           *

***********************************/

use fpdf;
//require_once('fpdf.php');

define('decimal_symbol',','); // Decimal Symbol 152,56
define('thousand_symbol','.'); // Thousand Symbol 1.255,22

class Invoice extends FPDF
{
	var $ang = 0;

	function Rotate($ang, $x = -1, $y = -1)
	{
		if ($x == -1)
			$x = $this->x;
		if ($y == -1)
			$y = $this->y;
		if ($this->ang != 0)
			$this->_out('Q');
		$this->ang = $ang;
		if ($ang != 0) {
			$ang *= M_PI / 180;
			$c = cos($ang);
			$s = sin($ang);
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
		}
	}

	function RotatedText($x, $y, $txt, $ang)
	{
		$this->SetFont('UniFont', '', 6);
		$this->SetTextColor(103, 103, 103);
		$this->Rotate($ang, $x, $y);
		$this->Text($x, $y, $txt);
		$this->Rotate(0);
	}

	function Head($data)
	{

		// LOGO
		//$this->Image(dirname(__FILE__)."/logo/".str_replace('-', '', $data['company_data']['ident'].'.png'), 10, 8);
		$this->Image('storage/tiendaLogos/1519050388.png', 10, 8);

		$this->SetFillColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);
		$this->SetDrawColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);

		//LETTER
		$invoiceLetter = $this->getInvoiceLetter($data['tipo_comp']);
		$this->SetY(8);
		$this->SetX(100);
		$this->SetFont('UniFont', '', 36);
		$this->SetTextColor(255, 255, 255);
		$this->Cell(10, 15, $invoiceLetter, 1, 1, 'C', true);

		$this->SetY(20);
		$this->SetX(103);
		$this->SetFont('UniFont', '', 6);
		$this->SetTextColor(255, 255, 255);
		$this->Cell(5, 3, 'Cód.'. sprintf('%02d', $data['tipo_comp']), 1, 1, 'C', true);


		//NAME
		$this->SetX(9);
		$this->SetY(32);
		$this->SetFont('UniFont', '', 16);
		$this->SetTextColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);
		$this->Cell(80, 2, $data['company_data']['name'], 0, 1, 'L');

		$this->SetX(9);
		$this->SetY(38);
		$this->SetFont('UniFont', '', 7);


		//ADDRESS
		$this->SetX(9);
		$this->Cell(80, 2, $data['company_data']['address'] . ' ' . $data['company_data']['city'] . ', CP:' .$data['company_data']['postal_code'], 0, 1, 'L');

		// PHONE
		$this->SetX(9);
		$this->Cell(80, 2, $data['text']['phone'] . ' ' . $data['company_data']['phone'], 0, 1, 'L');

		// EMAIL
		$this->SetX(9);
		$this->Cell(80, 2, $data['text']['email'] . ' ' . $data['company_data']['email'], 0, 1, 'L');

		// WEB
		$this->SetX(9);
		$this->Cell(80, 2, $data['company_data']['web'], 0, 1, 'L');
		$this->Ln();


		// CUSTOMER DATA /////////////////////////////////////////////////////////////////////////////

		$this->SetFont('UniFont', '', 9);
		$this->SetTextColor(0, 0, 0);

		$this->SetDrawColor(66, 66, 66);
		$this->SetLineWidth(0.1);
		$this->SetX(9);
		$this->Cell(190, 0, '', 1, 1, 'L');

		// TEXT CUSTOMER
//		$this->SetFont('UniFont', '', 12);

		$this->y += 3;
		// CUSTOMER NAME
		$this->SetX(9);
		$this->Cell(80, 10, mb_strtoupper($data['customer_data']['name'], 'UTF-8'), 0, 1, 'L');

		// CUSTOMER IDENTY
		$this->SetX(9);
		$this->Cell(80, 10, mb_strtoupper($data['customer_data']['ident'], 'UTF-8'), 0, 1, 'L');

		// CUSTOMER ADDRESS
		$this->SetX(9);
		$this->Cell(80, 10, mb_strtoupper($data['customer_data']['address'] . ' ' . $data['customer_data']['city'] . ' CP: ' . $data['customer_data']['postal_code'], 'UTF-8'), 0, 1, 'L');

		// CONDICION DE IVA
		$this->SetX(9);
		$this->Cell(80, 10, isset($data['customer_data']['condicion_iva']) ? $data['customer_data']['condicion_iva'] : "", 0, 1, 'L');

		// INVOICE DATA

		$this->SetTextColor(0, 0, 0);
		$this->SetFont('UniFont', '', 18);
		$this->SetY(8);
		$this->SetX(114);

		$textInvoice = $this->getTextInvoice($data['tipo_comp']);
		$this->Cell(36, 7, $textInvoice, 0, 1, 'L');

		$this->SetFont('UniFont', '', 8);

		$this->SetX(114);
		$this->Cell(36, 7, mb_strtoupper($data['text']['invoice_num'].sprintf('%04d-', $data['pto_vta']).mb_strtoupper(substr($data['invoice_num'], -8), 'UTF-8'), 'UTF-8'), 0, 1, 'L');

		$this->SetTextColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);

		// IDENTI
		$this->SetX(114);
		$this->Cell(36, 3, $data['text']['document_id'] . ' ' . mb_strtoupper($data['company_data']['ident'], 'UTF-8'), 0, 1, 'L');

		// IIBB
		$this->SetX(114);
		$this->Cell(36, 3, "Ingresos Brutos: " . ' ' . mb_strtoupper($data['company_data']['iibb'], 'UTF-8'), 0, 1, 'L');

		// Inicio Actividades
		$this->SetX(114);
		$this->Cell(36, 3, "Inicio Actividades: " . ' ' . mb_strtoupper($data['company_data']['ini_act'], 'UTF-8'), 0, 1, 'L');

		// FECHA

		$this->SetX(115);
		$this->y += 3;
		$this->SetFontSize(9);
		$this->SetTextColor(255, 255, 255);
		$this->SetDrawColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);
		$this->Cell(86, 6, "FECHA", 1, 1, 'C', true);
		$this->SetX(115);
		$this->SetTextColor(0, 0, 0);
		$this->Cell(86, 8, mb_strtoupper($data['text']['date'], 'UTF-8') . $data['date'], 1, 1, 'C');

	}

	function getTextInvoice($tipoComp){
		switch($tipoComp){
			case 1:
			case 6:
			case 11:
			case 4:return 'FACTURA';
			case 2:
			case 7:
			case 12:
			case 4:return 'NOTA DE DEBITO';
			case 3:
			case 8:
			case 13:
			case 4:return 'NOTA DE CREDITO';
		}
	}

	function getInvoiceLetter($tipoComp){
		switch($tipoComp){
			case 1:
			case 2:
			case 3:
			case 5:
			case 5:return 'A';
			case 6:
			case 7:
			case 8:
			case 9:
			case 10:return 'B';
			case 11:
			case 12:
			case 13:
			case 14:
			case 15:return 'C';
		}
	}

	function THead($text, $color)
	{
		$this->SetFillColor($color['red'], $color['green'], $color['blue']);
		$this->SetTextColor(255);
		$this->SetDrawColor(123, 122, 122);
		$this->SetLineWidth(0.1);
		$this->SetFont('UniFont', '', 8);

		$this->SetY(85);
		$this->SetX(9);

		$w = array(30, 72, 20, 10, 20, 20, 20, 20);

		$this->Cell($w[0], 7, "Código", 1, 0, 'C', true);
		$this->Cell($w[1], 7, $text['desc'], 1, 0, 'L', true);
		$this->Cell($w[2], 7, $text['price'], 1, 0, 'R', true);
		$this->Cell($w[3], 7, $text['quantity'], 1, 0, 'R', true);
		$this->Cell($w[4], 7, $text['sum_price'], 1, 0, 'R', true);
		$this->Cell($w[6], 7, 'Desc.'.'%', 1, 0, 'R', true);
		$this->Cell($w[7], 7, $text['pro_total'], 1, 0, 'R', true);

		$this->Ln();

//		$this->SetX(9);
//		$this->Cell($w[0], 138, '', 'LR', 0, 'L');
//		$this->Cell($w[1], 138, '', 'LR', 0, 'C');
//		$this->Cell($w[2], 138, '', 'LR', 0, 'C');
//		$this->Cell($w[3], 138, '', 'LR', 0, 'C');
//		$this->Cell($w[4], 138, '', 'LR', 0, 'R');
//		$this->Cell($w[5], 138, '', 'LR', 0, 'R');
//		$this->Cell($w[6], 138, '', 'LR', 0, 'R');
//		$this->Ln();
//		$this->SetX(9);
//
//		$this->Cell(array_sum($w), 0, '', 'T');
	}

	function Products($products)
	{
		$this->SetFillColor(255, 0, 0);
		$this->SetTextColor(255);
		$this->SetDrawColor(128, 0, 0);
		$this->SetLineWidth(0.1);
		$this->SetFont('UniFont', '', 8);
		$this->SetX(9);

		$w = array(30, 72, 20, 10, 20, 20, 20, 20);
		$this->SetFillColor(224, 235, 255);
		$this->SetTextColor(0);
		$this->SetY(92);
		foreach ($products as $product) {
			$code = isset($product['code']) ? $product['code'] : "";
			$this->SetX(9);
			$this->Cell($w[0], 8, $code, '', 0, 'C', false);
			$this->Cell($w[1], 8, $product['description'], '', 0, 'L', false);
			$this->Cell($w[2], 8, sprintf('%0.2f', $product['price']), '', 0, 'R', false);
			$this->Cell($w[3], 8, $product['quantity'], '', 0, 'R', false);
			$this->Cell($w[4], 8, sprintf('%0.2f',$product['sum_price']), '', 0, 'R', false);
			$this->Cell($w[5], 8, sprintf('%0.2f',$product['discount']).'%', '', 0, 'R', false);
			$this->Cell($w[6], 8, sprintf('%0.2f',$product['total']), '', 0, 'R', false);
			$this->Ln();
		}
	}

	function Base($data, $final = true)
	{
		$this->SetFillColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);
		$this->SetTextColor(255);
		$this->SetDrawColor(123, 122, 122);
		$this->SetLineWidth(0.1);
		$this->SetFont('UniFont', '', 11);
		$this->SetY(240);
		$this->SetX(9);

		$w = array(30, 30, 30, 30);
		$this->Cell($w[0], 7, $data['text']['sub_total'], 1, 0, 'C', true);
		$this->Cell($w[1], 7, $data['text']['tax_rate'], 1, 0, 'C', true);
		$this->Cell($w[2], 7, $data['text']['sum_tax'], 1, 0, 'C', true);

		$this->SetY(247);
		$this->SetX(9);
		$this->SetFont('UniFont', '', 10);
		$this->SetTextColor(0);
		$this->Cell($w[0], 8, ($final) ? $data['base']['subtotal'] : '- -', 'LR', 0, 'C');
		$this->Cell($w[1], 8, ($final) ? $data['tax'] . ' %' : '- -', 'LR', 0, 'C');
		$this->Cell($w[2], 8, ($final) ? $data['base']['sum_tax'] : '- -', 'LR', 0, 'C');

		$this->SetY(255);
		$this->SetX(9);
		$this->Cell(array_sum($w), 0, '', 'T');
	}

	function Payment($payment_method)
	{

		$this->SetY(260);
		$this->SetX(8);
		$this->SetFont('UniFont', '', 11);
		$this->SetTextColor(0, 0, 0);
		$this->Cell(100, 7, 'Payment Method:', 0, 1, 'L');

		$this->SetY(260);
		$this->SetX(45);
		$this->Cell(150, 7, $payment_method, 0, 1, 'L');
	}

	function Total($data)
	{

		// FECHA

		$cellW = 43;
        $ypos = 250;
        $cellX = 9;

		$this->SetX(9);

		$this->SetY($ypos, false);
		$this->SetFontSize(9);
		$this->SetTextColor(255, 255, 255);
		$this->SetDrawColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);
		$this->Cell($cellW, 6, "SUBTOTAL", 1, 1, 'C', true);
		$this->SetX(9);
		$this->SetTextColor(0, 0, 0);
		$this->Cell($cellW, 8, $data['text']['simbol_left'].' '.sprintf('%0.2f',$data['base']['subtotal']), 1, 1, 'C');

		$cellX += $cellW+ 2;

		$this->SetX($cellX);
		$this->SetY($ypos, false);
		$this->SetFontSize(9);
		$this->SetTextColor(255, 255, 255);
		$this->SetDrawColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);
		$this->Cell($cellW, 6, $data['text']['sum_tax'], 1, 1, 'C', true);
		$this->SetX($cellX);
		$this->SetTextColor(0, 0, 0);
		$this->Cell($cellW, 8, $data['text']['simbol_left'].' '.sprintf('%0.2f',$data['base']['sum_tax']), 1, 1, 'C');

		$cellX += $cellW+ 2;

		$this->SetX($cellX);
		$this->SetY($ypos, false);
		$this->SetFontSize(9);
		$this->SetTextColor(255, 255, 255);
		$this->SetDrawColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);
		$this->Cell($cellW, 6, "DESCUENTO", 1, 1, 'C', true);
		$this->SetX($cellX);
		$this->SetTextColor(0, 0, 0);
		$this->Cell($cellW, 8, $data['text']['simbol_left'].' '.sprintf('%0.2f',$data['base']['discount']), 1, 1, 'C');
		$cellX += $cellW+ 2;

		$this->SetX($cellX);
		$this->SetY($ypos, false);
		$this->SetFontSize(9);
		$this->SetTextColor(255, 255, 255);
		$this->SetDrawColor($data['color']['red'], $data['color']['green'], $data['color']['blue']);
		$this->Cell($cellW + 16, 6, "TOTAL", 1, 1, 'C', true);
		$this->SetX($cellX);
		$this->SetTextColor(0, 0, 0);
		$this->Cell($cellW + 16, 8, $data['text']['simbol_left'].' '.sprintf('%0.2f',$data['base']['total']), 1, 1, 'C');
	}

	function NextIvoice($text)
	{

		$this->SetFont('UniFont', '', 12);
		$this->SetTextColor(0, 0, 0);
		$this->SetY(258);
		$this->SetX(168);
		$this->Cell(31, 7, $text . ($this->PageNo() + 1) . ' ...', 0, 1, 'R');
	}

    function i25($xpos, $ypos, $code, $basewidth=1, $height=10){

        $wide = $basewidth;
        $narrow = $basewidth / 3 ;

        // wide/narrow codes for the digits
        $barChar['0'] = 'nnwwn';
        $barChar['1'] = 'wnnnw';
        $barChar['2'] = 'nwnnw';
        $barChar['3'] = 'wwnnn';
        $barChar['4'] = 'nnwnw';
        $barChar['5'] = 'wnwnn';
        $barChar['6'] = 'nwwnn';
        $barChar['7'] = 'nnnww';
        $barChar['8'] = 'wnnwn';
        $barChar['9'] = 'nwnwn';
        $barChar['A'] = 'nn';
        $barChar['Z'] = 'wn';

        // add leading zero if code-length is odd
        if(strlen($code) % 2 != 0){
            $code = '0' . $code;
        }

        $this->SetFont('Arial','',10);
        $this->Text($xpos, $ypos + $height + 4, $code);
        $this->SetFillColor(0);

        // add start and stop codes
        $code = 'AA'.strtolower($code).'ZA';

        for($i=0; $i<strlen($code); $i=$i+2){
            // choose next pair of digits
            $charBar = $code[$i];
            $charSpace = $code[$i+1];
            // check whether it is a valid digit
            if(!isset($barChar[$charBar])){
                $this->Error('Invalid character in barcode: '.$charBar);
            }
            if(!isset($barChar[$charSpace])){
                $this->Error('Invalid character in barcode: '.$charSpace);
            }
            // create a wide/narrow-sequence (first digit=bars, second digit=spaces)
            $seq = '';
            for($s=0; $s<strlen($barChar[$charBar]); $s++){
                $seq .= $barChar[$charBar][$s] . $barChar[$charSpace][$s];
            }
            for($bar=0; $bar<strlen($seq); $bar++){
                // set lineWidth depending on value
                if($seq[$bar] == 'n'){
                    $lineWidth = $narrow;
                }else{
                    $lineWidth = $wide;
                }
                // draw every second value, because the second digit of the pair is represented by the spaces
                if($bar % 2 == 0){
                    $this->Rect($xpos, $ypos, $lineWidth, $height, 'F');
                }
                $xpos += $lineWidth;
            }
        }
    }

	function Footer()
	{

		global $AliasNbPages;
		$this->SetY(-10);
		$this->SetFont('UniFont', '', 9);
		$this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
	}

	function Generate($data, $format = '')
	{
//////////////////////// START GENERATE PDF INVOCE ////////////////////
    $this->SetCompression(false);
//Add Font (create manuality previament)
		$this->AddFont('UniFont', '', 'DejaVuSans.php');

//Pages count
		$this->AliasNbPages();

		if (count($data['products']) >= 15) {

			//separate and paked in array of 17 products
			$pack_products = array_chunk($data['products'], 17);
			$limit = count($pack_products);
			$i = 1;

			foreach ($pack_products as $list_products) {
				$this->AddPage();
				$this->Head($data);
				$this->Products($list_products);
				$this->THead($data['text'], $data['color']);
				if ($i == $limit) {
					$this->Base($data);
					$this->Payment($data['payment_m']);
					$this->Total($data);
				} else {
					$this->Base($data, false);
					$this->NextIvoice($data['text']['continued']);
				}
//				$this->RotatedText(8, 236, $data['description_left'], 90);
				$i++;
			}
		} else {

			//Load normality products < 18
			$this->AddPage();
			$this->Head($data);
			$dataContent = json_encode($data);
			$this->Products($data['products']);
			$this->THead($data['text'], $data['color']);
			//$this->Payment($data['payment_m']);
			$this->Total($data);
			$this->SetTextColor(0, 0, 0);
			$this->SetFontSize(7);
			$barcode = $data['barcode'].$this->digitoVerificador($data['barcode']);
			$this->i25(9,265,$barcode);
			$this->Text(165, 270, 'CAE: ' . $data['CAE']);
			$this->Text(165, 274, 'VTO: ' . $data['Vto']);
		}

		return $this->Output($format);
	}

	private function digitoVerificador( $codigo ){
		$digitos = str_split( $codigo );

		$impares = 0;
		for( $i = 0; $i < count( $digitos ); $i+=2 ){
			$impares += $digitos[$i];
		}

		$impares = $impares * 3;

		$pares = 0;
		for( $i = 1; $i < count( $digitos ); $i+=2 ){
			$pares += $digitos[$i];
		}
		$acum = $impares + $pares;

		$digito = ceil($acum / 10.0) * 10 - $acum;
		return $digito;
	}
}
?>