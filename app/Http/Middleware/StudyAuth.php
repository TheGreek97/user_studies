<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;

class StudyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user() === null) {
            $least_popular_condition = $this->getWarningTypeToAssign();
            //$warning_type["type"] = $least_popular_condition["type"];
            //$warning_type["explanation"] = $least_popular_condition["explanation"];
            $warning_type["type"] = "popup_link";
            $warning_type["explanation"] = 1;
            $new_user = new User();
            $new_user->name = "Alice";
            $new_user->email = 'alice1994@livemail.it';
            $new_user->password = Hash::make('prolific');
            $new_user->warning_type = $warning_type["type"];
            $new_user->show_explanation = $warning_type["explanation"];
            $new_user->save();
            Auth::login($new_user);
        }
        return $next($request);
    }

    /*
     * Returns the least popular warning type
     */
    #[ArrayShape(["type" => "string", "explanation" => "false"])] private function getWarningTypeToAssign(): array
    {
        $users = DB::table('users')
            ->select('warning_type', 'show_explanation', DB::raw('count(*) as total'))
            ->whereNotNull('study_completed')
            ->groupBy(['warning_type', 'show_explanation'])
            ->get();

        $conditions_count= [];

        // Condition 1 (Baseline)
        $temp_group = $users->where('warning_type', "=", "tooltip")
            ->where('show_explanation', "=", 0)->first();
        if ($temp_group) {
            $conditions_count["tooltip"] = $temp_group->total;
        } else {
            $conditions_count["tooltip"] = 0;
        }

        // Condition 2 (Active before - no explanation)
        $temp_group = $users->where('warning_type', "=", "popup_email")
            ->where('show_explanation', "=", 0)->first();
        if ($temp_group) {
            $conditions_count["active_no_exp"] = $temp_group->total;
        } else {
            $conditions_count["active_no_exp"] = 0;
        }

        // Condition 3 (Active before - explanation)
        $temp_group = $users->where('warning_type', "=", "popup_email",)
            ->where('show_explanation', "=", 1)->first();
        if ($temp_group) {
            $conditions_count["active_exp"] = $temp_group->total;
        } else {
            $conditions_count["active_exp"] = 0;
        }

        // Condition 4 (Tooltip - explanation)
        $temp_group = $users->where('warning_type', "=", "tooltip")
            ->where('show_explanation', "=", 1)->first();
        if ($temp_group) {
            $conditions_count["tooltip_exp"] = $temp_group->total;
        } else {
            $conditions_count["tooltip_exp"] = 0;
        }

        // Condition 5 (Active after - no explanation)
        $temp_group = $users->where('warning_type', "=", "popup_link")
            ->where('show_explanation', "=", 0)->first();
        if ($temp_group) {
            $conditions_count["active_after_no_exp"] = $temp_group->total;
        } else {
            $conditions_count["active_after_no_exp"] = 0;
        }

        // Condition 6 (Active after - explanation)
        $temp_group = $users->where('warning_type', "=", "popup_link")
            ->where('show_explanation', "=", 1)->first();
        if ($temp_group) {
            $conditions_count["active_after_exp"] = $temp_group->total;
        } else {
            $conditions_count["active_after_exp"] = 0;
        }

        $condition_to_assign = array_keys($conditions_count, min($conditions_count));
        if (count($condition_to_assign) > 1) {  // If there are 2 or more minimum values, take one condition at random between them
            $random_idx = rand(0, count($condition_to_assign)-1);
            $condition_to_assign = $condition_to_assign[$random_idx];
        } else {
            $condition_to_assign = $condition_to_assign[0];
        }
        $type = match ($condition_to_assign) {
            'active_exp' => 'popup_email',
            'active_no_exp' => 'popup_email',
            'tooltip_exp' => 'tooltip',
            'tooltip' => 'tooltip', // = tooltip no_exp
            'active_after_exp' => 'popup_link',
            'active_after_no_exp' => 'popup_link'
        };
        $show_explanation = match ($condition_to_assign) {
            'active_exp' => 1,
            'active_no_exp' => 0,
            'tooltip_exp' => 1,
            'tooltip' => 0, // = tooltip no_exp
            'active_after_exp' => 1,
            'active_after_no_exp' => 0
        };
        return ["type" => $type, "explanation" => $show_explanation];
    }
}
