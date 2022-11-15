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
                        <h1 x-show="step !== 0 && step !== 6"
                            class="text-3xl font-bold text-center mb-4 cursor-pointer">Post-test questionnaire</h1>
                    </div>
                    <!--- DISCLAIMER SECTION 0 --->
                    <div x-show="step === 0" id="section-0">
                        <div class="text-3xl font-bold text-left mb-5 cursor-pointer">Thank you for completing our
                            test.
                        </div>
                        <p class="text-xl max-w-2xl text-left pt-3">
                            When we invited you to participate in the study, we told you that the purpose of the study
                            was to evaluate an email client. However, there is another purpose that we did not tell you
                            so as not to influence your actions. Indeed, this test aimed also to assess the inclusion of
                            warning message in the email client.
                        </p>
                        <p class="text-xl max-w-2xl text-left pt-4">
                            To complete this study, we ask you to go ahead and fill in the final questionnaire on the
                            warning messages you have seen during the interaction.
                        </p>
                    </div>
                    <!--- SECTION 1 --->
                    <div x-show="step === 1" id="section-1">
                        <p class="text-2xl w-full text-center">Section 1 of 5</p>
                        <div class="my-5">
                            <p class="font-bold pb-1">Which parts of the email seemed suspicious?</p>
                            <input required type="text" name="parts_email_suspicious"
                                   placeholder="Type your answer here..."
                                   class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"/>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">How well do you know {{$vendor_1}}?</p>
                            <label for="steps-range"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    1
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    5
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="know_email_1" value="1" min="1" max="5" step="1"
                                       name="know_email_1" class="w-full">
                                <datalist id="know_email_1">
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">How well do you know {{$vendor_2}}?</p>
                            <label for="steps-range"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    1
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    5
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="know_email_2" value="1" min="1" max="5" step="1"
                                       name="know_email_2" class="w-full">
                                <datalist id="know_email_2">
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                </datalist>
                            </div>
                        </div>
                    </div>
                    <!--- SECTION 2 --->
                    <div x-show="step === 2" id="section-2">
                        <p class="text-2xl w-full text-center">Section 2 of 5</p>
                        <p class="text-lg text-left pt-5">
                            During the study you were exposed to this warning message that alerted you against phishing
                            attacks:
                        </p>
                        <img class="mx-auto my-7 w-2/4"
                             src="{{ url('/assets/img/' . \Illuminate\Support\Facades\Auth::user()->warning_type .'.png') }}">
                        <div class="my-5">
                            <p class="font-bold pb-1">Have you read the warning during the study?</p>
                            <div class="flex">
                                <div class="flex items-center mr-4">
                                    <input id="inline-radio" type="radio" value="1" name="have_read_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="inline-radio"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Yes</label>
                                </div>
                                <div class="flex items-center mr-4">
                                    <input id="inline-2-radio" type="radio" value="0" name="have_read_warning"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                           required>
                                    <label for="inline-2-radio"
                                           class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">No</label>
                                </div>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">How useful was the warning to understand that the link was a
                                scam?</p>
                            <label for="steps-range"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    1
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    5
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="how_warning_useful_identifying_link" value="1" min="1" max="5"
                                       step="1"
                                       name="how_warning_useful_identifying_link" class="w-full">
                                <datalist id="how_warning_useful_identifying_link">
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">How annoying was the warning?</p>
                            <label for="steps-range"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    1
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    5
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="how_annoying_warning_was" value="1" min="1" max="5" step="1"
                                       name="how_annoying_warning_was" class="w-full">
                                <datalist id="how_annoying_warning_was">
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">How much did the warning affect your perception on the genuinity
                                (safe or scam) of the email?</p>
                            <label for="steps-range"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    1
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    5
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="how_warning_perception_link" value="1" min="1" max="5"
                                       step="1"
                                       name="how_warning_perception_link" class="w-full">
                                <datalist id="how_warning_perception_link">
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">How evident was the warning?</p>
                            <label for="steps-range"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex flex-row w-full">
                                <div>
                                    1
                                </div>
                                <div class="flex-1"></div>
                                <div>
                                    5
                                </div>
                            </label>
                            <div class="w-full">
                                <input type="range" list="how_evident_was_warning" value="1" min="1" max="5" step="1"
                                       name="how_evident_was_warning" class="w-full">
                                <datalist id="how_evident_was_warning">
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-5">
                            <p class="font-bold pb-1">How do you think we can improve the warning messages?
                                (optional)</p>
                            <textarea rows="2" name="explanation_feedback" placeholder="Type your answer here..."
                                      class="block text-sm px-4 rounded-lg py-3 w-full border outline-none"></textarea>
                        </div>
                    </div>
                    <!--- SECTION 3 --->
                    <div x-show="step === 3" id="section-3">
                        <p class="text-2xl w-full text-center">Section 3 of 5</p>
                        <div class="my-5">
                            <p class="font-bold pb-1">How mentally demanding was the task?</p>
                            <label for="steps-range"
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
                                <input type="range" list="tick-1" value="5" min="0" max="10" step="1"
                                       name="nasa_mental_demand" class="w-full">
                                <datalist id="tick-1">
                                    <option value="0"></option>
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                    <option value="6"></option>
                                    <option value="7"></option>
                                    <option value="8"></option>
                                    <option value="9"></option>
                                    <option value="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How physically demanding was the task?</p>
                            <label for="steps-range"
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
                                <input type="range" list="tick-2" value="5" min="0" max="10" step="1"
                                       name="nasa_physical_demand" class="w-full">
                                <datalist id="tick-2">
                                    <option value="0"></option>
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                    <option value="6"></option>
                                    <option value="7"></option>
                                    <option value="8"></option>
                                    <option value="9"></option>
                                    <option value="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How hurried or rushed was the pace of the task?</p>
                            <label for="steps-range"
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
                                <input type="range" list="tick-3" value="5" min="0" max="10" step="1"
                                       name="nasa_temporal_demand" class="w-full">
                                <datalist id="tick-3">
                                    <option value="0"></option>
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                    <option value="6"></option>
                                    <option value="7"></option>
                                    <option value="8"></option>
                                    <option value="9"></option>
                                    <option value="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How successful were you in accomplishing what you were asked to
                                do?</p>
                            <label for="steps-range"
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
                                <input type="range" list="tick-4" value="5" min="0" max="10" step="1"
                                       name="nasa_performance" class="w-full">
                                <datalist id="tick-4">
                                    <option value="0"></option>
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                    <option value="6"></option>
                                    <option value="7"></option>
                                    <option value="8"></option>
                                    <option value="9"></option>
                                    <option value="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How hard did you have to work to accomplish your level of
                                performance?</p>
                            <label for="steps-range"
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
                                <input type="range" list="tick-5" value="5" min="0" max="10" step="1" name="nasa_effort"
                                       class="w-full">
                                <datalist id="tick-5">
                                    <option value="0"></option>
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                    <option value="6"></option>
                                    <option value="7"></option>
                                    <option value="8"></option>
                                    <option value="9"></option>
                                    <option value="10"></option>
                                </datalist>
                            </div>
                        </div>
                        <div class="my-6">
                            <p class="font-bold pb-1">How insecure, discouraged, irritated, stressed and annoyed were
                                you?</p>
                            <label for="steps-range"
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
                                <input type="range" list="tick-6" value="5" min="0" max="10" step="1"
                                       name="nasa_frustration_level" class="w-full">
                                <datalist id="tick-6">
                                    <option value="0"></option>
                                    <option value="1"></option>
                                    <option value="2"></option>
                                    <option value="3"></option>
                                    <option value="4"></option>
                                    <option value="5"></option>
                                    <option value="6"></option>
                                    <option value="7"></option>
                                    <option value="8"></option>
                                    <option value="9"></option>
                                    <option value="10"></option>
                                </datalist>
                            </div>
                        </div>
                    </div>
                    <!--- SECTION 4 --->
                    <div x-show="step === 4" id="section-4">
                        <p class="text-2xl w-full text-center">Section 4 of 5</p>
                        <div class="my-8">
                            <p class="font-bold pb-1">What does the “https://” at the beginning of a URL denote, as
                                opposed to
                                http:// (without the “s”)?</p>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_1" type="radio" value="That the
                            site has special high definition" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">That
                                    the
                                    site has special high definition</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_2" type="radio" value="That
                            information entered into the site is encrypted" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">That
                                    information entered into the site is encrypted</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_3" type="radio" value="That the
                            site is the newest version available" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">That
                                    the
                                    site is the newest version available</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_4" type="radio" value="That the
                            site is not accessible to certain computers" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">That
                                    the
                                    site is not accessible to certain computers</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_5" type="radio" value="None of the
                            above" name="cyber_1"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_0_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">None
                                    of the
                                    above</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_0_6" type="radio" value="Not sure" name="cyber_1"
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
                                <input id="cb_1-1" type="radio" value="Sending
                            someone an email that contains a malicious link that is disguised to look like an email from
                            someone the person knows" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Sending
                                    someone an email that contains a malicious link that is disguised to look like an
                                    email from
                                    someone the person knows</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-2" type="radio" value="Creating a
                            fake website that looks nearly identical to a real website in order to trick users into
                            entering their login information" name="cyber_2"
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
                                <input id="cb_1-3" type="radio" value="Sending
                            someone a text message that contains a malicious link that is disguised to look like a
                            notification that the person has won a contest" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Sending
                                    someone a text message that contains a malicious link that is disguised to look like
                                    a
                                    notification that the person has won a contest</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-4" type="radio" value="All of the
                            above" name="cyber_2"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_1-4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">All
                                    of the
                                    above</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_1-5" type="radio" value="Not sure" name="cyber_2"
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
                                <input id="cb_2_1" type="radio" value="Botnet" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Botnet</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_2" type="radio" value="Rootkit" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Rootkit</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_3" type="radio" value="DDoS" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">DDoS</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_4" type="radio" value="Operating system" name="cyber_3"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_2_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Operating
                                    system</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_2_5" type="radio" value="Not sure" name="cyber_3"
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
                                                                        src="{{ url('assets/img/cyber_4_1.png') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_2" type="radio" value="1" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_2" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ url('assets/img/cyber_4_2.webp') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_3" type="radio" value="2" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_3" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ url('assets/img/cyber_4_3.png') }}"></label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_3_4" type="radio" value="3" name="cyber_4"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_3_4" class="w-full"><img class="max-h-64 ml-2"
                                                                        src="{{ url('assets/img/cyber_4_4.png') }}"></label>
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
                                <input id="cb_4_1" type="radio" value="Boat123" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Boat123</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_2" type="radio" value="WTh!5Z" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">WTh!5Z</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_3" type="radio" value="into*48" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">into*48</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_4" type="radio" value="123456" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">123456</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_4_5" type="radio" value="Not sure" name="cyber_5"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_4_5"
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
                                <input id="cb_5_1" type="radio" value="Botnet" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Botnet</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_2" type="radio" value="Ransomware" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Ransomware</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_3" type="radio" value="Driving" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Driving</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_4" type="radio" value="Spam" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Spam</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_5" type="radio" value="None of the above" name="cyber_6"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_5_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">None
                                    of the
                                    above</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_5_6" type="radio" value="Not sure" name="cyber_6"
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
                                <input id="cb_6_1" type="radio" value="Yes" name="cyber_7"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_6_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Yes</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_6_2" type="radio" value="No" name="cyber_7"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_6_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">No</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_6_3" type="radio" value="Not sure" name="cyber_7"
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
                                <input id="cb_7_1" type="radio" value="True" name="cyber_8"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_7_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">True</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_7_2" type="radio" value="False" name="cyber_8"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_7_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">False</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_7_3" type="radio" value="Not sure" name="cyber_8"
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
                                <input id="cb_8_1" type="radio" value="Yes, it is safe" name="cyber_9"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_8_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Yes,
                                    it is
                                    safe</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_8_2" type="radio" value="No, it is not safe" name="cyber_9"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_8_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">No,
                                    it is
                                    not safe</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_8_3" type="radio" value="Not sure" name="cyber_9"
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
                                <input id="cb_9_1" type="radio" value="Use of insecure Wi-Fi networks" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_1"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Use
                                    of
                                    insecure Wi-Fi networks</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_2" type="radio" value="Key-logging" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_2"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Key-logging</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_3" type="radio" value="De-anonymization by network operators"
                                       name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_3"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">De-anonymization
                                    by network operators</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_4" type="radio" value="Phishing attacks" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_4"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Phishing
                                    attacks</label>
                            </div>
                            <div
                                class="flex items-center my-1 pl-4 rounded border border-gray-200 dark:border-gray-700">
                                <input id="cb_9_5" type="radio" value="Not sure" name="cyber_10"
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="cb_9_5"
                                       class="py-4 ml-2 w-full text-sm font-medium text-gray-900 dark:text-gray-300">Not
                                    sure</label>
                            </div>
                        </div>
                    </div>
                    <div x-show="step === 5" class="pt-1">
                        <p class="text-2xl w-full text-center">Section 5 of 5</p>
                        <div class="my-5">
                            <p class="font-bold pb-1">How old are you?</p>
                            <input required type="number" name="age" min="14" max="100" value="14"
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
                                <input required type="number" name="num_hours_day_internet" min="0" value="0"
                                       class="block text-sm px-4 rounded-lg py-3 border outline-none"/>
                            </div>
                        </div>
                    </div>
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
                            <div x-show="step > 0">
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
                $("#form").show();
                return {
                    step: 0,
                    previous(e) {
                        if (this.step > 0)
                            this.step--;

                        $("#alert").hide();

                        $(window).scrollTop(0);

                        if (this.step < 5) {
                            $("#next").text("Next");
                        }
                    },
                    next(e) {
                        let check = true;
                        switch (this.step) {
                            case 1:
                                $("#section-1 :input").each(function () {
                                    check = ($(this).val() != "" && check);
                                    if ($(this).val() == "") {
                                        $(this).addClass('border-red-500');
                                        $(this).change(_ => $(this).removeClass('border-red-500'));
                                    } else
                                        $(this).removeClass('border-red-500');
                                });
                                break;
                            case 2:
                                if (!$("input[name=have_read_warning]").is(':checked')) {
                                    check = false;
                                    $("input[name=have_read_warning]").removeClass('border-gray-300');
                                    $("input[name=have_read_warning]").addClass('border-red-500');
                                    $('input[name=have_read_warning]').change(_ => {
                                        $("input[name=have_read_warning]").removeClass('border-red-500');
                                        $("input[name=have_read_warning]").addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);
                                break;

                            case 4:
                                for (let i = 1; i <= 10; i++) {
                                    let input = 'input[name=cyber_' + i + ']';
                                    if (!$(input).is(':checked')) {
                                        check = false;
                                        $(input).parent().removeClass('border-gray-300');
                                        $(input).parent().addClass('border-red-500');
                                        $(input).change(_ => {
                                            $(input).parent().removeClass('border-red-500');
                                            $(input).parent().addClass('border-gray-300');
                                        });
                                    } else
                                        check = (check && true);
                                }
                                break;
                            case 5:
                                if (!$("input[name=gender]").is(':checked')) {
                                    check = false;
                                    $("input[name=gender]").removeClass('border-gray-300');
                                    $("input[name=gender]").addClass('border-red-500');
                                    $('input[name=gender]').change(_ => {
                                        $("input[name=gender]").removeClass('border-red-500');
                                        $("input[name=gender]").addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);

                                if ($('input[name=age]').val() < 14 || $('input[name=age]').val() > 100) {
                                    check = false;
                                    $("input[name=age]").removeClass('border-gray-300');
                                    $("input[name=age]").addClass('border-red-500');
                                    $('input[name=age]').change(_ => {
                                        $("input[name=age]").removeClass('border-red-500');
                                        $("input[name=age]").addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);

                                if ($('input[name=num_hours_day_internet]').val() < 0) {
                                    check = false;
                                    $("input[name=num_hours_day_internet]").removeClass('border-gray-300');
                                    $("input[name=num_hours_day_internet]").addClass('border-red-500');
                                    $('input[name=num_hours_day_internet]').change(_ => {
                                        $("input[name=num_hours_day_internet]").removeClass('border-red-500');
                                        $("input[name=num_hours_day_internet]").addClass('border-gray-300');
                                    });
                                } else
                                    check = (check && true);

                                break;
                        }

                        if (!check) {
                            $("#alert").show();
                            $("#alert").fadeTo("fast", 1, function () {
                                setTimeout(function () {
                                    $("#alert").fadeTo("fast", 0, function () {
                                        $("#alert").hide();
                                    });
                                }, 10000);
                            });

                        }

                        if (this.step !== 6 && check) { //!==5
                            this.step++;
                            $("#alert").hide();
                        }

                        $(window).scrollTop(0);

                        if (this.step === 5) {
                            $("#next").text("Finish");
                        }

                        if (this.step === 6) {
                            $("#form").submit();
                        }
                    }
                }
            }
        </script>
    </x-slot>
</x-app-layout>
