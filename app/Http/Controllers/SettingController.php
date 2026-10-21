<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'app_name' => app_setting('app_name', 'Warung Aboen'),
            'store_tagline' => app_setting('store_tagline', 'Warung Sembako & Kebutuhan Sehari-hari Lengkap'),
            'store_address' => app_setting('store_address', 'Kp Cigarunggang RT 27 RW 13, Desa Sumberjaya, Kecamatan Cihaurbeuti, Kabupaten Ciamis, Provinsi Jawa Barat'),
            'store_phone' => app_setting('store_phone', '082262438494'),
            'receipt_footer' => app_setting('receipt_footer', 'Terima kasih telah berbelanja di Warung Aboen! Semoga berkah dan sehat selalu.'),
            'initial_cash_balance' => app_setting('initial_cash_balance', '1500000'),
        ];

        $auditLogs = AuditLog::with('user')->latest()->paginate(25);

        return view('settings.index', compact('settings', 'auditLogs'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'store_tagline' => 'nullable|string|max:255',
            'store_address' => 'nullable|string|max:255',
            'store_phone' => 'nullable|string|max:50',
            'receipt_footer' => 'nullable|string|max:255',
        ]);

        $oldName = app_setting('app_name');

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        AuditLog::log(
            'setting_updated',
            "Pengaturan warung diperbarui. Nama warung: '{$validated['app_name']}' (Sebelumnya: '{$oldName}')"
        );

        return redirect()->route('settings.index')->with('success', 'Pengaturan Warung Aboen berhasil diperbarui!');
    }

    public function backup()
    {
        $dbName = config('database.connections.mysql.database');
        $fileName = 'backup_warung_aboen_' . date('Y-m-d_H-i-s') . '.sql';

        $headers = [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        AuditLog::log('database_backup', "Pencadangan database '{$fileName}' berhasil diunduh.");

        $callback = function () use ($dbName) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "-- Cadangan Database Warung Aboen\n");
            fwrite($handle, "-- Waktu Dibuat: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Database: {$dbName}\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = DB::select('SHOW TABLES');
            $prop = 'Tables_in_' . $dbName;

            foreach ($tables as $t) {
                $table = $t->$prop;
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($handle, $createTable[0]->{'Create Table'} . ";\n\n");

                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    fwrite($handle, "INSERT INTO `{$table}` VALUES \n");
                    $values = [];
                    foreach ($rows as $row) {
                        $rowArr = (array) $row;
                        $escaped = array_map(function ($val) {
                            if (is_null($val)) return 'NULL';
                            return "'" . addslashes($val) . "'";
                        }, $rowArr);
                        $values[] = "(" . implode(", ", $escaped) . ")";
                    }
                    fwrite($handle, implode(",\n", $values) . ";\n\n");
                }
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
