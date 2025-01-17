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

            $new_user = new User();
            $new_user->name = "Alice";
            $new_user->email = 'alice1994@livemail.it';
            $new_user->password = Hash::make('prolific');
            $new_user->warning_type = $least_popular_condition["type"];
            $new_user->show_explanation = $least_popular_condition["show_explanation"];
            $new_user->show_details = $least_popular_condition["show_details"];
            $new_user->llm = $least_popular_condition["llm"];
            $new_user->explanation_type = $least_popular_condition["explanation_type"];

            $new_user->save();
            Auth::login($new_user);
        }
        return $next($request);
    }


    /*
     * Returns the least popular condition (not showing details, showing details always, or only on-demand) for study #2
     */
    #[ArrayShape(["type" => "string", "show_explanation" => "boolean", "show_details" => "string", "llm" => "string",
        "explanation_type" => "string"])]
    private function getWarningTypeToAssign(): array
    {
        $users = DB::table('users')
            ->select('llm', 'explanation_type', DB::raw('count(*) as total'))
            ->whereNotNull('study_completed')
            ->where('warning_type', '=', 'popup_link')
            ->where('show_explanation', '=', '1')
            ->whereNotNull(['llm', 'explanation_type'])
            ->groupBy('llm', 'explanation_type')
            ->get();

        //$show_details = StudyAuth::getLeastPopularValueInColumn($users, "show_details", ["no", "on_demand"]);
        $llm = StudyAuth::getLeastPopularValueInColumn($users, "llm", ["llama3.2-11b", "claude3.5sonnet"]);
        $explanation_type = StudyAuth::getLeastPopularValueInColumn($users, "explanation_type", ["feature_based", "counterfactual"]);

        return ["type" => "popup_link", "show_explanation" => 1, "show_details" => "no",
            "llm" => $llm, "explanation_type" => $explanation_type];
    }


    private function getLeastPopularValueInColumn($users, $column, $values): string
    {
        $conditions_count_details= [];
        foreach ($values as $cond) {
            $temp_group = $users->where($column, "=", $cond)->first();
            if ($temp_group) {
                $conditions_count_details[$cond] = $temp_group->total;
            } else {
                $conditions_count_details[$cond] = 0;
            }
        }
        # Take the condition with the minimum value
        $conditions_array = array_keys($conditions_count_details, min($conditions_count_details));
        if (count($conditions_array) > 1) {  // If there are 2 or more minimum values, take one condition at random between them
            $random_idx = rand(0, count($conditions_array)-1);
            $least_popular_condition = $conditions_array[$random_idx];
        } else {
            $least_popular_condition = $conditions_array[0];
        }
        return $least_popular_condition;
    }


    /*
     * [OLD] Returns the least popular warning type. This method was used to assign users to conditions in the first
     * studies (with 900 participants)

    #[ArrayShape(["type" => "string", "explanation" => "boolean"])]
    private function getWarningTypeToAssign_old(): array
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
    }  */
}
