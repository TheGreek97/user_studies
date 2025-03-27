@php
    $user= \Illuminate\Support\Facades\Auth::user();
@endphp

<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div id="alert"
                 class="p-4 mb-4 text-sm text-red-700 bg-red-100 border-red-300 border rounded-lg absolute top-5 z-50 shadow-xl"
                 style="display: none; opacity: 0;" role="alert">
                <span class="font-medium">Some required fields have not been filled in.</span>
            </div>
            <div class="pt-12 px-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <form action="{{ Request::url() }}" method="POST" x-data="load()" id="form" style="display:none;">
                    @csrf
                    <div>
                        <h1 x-show="step !== 1 && step !== 6"
                            class="text-3xl font-bold text-center mb-4 cursor-pointer">Post-study questionnaire</h1>
                    </div>

                    <!--- WARNING QUESTIONS --->
                    <div x-show="step === 1" id="section-1">
                        <p class="text-2xl w-full text-center">Section 1 of 5</p>
                        <p class="text-lg text-left pt-5">
                            @if($user->shown_warning > 0) {{-- it means that the user was shown a warning --}}
                                You were exposed to this type of alert during the study:
                            @else   {{-- it means that the user was not shown a warning during the study --}}
                                Imagine that the following alert would have been shown when a link in an email was
                            @if($user->warning_type === "tooltip") hovered: @else clicked: @endif
                            @endif
                        </p>
                        <img class="mx-auto my-7 w-2/4" style="border-radius:6px;"
                             src="{{$url_image_warning}}">
                        <div class="my-10">
                            <p class="font-bold pb-1">Did you read the entire text of the warning dialogs that were presented to you?</p>
                            <div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="read_warning_1" type="radio" value="2" name="read_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="read_warning_1"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Yes</label>
                                </div>
                                <div class="flex items-center mr-4  mb-2">
                                    <input id="read_warning_2" type="radio" value="1" name="read_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="read_warning_2"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Partially</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="read_warning_3" type="radio" value="0" name="read_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="read_warning_3"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="my-10">
                            <p class="font-bold pb-1">When you saw the warning dialog, what was your first
                                reaction?</p>
                            <input required type="text" name="reaction"
                                   placeholder="Type your answer here..." maxlength="255"
                                   class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"/>
                        </div>

                        <div class="my-10">
                            <p class="font-bold pb-1">I understood the warning dialog clearly.</p>
                            <label for="understood_warning"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    (Don't agree)
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    (Totally agree)
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="understood_warning" value="3" min="1" max="5" step="1"
                                       name="understood_warning" class="w-full">
                                <datalist id="understood_warning">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                </datalist>
                            </div>
                        </div>

                        <div class="my-10">
                            <p class="font-bold pb-1">I am familiar with this warning dialog.</p>
                            <label for="familiar_warning"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    (Don't agree)
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    (Totally agree)
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="familiar_warning" value="3" min="1" max="5" step="1"
                                       name="familiar_warning" class="w-full">
                                <datalist id="familiar_warning">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                </datalist>
                            </div>
                        </div>

                        <div class="my-10">
                            <p class="font-bold pb-1">I am not interested in this warning dialog.</p>
                            <label for="interested_warning"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    (Don't agree)
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    (Totally agree)
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="interested_warning" value="3" min="1" max="5" step="1"
                                       name="interested_warning" class="w-full">
                                <datalist id="interested_warning">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                </datalist>
                            </div>
                        </div>

                        <div class="my-10">
                            <p class="font-bold pb-1">Which word(s) did you find confusing or too technical?</p>
                            <input type="text" name="confusing_words"
                                   placeholder="Type your answer here..." minlength="0" maxlength="255"
                                   class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"/>
                        </div>

                        <div class="my-10">
                            <p class="font-bold pb-1">
                                Please rate the extent of risk you feel you were warned about.</p>
                            <div class="">
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="felt_risk_0" type="radio" value="0" name="felt_risk"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="felt_risk_0"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">No risk</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="felt_risk_1" type="radio" value="1" name="felt_risk"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="felt_risk_1"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Very low risk</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="felt_risk_2" type="radio" value="2" name="felt_risk"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="felt_risk_2"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Low risk</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="felt_risk_3" type="radio" value="3" name="felt_risk"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="felt_risk_3"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Risky</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="felt_risk_4" type="radio" value="4" name="felt_risk"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="felt_risk_4"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Very high risk</label>
                                </div>
                            </div>
                        </div>

                        <div class="my-10">
                            <p class="font-bold pb-1">
                                What action, if any, did the warning dialog want you to take?
                            </p>
                            <div class="">
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="actions_warning_1" type="radio" value="continue" name="actions_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="actions_warning_1"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">To continue to the website</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="actions_warning_2" type="radio" value="be_careful" name="actions_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="actions_warning_2"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">To be careful while continuing to the website</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="actions_warning_3" type="radio" value="not_continue" name="actions_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="actions_warning_3"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">To not continue to the website</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="actions_warning_4" type="radio" value="none" name="actions_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="actions_warning_4"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">I did not feel anything</label>
                                </div>
                            </div>
                        </div>

                        <div class="my-10">
                            <p class="font-bold pb-1">What do you think this warning dialog means?</p>
                            <input required type="text" name="meaning_warning"
                                   placeholder="Type your answer here..." maxlength="255"
                                   class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"/>
                        </div>


                        <div class="my-10">
                            <p class="font-bold pb-1">I did not understand the warning dialog clearly.</p>
                            <label for="not_understood_warning"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    (Don't agree)
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    (Totally agree)
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="not_understood_warning" value="1" min="1" max="5" step="1"
                                       name="not_understood_warning" class="w-full">
                                <datalist id="not_understood_warning">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                </datalist>
                            </div>
                        </div>

                        <div class="my-10">
                            <p class="font-bold pb-1">
                                Please rate your level of trust in this warning dialog.
                            </p>
                            <div class="mb-2">
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="trust_warning_0" type="radio" value="0" name="trust_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="trust_warning_0"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Not at all confident</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="trust_warning_1" type="radio" value="1" name="trust_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="trust_warning_1"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Not very confident</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="trust_warning_2" type="radio" value="2" name="trust_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="trust_warning_2"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Neutral</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="trust_warning_3" type="radio" value="3" name="trust_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="trust_warning_3"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Confident</label>
                                </div>
                                <div class="flex items-center mr-4 mb-2">
                                    <input id="trust_warning_4" type="radio" value="4" name="trust_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="trust_warning_4"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Very confident</label>
                                </div>
                            </div>
                        </div>
                        {{--
                        @if ($user_ignored_warning)
                            <div class="my-10">
                                <p class="font-bold pb-1">Why did you choose to ignore the warning?</p>
                                <input required type="text" name="warning_ignored_motivation"
                                       placeholder="Type your answer here..." maxlength="255"
                                       class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"/>
                            </div>
                        @endif
                        --}}
                        <div class="my-10">
                            <p class="font-bold pb-1">What is the first word in this warning dialog?</p>
                            <input required type="text" name="first_word"
                                   placeholder="Type your answer here..." maxlength="255"
                                   class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"/>
                        </div>
                    </div>

                    <!--- NASA-TLX Questionnaire --->
                    <div x-show="step === 2" id="section-2">
                        <p class="text-2xl w-full text-center">Section 2 of 5</p>
                        <div class="my-5">
                            <p class="font-bold pb-1">How mentally demanding was the task of interacting with the alert?</p>
                            <label for="tick-1"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Very Low
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Very High
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="tick-1" value="1" min="1" max="10" step="1"
                                       name="nasa_mental_demand" class="w-full">
                                <datalist id="tick-1">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                    <option value="6" label="6"></option>
                                    <option value="7" label="7"></option>
                                    <option value="8" label="8"></option>
                                    <option value="9" label="9"></option>
                                    <option value="10" label="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How physically demanding was the task of interacting with the alert?</p>
                            <label for="tick-2"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Very Low
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Very High
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="tick-2" value="1" min="1" max="10" step="1"
                                       name="nasa_physical_demand" class="w-full">
                                <datalist id="tick-2">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                    <option value="6" label="6"></option>
                                    <option value="7" label="7"></option>
                                    <option value="8" label="8"></option>
                                    <option value="9" label="9"></option>
                                    <option value="10" label="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How hurried or rushed was the pace of the task of interacting with the alert?</p>
                            <label for="tick-3"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Very Low
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Very High
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="tick-3" value="1" min="1" max="10" step="1"
                                       name="nasa_temporal_demand" class="w-full">
                                <datalist id="tick-3">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                    <option value="6" label="6"></option>
                                    <option value="7" label="7"></option>
                                    <option value="8" label="8"></option>
                                    <option value="9" label="9"></option>
                                    <option value="10" label="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How successful were you in accomplishing what you were asked to do?</p>
                            <label for="tick-4"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Very Low
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Very High
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="tick-4" value="1" min="1" max="10" step="1"
                                       name="nasa_performance" class="w-full">
                                <datalist id="tick-4">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                    <option value="6" label="6"></option>
                                    <option value="7" label="7"></option>
                                    <option value="8" label="8"></option>
                                    <option value="9" label="9"></option>
                                    <option value="10" label="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How hard did you have to work to accomplish your level of
                                performance?</p>
                            <label for="tick-5"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Very Low
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Very High
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="tick-5" value="1" min="1" max="10" step="1" name="nasa_effort"
                                       class="w-full">
                                <datalist id="tick-5">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                    <option value="6" label="6"></option>
                                    <option value="7" label="7"></option>
                                    <option value="8" label="8"></option>
                                    <option value="9" label="9"></option>
                                    <option value="10" label="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How insecure, discouraged, irritated, stressed and annoyed were
                                you?</p>
                            <label for="tick-6"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Very Low
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Very High
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="tick-6" value="1" min="1" max="10" step="1"
                                       name="nasa_frustration_level" class="w-full">
                                <datalist id="tick-6">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                    <option value="6" label="6"></option>
                                    <option value="7" label="7"></option>
                                    <option value="8" label="8"></option>
                                    <option value="9" label="9"></option>
                                    <option value="10" label="10"></option>
                                </datalist>
                            </div>
                        </div>

                        <div class="my-5">
                            <p class="font-bold pb-1">How mentally easy was the task of interacting with the alert?</p>
                            <label for="tick-7"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Very Low
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Very High
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="tick-7" value="1" min="1" max="10" step="1"
                                       name="nasa_mental_demand_reverse" class="w-full">
                                <datalist id="tick-7">
                                    <option value="1" label="1"></option>
                                    <option value="2" label="2"></option>
                                    <option value="3" label="3"></option>
                                    <option value="4" label="4"></option>
                                    <option value="5" label="5"></option>
                                    <option value="6" label="6"></option>
                                    <option value="7" label="7"></option>
                                    <option value="8" label="8"></option>
                                    <option value="9" label="9"></option>
                                    <option value="10" label="10"></option>
                                </datalist>
                            </div>
                        </div>
                    </div>


                    <!-- NEED FOR COGNITION - 9-point Likert scale questions -->
                    <div x-show="step === 3" id="section-3">
                        <p class="text-2xl w-full text-center">Section 3 of 5</p>
                        <div class="my-3"> Express your agreement with the <b>18</b> following sentences on a scale from -4 to +4 <br>
                            <i>(-4= strongly disagree; 0= neither disagree or agree; +4= strongly agree)</i>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">1. I would prefer complex to simple problems.</p>
                            <label for="n4c-1"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Strongly disagree
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Strongly agree
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="n4c-1" value="0" min="-4" max="4" step="1"
                                       name="n4c_1" class="w-full">
                                <datalist id="n4c-1">
                                    <option value="-4" label="-4"></option>
                                    <option value="-3" label="-3"></option>
                                    <option value="-2" label="-2"></option>
                                    <option value="-1" label="-1"></option>
                                    <option value="0" label="0"></option>
                                    <option value="1" label="+1"></option>
                                    <option value="2" label="+2"></option>
                                    <option value="3" label="+3"></option>
                                    <option value="4" label="+4"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">2. I like to have the responsibility of handling a situation that requires a lot of thinking.</p>
                            <label for="n4c-2"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Strongly disagree
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Strongly agree
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="n4c-2" value="0" min="-4" max="4" step="1"
                                       name="n4c_2" class="w-full">
                                <datalist id="n4c-2">
                                    <option value="-4" label="-4"></option>
                                    <option value="-3" label="-3"></option>
                                    <option value="-2" label="-2"></option>
                                    <option value="-1" label="-1"></option>
                                    <option value="0" label="0"></option>
                                    <option value="1" label="+1"></option>
                                    <option value="2" label="+2"></option>
                                    <option value="3" label="+3"></option>
                                    <option value="4" label="+4"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">3. Thinking is not my idea of fun.</p>
                            <label for="n4c-3"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Strongly disagree
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Strongly agree
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="n4c-3" value="0" min="-4" max="4" step="1"
                                       name="n4c_3" class="w-full">
                                <datalist id="n4c-3">
                                    <option value="-4" label="-4"></option>
                                    <option value="-3" label="-3"></option>
                                    <option value="-2" label="-2"></option>
                                    <option value="-1" label="-1"></option>
                                    <option value="0" label="0"></option>
                                    <option value="1" label="+1"></option>
                                    <option value="2" label="+2"></option>
                                    <option value="3" label="+3"></option>
                                    <option value="4" label="+4"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">4. I would rather do something that requires little thought than something that is sure to challenge my thinking abilities.</p>
                            <label for="n4c-4"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Strongly disagree
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Strongly agree
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="n4c-4" value="0" min="-4" max="4" step="1"
                                       name="n4c_4" class="w-full">
                                <datalist id="n4c-4">
                                    <option value="-4" label="-4"></option>
                                    <option value="-3" label="-3"></option>
                                    <option value="-2" label="-2"></option>
                                    <option value="-1" label="-1"></option>
                                    <option value="0" label="0"></option>
                                    <option value="1" label="+1"></option>
                                    <option value="2" label="+2"></option>
                                    <option value="3" label="+3"></option>
                                    <option value="4" label="+4"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">5. Select "-2" as an answer to this question.</p>
                            <label for="n4c-attention"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Strongly disagree
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Strongly agree
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="n4c-attention" value="0" min="-4" max="4" step="1"
                                       name="n4c_attention" class="w-full">
                                <datalist id="n4c-attention">
                                    <option value="-4" label="-4"></option>
                                    <option value="-3" label="-3"></option>
                                    <option value="-2" label="-2"></option>
                                    <option value="-1" label="-1"></option>
                                    <option value="0" label="0"></option>
                                    <option value="1" label="+1"></option>
                                    <option value="2" label="+2"></option>
                                    <option value="3" label="+3"></option>
                                    <option value="4" label="+4"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">6. I really enjoy a task that involves coming up with new solutions to problems.</p>
                            <label for="n4c-5"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Strongly disagree
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Strongly agree
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="n4c-5" value="0" min="-4" max="4" step="1"
                                       name="n4c_5" class="w-full">
                                <datalist id="n4c-5">
                                    <option value="-4" label="-4"></option>
                                    <option value="-3" label="-3"></option>
                                    <option value="-2" label="-2"></option>
                                    <option value="-1" label="-1"></option>
                                    <option value="0" label="0"></option>
                                    <option value="1" label="+1"></option>
                                    <option value="2" label="+2"></option>
                                    <option value="3" label="+3"></option>
                                    <option value="4" label="+4"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">7. I would prefer a task that is intellectual, difficult, and important to one that is somewhat important but does not require much thought.</p>
                            <label for="n4c-6"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    Strongly disagree
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    Strongly agree
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="n4c-6" value="0" min="-4" max="4" step="1"
                                       name="n4c_6" class="w-full">
                                <datalist id="n4c-6">
                                    <option value="-4" label="-4"></option>
                                    <option value="-3" label="-3"></option>
                                    <option value="-2" label="-2"></option>
                                    <option value="-1" label="-1"></option>
                                    <option value="0" label="0"></option>
                                    <option value="1" label="+1"></option>
                                    <option value="2" label="+2"></option>
                                    <option value="3" label="+3"></option>
                                    <option value="4" label="+4"></option>
                                </datalist>
                            </div>
                        </div>
                    </div>

                    <!--- CYBERSECURITY QUESTIONNAIRE --->
                    <div x-show="step === 4" id="section-4">
                        <p class="text-2xl w-full text-center">Section 4 of 5</p>
                        <div class="my-8">
                            <p class="font-bold pb-1">What does the “https://” at the beginning of a URL denote, as
                                opposed to
                                http:// (without the “s”)?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_1" type="radio" value="0" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">That
                                    the
                                    site has special high definition</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_2" type="radio" value="1" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">That
                                    information entered into the site is encrypted</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_3" type="radio" value="2" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">That
                                    the
                                    site is the newest version available</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_4" type="radio" value="3" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">That
                                    the
                                    site is not accessible to certain computers</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_5" type="radio" value="4" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">None
                                    of the
                                    above</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_6" type="radio" value="5" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_6"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">Which of the following is an example of a “phishing” attack?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-1" type="radio" value="1" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Sending
                                    someone an email that contains a malicious link that is disguised to look like an
                                    email from
                                    someone the person knows</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-2" type="radio" value="2" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Creating
                                    a
                                    fake website that looks nearly identical to a real website in order to trick users
                                    into
                                    entering their login information</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-3" type="radio" value="3" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Sending
                                    someone a text message that contains a malicious link that is disguised to look like
                                    a
                                    notification that the person has won a contest</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-4" type="radio" value="4" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">All
                                    of the
                                    above</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-5" type="radio" value="5" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">A group of computers that is networked together and used by
                                hackers to
                                steal information is called a...</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_1" type="radio" value="1" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Botnet</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_2" type="radio" value="2" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Rootkit</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_3" type="radio" value="3" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">DDoS</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_4" type="radio" value="4" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Operating
                                    system</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_5" type="radio" value="5" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">Some websites and online services use a security process called
                                two-step
                                authentication. Which of the following images is an example of two-step
                                authentication?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_1" type="radio" value="0" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_1" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ asset('assets/img/cyber_4_1.png') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_2" type="radio" value="1" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_2" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ asset('assets/img/cyber_4_2.webp') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_3" type="radio" value="2" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_3" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ asset('assets/img/cyber_4_3.png') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_4" type="radio" value="3" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_4" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ asset('assets/img/cyber_4_4.png') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_5" type="radio" value="4" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">None
                                    of
                                    these</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_6" type="radio" value="5" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_6"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">Which of the following four passwords is the most secure?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_1" type="radio" value="1" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Boat123</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_2" type="radio" value="2" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">WTh!5Z</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_3" type="radio" value="3" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">into*48</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_4" type="radio" value="4" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">123456</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_5" type="radio" value="5" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>

                        <div class="my-8">
                            <p class="font-bold pb-1">Please select "red" for this question to show that you are paying attention.</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_11_1" type="radio" value="0" name="cyber_11"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_11_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">White</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_11_2" type="radio" value="1" name="cyber_11"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_11_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Red</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_11_3" type="radio" value="0" name="cyber_11"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_11_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Gray</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_11_4" type="radio" value="0" name="cyber_11"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_11_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Yellow</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_11_5" type="radio" value="0" name="cyber_11"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_11_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>

                        <div class="my-8">
                            <p class="font-bold pb-1">Criminals access someone’s computer and encrypt the user’s
                                personal files
                                and data. The user is unable to access this data unless they pay the criminals to
                                decrypt the
                                files. This practice is called...</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_1" type="radio" value="1" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Botnet</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_2" type="radio" value="2" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Ransomware</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_3" type="radio" value="3" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Driving</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_4" type="radio" value="4" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Spam</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_5" type="radio" value="5" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">None
                                    of the
                                    above</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_6" type="radio" value="6" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_6"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">“Private browsing” is a feature in many internet browsers that
                                lets users
                                access web pages without any information (like browsing history) being stored by the
                                browser.
                                Can internet service providers see the online activities of their subscribers when those
                                subscribers are using private browsing?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_6_1" type="radio" value="0" name="cyber_7"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_6_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Yes</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_6_2" type="radio" value="1" name="cyber_7"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_6_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">No</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_6_3" type="radio" value="2" name="cyber_7"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_6_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">Turning off the GPS function of your smartphone prevents any
                                tracking of
                                your phone’s location.</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_7_1" type="radio" value="0" name="cyber_8"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_7_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">True</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_7_2" type="radio" value="1" name="cyber_8"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_7_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">False</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_7_3" type="radio" value="2" name="cyber_8"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_7_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">If a public Wi-Fi network (such as in an airport or café) requires
                                a
                                password to access, is it generally safe to use that network for sensitive activities
                                such as
                                online banking?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_8_1" type="radio" value="0" name="cyber_9"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_8_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Yes,
                                    it is
                                    safe</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_8_2" type="radio" value="1" name="cyber_9"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_8_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">No,
                                    it is
                                    not safe</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_8_3" type="radio" value="2" name="cyber_9"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_8_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                        <div class="my-8">
                            <p class="font-bold pb-1">What kind of cybersecurity risks can be minimized by using a
                                Virtual
                                Private Network (VPN)?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_1" type="radio" value="1" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Use
                                    of
                                    insecure Wi-Fi networks</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_2" type="radio" value="2" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Key-logging</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_3" type="radio" value="3"
                                       name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">De-anonymization
                                    by network operators</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_4" type="radio" value="4" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Phishing
                                    attacks</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_5" type="radio" value="5" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                    </div>

                    <!-- DEMOGRAPHICS -->
                    <div x-show="step === 5" class="pt-1">
                        <p class="text-2xl w-full text-center">Section 5 of 5</p>
                        @if(Illuminate\Support\Facades\Auth::user()->prolific_id == null)
                            <div class="my-5">
                            <p class="font-bold pb-1">What is your ©PROLIFIC ID?</p>
                            <input required type="text" name="prolific_id" min="10" max="100" placeholder="Prolific ID"
                                   class="block text-sm px-4 rounded-lg py-3 border outline-none"/>
                            </div>
                        @endif
                        <div class="my-5">
                            <p class="font-bold pb-1">How old are you?</p>
                            <input required type="number" name="age" min="10" max="100" value="20"
                                   class="block text-sm px-4 rounded-lg py-3 border outline-none"/>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">Gender:</p>
                            <div class="flex">
                                <div class="flex items-center mr-4">
                                    <input id="inline-gender" type="radio" value="Male" name="gender"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="inline-gender"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Male</label>
                                </div>
                                <div class="flex items-center mr-4">
                                    <input id="inline-2-gender" type="radio" value="Female" name="gender"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="inline-2-gender"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Female</label>
                                </div>
                                <div class="flex items-center mr-4">
                                    <input id="inline-3-gender" type="radio" value="Others" name="gender"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="inline-3-gender"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Others</label>
                                </div>
                                <div class="flex items-center mr-4">
                                    <input id="inline-4-gender" type="radio" value="Prefer not to say" name="gender"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="inline-4-gender"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Prefer not
                                        to say</label>
                                </div>
                            </div>
                            <div class="my-5">
                                <p class="font-bold pb-1">How many hours do you spend on the Internet each day?</p>
                                <input required type="number" name="num_hours_day_internet" min="0" max="24" value="0"
                                       class="block text-sm px-4 rounded-lg py-3 border outline-none"/>
                            </div>
                        </div>
                    </div>

                    <!-- Loading view -->
                    <div x-show="step === 6" class="pt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="w-12 h-12 mx-auto stroke-green-500 animate-spin">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        <div class="text-center text-green-500 font-bold my-3 text-xl w-full">
                            Loading
                        </div>
                    </div>

                    <div class="text-center mt-14 mb-10">
                        <div x-show="step !== 6" class="flex flex-row w-full" :class="{'space-x-6' : step > 0}">
                            <div x-show="step > 1">
                                <button type="button" id="back" @click="previous()"
                                        class="py-3 w-64 text-lg text-black bg-gray-300 hover:bg-gray-400 rounded-2xl">
                                    Previous
                                </button>
                            </div>
                            <div x-show="step > 0" class="flex-1"></div>
                            <div :class="{'flex-1 w-full': step === 0}">
                                <button type="button" id="next" @click="next()"
                                        :class="{'w-full' : step === 0, 'w-64' : step > 0}"
                                        class="py-3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Next
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            function load() {
                let num_steps = 5;
                $("#form").show();
                return {
                    step: 1,
                    previous(e) {
                        if (this.step > 1)
                            this.step--;

                        $("#alert").hide();

                        $(window).scrollTop(0);

                        if (this.step < num_steps) {
                            $("#next").text("Next");
                        }
                    },
                    next(e) {
                        let check = true;
                        switch (this.step) {   // manages the different sub-questionnaires
                            case 1:  // Warning questionnaire
                                $("#section-1 :input").each(function () {
                                    if ($(this).attr("name") !== "confusing_words") {
                                        check = ($(this).val() != "" && check);
                                        if ($(this).val() == "") {
                                            $(this).addClass('border-red-500');
                                            $(this).change(_ => $(this).removeClass('border-red-500'));
                                        } else
                                            $(this).removeClass('border-red-500');
                                    }

                                });
                                const questions = ["read_warning", "felt_risk", "actions_warning", "trust_warning"]
                                for (const question in questions) {
                                    let input_answer_i = $('input[name=' + questions[question] + ']');
                                    if (!input_answer_i.is(':checked')) {
                                        check = false;
                                        let parent = input_answer_i.parent().parent()
                                        console.log (input_answer_i)
                                        parent.addClass('border-red-500');
                                        parent.attr('style', 'border-width:1px;')
                                        input_answer_i.change(_ => {
                                            parent.removeClass('border-red-500');
                                            parent.removeAttr('style')
                                        });
                                    } else
                                        check = (check && true);
                                }
                                break;
                            case 2:  // NASA-TLX questionnaire
                                $("#section-2 :input").each(function () {
                                    check = ($(this).val() != "" && check);
                                    if ($(this).val() == "") {
                                        $(this).addClass('border-red-500');
                                        $(this).change(_ => $(this).removeClass('border-red-500'));
                                    } else
                                        $(this).removeClass('border-red-500');
                                });
                                break;
                            case 3:  // Need for Cognition questionnaire
                                $("#section-3 :input").each(function () {
                                    check = ($(this).val() != "" && check);
                                    if ($(this).val() == "") {
                                        $(this).addClass('border-red-500');
                                        $(this).change(_ => $(this).removeClass('border-red-500'));
                                    } else
                                        $(this).removeClass('border-red-500');
                                });
                                break;
                            case 4:  // Cybersecurity skills questionnaire
                                for (let i = 1; i <= 11; i++) {  // 10 questions + attention check question
                                    let input_answer_i = $('input[name=cyber_' + i + ']');
                                    if (!input_answer_i.is(':checked')) {
                                        check = false;
                                        input_answer_i.parent().removeClass('border-gray-300');
                                        input_answer_i.parent().addClass('border-red-500');
                                        input_answer_i.change(_ => {
                                            input_answer_i.parent().removeClass('border-red-500');
                                            input_answer_i.parent().addClass('border-gray-300');
                                        });
                                    } else
                                        check = (check && true);
                                }
                                break;
                            case 5:  // Demographics questionnaire
                                let input_gender = $("input[name=gender]")
                                if (!input_gender.is(':checked')) {
                                    check = false;
                                    input_gender.removeClass('border-gray-300');
                                    input_gender.addClass('border-red-500');
                                    input_gender.change(_ => {
                                        input_gender.removeClass('border-red-500');
                                        input_gender.addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);

                                let input_age = $('input[name=age]')
                                if (input_age.val() < 10 || input_age.val() > 100) {
                                    check = false;
                                    input_age.removeClass('border-gray-300');
                                    input_age.addClass('border-red-500');
                                    input_age.change(_ => {
                                        input_age.removeClass('border-red-500');
                                        input_age.addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);

                                let input_hours = $('input[name=num_hours_day_internet]')
                                if (input_hours.val() < 0) {
                                    check = false;
                                    input_hours.removeClass('border-gray-300');
                                    input_hours.addClass('border-red-500');
                                    input_hours.change(_ => {
                                        input_hours.removeClass('border-red-500');
                                        input_hours.addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);

                                break;
                        }

                        if (!check) {
                            let alert = $("#alert")
                            alert.show();
                            alert.fadeTo("fast", 1, function () {
                                setTimeout(function () {
                                    alert.fadeTo("fast", 0, function () {
                                        alert.hide();
                                    });
                                }, 10000);
                            });

                        }

                        if (this.step !== num_steps+1 && check) {
                            this.step++;
                            $("#alert").hide();
                        }

                        $(window).scrollTop(0);

                        if (this.step === num_steps) {
                            $("#next").text("Finish");
                        }

                        if (this.step === num_steps+1) {
                            $("#form").submit();
                        }
                    }
                }
            }
        </script>
    </x-slot>
</x-app-layout>
