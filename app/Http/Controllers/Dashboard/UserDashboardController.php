<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserDashboardController extends Controller
{
    public function statistik()
    {
        $data = [
            'total_users' => User::count(),
            'total_jabatan' => User::distinct('jabatan')->count('jabatan'),
            'total_bagian'  => User::distinct('bagian')->count('bagian'),

            'by_jabatan' => User::select('jabatan', DB::raw('count(*) as total'))
                ->groupBy('jabatan')
                ->get(),
            'by_bagian' => User::select('bagian', DB::raw('count(*) as total'))
                ->groupBy('bagian')
                ->get(),
        ];

        return response()->json($data);
    }
}
