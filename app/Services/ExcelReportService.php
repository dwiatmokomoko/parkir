<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Carbon\Carbon;

class ExcelReportService
{
    /**
     * Generate Excel report from transactions
     *
     * @param Collection $transactions
     * @param array $filters
     * @return string Path to generated Excel file
     */
    public function generate(Collection $transactions, array $filters): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Parkir');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);

        // Add header section
        $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI PEMBAYARAN PARKIR');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Dinas Perhubungan');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add metadata
        $row = 4;
        $sheet->setCellValue("A{$row}", 'Periode:');
        $sheet->setCellValue("B{$row}", Carbon::createFromFormat('Y-m-d', $filters['start_date'])->format('d/m/Y') . ' s/d ' . Carbon::createFromFormat('Y-m-d', $filters['end_date'])->format('d/m/Y'));
        $row++;

        if (!empty($filters['street_section'])) {
            $sheet->setCellValue("A{$row}", 'Lokasi:');
            $sheet->setCellValue("B{$row}", $filters['street_section']);
            $row++;
        }

        $sheet->setCellValue("A{$row}", 'Tanggal Cetak:');
        $sheet->setCellValue("B{$row}", now()->format('d/m/Y H:i:s'));
        $row += 2;

        // Add table headers
        $headerRow = $row;
        $headers = ['No', 'Tanggal/Jam', 'Juru Parkir', 'Lokasi', 'Jenis Kendaraan', 'Jumlah', 'Status'];
        
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, $headerRow, $header);
        }

        // Style header row
        $headerStyle = $sheet->getStyle("A{$headerRow}:G{$headerRow}");
        $headerStyle->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF333333');
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Add transaction data
        $dataRow = $headerRow + 1;
        $totalRevenue = 0;
        $successCount = 0;

        foreach ($transactions as $index => $transaction) {
            $sheet->setCellValueByColumnAndRow(1, $dataRow, $index + 1);
            $sheet->setCellValueByColumnAndRow(2, $dataRow, $transaction->created_at->format('d/m/Y H:i'));
            $sheet->setCellValueByColumnAndRow(3, $dataRow, $transaction->parkingAttendant->name ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $dataRow, $transaction->street_section);
            
            $vehicleType = $transaction->vehicle_type === 'motorcycle' ? 'Motor' : ($transaction->vehicle_type === 'car' ? 'Mobil' : $transaction->vehicle_type);
            $sheet->setCellValueByColumnAndRow(5, $dataRow, $vehicleType);
            
            $sheet->setCellValueByColumnAndRow(6, $dataRow, $transaction->amount);
            $sheet->getStyle("F{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');
            
            $statusText = match($transaction->payment_status) {
                'success' => 'Berhasil',
                'failed' => 'Gagal',
                'pending' => 'Pending',
                default => $transaction->payment_status,
            };
            $sheet->setCellValueByColumnAndRow(7, $dataRow, $statusText);

            // Track totals
            if ($transaction->payment_status === 'success') {
                $totalRevenue += $transaction->amount;
                $successCount++;
            }

            // Style data row
            $sheet->getStyle("A{$dataRow}:G{$dataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("F{$dataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("G{$dataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $dataRow++;
        }

        // Add summary section
        $summaryRow = $dataRow + 1;
        $sheet->setCellValue("A{$summaryRow}", 'RINGKASAN');
        $sheet->mergeCells("A{$summaryRow}:G{$summaryRow}");
        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$summaryRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F5F5');

        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", 'Total Transaksi:');
        $sheet->setCellValue("B{$summaryRow}", $transactions->count());
        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);

        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", 'Transaksi Berhasil:');
        $sheet->setCellValue("B{$summaryRow}", $successCount);
        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);

        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", 'Transaksi Gagal:');
        $sheet->setCellValue("B{$summaryRow}", $transactions->where('payment_status', 'failed')->count());
        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);

        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", 'Transaksi Pending:');
        $sheet->setCellValue("B{$summaryRow}", $transactions->where('payment_status', 'pending')->count());
        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);

        $summaryRow++;
        $sheet->setCellValue("A{$summaryRow}", 'Total Pendapatan:');
        $sheet->setCellValue("B{$summaryRow}", $totalRevenue);
        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("B{$summaryRow}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("B{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0');

        // Freeze panes (freeze header row)
        $sheet->freezePane("A" . ($headerRow + 1));

        // Add filters (AutoFilter)
        $sheet->setAutoFilter("A{$headerRow}:G" . ($dataRow - 1));

        // Save file
        $filename = 'reports/laporan_parkir_' . now()->timestamp . '.xlsx';
        $path = storage_path('app/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $filename;
    }
}
