<?php

namespace App\Helpers;

use setasign\Fpdi\Tcpdf\Fpdi;

class Pdf extends Fpdi
{
  private $documentWidth = 270;
  private $pageBreakeWidth = 180;
  private $topOfTable = 0;
  private $pagePaddingLeft = 0;
  private $currentRow = 0;
  private $topRowPosition = 0;
  private $fontName = 'Helvetica';
  public $landscape = 'L';
  public $textSize = 11;
  public $titleSize = 15;
  public $textToInsertOnPageBreak = "";

  public function __construct($landscape = 'L')
  {
    parent::__construct($landscape, 'mm', 'A4');
    $this->setPrintHeader(false);
    $this->setPrintFooter(false);
    $this->landscape = $landscape;
    $this->addNewPage();
    $this->pagePaddingLeft = $this->getX();
    $this->SetTextColor(38, 166, 154);
    $this->SetDrawColor(255);

    $this->SetFont($this->fontName, '', $this->titleSize);
    $this->SetAutopageBreak(true);
    $this->SetCellPaddings(1, 1.5, 1, 1.5);
  }

  public function documentTitle($text, $textSize = 0, $fontStile = '')
  {
    if ($textSize == 0) {
      $textSize = $this->titleSize;
    }
    $this->SetDrawColor(255);
    $this->SetTextColor(0);
    $this->SetFont($this->fontName, $fontStile, $textSize);
    $this->setCellPaddings(0, 0, 0, 0.5);
    $this->setCellHeightRatio(1.3);
    $this->MultiCell(0, 0, $text, 0, 2);
  }

  public function paragraph($text, $textSize = 0, $fontStile = '', $options = [])
  {
    if (isset($options['rows']) && $this->topRowPosition === 0) {
      $this->topRowPosition = $this->GetY();
    } 
    $this->SetAutopageBreak(false);
    if ($textSize == 0) {
      $textSize = $this->textSize;
    }

    $this->SetX($this->pagePaddingLeft + $this->documentWidth / $options['rows'] * $this->currentRow);
    if (
      isset($options['linesOnSamePage']) &&
      $this->GetY() >= $this->pageBreakeWidth + $textSize - $options['linesOnSamePage'] * $textSize
    ) {
      if (isset($options['rows']) && $this->currentRow < $options['rows'] - 1) {
        $this->currentRow++;
        $this->SetY($this->topRowPosition);
        $this->SetX($this->pagePaddingLeft + $this->documentWidth / $options['rows'] * $this->currentRow);
      } else {
        $this->currentRow = 0;
        $this->SetX($this->pagePaddingLeft);
        $this->addNewPage();
      }
    }

    $this->SetDrawColor(255);
    $this->SetTextColor(0);
    $this->SetFont($this->fontName, $fontStile, $textSize);
    $this->MultiCell($this->documentWidth, $textSize / 1.8, $text, 0, 'L', false);
  }

  public function table($titles, $lines, $cellsWidth = [], $options = [])
  {
    while (count($cellsWidth) < count($titles)) {
      array_push($cellsWidth, 1);
    }

    $this->SetTextColor(0);
    $this->SetDrawColor(200);
    $this->setCellPaddings(1, 1, 1, 1);
    $this->tableHeader($titles, $cellsWidth);

    $this->setTableDefaultStyle();
    foreach ($lines as $index => $line) {
      if ($this->GetY() >= $this->pageBreakeWidth) {
        $this->verticalLines($cellsWidth);
        $this->addNewPage();
        $this->insertPageBreakTextIfNeeded();
        $this->topOfTable = $this->GetY();
        $this->Line($this->GetX(), $this->topOfTable, $this->GetX() + $this->documentWidth,  $this->topOfTable);
      }
      if ($index == count($lines) - 1 && isset($options['lastLineBold']) && $options['lastLineBold']) {
        $this->SetFont($this->fontName, 'B', $this->textSize);
      }
      if (isset($options['lineBreakEnabledOnLines']) && in_array($index + 1, $options['lineBreakEnabledOnLines'])) {
        $this->SetAutopageBreak(true);
      } else {
        $this->SetAutopageBreak(false);
      }
      $this->tableLine($line, $cellsWidth);
    }
    $this->verticalLines($cellsWidth);
    $this->Ln();
  }

  public function signaturePlaceHolder()
  {
    $this->SetFont($this->fontName, '', $this->textSize);
    $this->SetXY(110, 185);
    $this->Cell(100, 4, 'Datum:_________________________________Unterschrift:__________________________________', 0, 0, 'L', false);
  }

  public function newLine()
  {
    $this->Ln();
  }

  public function addNewPage($landscape = null)
  {
    if (!$landscape) $landscape = $this->landscape;

    $this->AddPage($landscape);
    if ($landscape == 'P') {
      $this->documentWidth = 190;
      $this->pageBreakeWidth = 260;
    } else {
      $this->documentWidth = 270;
      $this->pageBreakeWidth = 180;
    }
  }

  public function export($fileName)
  {
    $headers = [
      'Content-Type' => 'application/pdf',
      'Pragma' => utf8_decode($fileName)
    ];
    $fileNameOnServer = str_random(8) . ".pdf";
    $dirName = storage_path() . "/pdfs/";
    $file = $dirName . $fileNameOnServer;
    if (!is_dir($dirName)) {
      mkdir($dirName);
    }
    $this->Output($file, 'F');
    return response()->download($file, $fileNameOnServer, $headers)->deleteFileAfterSend();
  }

  public function error($errorMessage)
  {
    $this->documentTitle($errorMessage);
    $this->export('fehler.pdf');
  }

  public function comment($date, $comment)
  {
    $this->SetLineWidth(0);
    if ($this->GetY() >= $this->pageBreakeWidth) {
      $this->addNewPage();
      $this->insertPageBreakTextIfNeeded();
    }
    $this->SetFont($this->fontName, 'B', $this->textSize);
    $this->Cell(35, 6, $date->format('d.m.Y'), 0, 0, 'L', false);


    $this->SetFont($this->fontName, '', $this->textSize);
    $this->MultiCell($this->documentWidth - 50, 6, $comment['text'], 0, 'L', false);
  }

  private function tableHeader($titles, $cellsWidth)
  {
    $this->SetFont($this->fontName, 'B', $this->textSize);
    $this->topOfTable = $this->GetY();
    $this->SetFillColor(240);

    for ($i = 0; $i < count($titles); $i++) {
      $cellWidth = ($this->documentWidth / array_sum($cellsWidth)) * $cellsWidth[$i];
      $this->Cell($cellWidth, 8, $titles[$i], 0, 0, 'L', true);
    }
    $this->Ln();
    $this->Line($this->GetX(), $this->topOfTable, $this->GetX() + $this->documentWidth, $this->topOfTable);
    $this->Line($this->GetX(), $this->GetY(), $this->GetX() + $this->documentWidth, $this->GetY());
  }

  private function tableLine($cells, $cellsWidth)
  {
    $counter = 0;
    $marginLeft = $this->GetX();
    $X = $this->GetX();
    $marginTop = $this->GetY();
    $maxHeight = 0;

    foreach ($cells as $cell) {
      $cellWidth = ($this->documentWidth / array_sum($cellsWidth)) * $cellsWidth[$counter];
      $this->SetXY($marginLeft, $marginTop);
      $this->MultiCell($cellWidth, 8, $cell, 0, 'L', false);
      $maxHeight = $this->GetY() > $maxHeight ? $this->GetY() : $maxHeight;
      $marginLeft += $cellWidth;
      $counter++;
    }
    $this->Line($X, $maxHeight, $X + $this->documentWidth, $maxHeight);
    $this->SetXY($X, $maxHeight);
  }

  private function verticalLines($cellsWidth)
  {
    foreach ($cellsWidth as $cellWidth) {
      $cellWidth = ($this->documentWidth / array_sum($cellsWidth)) * $cellWidth;
      $this->Line($this->GetX(), $this->topOfTable, $this->GetX(), $this->GetY());
      $this->SetX($this->GetX() + $cellWidth);
    }
    $this->Line($this->GetX(), $this->topOfTable, $this->GetX(), $this->GetY());
  }

  private function insertPageBreakTextIfNeeded()
  {
    if ($this->textToInsertOnPageBreak !== "") {
      $this->documentTitle($this->textToInsertOnPageBreak);
      $this->setTableDefaultStyle();
    }
  }

  private function setTableDefaultStyle()
  {
    $this->SetTextColor(0);
    $this->SetDrawColor(200);

    $this->SetFont($this->fontName, '', $this->textSize);
    $this->setCellPadding(1);
  }
}
