<?php

namespace App\Services;

use ZipArchive;

class ExcelExporter
{
    /**
     * Genera un archivo XLSX válido en binario con encabezados estilizados,
     * anchos calculados automáticamente, tipos de datos correctos y fila de totales opcional.
     *
     * @param array $headers Etiquetas de cabecera
     * @param array $rows Matriz de filas de datos
     * @param string $sheetTitle Nombre de la pestaña (máx 31 caracteres)
     * @param array $options Opciones adicionales:
     *                       - 'columnTypes': array de tipos por columna ('string', 'decimal', 'integer', 'date', 'center')
     *                       - 'totalRow': array de valores para la fila de totales finales
     * @return string Contenido binario del archivo .xlsx
     */
    public static function generate(
        array $headers,
        array $rows,
        string $sheetTitle = 'Ventas de Descarte',
        array $options = []
    ): string {
        $sheetTitle = preg_replace('/[\\\\\\/*?\\[\\]:]/', '', substr($sheetTitle, 0, 31));
        if (trim($sheetTitle) === '') {
            $sheetTitle = 'Datos';
        }

        $columnTypes = $options['columnTypes'] ?? [];
        $totalRow = $options['totalRow'] ?? null;

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
        $stylesXml = self::getStylesXml();

        // 6. xl/worksheets/sheet1.xml
        $colCount = count($headers);

        // Auto-cálculo de anchos inteligentes para que el texto nunca aparezca recortado ni con ###
        $colWidths = [];
        for ($c = 0; $c < $colCount; $c++) {
            $headerText = isset($headers[$c]) ? (string) $headers[$c] : '';
            $maxLen = mb_strlen($headerText, 'UTF-8');
            foreach ($rows as $row) {
                $val = isset($row[$c]) ? (string) $row[$c] : '';
                $len = mb_strlen($val, 'UTF-8');
                if ($len > $maxLen) {
                    $maxLen = $len;
                }
            }
            if ($totalRow && isset($totalRow[$c])) {
                $tLen = mb_strlen((string) $totalRow[$c], 'UTF-8');
                if ($tLen > $maxLen) {
                    $maxLen = $tLen;
                }
            }
            // Margen generoso entre 13 y 48 caracteres
            $colWidths[$c] = max(13, min(48, $maxLen + 4));
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<sheetViews><sheetView tabSelected="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>' .
            '<cols>';

        for ($c = 1; $c <= $colCount; $c++) {
            $w = $colWidths[$c - 1] ?? 20;
            $sheetXml .= '<col min="' . $c . '" max="' . $c . '" width="' . $w . '" customWidth="1"/>';
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
            $sheetXml .= '<row r="' . $rIndex . '" ht="21" customHeight="1">';
            $cIndex = 0;
            foreach ($row as $val) {
                $colLetter = self::colToLetter($cIndex);
                $cellRef = $colLetter . $rIndex;
                $colType = $columnTypes[$cIndex] ?? null;

                if (is_null($val) || $val === '') {
                    $sheetXml .= '<c r="' . $cellRef . '" s="0"/>';
                } elseif ($colType === 'string') {
                    // Texto explícito forzado (RUC, Brevete, Placa, Teléfono - sin separación de miles ni notación exponencial)
                    $sheetXml .= '<c r="' . $cellRef . '" s="0" t="inlineStr"><is><t>' . htmlspecialchars((string) $val, ENT_XML1, 'UTF-8') . '</t></is></c>';
                } elseif ($colType === 'date' || $colType === 'center') {
                    // Texto centrado (fechas, códigos, estados)
                    $sheetXml .= '<c r="' . $cellRef . '" s="4" t="inlineStr"><is><t>' . htmlspecialchars((string) $val, ENT_XML1, 'UTF-8') . '</t></is></c>';
                } elseif ($colType === 'decimal') {
                    $sheetXml .= '<c r="' . $cellRef . '" s="2"><v>' . (float) $val . '</v></c>';
                } elseif ($colType === 'integer') {
                    $sheetXml .= '<c r="' . $cellRef . '" s="3"><v>' . (int) $val . '</v></c>';
                } elseif (is_float($val)) {
                    $sheetXml .= '<c r="' . $cellRef . '" s="2"><v>' . $val . '</v></c>';
                } elseif (is_int($val)) {
                    $sheetXml .= '<c r="' . $cellRef . '" s="3"><v>' . $val . '</v></c>';
                } elseif (is_string($val) && preg_match('/^\\d{2}\\/\\d{2}\\/\\d{4}/', $val)) {
                    // Detección automática de fechas d/m/Y
                    $sheetXml .= '<c r="' . $cellRef . '" s="4" t="inlineStr"><is><t>' . htmlspecialchars($val, ENT_XML1, 'UTF-8') . '</t></is></c>';
                } elseif (is_numeric($val) && str_contains((string) $val, '.')) {
                    $sheetXml .= '<c r="' . $cellRef . '" s="2"><v>' . (float) $val . '</v></c>';
                } elseif (is_numeric($val) && !preg_match('/^0[0-9]/', (string) $val) && strlen((string) $val) <= 7) {
                    $sheetXml .= '<c r="' . $cellRef . '" s="3"><v>' . (int) $val . '</v></c>';
                } else {
                    $sheetXml .= '<c r="' . $cellRef . '" s="0" t="inlineStr"><is><t>' . htmlspecialchars((string) $val, ENT_XML1, 'UTF-8') . '</t></is></c>';
                }
                $cIndex++;
            }
            $sheetXml .= '</row>';
            $rIndex++;
        }

        // Fila de Totales Finales (si fue proporcionada)
        if ($totalRow && is_array($totalRow)) {
            $sheetXml .= '<row r="' . $rIndex . '" ht="24" customHeight="1">';
            $cIndex = 0;
            foreach ($totalRow as $val) {
                $colLetter = self::colToLetter($cIndex);
                $cellRef = $colLetter . $rIndex;

                if (is_null($val) || $val === '') {
                    $sheetXml .= '<c r="' . $cellRef . '" s="5"/>';
                } elseif (is_float($val) || (is_numeric($val) && str_contains((string) $val, '.'))) {
                    $sheetXml .= '<c r="' . $cellRef . '" s="6"><v>' . (float) $val . '</v></c>';
                } elseif (is_int($val) || (is_numeric($val) && !preg_match('/^0[0-9]/', (string) $val) && strlen((string) $val) <= 7)) {
                    $sheetXml .= '<c r="' . $cellRef . '" s="7"><v>' . (int) $val . '</v></c>';
                } else {
                    $sheetXml .= '<c r="' . $cellRef . '" s="5" t="inlineStr"><is><t>' . htmlspecialchars((string) $val, ENT_XML1, 'UTF-8') . '</t></is></c>';
                }
                $cIndex++;
            }
            $sheetXml .= '</row>';
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

    /**
     * Retorna la definición XML de estilos estándar corporativos TALSA.
     */
    public static function getStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<numFmts count="2">' .
            '<numFmt numFmtId="164" formatCode="#,##0.00"/>' .
            '<numFmt numFmtId="165" formatCode="#,##0"/>' .
            '</numFmts>' .
            '<fonts count="6">' .
            '<font><sz val="10"/><name val="Segoe UI"/><color rgb="FF1F2937"/></font>' . // 0: Normal texto
            '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' . // 1: Header blanco bold
            '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FF0F2B1F"/></font>' . // 2: Bold verde oscuro
            '<font><b/><sz val="11"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' . // 3: Banner title white bold
            '<font><b/><sz val="9.5"/><name val="Segoe UI"/><color rgb="FF0369A1"/></font>' . // 4: Subheader blue bold
            '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FF166534"/></font>' . // 5: Total col bold verde
            '</fonts>' .
            '<fills count="8">' .
            '<fill><patternFill patternType="none"/></fill>' . // 0
            '<fill><patternFill patternType="gray125"/></fill>' . // 1
            '<fill><patternFill patternType="solid"><fgColor rgb="FF166534"/></patternFill></fill>' . // 2: Verde fundo header
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' . // 3: Zebra light
            '<fill><patternFill patternType="solid"><fgColor rgb="FFEBF5EE"/></patternFill></fill>' . // 4: Verde muy suave para totales
            '<fill><patternFill patternType="solid"><fgColor rgb="FF0F2B1F"/></patternFill></fill>' . // 5: Verde bosque TALSA banner
            '<fill><patternFill patternType="solid"><fgColor rgb="FFE0F2FE"/></patternFill></fill>' . // 6: Azul cielo suave subheader
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF0FDF4"/></patternFill></fill>' . // 7: Verde menta suave
            '</fills>' .
            '<borders count="3">' .
            '<border><left/><right/><top/><bottom/></border>' . // 0: Sin bordes
            '<border>' . // 1: Borde fino estándar
            '<left style="thin"><color rgb="FFE2E8F0"/></left>' .
            '<right style="thin"><color rgb="FFE2E8F0"/></right>' .
            '<top style="thin"><color rgb="FFE2E8F0"/></top>' .
            '<bottom style="thin"><color rgb="FFE2E8F0"/></bottom>' .
            '</border>' .
            '<border>' . // 2: Borde contable total
            '<left style="thin"><color rgb="FFCBD5E1"/></left>' .
            '<right style="thin"><color rgb="FFCBD5E1"/></right>' .
            '<top style="thin"><color rgb="FF166534"/></top>' .
            '<bottom style="double"><color rgb="FF166534"/></bottom>' .
            '</border>' .
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="12">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/>' . // 0: Normal texto izquierda
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' . // 1: Header verde
            '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . // 2: Decimal (123.45)
            '<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . // 3: Entero (123)
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . // 4: Centrado (fechas, códigos)
            '<xf numFmtId="0" fontId="2" fillId="4" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>' . // 5: Total texto
            '<xf numFmtId="164" fontId="2" fillId="4" borderId="2" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . // 6: Total decimal
            '<xf numFmtId="165" fontId="2" fillId="4" borderId="2" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . // 7: Total entero
            '<xf numFmtId="0" fontId="3" fillId="5" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . // 8: Banner Título
            '<xf numFmtId="0" fontId="4" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' . // 9: Subheader azul
            '<xf numFmtId="164" fontId="5" fillId="7" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' . // 10: Total Columna verde menta
            '<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>' . // 11: Cliente nombre bold
            '</cellXfs>' .
            '</styleSheet>';
    }

    /**
     * Genera un archivo Excel (.xlsx) con dos tablas matriciales cruzadas (Kilogramos y Montos)
     * una debajo de la otra, con encabezados de doble nivel y sin fila de TOTAL GENERAL.
     *
     * @param array $matrizClientes Matriz de clientes calculada
     * @param string $sheetTitle Título de la pestaña
     * @return string Contenido binario del archivo .xlsx
     */
    public static function generateMatricesClientes(
        array $matrizClientes,
        string $sheetTitle = 'Ventas por Cliente'
    ): string {
        $sheetTitle = preg_replace('/[\\\\\\/*?\\[\\]:]/', '', substr($sheetTitle, 0, 31));
        if (trim($sheetTitle) === '') {
            $sheetTitle = 'Clientes';
        }

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

        // 5. xl/styles.xml
        $stylesXml = self::getStylesXml();

        // 6. xl/worksheets/sheet1.xml
        $colWidths = [
            1 => 36, // CLIENTE
            2 => 18, // CAMPO - GRANOS
            3 => 18, // CAMPO - RACIMOS
            4 => 22, // CAMPO - RACIMOS CON PLAGA
            5 => 18, // PACKING - GRANOS
            6 => 18, // PACKING - RACIMOS
            7 => 18, // NACIONAL - GRANOS
            8 => 18, // NACIONAL - RACIMOS
            9 => 20, // TOTAL (KG o S/)
        ];

        // Ajustar ancho de CLIENTE si hay nombres más largos
        foreach ($matrizClientes as $c) {
            $cName = (string) ($c['cliente'] ?? '');
            $len = mb_strlen($cName, 'UTF-8');
            if ($len + 4 > $colWidths[1]) {
                $colWidths[1] = min(50, $len + 4);
            }
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews>' .
            '<cols>';

        for ($c = 1; $c <= 9; $c++) {
            $w = $colWidths[$c] ?? 18;
            $sheetXml .= '<col min="' . $c . '" max="' . $c . '" width="' . $w . '" customWidth="1"/>';
        }
        $sheetXml .= '</cols><sheetData>';

        $merges = [];
        $r = 1;

        // -------------------------------------------------------------
        // TABLA 1: DISTRIBUCIÓN POR VOLUMEN — KILOGRAMOS (KG)
        // -------------------------------------------------------------
        // Título Banner
        $sheetXml .= '<row r="' . $r . '" ht="28" customHeight="1">';
        $sheetXml .= '<c r="A' . $r . '" s="8" t="inlineStr"><is><t>1. DISTRIBUCIÓN POR VOLUMEN — KILOGRAMOS (KG)</t></is></c>';
        for ($col = 1; $col < 9; $col++) {
            $sheetXml .= '<c r="' . self::colToLetter($col) . $r . '" s="8"/>';
        }
        $sheetXml .= '</row>';
        $merges[] = 'A' . $r . ':I' . $r;
        $r++;

        // Fila 1 de Cabecera (Tier 1: Motivos)
        $rTier1 = $r;
        $sheetXml .= '<row r="' . $rTier1 . '" ht="26" customHeight="1">';
        $sheetXml .= '<c r="A' . $rTier1 . '" s="1" t="inlineStr"><is><t>CLIENTE</t></is></c>';
        $sheetXml .= '<c r="B' . $rTier1 . '" s="1" t="inlineStr"><is><t>DESCARTE CAMPO</t></is></c>';
        $sheetXml .= '<c r="C' . $rTier1 . '" s="1"/><c r="D' . $rTier1 . '" s="1"/>';
        $sheetXml .= '<c r="E' . $rTier1 . '" s="1" t="inlineStr"><is><t>DESCARTE PACKING</t></is></c>';
        $sheetXml .= '<c r="F' . $rTier1 . '" s="1"/>';
        $sheetXml .= '<c r="G' . $rTier1 . '" s="1" t="inlineStr"><is><t>COSECHA NACIONAL</t></is></c>';
        $sheetXml .= '<c r="H' . $rTier1 . '" s="1"/>';
        $sheetXml .= '<c r="I' . $rTier1 . '" s="1" t="inlineStr"><is><t>TOTAL KG</t></is></c>';
        $sheetXml .= '</row>';
        $r++;

        // Fila 2 de Cabecera (Tier 2: Tipos)
        $rTier2 = $r;
        $sheetXml .= '<row r="' . $rTier2 . '" ht="22" customHeight="1">';
        $sheetXml .= '<c r="A' . $rTier2 . '" s="1"/>';
        $sheetXml .= '<c r="B' . $rTier2 . '" s="9" t="inlineStr"><is><t>GRANOS</t></is></c>';
        $sheetXml .= '<c r="C' . $rTier2 . '" s="9" t="inlineStr"><is><t>RACIMOS</t></is></c>';
        $sheetXml .= '<c r="D' . $rTier2 . '" s="9" t="inlineStr"><is><t>RACIMOS CON PLAGA</t></is></c>';
        $sheetXml .= '<c r="E' . $rTier2 . '" s="9" t="inlineStr"><is><t>GRANOS</t></is></c>';
        $sheetXml .= '<c r="F' . $rTier2 . '" s="9" t="inlineStr"><is><t>RACIMOS</t></is></c>';
        $sheetXml .= '<c r="G' . $rTier2 . '" s="9" t="inlineStr"><is><t>GRANOS</t></is></c>';
        $sheetXml .= '<c r="H' . $rTier2 . '" s="9" t="inlineStr"><is><t>RACIMOS</t></is></c>';
        $sheetXml .= '<c r="I' . $rTier2 . '" s="1"/>';
        $sheetXml .= '</row>';

        $merges[] = 'A' . $rTier1 . ':A' . $rTier2;
        $merges[] = 'B' . $rTier1 . ':D' . $rTier1;
        $merges[] = 'E' . $rTier1 . ':F' . $rTier1;
        $merges[] = 'G' . $rTier1 . ':H' . $rTier1;
        $merges[] = 'I' . $rTier1 . ':I' . $rTier2;
        $r++;

        // Filas de Datos (Kilogramos)
        if (count($matrizClientes) === 0) {
            $sheetXml .= '<row r="' . $r . '" ht="24" customHeight="1">';
            $sheetXml .= '<c r="A' . $r . '" s="0" t="inlineStr"><is><t>No hay registros de ventas para clasificar por cliente.</t></is></c>';
            for ($col = 1; $col < 9; $col++) {
                $sheetXml .= '<c r="' . self::colToLetter($col) . $r . '" s="0"/>';
            }
            $sheetXml .= '</row>';
            $merges[] = 'A' . $r . ':I' . $r;
            $r++;
        } else {
            foreach ($matrizClientes as $c) {
                $cName = (string) ($c['cliente'] ?? '');

                $sheetXml .= '<row r="' . $r . '" ht="20" customHeight="1">';
                $sheetXml .= '<c r="A' . $r . '" s="11" t="inlineStr"><is><t>' . htmlspecialchars($cName, ENT_XML1, 'UTF-8') . '</t></is></c>';
                $sheetXml .= '<c r="B' . $r . '" s="2"><v>' . (float) ($c['kg']['Campo']['Granos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="C' . $r . '" s="2"><v>' . (float) ($c['kg']['Campo']['Racimos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="D' . $r . '" s="2"><v>' . (float) ($c['kg']['Campo']['Racimos con Plaga'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="E' . $r . '" s="2"><v>' . (float) ($c['kg']['Packing']['Granos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="F' . $r . '" s="2"><v>' . (float) ($c['kg']['Packing']['Racimos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="G' . $r . '" s="2"><v>' . (float) ($c['kg']['Cosecha Nacional']['Granos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="H' . $r . '" s="2"><v>' . (float) ($c['kg']['Cosecha Nacional']['Racimos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="I' . $r . '" s="10"><v>' . (float) ($c['total_kg'] ?? 0) . '</v></c>';
                $sheetXml .= '</row>';
                $r++;
            }
        }

        // Fila Separadora en blanco
        $sheetXml .= '<row r="' . $r . '" ht="18" customHeight="1"/>';
        $r++;

        // -------------------------------------------------------------
        // TABLA 2: DISTRIBUCIÓN ECONÓMICA — MONTOS (SOLES S/.)
        // -------------------------------------------------------------
        // Título Banner
        $sheetXml .= '<row r="' . $r . '" ht="28" customHeight="1">';
        $sheetXml .= '<c r="A' . $r . '" s="8" t="inlineStr"><is><t>2. DISTRIBUCIÓN ECONÓMICA — MONTOS (SOLES S/.)</t></is></c>';
        for ($col = 1; $col < 9; $col++) {
            $sheetXml .= '<c r="' . self::colToLetter($col) . $r . '" s="8"/>';
        }
        $sheetXml .= '</row>';
        $merges[] = 'A' . $r . ':I' . $r;
        $r++;

        // Fila 1 de Cabecera (Tier 1: Motivos)
        $rTier1M = $r;
        $sheetXml .= '<row r="' . $rTier1M . '" ht="26" customHeight="1">';
        $sheetXml .= '<c r="A' . $rTier1M . '" s="1" t="inlineStr"><is><t>CLIENTE</t></is></c>';
        $sheetXml .= '<c r="B' . $rTier1M . '" s="1" t="inlineStr"><is><t>DESCARTE CAMPO</t></is></c>';
        $sheetXml .= '<c r="C' . $rTier1M . '" s="1"/><c r="D' . $rTier1M . '" s="1"/>';
        $sheetXml .= '<c r="E' . $rTier1M . '" s="1" t="inlineStr"><is><t>DESCARTE PACKING</t></is></c>';
        $sheetXml .= '<c r="F' . $rTier1M . '" s="1"/>';
        $sheetXml .= '<c r="G' . $rTier1M . '" s="1" t="inlineStr"><is><t>COSECHA NACIONAL</t></is></c>';
        $sheetXml .= '<c r="H' . $rTier1M . '" s="1"/>';
        $sheetXml .= '<c r="I' . $rTier1M . '" s="1" t="inlineStr"><is><t>TOTAL S/</t></is></c>';
        $sheetXml .= '</row>';
        $r++;

        // Fila 2 de Cabecera (Tier 2: Tipos)
        $rTier2M = $r;
        $sheetXml .= '<row r="' . $rTier2M . '" ht="22" customHeight="1">';
        $sheetXml .= '<c r="A' . $rTier2M . '" s="1"/>';
        $sheetXml .= '<c r="B' . $rTier2M . '" s="9" t="inlineStr"><is><t>GRANOS</t></is></c>';
        $sheetXml .= '<c r="C' . $rTier2M . '" s="9" t="inlineStr"><is><t>RACIMOS</t></is></c>';
        $sheetXml .= '<c r="D' . $rTier2M . '" s="9" t="inlineStr"><is><t>RACIMOS CON PLAGA</t></is></c>';
        $sheetXml .= '<c r="E' . $rTier2M . '" s="9" t="inlineStr"><is><t>GRANOS</t></is></c>';
        $sheetXml .= '<c r="F' . $rTier2M . '" s="9" t="inlineStr"><is><t>RACIMOS</t></is></c>';
        $sheetXml .= '<c r="G' . $rTier2M . '" s="9" t="inlineStr"><is><t>GRANOS</t></is></c>';
        $sheetXml .= '<c r="H' . $rTier2M . '" s="9" t="inlineStr"><is><t>RACIMOS</t></is></c>';
        $sheetXml .= '<c r="I' . $rTier2M . '" s="1"/>';
        $sheetXml .= '</row>';

        $merges[] = 'A' . $rTier1M . ':A' . $rTier2M;
        $merges[] = 'B' . $rTier1M . ':D' . $rTier1M;
        $merges[] = 'E' . $rTier1M . ':F' . $rTier1M;
        $merges[] = 'G' . $rTier1M . ':H' . $rTier1M;
        $merges[] = 'I' . $rTier1M . ':I' . $rTier2M;
        $r++;

        // Filas de Datos (Montos)
        if (count($matrizClientes) === 0) {
            $sheetXml .= '<row r="' . $r . '" ht="24" customHeight="1">';
            $sheetXml .= '<c r="A' . $r . '" s="0" t="inlineStr"><is><t>No hay registros de ventas para clasificar por cliente.</t></is></c>';
            for ($col = 1; $col < 9; $col++) {
                $sheetXml .= '<c r="' . self::colToLetter($col) . $r . '" s="0"/>';
            }
            $sheetXml .= '</row>';
            $merges[] = 'A' . $r . ':I' . $r;
            $r++;
        } else {
            foreach ($matrizClientes as $c) {
                $cName = (string) ($c['cliente'] ?? '');

                $sheetXml .= '<row r="' . $r . '" ht="20" customHeight="1">';
                $sheetXml .= '<c r="A' . $r . '" s="11" t="inlineStr"><is><t>' . htmlspecialchars($cName, ENT_XML1, 'UTF-8') . '</t></is></c>';
                $sheetXml .= '<c r="B' . $r . '" s="2"><v>' . (float) ($c['monto']['Campo']['Granos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="C' . $r . '" s="2"><v>' . (float) ($c['monto']['Campo']['Racimos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="D' . $r . '" s="2"><v>' . (float) ($c['monto']['Campo']['Racimos con Plaga'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="E' . $r . '" s="2"><v>' . (float) ($c['monto']['Packing']['Granos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="F' . $r . '" s="2"><v>' . (float) ($c['monto']['Packing']['Racimos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="G' . $r . '" s="2"><v>' . (float) ($c['monto']['Cosecha Nacional']['Granos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="H' . $r . '" s="2"><v>' . (float) ($c['monto']['Cosecha Nacional']['Racimos'] ?? 0) . '</v></c>';
                $sheetXml .= '<c r="I' . $r . '" s="10"><v>' . (float) ($c['total_monto'] ?? 0) . '</v></c>';
                $sheetXml .= '</row>';
                $r++;
            }
        }

        $sheetXml .= '</sheetData>';

        // Merges de celdas
        if (!empty($merges)) {
            $sheetXml .= '<mergeCells count="' . count($merges) . '">';
            foreach ($merges as $mRef) {
                $sheetXml .= '<mergeCell ref="' . $mRef . '"/>';
            }
            $sheetXml .= '</mergeCells>';
        }

        $sheetXml .= '</worksheet>';

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
     * Genera una matriz XLSX de una sola tabla para el resumen por lote.
     * Los encabezados de motivos se fusionan igual que en la matriz por cliente.
     */
    public static function generateMatrizLotes(
        array $matrizLotes,
        array $columnasMatriz,
        array $totalesColumnasKg,
        float $granTotalKg,
        string $sheetTitle = 'Ventas por Lote'
    ): string {
        $sheetTitle = preg_replace('~[\\/*?\[\]:]~', '', substr($sheetTitle, 0, 31));
        if (trim($sheetTitle) === '') {
            $sheetTitle = 'Lotes';
        }

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';

        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';

        $wbRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';

        $wbXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<sheets><sheet name="' . htmlspecialchars($sheetTitle, ENT_XML1, 'UTF-8') . '" sheetId="1" r:id="rId1"/></sheets>' .
            '</workbook>';

        $stylesXml = self::getStylesXml();
        $totalColumns = 2;
        foreach ($columnasMatriz as $colDef) {
            $totalColumns += count($colDef['tipos']);
        }
        $totalColumns += 1;

        $colWidths = [1 => 20, 2 => 14];
        for ($column = 3; $column < $totalColumns; $column++) {
            $colWidths[$column] = 18;
        }
        $colWidths[$totalColumns] = 18;

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews><cols>';

        for ($column = 1; $column <= $totalColumns; $column++) {
            $sheetXml .= '<col min="' . $column . '" max="' . $column . '" width="' . ($colWidths[$column] ?? 18) . '" customWidth="1"/>';
        }
        $sheetXml .= '</cols><sheetData>';

        $merges = [];
        $row = 1;
        $lastColumn = self::colToLetter($totalColumns - 1);

        // Primera fila: encabezados agrupados por motivo.
        $sheetXml .= '<row r="1" ht="26" customHeight="1">';
        $sheetXml .= '<c r="A1" s="1" t="inlineStr"><is><t>FUNDO</t></is></c>';
        $sheetXml .= '<c r="B1" s="1" t="inlineStr"><is><t>LOTE</t></is></c>';
        $columnIndex = 3;
        foreach ($columnasMatriz as $colDef) {
            $start = self::colToLetter($columnIndex - 1);
            $end = self::colToLetter($columnIndex + count($colDef['tipos']) - 2);
            $sheetXml .= '<c r="' . $start . '1" s="1" t="inlineStr"><is><t>' . htmlspecialchars($colDef['titulo'], ENT_XML1, 'UTF-8') . '</t></is></c>';
            for ($blank = $columnIndex + 1; $blank <= $columnIndex + count($colDef['tipos']) - 1; $blank++) {
                $sheetXml .= '<c r="' . self::colToLetter($blank - 1) . '1" s="1"/>';
            }
            $merges[] = $start . '1:' . $end . '1';
            $columnIndex += count($colDef['tipos']);
        }
        $sheetXml .= '<c r="' . $lastColumn . '1" s="1" t="inlineStr"><is><t>TOTAL KG</t></is></c></row>';

        // Segunda fila: tipos de descarte.
        $row = 2;
        $sheetXml .= '<row r="2" ht="22" customHeight="1">';
        $sheetXml .= '<c r="A2" s="1"/><c r="B2" s="1"/>';
        $columnIndex = 3;
        foreach ($columnasMatriz as $colDef) {
            foreach ($colDef['tipos'] as $tipo) {
                $cell = self::colToLetter($columnIndex - 1);
                $sheetXml .= '<c r="' . $cell . '2" s="9" t="inlineStr"><is><t>' . htmlspecialchars(strtoupper($tipo), ENT_XML1, 'UTF-8') . '</t></is></c>';
                $columnIndex++;
            }
        }
        $sheetXml .= '<c r="' . $lastColumn . '2" s="1"/></row>';
        $merges[] = 'A1:A2';
        $merges[] = 'B1:B2';
        $merges[] = $lastColumn . '1:' . $lastColumn . '2';

        $row = 3;
        if (count($matrizLotes) === 0) {
            $sheetXml .= '<row r="3" ht="24" customHeight="1"><c r="A3" s="0" t="inlineStr"><is><t>No hay registros de ventas para clasificar por lote.</t></is></c></row>';
            $merges[] = 'A3:' . $lastColumn . '3';
            $row++;
        } else {
            foreach ($matrizLotes as $lote) {
                $sheetXml .= '<row r="' . $row . '" ht="20" customHeight="1">';
                $sheetXml .= '<c r="A' . $row . '" s="11" t="inlineStr"><is><t>' . htmlspecialchars((string) ($lote['fundo'] ?? ''), ENT_XML1, 'UTF-8') . '</t></is></c>';
                $sheetXml .= '<c r="B' . $row . '" s="11" t="inlineStr"><is><t>' . htmlspecialchars((string) ($lote['lote'] ?? ''), ENT_XML1, 'UTF-8') . '</t></is></c>';
                $columnIndex = 3;
                foreach ($columnasMatriz as $motivoKey => $colDef) {
                    foreach ($colDef['tipos'] as $tipo) {
                        $cell = self::colToLetter($columnIndex - 1);
                        $value = (float) ($lote['kg'][$motivoKey][$tipo] ?? 0);
                        $sheetXml .= '<c r="' . $cell . $row . '" s="2"><v>' . $value . '</v></c>';
                        $columnIndex++;
                    }
                }
                $sheetXml .= '<c r="' . $lastColumn . $row . '" s="10"><v>' . (float) ($lote['total_kg'] ?? 0) . '</v></c></row>';
                $row++;
            }
        }

        // Total general con la misma estructura de columnas.
        $sheetXml .= '<row r="' . $row . '" ht="24" customHeight="1">';
        $sheetXml .= '<c r="A' . $row . '" s="5" t="inlineStr"><is><t>TOTAL GENERAL</t></is></c><c r="B' . $row . '" s="5"/>';
        $merges[] = 'A' . $row . ':B' . $row;
        $columnIndex = 3;
        foreach ($columnasMatriz as $motivoKey => $colDef) {
            foreach ($colDef['tipos'] as $tipo) {
                $cell = self::colToLetter($columnIndex - 1);
                $value = (float) ($totalesColumnasKg[$motivoKey][$tipo] ?? 0);
                $sheetXml .= '<c r="' . $cell . $row . '" s="6"><v>' . $value . '</v></c>';
                $columnIndex++;
            }
        }
        $sheetXml .= '<c r="' . $lastColumn . $row . '" s="6"><v>' . $granTotalKg . '</v></c></row></sheetData>';

        if (!empty($merges)) {
            $sheetXml .= '<mergeCells count="' . count($merges) . '">';
            foreach ($merges as $merge) {
                $sheetXml .= '<mergeCell ref="' . $merge . '"/>';
            }
            $sheetXml .= '</mergeCells>';
        }
        $sheetXml .= '</worksheet>';

        return self::createZip([
            '[Content_Types].xml' => $contentTypesXml,
            '_rels/.rels' => $relsXml,
            'xl/_rels/workbook.xml.rels' => $wbRelsXml,
            'xl/workbook.xml' => $wbXml,
            'xl/styles.xml' => $stylesXml,
            'xl/worksheets/sheet1.xml' => $sheetXml,
        ]);
    }
}
