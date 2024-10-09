<?php

namespace App\Controllers;

class Recap extends BaseController
{
    protected $db, $builder;

    public function __construct(){
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('machine'); // Connect to the 'machine' table
    }

    public function index(): string
    {
        // Fetch all machine names from the 'machine' table
        $machines = $this->builder->select('realName')->get()->getResultArray();

        // Pass the machine names to the view
        $data = [
            'title' => 'Machine Recap',
            'sidebarData' => 'recap',
            'machines' => $machines
        ];

        return view('user/recap', $data);
    }

    public function fetchMachineData() {
        // Get POST data
        $input = $this->request->getJSON(true);
        $machineName = $input['machineName'] ?? '';
        $date = $input['date'] ?? '';

        // Fetch machine ID based on machine name
        $machine = $this->builder->select('machineID')
            ->where('realName', $machineName)
            ->get()
            ->getFirstRow();

        if (!$machine) {
            return $this->response->setJSON(['error' => 'Machine not found']);
        }

        $machineID = $machine->machineID;

        // Query the machine history based on machineID and date
        $historyBuilder = $this->db->table('machinehistory1');
        $data = $historyBuilder->select('ArcOn, ArcOff')
            ->where('MachineID', $machineID)
            ->where('Date', $date)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }
}
