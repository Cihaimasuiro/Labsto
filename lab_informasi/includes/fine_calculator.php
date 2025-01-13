<?php

class FineCalculator {
    // Konstanta untuk perhitungan denda
    const LATE_RETURN_FINE_PER_DAY = 5000;  // Denda keterlambatan per hari
    const LOST_ITEM_FINE_PERCENTAGE = 0.8;   // 80% dari harga barang untuk denda kehilangan

    /**
     * Menghitung denda keterlambatan
     * @param string $tanggal_kembali Tanggal pengembalian seharusnya
     * @param string $tanggal_aktual Tanggal pengembalian aktual
     * @return int Total denda keterlambatan
     */
    public static function calculateLateFine($tanggal_kembali, $tanggal_aktual) {
        $date_kembali = new DateTime($tanggal_kembali);
        $date_aktual = new DateTime($tanggal_aktual);
        $interval = $date_aktual->diff($date_kembali);
        
        // Hitung jumlah hari terlambat
        $days_late = $interval->days;
        if ($days_late <= 0) {
            return 0;
        }
        
        return $days_late * self::LATE_RETURN_FINE_PER_DAY;
    }

    /**
     * Menghitung denda kehilangan barang
     * @param float $harga_barang Harga barang yang hilang
     * @return float Total denda kehilangan
     */
    public static function calculateLostItemFine($harga_barang) {
        return $harga_barang * self::LOST_ITEM_FINE_PERCENTAGE;
    }
}
