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
        $gameUsers = GameUser::all();

        $this->attendanceService->loginWtihPool($gameUsers);

        return response()->json([
            'message' => 'Get cookie successful'
        ]);
    }

    public function searchCoinInfo()
    {
        $gameUsers = GameUser::all();

        $this->attendanceService->searchCoinInfo($gameUsers);

        return response()->json([
            'message' => 'Search coin ifno successful'
        ]);
    }

    public function getGift()
    {
        $gameUsers = GameUser::all();

        $this->attendanceService->getGift($gameUsers);

        return response()->json([
            'message' => 'Get gift successful'
        ]);
    }

    public function searchCodeInfo()
    {
        $gameUsers = GameUser::all();

        $this->attendanceService->searchCodeInfo($gameUsers);

        return response()->json([
            'message' => 'Search code info successful'
        ]);
    }

    public function saveCode()
    {
        $gameUsers = GameUser::all();

        $codes = $gameUsers->filter(function($item) {
            if (count(json_decode($item->code_info)) > 0) {
                return true;
            }
        })->map(function($item) {
            return $item->code_info;
        });

        return response()->json([
            'message' => 'Save code successful',
        ]);
    }

    public function checkIn()
    {
        $gameUsers = GameUser::all();

        $this->attendanceService->checkIn($gameUsers);

        return response()->json([
            'message' => 'Check in successful'
        ]);
    }
}
