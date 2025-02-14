<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="pt-12 px-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sky-900">
                        <!-- Questionnaire -->
                        <form class="pb-4" action="{{ route('save-final-data') }}" method="POST">
                            @csrf
                            @method('post')

                            <p class="text-3xl font-bold text-center mb-4 cursor-pointer">
                                Complete the final data of the study</p>
                             <!-- DEMOGRAPHICS -->
                            <div class="pt-1">
                                <div class="my-5">
                                    <p class="font-bold pb-1">What is your ©PROLIFIC ID?</p>
                                    <input required type="text" name="prolific_id" min="10" max="100" placeholder="Prolific ID"
                                        class="block text-sm px-4 rounded-lg py-3 border outline-none"/>
                                </div>
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
                                <div class="flex w-full justify-center">
                                    <button type="submit" id="submit"
                                            class="py-3 w-1/3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">Submit
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>

<!-- Error modal -->
<x-modal name="error-modal" id="error-modal" title="Compile all the questions!" :show="false">
    <div class="p-4 rounded-lg relative text-center">
        <p class="text-2xl font-semibold text-red-700 pb-8">@lang('questionnaire-campaign.bfi2xs.compileError')</p>
        <x-primary-button x-on:click="$dispatch('close')">Close</x-primary-button>
    </div>
</x-modal>
