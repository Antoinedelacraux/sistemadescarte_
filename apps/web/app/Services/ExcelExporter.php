<?php

namespace App\Services;

use ZipArchive;

class ExcelExporter
{
    /**
     * Genera un archivo XLSX válido en binario con encabezados estilizados y tipos de datos correctos.
     *
     * @param array $headers Etiquetas de cabecera
     * @param array $rows Matriz de filas de datos
     * @param string $sheetTitle Nombre de la hoja
     * @return string Contenido binario del archivo .xlsx
     */
    public static function generate(array $headers, array $rows, string $sheetTitle = 'Ventas de Descarte'): string
    {
        $sheetTitle = preg_replace('/[\\\\\\/*?\\[\\]:]/', '', substr($sheetTitle, 0, 31));

        // 1. [Content_Types].xml
        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';

        // 2. _rels/.rels
        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';

        // 3. xl/_rels/workbook.xml.rels
        $wbRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';

        // 4. xl/workbook.xml
        $wbXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<sheets>' .
            '<sheet name="' . htmlspecialchars($sheetTitle, ENT_XML1, 'UTF-8') . '" sheetId="1" r:id="rId1"/>' .
            '</sheets>' .
            '</workbook>';

        // 5. xl/styles.xml (Estilo corporativo verde agrícola, fuentes nítidas y formatos numéricos)
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<numFmts count="2">' .
            '<numFmt numFmtId="164" formatCode="#,##0.00"/>' .
            '<numFmt numFmtId="165" formatCode="#,##0"/>' .
            '</numFmts>' .
            '<fonts count="3">' .
            '<font><sz val="10"/><name val="Segoe UI"/><color rgb="FF1F2937"/></font>' . // 0: Normal
            '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' . // 1: Header blanco bold
            '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FF0F2B1F"/></font>' . // 2: Bold oscuro
            '</fonts>' .
            '<fills count="4">' .
            '<fill><patternFill patternType="none"/></fill>' . // 0
            '<fill><patternFill patternType="gray125"/></fill>' . // 1
            '<fill><patternFill patternType="solid"><fgColor rgb="FF166534"/></patternFill></fill>' . // 2: Verde fundo
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' . // 3: Zebra light
            '</fills>' .
            '<borders count="2">' .
            '<border><left/><right/><top/><bottom/></border>' . // 0: Sin bordes
            '<border>' .
            '<left style="thin"><color rgb="FFE2E8F0"/></left>' .
            '<right style="thin"><color rgb="FFE2E8F0"/></right>' .
            '<top style="thin"><color rgb="FFE2E8F0"/></top>' .
            '<bottom style="thin"><color rgb="FFE2E8F0"/></bottom>' .
            '</border>' . // 1: Borde fino
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="5">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/>' . // 0: Normal texto
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' . // 1: Header
            '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf>' . // 2: Decimal (123.45)
            '<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf>' . // 3: Entero
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center"/></xf>' . // 4: Centrado
            '</cellXfs>' .
            '</styleSheet>';

        // 6. xl/worksheets/sheet1.xml
        $colCount = count($headers);
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<sheetViews><sheetView tabSelected="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>' .
            '<cols>';

        for ($c = 1; $c <= $colCount; $c++) {
            $sheetXml .= '<col min="' . $c . '" max="' . $c . '" width="20" customWidth="1"/>';
        }
        $sheetXml .= '</cols><sheetData>';

        // Fila 1: Cabeceras
        $sheetXml .= '<row r="1" ht="28" customHeight="1">';
        $cIndex = 0;
        foreach ($headers as $hText) {
            $colLetter = self::colToLetter($cIndex);
            $cellRef = $colLetter . '1';
            $sheetXml .= '<c r="' . $cellRef . '" s="1" t="inlineStr"><is><t>' . htmlspecialchars($hText, ENT_XML1, 'UTF-8') . '</t></is></c>';
            $cIndex++;
        }
        $sheetXml .= '</row>';

        // Filas de Datos
        $rIndex = 2;
        foreach ($rows as $row) {
            $sheetXml .= '<row r="' . $rIndex . '" ht="20" customHeight="1">';
            $cIndex = 0;
            foreach ($row as $val) {
                $colLetter = self::colToLetter($cIndex);
                $cellRef = $colLetter . $rIndex;

                if (is_null($val) || $val === '') {
                    $sheetXml .= '<c r="' . $cellRef . '" s="0"/>';
                } elseif (is_float($val) || (is_numeric($val) && str_contains((string) $val, '.'))) {
                    $sheetXml .= '<c r="' . $cellRef . '" s="2"><v>' . (float) $val . '</v></c>';
                } elseif (is_int($val) || (is_numeric($val) && !preg_match('/^0[0-9]/', (string) $val))) {
                    $sheetXml .= '<c r="' . $cellRef . '" s="3"><v>' . (int) $val . '</v></c>';
                } else {
                    $sheetXml .= '<c r="' . $cellRef . '" s="0" t="inlineStr"><is><t>' . htmlspecialchars((string) $val, ENT_XML1, 'UTF-8') . '</t></is></c>';
                }
                $cIndex++;
            }
            $sheetXml .= '</row>';
            $rIndex++;
        }

        $sheetXml .= '</sheetData></worksheet>';

        // 7. Empaquetar todo en un ZIP
        $files = [
            '[Content_Types].xml' => $contentTypesXml,
            '_rels/.rels' => $relsXml,
            'xl/_rels/workbook.xml.rels' => $wbRelsXml,
            'xl/workbook.xml' => $wbXml,
            'xl/styles.xml' => $stylesXml,
            'xl/worksheets/sheet1.xml' => $sheetXml,
        ];

        return self::createZip($files);
    }

    /**
     * Convierte índice 0-indexed a letra de columna Excel (0 -> A, 27 -> AB).
     */
    private static function colToLetter(int $col): string
    {
        $letter = '';
        while ($col >= 0) {
            $letter = chr(($col % 26) + 65) . $letter;
            $col = intdiv($col, 26) - 1;
        }
        return $letter;
    }

    /**
     * Empaqueta archivos en un archivo ZIP binario usando ZipArchive o Zip nativo.
     */
    private static function createZip(array $files): string
    {
        if (class_exists(ZipArchive::class)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
            $zip = new ZipArchive();
            if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($files as $name => $content) {
                    $zip->addFromString($name, $content);
                }
                $zip->close();
                $binary = file_get_contents($tempFile);
                @unlink($tempFile);
                return $binary;
            }
        }

        // Fallback: Generador nativo de ZIP compatible PKZIP 2.0 (pure PHP)
        return self::nativeZip($files);
    }

    /**
     * Empaquetador ZIP en PHP puro sin dependencias externas.
     */
    private static function nativeZip(array $files): string
    {
        $data = '';
        $cdir = '';
        $offset = 0;

        foreach ($files as $name => $content) {
            $name = str_replace('\\', '/', $name);
            $uncLen = strlen($content);
            $crc = crc32($content);
            $gzData = gzdeflate($content);
            $cLen = strlen($gzData);

            // Local file header
            $header = "\x50\x4b\x03\x04" .
                "\x14\x00" . // Versión mínima (2.0)
                "\x00\x00" . // General purpose flag
                "\x08\x00" . // Compresión: Deflate
                "\x00\x00\x00\x00" . // Hora/Fecha
                pack('V', $crc) .
                pack('V', $cLen) .
                pack('V', $uncLen) .
                pack('v', strlen($name)) .
                "\x00\x00" . // Extra length
                $name .
                $gzData;

            $data .= $header;

            // Central directory entry
            $cdEntry = "\x50\x4b\x01\x02" .
                "\x14\x00" . // Versión hecha por
                "\x14\x00" . // Versión requerida
                "\x00\x00" .
                "\x08\x00" .
                "\x00\x00\x00\x00" .
                pack('V', $crc) .
                pack('V', $cLen) .
                pack('V', $uncLen) .
                pack('v', strlen($name)) .
                "\x00\x00\x00\x00\x00\x00" . // Extra, comentario, disk start
                "\x00\x00\x00\x00" . // Atributos internos
                "\x20\x00\x00\x00" . // Atributos externos
                pack('V', $offset) .
                $name;

            $cdir .= $cdEntry;
            $offset = strlen($data);
        }

        $cdLen = strlen($cdir);
        $count = count($files);

        // End of central directory record
        $eocd = "\x50\x4b\x05\x06" .
            "\x00\x00\x00\x00" . // Número de disco
            pack('v', $count) .
            pack('v', $count) .
            pack('V', $cdLen) .
            pack('V', $offset) .
            "\x00\x00"; // Comentario longitud 0

        return $data . $cdir . $eocd;
    }
}
