<?php
namespace App\Helpers;

use Fpdf;
use Illuminate\Support\Facades\Cache;

class Pdf extends Fpdf
{
  private $documentWidth = 270;
  private $pageBreakeWidth = 180;
  private $landscape = 'L';
  private $topOfTable = 0;
  public $textSize = 11;
  public $titleSize = 15;
  public $textToInsertOnPageBreak = "";

  public function __construct($landscape = 'L')
  {
    new Fpdf('P', 'mm', 'A4');
    $this->landscape = $landscape;
    $this->addPage();
    Fpdf::SetFillColor(38, 166, 154);
    Fpdf::SetDrawColor(255);
    Fpdf::SetLineWidth(.3);
    Fpdf::AddFont('Raleway', '', 'Raleway-Regular.php');
    Fpdf::AddFont('Raleway', 'B', 'Raleway-Bold.php');
    Fpdf::AddFont('Raleway', 'I', 'Raleway-Italic.php');

    Fpdf::SetFont('Raleway', '', $this->titleSize);
    Fpdf::SetAutopageBreak(false);
  }

  public static function validateToken($token)
  {
    if ($token != Cache::pull('pdfToken')) {
      abort(401, 'This action is unauthorized.');
    }
  }

  public function documentTitle($text, $textSize = 0, $fontStile = '')
  {
    if ($textSize == 0) {
      $textSize = $this->titleSize;
    }
    Fpdf::SetDrawColor(255);
    Fpdf::SetTextColor(0);
    Fpdf::SetFont('Raleway', $fontStile, $textSize);
    Fpdf::Cell(0, $textSize / 1.8, utf8_decode($text), 0, 2);
  }

  public function paragraph($text, $textSize = 0, $fontStile = '')
  {
    if ($textSize == 0) {
      $textSize = $this->textSize;
    }
    Fpdf::SetDrawColor(255);
    Fpdf::SetTextColor(0);
    Fpdf::SetFont('Raleway', $fontStile, $textSize);
    Fpdf::MultiCell($this->documentWidth, $textSize / 1.8, utf8_decode($text), 0, 'L', false);
  }

  public function table($titles, $lines, $cellsWidth = [], $options = [])
  {
    while (count($cellsWidth) < count($titles)) {
      array_push($cellsWidth, 1);
    }

    Fpdf::SetTextColor(0);
    Fpdf::SetDrawColor(200);
    $this->tableHeader($titles, $cellsWidth);

    $this->setTableDefaultStyle();
    foreach ($lines as $index => $line) {
      if ($index == count($lines) - 1 && isset($options['lastLineBold'])) {
        Fpdf::SetFont('Raleway', 'B', $this->textSize);
      }
      if (isset($options['lineBreakEnabledOnLines']) && in_array($index + 1, $options['lineBreakEnabledOnLines'])) {
        Fpdf::SetAutopageBreak(true);
      } else {
        Fpdf::SetAutopageBreak(false);
      }
      if (Fpdf::GetY() >= $this->pageBreakeWidth) {
        $this->verticalLines($cellsWidth);
        $this->addPage();
        $this->insertPageBreakTextIfNeeded();
        $this->topOfTable = Fpdf::GetY();
        Fpdf::Line(Fpdf::GetX(), $this->topOfTable, Fpdf::GetX() + $this->documentWidth,  $this->topOfTable);
      }
      $this->tableLine($line, $cellsWidth);
    }
    $this->verticalLines($cellsWidth);
    Fpdf::Ln();
  }

  public function signaturePlaceHolder()
  {
    Fpdf::SetFont('Raleway', '', $this->textSize);
    Fpdf::SetXY(110, 185);
    Fpdf::Cell(100, 4, utf8_decode('Datum:_________________________________Unterschrift:__________________________________'), 0, 0, 'L', false);
  }

  public function newLine()
  {
    Fpdf::Ln();
  }

  public function addPage()
  {
    Fpdf::AddPage($this->landscape);
    if ($this->landscape == 'P') {
      $this->documentWidth = 190;
      $this->pageBreakeWidth = 260;
    } else {
      $this->documentWidth = 270;
      $this->pageBreakeWidth = 180;
    }
  }

  public function export($fileName)
  {
    Fpdf::Output('D', utf8_decode($fileName));
  }

  private function tableHeader($titles, $cellsWidth)
  {
    Fpdf::SetFont('Raleway', 'B', $this->textSize);
    $this->topOfTable = Fpdf::GetY();
    Fpdf::SetFillColor(240);

    for ($i = 0; $i < count($titles); $i++) {
      $cellWidth = ($this->documentWidth / array_sum($cellsWidth)) * $cellsWidth[$i];
      Fpdf::Cell($cellWidth, 8, utf8_decode($titles[$i]), 0, 0, 'L', true);
    }
    Fpdf::Ln();
    Fpdf::Line(Fpdf::GetX(), $this->topOfTable, Fpdf::GetX() + $this->documentWidth, $this->topOfTable);
    Fpdf::Line(Fpdf::GetX(), Fpdf::GetY(), Fpdf::GetX() + $this->documentWidth, Fpdf::GetY());
  }

  private function tableLine($cells, $cellsWidth)
  {
    $counter = 0;
    $marginLeft = Fpdf::GetX();
    $X = Fpdf::GetX();
    $marginTop = Fpdf::GetY();
    $maxHeight = 0;

    foreach ($cells as $cell) {
      $cellWidth = ($this->documentWidth / array_sum($cellsWidth)) * $cellsWidth[$counter];
      Fpdf::SetXY($marginLeft, $marginTop);
      Fpdf::MultiCell($cellWidth, 8, utf8_decode($cell), 0, 'L', false);
      $maxHeight = Fpdf::GetY() > $maxHeight ? Fpdf::GetY() : $maxHeight;
      $marginLeft += $cellWidth;
      $counter++;
    }
    Fpdf::Line($X, $maxHeight, $X + $this->documentWidth, $maxHeight);
    Fpdf::SetXY($X, $maxHeight);
  }

  private function verticalLines($cellsWidth)
  {
    foreach ($cellsWidth as $cellWidth) {
      $cellWidth = ($this->documentWidth / array_sum($cellsWidth)) * $cellWidth;
      Fpdf::Line(Fpdf::GetX(), $this->topOfTable, Fpdf::GetX(), Fpdf::GetY());
      Fpdf::SetX(Fpdf::GetX() + $cellWidth);
    }
    Fpdf::Line(Fpdf::GetX(), $this->topOfTable, Fpdf::GetX(), Fpdf::GetY());
  }

  private function insertPageBreakTextIfNeeded()
  {
    if ($this->textToInsertOnPageBreak !== "") {
      $this->documentTitle($this->textToInsertOnPageBreak);
      $this->newLine();
      $this->setTableDefaultStyle();
    }
  }

  private function setTableDefaultStyle()
  {
    Fpdf::SetTextColor(0);
    Fpdf::SetDrawColor(200);

    Fpdf::SetFont('Raleway', '', $this->textSize);
  }
}
