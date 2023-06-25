<?php

namespace App\Http\Controllers;

use App\Models\GameUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function importGameUser()
    {
        $filename = 'game_users.txt';

        if (Storage::exists($filename)) {
            $contents = Storage::get($filename);
            $lines = explode("\n", $contents);

            foreach ($lines as $line) {
                if (empty($line)) {
                    continue;
                }

                GameUser::updateOrCreate(
                    ['username' => Str::before($line, '----')],
                    ['password' => Str::after($line, '----')]
                );
            }
        } else {
            echo "{$filename} does not exist.";
        }

        return response()->json([
            'message' => 'Import successful'
        ]);
    }

    public function getCookie()
    {
        $gameUser = GameUser::limit(5)->get();
        dd($gameUser);

        $this->attendanceService->login($gameUser);

        return response()->json([
            'message' => 'Login successful'
        ]);
    }

    public function checkIn()
    {
        $checkInResponse = $this->attendanceService->checkIn();

        return response()->json([
            'message' => 'Check in successful',
            'response' => $checkInResponse
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
