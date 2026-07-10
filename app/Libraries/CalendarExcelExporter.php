<?php

namespace App\Libraries;

class CalendarExcelExporter
{
    private array $merges = [];
    private array $rows = [];

    public function export(array $events, string $month): array
    {
        return $this->exportRange([$month => $events], $month, $month);
    }

    public function exportRange(array $eventsByMonth, string $startMonth, string $endMonth): array
    {
        $this->rows = [];
        $this->merges = [];

        $this->buildRangeRows($eventsByMonth, $startMonth, $endMonth);
        $filenameSuffix = $startMonth === $endMonth ? $startMonth : $startMonth . '_sd_' . $endMonth;

        if (class_exists(\ZipArchive::class)) {
            return [
                'filename' => 'kalender-event-bsi-group-' . $filenameSuffix . '.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'content' => $this->buildXlsx(),
            ];
        }

        return [
            'filename' => 'kalender-event-bsi-group-' . $filenameSuffix . '.xls',
            'mime' => 'application/vnd.ms-excel',
            'content' => $this->buildHtmlWorkbook(),
        ];
    }

    private function buildRangeRows(array $eventsByMonth, string $startMonth, string $endMonth): void
    {
        foreach ($this->monthSequence($startMonth, $endMonth) as $index => $month) {
            if ($index > 0) {
                $this->addBlankRow();
                $this->addBlankRow();
            }

            $this->appendMonthRows($eventsByMonth[$month] ?? [], $month, $this->yearRangeLabel($startMonth, $endMonth));
        }
    }

    private function appendMonthRows(array $events, string $month, string $yearLabel): void
    {
        $date = new \DateTimeImmutable($month . '-01');
        $titleRow = count($this->rows) + 1;

        $this->addRow([
            $this->cell('KALENDER EVENT BSI GROUP ' . $yearLabel, 'title'),
            $this->cell('', 'title'), $this->cell('', 'title'), $this->cell('', 'title'),
            $this->cell('', 'title'), $this->cell('', 'title'), $this->cell('', 'title'),
            $this->cell('', 'title'),
        ]);
        $this->merge('A' . $titleRow . ':H' . $titleRow);
        $this->addBlankRow();

        $this->addCalendarRows($date);
        $this->addBlankRow();

        foreach (['UBSI', 'UNM', 'CU'] as $campus) {
            $campusEvents = $events[$campus] ?? [];
            $this->addSection($campus, $campusEvents);
        }
    }

    private function addCalendarRows(\DateTimeImmutable $date): void
    {
        $monthLabel = $this->shortMonthLabel($date);
        $this->addRow([
            $this->cell('M', 'weekdaySunday'),
            $this->cell('SN', 'weekday'),
            $this->cell('SL', 'weekday'),
            $this->cell('RB', 'weekday'),
            $this->cell('KM', 'weekday'),
            $this->cell('JM', 'weekday'),
            $this->cell('SB', 'weekday'),
            $this->cell($monthLabel, 'monthPanel'),
        ]);

        $startRow = count($this->rows);
        $this->merge('H' . $startRow . ':H' . ($startRow + 6));

        $daysInMonth = (int) $date->format('t');
        $firstWeekday = (int) $date->format('w');
        $day = 1;

        for ($week = 0; $week < 6; $week++) {
            $cells = [];
            for ($column = 0; $column < 7; $column++) {
                $position = ($week * 7) + $column;
                if ($position < $firstWeekday || $day > $daysInMonth) {
                    $cells[] = $this->cell('', 'calendarCell');
                    continue;
                }

                $cells[] = $this->cell((string) $day, $column === 0 ? 'calendarSunday' : 'calendarCell');
                $day++;
            }
            $cells[] = $this->cell('', 'monthPanel');
            $this->addRow($cells);
        }
    }

    private function addSection(string $campus, array $events): void
    {
        $rowNumber = count($this->rows) + 1;
        $this->addRow(array_fill(0, 8, $this->cell('EVENT ' . $campus, 'section')));
        $this->merge('A' . $rowNumber . ':H' . $rowNumber);

        if ($events === []) {
            $rowNumber = count($this->rows) + 1;
            $this->addRow([
                $this->cell('', 'dateCell'),
                $this->cell('', 'dateCell'),
                $this->cell('Belum ada event Google Calendar untuk ' . $campus . '.', 'eventCell'),
                $this->cell('', 'eventCell'), $this->cell('', 'eventCell'), $this->cell('', 'eventCell'),
                $this->cell('', 'eventCell'), $this->cell('', 'eventCell'),
            ]);
            $this->merge('A' . $rowNumber . ':B' . $rowNumber);
            $this->merge('C' . $rowNumber . ':H' . $rowNumber);
            return;
        }

        foreach ($this->eventsGroupedByDate($events) as $dateLabel => $dateEvents) {
            $startRow = count($this->rows) + 1;

            foreach ($dateEvents as $eventIndex => $event) {
                $this->addRow([
                    $this->cell($eventIndex === 0 ? $dateLabel : '', 'dateCell'),
                    $this->cell('', 'dateCell'),
                    $this->cell($this->eventExportLabel($event, $campus), 'eventCell'),
                    $this->cell('', 'eventCell'), $this->cell('', 'eventCell'), $this->cell('', 'eventCell'),
                    $this->cell('', 'eventCell'), $this->cell('', 'eventCell'),
                ]);

                $rowNumber = count($this->rows);
                $this->merge('C' . $rowNumber . ':H' . $rowNumber);
            }

            $endRow = count($this->rows);
            $this->merge('A' . $startRow . ':B' . $endRow);
        }
    }

    private function eventsGroupedByDate(array $events): array
    {
        $grouped = [];

        foreach ($events as $event) {
            $dateLabel = $this->dateRangeLabel($event);
            $grouped[$dateLabel][] = $event;
        }

        return $grouped;
    }

    private function eventExportLabel(array $event, string $sectionCampus): string
    {
        $summary = trim((string) ($event['summary'] ?? '(Tanpa judul)'));
        $campus = $this->eventCampusLabel($event, $sectionCampus);

        return $campus !== '' ? $campus . ' - ' . $summary : $summary;
    }

    private function eventCampusLabel(array $event, string $sectionCampus): string
    {
        $campus = trim((string) ($event['campus_name'] ?? $event['campus'] ?? ''));
        if ($campus !== '') {
            return $campus;
        }

        return trim($sectionCampus);
    }

    private function addBlankRow(): void
    {
        $this->addRow(array_fill(0, 8, $this->cell('', 'default')));
    }

    private function addRow(array $cells): void
    {
        $this->rows[] = $cells;
    }

    private function cell(string $value, string $style): array
    {
        return ['value' => $value, 'style' => $style];
    }

    private function merge(string $range): void
    {
        $this->merges[] = $range;
    }

    private function buildXlsx(): string
    {
        $cacheDir = WRITEPATH . 'cache';
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        $path = tempnam($cacheDir, 'calendar-export-');
        if ($path === false) {
            throw new \RuntimeException('Gagal membuat file sementara export kalender.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal menyiapkan file Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('docProps/app.xml', $this->appXml());
        $zip->addFromString('docProps/core.xml', $this->coreXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml());
        $zip->close();

        $content = file_get_contents($path);
        @unlink($path);

        if (! is_string($content)) {
            throw new \RuntimeException('Gagal membaca file Excel.');
        }

        return $content;
    }

    private function worksheetXml(): string
    {
        $xml = [
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">',
            '<cols><col min="1" max="2" width="18" customWidth="1"/><col min="3" max="7" width="11" customWidth="1"/><col min="8" max="8" width="72" customWidth="1"/></cols>',
            '<sheetData>',
        ];

        foreach ($this->rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $xml[] = '<row r="' . $rowNumber . '">';
            foreach ($row as $columnIndex => $cell) {
                $reference = $this->columnName($columnIndex + 1) . $rowNumber;
                $styleId = $this->styleId($cell['style']);
                $value = (string) $cell['value'];
                if ($value === '') {
                    $xml[] = '<c r="' . $reference . '" s="' . $styleId . '"/>';
                    continue;
                }
                $xml[] = '<c r="' . $reference . '" t="inlineStr" s="' . $styleId . '"><is><t>' . $this->xml($value) . '</t></is></c>';
            }
            $xml[] = '</row>';
        }

        $xml[] = '</sheetData>';

        if ($this->merges !== []) {
            $xml[] = '<mergeCells count="' . count($this->merges) . '">';
            foreach ($this->merges as $merge) {
                $xml[] = '<mergeCell ref="' . $merge . '"/>';
            }
            $xml[] = '</mergeCells>';
        }

        $xml[] = '</worksheet>';

        return implode('', $xml);
    }

    private function buildHtmlWorkbook(): string
    {
        $html = [
            '<html><head><meta charset="UTF-8"><style>',
            'table{border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px;}',
            'td{border:1px solid #7f7f7f;padding:4px 6px;}',
            '.title{font-size:20px;text-align:center;border:2px solid #2f6bff;}',
            '.weekdaySunday{background:#ff0000;color:#fff;font-weight:bold;text-align:center;}',
            '.weekday{background:#92d050;font-weight:bold;text-align:center;}',
            '.calendarSunday{color:#ff0000;text-align:center;}',
            '.calendarCell{text-align:center;}',
            '.monthPanel{background:#ffc000;font-weight:bold;text-align:center;}',
            '.section{background:#ffff00;font-weight:bold;text-align:center;}',
            '.dateCell{font-weight:bold;text-align:center;}',
            '.eventCell{font-weight:bold;}',
            '</style></head><body><table>',
        ];

        foreach ($this->rows as $rowIndex => $row) {
            $html[] = '<tr>';
            foreach ($row as $columnIndex => $cell) {
                if ($this->isCoveredByMerge($rowIndex + 1, $columnIndex + 1)) {
                    continue;
                }

                [$colspan, $rowspan] = $this->spanFor($rowIndex + 1, $columnIndex + 1);
                $html[] = '<td class="' . $this->html($cell['style']) . '"' . ($colspan > 1 ? ' colspan="' . $colspan . '"' : '') . ($rowspan > 1 ? ' rowspan="' . $rowspan . '"' : '') . '>' . $this->html($cell['value']) . '</td>';
            }
            $html[] = '</tr>';
        }

        $html[] = '</table></body></html>';

        return implode('', $html);
    }

    private function spanFor(int $row, int $column): array
    {
        foreach ($this->merges as $range) {
            [$startColumn, $startRow, $endColumn, $endRow] = $this->parseMerge($range);
            if ($row === $startRow && $column === $startColumn) {
                return [$endColumn - $startColumn + 1, $endRow - $startRow + 1];
            }
        }

        return [1, 1];
    }

    private function isCoveredByMerge(int $row, int $column): bool
    {
        foreach ($this->merges as $range) {
            [$startColumn, $startRow, $endColumn, $endRow] = $this->parseMerge($range);
            if ($row >= $startRow && $row <= $endRow && $column >= $startColumn && $column <= $endColumn) {
                return ! ($row === $startRow && $column === $startColumn);
            }
        }

        return false;
    }

    private function parseMerge(string $range): array
    {
        preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $range, $matches);

        return [
            $this->columnNumber($matches[1]),
            (int) $matches[2],
            $this->columnNumber($matches[3]),
            (int) $matches[4],
        ];
    }

    private function dateRangeLabel(array $event): string
    {
        $start = new \DateTimeImmutable((string) $event['start_at']);
        $endValue = (string) ($event['end_at'] ?? $event['start_at']);
        $end = new \DateTimeImmutable($endValue !== '' ? $endValue : (string) $event['start_at']);

        if ((int) ($event['is_all_day'] ?? 0) === 1 && $end > $start) {
            $end = $end->modify('-1 day');
        }

        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            return $this->dayMonthLabel($start);
        }

        if ($start->format('m Y') === $end->format('m Y')) {
            return $start->format('j') . ' - ' . $this->dayMonthLabel($end);
        }

        return $this->dayMonthLabel($start) . ' - ' . $this->dayMonthLabel($end);
    }

    private function dayMonthLabel(\DateTimeImmutable $date): string
    {
        return $date->format('j') . ' ' . $this->monthName((int) $date->format('n')) . ' ' . $date->format('Y');
    }

    private function shortMonthLabel(\DateTimeImmutable $date): string
    {
        $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

        return $months[(int) $date->format('n')] . '-' . $date->format('y');
    }

    private function monthName(int $month): string
    {
        $months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        return $months[$month] ?? '';
    }

    private function monthSequence(string $startMonth, string $endMonth): array
    {
        $cursor = new \DateTimeImmutable($startMonth . '-01');
        $end = new \DateTimeImmutable($endMonth . '-01');
        $months = [];

        while ($cursor <= $end) {
            $months[] = $cursor->format('Y-m');
            $cursor = $cursor->modify('first day of next month');
        }

        return $months;
    }

    private function yearRangeLabel(string $startMonth, string $endMonth): string
    {
        $startYear = substr($startMonth, 0, 4);
        $endYear = substr($endMonth, 0, 4);

        return $startYear === $endYear ? $startYear : $startYear . ' - ' . $endYear;
    }

    private function styleId(string $style): int
    {
        return [
            'default' => 0,
            'title' => 1,
            'weekdaySunday' => 2,
            'weekday' => 3,
            'calendarSunday' => 4,
            'calendarCell' => 5,
            'monthPanel' => 6,
            'section' => 7,
            'dateCell' => 8,
            'eventCell' => 9,
        ][$style] ?? 0;
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="5"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="20"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFF0000"/><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="8"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFF0000"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF92D050"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFC000"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFFF00"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF00B0F0"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="3"><border/><border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/></border><border><left style="medium"><color rgb="FF2F6BFF"/></left><right style="medium"><color rgb="FF2F6BFF"/></right><top style="medium"><color rgb="FF2F6BFF"/></top><bottom style="medium"><color rgb="FF2F6BFF"/></bottom></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="10">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="6" borderId="2" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="2" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Kalender Event" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private function appXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>EventHub</Application></Properties>';
    }

    private function coreXml(): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>Kalender Event BSI Group</dc:title><dc:creator>EventHub</dc:creator><cp:lastModifiedBy>EventHub</cp:lastModifiedBy><dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:modified></cp:coreProperties>';
    }

    private function columnName(int $column): string
    {
        $name = '';
        while ($column > 0) {
            $column--;
            $name = chr(65 + ($column % 26)) . $name;
            $column = intdiv($column, 26);
        }

        return $name;
    }

    private function columnNumber(string $name): int
    {
        $number = 0;
        foreach (str_split($name) as $char) {
            $number = ($number * 26) + (ord($char) - 64);
        }

        return $number;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
