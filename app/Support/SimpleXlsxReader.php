<?php

namespace App\Support;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class SimpleXlsxReader
{
    /**
     * @return array<int, array<int, string>>
     */
    public function readFirstSheetRows(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("XLSX file not found: {$path}");
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException("Failed to open XLSX file: {$path}");
        }

        try {
            $sheetPath = $this->resolveFirstSheetPath($zip);
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetXml = $zip->getFromName($sheetPath);

            if ($sheetXml === false) {
                throw new RuntimeException("Failed to read worksheet XML: {$sheetPath}");
            }

            $sheet = simplexml_load_string($sheetXml);
            if (! $sheet instanceof SimpleXMLElement || ! isset($sheet->sheetData)) {
                throw new RuntimeException("Invalid worksheet XML in {$path}");
            }

            $rows = [];
            foreach ($sheet->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $ref = (string) $cell['r'];
                    if (! preg_match('/([A-Z]+)(\d+)/', $ref, $m)) {
                        continue;
                    }

                    $col = $this->columnLettersToIndex($m[1]);
                    $type = (string) $cell['t'];
                    $value = '';

                    if ($type === 's') {
                        $idx = isset($cell->v) ? (int) $cell->v : -1;
                        $value = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $value = (string) ($cell->is->t ?? '');
                    } else {
                        $value = isset($cell->v) ? (string) $cell->v : '';
                    }

                    $value = preg_replace('/\s+/u', ' ', trim($value));
                    $rowData[$col] = $value ?? '';
                }

                $rows[] = $rowData;
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    private function resolveFirstSheetPath(ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            throw new RuntimeException('Invalid XLSX workbook structure.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);

        if (! $workbook instanceof SimpleXMLElement || ! $rels instanceof SimpleXMLElement) {
            throw new RuntimeException('Failed to parse workbook XML.');
        }

        $firstSheet = $workbook->sheets->sheet[0] ?? null;
        if (! $firstSheet) {
            throw new RuntimeException('Workbook has no sheets.');
        }

        $rid = (string) $firstSheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;
        if ($rid === '') {
            throw new RuntimeException('Failed to resolve first sheet relationship ID.');
        }

        $target = null;
        foreach ($rels->Relationship as $rel) {
            if ((string) $rel['Id'] === $rid) {
                $target = (string) $rel['Target'];
                break;
            }
        }

        if (! $target) {
            throw new RuntimeException("Sheet relationship not found for {$rid}.");
        }

        return 'xl/'.ltrim($target, '/');
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml === false) {
            return [];
        }

        $sst = simplexml_load_string($sharedXml);
        if (! $sst instanceof SimpleXMLElement) {
            return [];
        }

        $values = [];
        foreach ($sst->si as $si) {
            if (isset($si->t)) {
                $values[] = (string) $si->t;
                continue;
            }

            $text = '';
            foreach ($si->r as $run) {
                $text .= (string) ($run->t ?? '');
            }
            $values[] = $text;
        }

        return $values;
    }

    private function columnLettersToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $n = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $n = $n * 26 + (ord($letters[$i]) - 64);
        }

        return $n;
    }
}

