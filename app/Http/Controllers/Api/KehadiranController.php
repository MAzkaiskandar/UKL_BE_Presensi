<?php

namespace App\Http\Controllers;

use App\Models\attendance;
use App\Models\kehadiran;
use App\Models\user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class KehadiranController extends Controller
{
    public function presensi(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'user_id' => 'required',
            'date' => 'required',
            'status' => 'required',
        ]);
        if($validator->fails()){
            return Response()->json($validator->errors()->toJson());
        }

        $save = kehadiran::create([
            'user_id'    =>$req->get('user_id'),
            'date'        =>$req->get('date'),
            'time'        =>date('H:i:s'),
            'status'        =>$req->get('status'),
        ]);
        if($save){
            return Response()->
            json([
                'status'=>true,
                'message' => 'Presensi berhasil dicatat',
                'data'=>$save]);
        }else {
            return Response()->json(['status'=>false, 'message' => 'Pengguna gagal ditambahkan']);
     }

    }

    public function show1($user_id) {
        $user = kehadiran::where('user_id',$user_id,)->get();
        return response()->json(['status'=>true, 'data' => $user]);
    }

    public function summary($user_id){
        $userRecords = kehadiran::where('user_id', $user_id)->get();
        $userGroupedByMonth = $userRecords->groupBy(function($date) {
            return Carbon::parse($date->date)->format('m-Y');
        });

        $summary = [];

        foreach ($userGroupedByMonth as $monthYear => $records) {
            // Count the statuses for each month
            $hadir = $records->where('status', 'Hadir')->count();
            $izin = $records->where('status', 'Izin')->count();
            $sakit = $records->where('status', 'Sakit')->count();

            $summary[] = [
                'month' => $monthYear,
                'attendance_summary' => [
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                ],
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user_id' => $user_id,
                    'attendance_summary_by_month' => $summary
                ]
            ]);
        
        }
    }

    public function analysis(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'start_date' => 'required',
            'end_date'   => 'required',
            'group_by'   => 'required',
        ]);
    
        // Parse the start and end date
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
    
        // Fetch users based on the group_by value (role)
        $users = user::where('role', $validated['group_by'])->get();
    
        // Initialize an empty array to store the grouped analysis
        $groupedAnalysis = [];

        foreach ($users as $user) {
            // Get attendance records for each user within the specified date range
            $attendanceRecords = kehadiran::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            // Calculate total attendance for each status
            $hadir = $attendanceRecords->where('status', 'Hadir')->count();
            $izin = $attendanceRecords->where('status', 'Izin')->count();
            $sakit = $attendanceRecords->where('status', 'Sakit')->count();
            $alpha = $attendanceRecords->where('status', 'Alpha')->count();

            // Calculate percentages
            $totalAttendance = $hadir + $izin + $sakit + $alpha;
            $hadirPercentage = $totalAttendance > 0 ? ($hadir / $totalAttendance) * 100 : 0;
            $izinPercentage = $totalAttendance > 0 ? ($izin / $totalAttendance) * 100 : 0;
            $sakitPercentage = $totalAttendance > 0 ? ($sakit / $totalAttendance) * 100 : 0;
            $alphaPercentage = $totalAttendance > 0 ? ($alpha / $totalAttendance) * 100 : 0;

            // Prepare grouped analysis data
            $groupedAnalysis[] = [
                'group' => $user->role, // 'Siswa' or 'Karyawan'
                'total_users' => $users->count(),
                'attendance_rate' => [
                    'hadir_percentage' => round($hadirPercentage, 2),
                    'izin_percentage' => round($izinPercentage, 2),
                    'sakit_percentage' => round($sakitPercentage, 2),
                    'alpha_percentage' => round($alphaPercentage, 2),
                ],
                'total_attendance' => [
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpha' => $alpha,
                ],
            ];
        }

        // Return the analysis response
        return response()->json([
            'status' => 'success',
            'data' => [
                'analysis_period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
                'grouped_analysis' => $groupedAnalysis,
            ]
        ], 200);
    }



}