<?php

namespace App\Controllers;

class User extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index(): string
    {
        // Initialize variables for Active Machine Count
        $totalRowCount = 0;

        // Query to get all tables that start with 'area'
        $tablesQuery = $this->db->query("SHOW TABLES LIKE 'area%'");
        $areaTables = $tablesQuery->getResultArray();

        // Loop through the table names and count the rows
        foreach ($areaTables as $table) {
            $tableName = current($table); // Get the table name from result
            $rowCountQuery = $this->db->query("SELECT COUNT(*) as count FROM $tableName");
            $rowCount = $rowCountQuery->getRow()->count;
            $totalRowCount += $rowCount;
        }

        // Get current date in Asia/Jakarta timezone
        $timezone = new \DateTimeZone('Asia/Jakarta');
        $currentDate = new \DateTime('now', $timezone);
        $dateString = $currentDate->format('Y-m-d'); // Format to match your database date format

        // Initialize total arc time in seconds
        $totalArcTimeInSeconds = 0;

        // Query to get all tables that start with 'machinehistory'
        $historyTablesQuery = $this->db->query("SHOW TABLES LIKE 'machinehistory%'");
        $historyTables = $historyTablesQuery->getResultArray();

        // Loop through the machinehistory tables and sum ArcTotal for today
        foreach ($historyTables as $table) {
            $tableName = current($table); // Get the table name from result

            // Fetch all rows with today's date and sum ArcTotal
            $arcTotalQuery = $this->db->query("
                SELECT TIME_TO_SEC(ArcTotal) as ArcTotalSeconds 
                FROM $tableName 
                WHERE DATE(Date) = ?", [$dateString]); // Replace some_date_column with the actual column name for date
            
            $arcTotals = $arcTotalQuery->getResultArray();
            
            // Sum up the ArcTotal in seconds
            foreach ($arcTotals as $arcRow) {
                $totalArcTimeInSeconds += $arcRow['ArcTotalSeconds'];
            }
        }

        // Convert total seconds to H:i:s format
        $totalArcHours = floor($totalArcTimeInSeconds / 3600);
        $totalArcMinutes = floor(($totalArcTimeInSeconds % 3600) / 60);
        $totalArcSeconds = $totalArcTimeInSeconds % 60;

        // Format the time as H:i:s, even if hours exceed 24
        $formattedArcTime = sprintf('%02d:%02d:%02d', $totalArcHours, $totalArcMinutes, $totalArcSeconds);

        // Pass the total row count and machine uptime to the view
        $data['title'] = 'Dashboard';
        $data['sidebarData'] = 'dashboard';
        $data['activeMachineCount'] = $totalRowCount; // Pass the active machine count
        $data['machineUptime'] = $formattedArcTime; // Pass the total machine uptime for today

        return view('user/index', $data);
    }
}
