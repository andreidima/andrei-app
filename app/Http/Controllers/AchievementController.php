<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Refrain;
use App\Models\Apps\Pontaj;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class AchievementController extends Controller
{
    private const WEEKDAY_HOURS_REQUIRED = 7;
    private const WEEKEND_HOURS_REQUIRED = 3.5;
    private const SECONDS_PER_HOUR = 3600;

    public function index (Request $request)
    {
        $request->session()->forget('returnUrl');

        $searchData = $request->searchData ? trim($request->searchData) : Carbon::today()->toDateString();
        $request->action === 'previousDay' ? $searchData = Carbon::parse($searchData)->subDay()->toDateString() : null;
        $request->action === 'nextDay' ? $searchData = Carbon::parse($searchData)->addDay()->toDateString() : null;

        // Calculate days of continuous work of 7 hours per day in weekdays and 3.5 hours per day in weekends
        $continuousWorkData = $this->getContinuousWorkData($searchData);
        $achievements['Continuous work']['since'] = $continuousWorkData['continuousWorkSince'];
        $achievements['Continuous work']['additionalTimeNeededForNextDay'] = $continuousWorkData['additionalTimeNeededForNextDay'];

        // Get the refrains and achieved milestones
        $refrains = Refrain::select('name', 'since')->get();
        foreach ($refrains as $key => $refrain) {
            $achievements[$refrain->name]['since'] = $refrain->since;
        }

        // Define the milestones
        $milestones = [
            ['name' => '24-Hour Cleanse', 'days' => 1],
            ['name' => 'Three-Day Detox', 'days' => 3],
            ['name' => 'Weekly Reset', 'days' => 7],
            ['name' => 'Fortnight Freedom', 'days' => 14],
            ['name' => 'Monthly Milestone', 'days' => 30],
            ['name' => '60-Day Challenge', 'days' => 60],
            ['name' => '90-Day Transformation', 'days' => 90],
            ['name' => 'Half-Year Reboot', 'days' => 180],
            ['name' => 'Year-Long Discipline', 'days' => 365],
            ['name' => '1000 Days of Mastery', 'days' => 1000],
        ];

        return view('achievements.index', compact('achievements', 'milestones', 'searchData'));
    }

    /**
     * Get the continuous work days
     */
    private function getContinuousWorkData($untilDate)
    {
        // Define the starting date and today's date
        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse($untilDate);

        $pontaje = Pontaj::select('inceput', 'sfarsit')
            ->whereDate('inceput', '>=', $startDate)
            ->whereDate('sfarsit', '<=', $endDate)
            ->latest()
            // ->take(10)
            ->get();
        $dailyDurations = [];

        // Initialize daily durations array
        for ($date = $endDate->copy(); $date->gte($startDate); $date->subDay()) {

            $dailyDurations[$date->format('Y-m-d')] = 0;
        }

        // Calculate daily work durations
        foreach ($pontaje as $record) {
            $date = Carbon::parse($record->inceput)->toDateString();
            if (!isset($dailyDurations[$date])) continue;

            $duration = Carbon::parse($record->sfarsit)->diffInSeconds(Carbon::parse($record->inceput));
            $dailyDurations[$date] += $duration;
        }

        // Check continuous work streak
        $extraTime = 0;
        foreach ($dailyDurations as $dateString => $duration) {
            $date = Carbon::parse($dateString);
            $requiredSeconds = $date->isWeekday()
                ? self::WEEKDAY_HOURS_REQUIRED * self::SECONDS_PER_HOUR
                : self::WEEKEND_HOURS_REQUIRED * self::SECONDS_PER_HOUR;

            $totalDuration = $duration + $extraTime;

            if ($totalDuration >= $requiredSeconds) {
                $extraTime = $totalDuration - $requiredSeconds;

            } else {
                return [
                    'continuousWorkSince' => $date->copy(),
                    'additionalTimeNeededForNextDay' => CarbonInterval::seconds(abs($requiredSeconds - $totalDuration))->cascade()->forHumans()
                ];
            }
        }

        return [
            'continuousWorkSince' => $startDate,
            'additionalTimeNeededForNextDay' => '0 seconds'
        ];
    }
}
