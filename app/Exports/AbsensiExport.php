<?php

namespace App\Exports;

use App\Models\DetailAbsensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AbsensiExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles, WithTitle, WithCustomStartCell
{
    protected $absensi;
    protected $detailAbsensi;

    public function __construct($absensi, $detailAbsensi)
    {
        $this->absensi = $absensi;
        $this->detailAbsensi = $detailAbsensi;
    }

    public function collection()
    {
        return $this->detailAbsensi;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Masuk',
            'Keluar',
            'Lembur (Jam)',
            'Kekurangan (Jam)',
        ];
    }

    public function full_tgl_indo($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);
        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }

    public function tgl_bulan($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);
        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]];
    }

    public function bulan_tahun($tanggal)
    {
        $bulan = array(
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);
        return $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }

    public function map($detailAbsensi): array
    {
        $masuk = '';
        $kekurangan = $detailAbsensi->kekurangan;
        if ($detailAbsensi->status !== 'libur') {
            $masuk = $detailAbsensi->masuk;
        }

        if ($detailAbsensi->kekurangan != 0) {
            $kekurangan .= PHP_EOL . $detailAbsensi->keterangan_kekurangan;
        }
        return [
            date('d-M-Y', strtotime($detailAbsensi->tgl)),
            $masuk,
            $detailAbsensi->keluar,
            $detailAbsensi->lembur,
            $kekurangan,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set the header style
        $sheet->getStyle('A4:E4')->getFont()->setBold(true);

        // Set the border for all cells
        $sheet->getStyle('A1:E' . ($this->detailAbsensi->count() + 5))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Set the background color for specific rows based on 'libur' status
        $total_lembur = 0;
        $total_kekurangan = 0;
        foreach ($this->detailAbsensi as $index => $detail) {
            $total_lembur += $detail->lembur;
            $total_kekurangan += $detail->kekurangan;
            $rowIndex = $index + 5; // +5 to account for the title and header rows
            if ($detail->status === 'libur') {
                $sheet->getStyle("B{$rowIndex}:E{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_RED);
            }
        }

        // Merge cells for the title
        $sheet->mergeCells('A1:A4');
        $sheet->mergeCells('B1:E1');
        $sheet->mergeCells('B2:E2');
        $sheet->mergeCells('B3:E3');

        // Set title text
        $startDate = date('Y-m-d', strtotime($this->absensi->tgl_mulai));
        $endDate = Date('Y-m-d', strtotime($this->absensi->tgl_selesai));

        // Set title text
        $sheet->setCellValue('A1', 'Tanggal');
        $sheet->setCellValue('B1', 'ABSENSI KARYAWAN BULAN: ' . $this->bulan_tahun($startDate));
        $sheet->setCellValue('B2', 'Periode: ' . $this->tgl_bulan($startDate) . ' - ' . $this->full_tgl_indo($endDate));
        $sheet->setCellValue('B3', $this->absensi->nama_user);

        $sheet->getStyle('A1')->getFont()->setBold(true);

        $sheet->getStyle('B:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B:E')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B4:E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B4:E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set wrap text for cells that may contain line breaks
        $sheet->getStyle('E1:E' . ($this->detailAbsensi->count() + 4))->getAlignment()->setWrapText(true);

        $sheet->mergeCells('A' . ($this->detailAbsensi->count() + 5) . ':C' . ($this->detailAbsensi->count() + 5));
        $sheet->setCellValue('A' . ($this->detailAbsensi->count() + 5), 'Total');
        $sheet->setCellValue('D' . ($this->detailAbsensi->count() + 5), $total_lembur);
        $sheet->setCellValue('E' . ($this->detailAbsensi->count() + 5), $total_kekurangan);
        $sheet->getStyle('A' . ($this->detailAbsensi->count() + 5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A' . ($this->detailAbsensi->count() + 5) . ':E' . ($this->detailAbsensi->count() + 5))->getFont()->setBold(true);
        $sheet->getStyle('D' . ($this->detailAbsensi->count() + 5) . ':E' . ($this->detailAbsensi->count() + 5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function title(): string
    {
        return 'Absensi Karyawan';
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }
}
