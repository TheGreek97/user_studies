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
        $prolificId_request = $request->query('PROLIFIC_PID');
        if ($prolificId_request) {
            $user= User::userCompletedPreviousStudy($prolificId_request);
            if ($user !== null) {  //  user already executed the study on Prolific
                session(['study_already_taken' => '1']);
                session(['consent' => '1']);
                Auth::login($user);
            }
        }
        if (Auth::user() === null) {
           // create new user
            $least_popular_condition = $this->getWarningTypeToAssign();

            $new_user = new User();
            $new_user->name = "Alice";
            $birth_year = now()->year - 28;  // Alice is 28 years old, according to the scenario
            $new_user->email = "alice$birth_year@livemail.it";
            $new_user->password = Hash::make('prolific');  // dummy password -> not used
            $new_user->warning_type = $least_popular_condition["type"];
            $new_user->show_explanation = $least_popular_condition["show_explanation"];
            $new_user->show_details = $least_popular_condition["show_details"];
            $new_user->llm = $least_popular_condition["llm"];
            $new_user->explanation_type = $least_popular_condition["explanation_type"];
            $new_user->prolific_id = $prolificId_request;
            $new_user->save();
            Auth::login($new_user);
        } else {
            if ($prolificId_request){
                Auth::user()->prolific_id = $prolificId_request;
                Auth::user()->save();
            }
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
        // Step 1: Define possible values for 'llm' and 'explanation_type'
        $llmValues = ['llama3_3', 'claude3_5'];
        $explanationValues = ['feature_based', 'counterfactual'];

        // Step 2: Generate all possible combinations
        $combinations = collect($llmValues)
            ->crossJoin($explanationValues) // Create Cartesian product
            ->map(function ($combo) {
                return ['llm' => $combo[0], 'explanation_type' => $combo[1]];
            });

        // Step 3: Create a query to count users for each combination
        $users_counts = DB::table(DB::raw('('.$combinations->map(function ($combo) {
                return 'SELECT "'.$combo['llm'].'" AS llm, "'.$combo['explanation_type'].'" AS explanation_type';
            })->implode(' UNION ALL ').') AS combinations'))
            ->leftJoin('users', function ($join) {
                $join->on('combinations.llm', '=', 'users.llm')
                    ->on('combinations.explanation_type', '=', 'users.explanation_type')
                    ->whereNotNull('study_completed')
                    ->where('warning_type', '=', 'popup_link')
                    ->where('show_explanation', '=', '1')
                    ->where('show_details', '=', 'no');
            })
            ->select('combinations.llm', 'combinations.explanation_type', DB::raw('COUNT(users.id) AS user_count'))

            ->groupBy('combinations.llm', 'combinations.explanation_type')
            ->get();

        // Step 4: Find the minimum count
        $minCount = $users_counts->min('user_count');

        // Step 5: Select a random least popular combination
        $leastPopularCombination = $users_counts->where('user_count', $minCount)->random();
        $llm = $leastPopularCombination->llm;
        $explanation_type = $leastPopularCombination->explanation_type;
        //echo ("$llm - $explanation_type");
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
