<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @link        https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2016 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Writer\Word2007\Element;

use PhpOffice\Common\XMLWriter;
use PhpOffice\PhpWord\Element\Cell as CellElement;
use PhpOffice\PhpWord\Element\Row as RowElement;
use PhpOffice\PhpWord\Element\Table as TableElement;
use PhpOffice\PhpWord\Style\Cell as CellStyle;
use PhpOffice\PhpWord\Style\Row as RowStyle;
use PhpOffice\PhpWord\Writer\Word2007\Style\Cell as CellStyleWriter;
use PhpOffice\PhpWord\Writer\Word2007\Style\Row as RowStyleWriter;
use PhpOffice\PhpWord\Writer\Word2007\Style\Table as TableStyleWriter;

/**
 * Table element writer
 *
 * @since 0.10.0
 */
class Table extends AbstractElement
{
    /**
     * Write element.
     *
     * @return void
     */
    public function write()
    {
        $xmlWriter = $this->getXmlWriter();
        $element = $this->getElement();
        if (!$element instanceof TableElement) {
            return;
        }

        $rows = $element->getRows();
        $rowCount = count($rows);

        if ($rowCount > 0) {
            $xmlWriter->startElement('w:tbl');

            // Write columns
            $this->writeColumns($xmlWriter, $element);

            // Write style
            $styleWriter = new TableStyleWriter($xmlWriter, $element->getStyle());
            $styleWriter->setWidth($element->getWidth());
            $styleWriter->write();

            // Write rows
            for ($i = 0; $i < $rowCount; $i++) {
                $this->writeRow($xmlWriter, $rows[$i]);
            }

            $xmlWriter->endElement(); // w:tbl
        }
    }

    /**
     * Write column.
     *
     * @param \PhpOffice\Common\XMLWriter $xmlWriter
     * @param \PhpOffice\PhpWord\Element\Table $element
     * @return void
     */
    private function writeColumns(XMLWriter $xmlWriter, TableElement $element)
    {
        $rows = $element->getRows();

        $cellWidths = $gridSpan = $goodDefine = array();
        foreach ($rows as $row) {
            $cells = $row->getCells();
            if (count($cells) <= count($cellWidths) && count($gridSpan) == 0) {
                continue;
            }
            $nbCell = -1;
            foreach ($cells as $cell) {
                $grid = $cell->getStyle()->getGridSpan();
                if(!empty($grid) && $grid > 1){
                    $nbCell += $grid;
                }else{
                    $nbCell++;
                }
                if(!empty($grid) && $grid > 1 && $grid < count($cellWidths)){
                    for($i=0;$i<$grid;$i++){
                        if(!isset($gridSpan[($nbCell+$i)]) && !isset($goodDefine[($nbCell+$i)])){
                            $cellWidths[$nbCell] = $cell->getWidth()/$grid;
                            $gridSpan[($nbCell+$i)] = ($nbCell+$i);
                        }
                    }
                }elseif(isset($gridSpan[$nbCell])){
                    $cellWidths[$nbCell] = $cell->getWidth();
                    unset($gridSpan[$nbCell]);
                    $goodDefine[$nbCell] = $nbCell;
                }else{
                    $goodDefine[$nbCell] = $nbCell;
                    $cellWidths[$nbCell] = $cell->getWidth();
                }
            }
        }
        ksort($cellWidths);
        $xmlWriter->startElement('w:tblGrid');
        foreach ($cellWidths as $width) {
            $xmlWriter->startElement('w:gridCol');
            if ($width !== null) {
                $xmlWriter->writeAttribute('w:w', number_format($width,0, ',',''));
                $xmlWriter->writeAttribute('w:type', 'dxa');
            }
            $xmlWriter->endElement();
        }
        $xmlWriter->endElement(); // w:tblGrid
    }

    /**
     * Write row.
     *
     * @param \PhpOffice\Common\XMLWriter $xmlWriter
     * @param \PhpOffice\PhpWord\Element\Row $row
     * @return void
     */
    private function writeRow(XMLWriter $xmlWriter, RowElement $row)
    {
        $xmlWriter->startElement('w:tr');

        // Write style
        $rowStyle = $row->getStyle();
        if ($rowStyle instanceof RowStyle) {
            $styleWriter = new RowStyleWriter($xmlWriter, $rowStyle);
            $styleWriter->setHeight($row->getHeight());
            $styleWriter->write();
        }

        // Write cells
        foreach ($row->getCells() as $cell) {
            $this->writeCell($xmlWriter, $cell);
        }

        $xmlWriter->endElement(); // w:tr
    }

    /**
     * Write cell.
     *
     * @param \PhpOffice\Common\XMLWriter $xmlWriter
     * @param \PhpOffice\PhpWord\Element\Cell $cell
     * @return void
     */
    private function writeCell(XMLWriter $xmlWriter, CellElement $cell)
    {

        $xmlWriter->startElement('w:tc');

        // Write style
        $cellStyle = $cell->getStyle();
        if ($cellStyle instanceof CellStyle) {
            $styleWriter = new CellStyleWriter($xmlWriter, $cellStyle);
            $styleWriter->setWidth($cell->getWidth());
            $styleWriter->write();
        }

        // Write content
        $containerWriter = new Container($xmlWriter, $cell);
        $containerWriter->write();

        $xmlWriter->endElement(); // w:tc
    }
}
