<x-app-layout>
    <x-slot name="slot">
        <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="py-12 px-12 bg-white rounded-2xl shadow-xl z-20">
                <form action="{{ Request::url() }}" id="emailform" method="POST">
                    @csrf
                    <div>
                        <h1 class="text-xl text-center mb-4">@if(!isset($_GET['nolink']))For the purpose of the study you will not visit the destination <br/>website but we ask you to answer the following questions. <br/>Then you will be redirected to the inbox page. @else Before continuing and coming back to the test, answer these questions @endif</h1>
                    </div>
                    <!--
                    <div class="my-5">
                        <p class="font-bold pb-1">What is the title of the email?</p>
                        <input type="text" name="title_email" placeholder="Answer here..." class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required/>
                    </div>
                    -->
                    <div class="my-5">
                        <p class="font-bold pb-1">Who was the sender of the email?</p>
                        <input type="text" name="sender_email" placeholder="Answer here..." class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required/>
                    </div>
                    @if(!isset($_GET['noquest']))
                    <div class="my-5">
                        <p class="font-bold pb-1">Were there links in the email?</p>
                        <input type="text" name="how_many_hyperlinks" placeholder="Answer here..." class="block text-sm px-4 rounded-lg py-3 w-full border outline-none" required/>
                    </div
                    @else
                        <!--<input type="hidden" name="title_email" value="_"/>-->
                        <input type="hidden" name="how_many_hyperlinks" value="-"/>
                    @endif
                    <div class="text-center mt-6">
                        <button id="submit_btn" class="py-3 w-64 text-xl text-white bg-blue-500 rounded-2xl">Continue the study</button>
                    </div>
                </form>
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            let form_submitted = false;
            $("#emailform").on('submit', (e) => {
                if(!form_submitted){
                    form_submitted = true;
                }else{
                    e.preventDefault();
                }
                $("#submit_btn").prop("disabled",true);
            });
        </script>
    </x-slot>
</x-app-layout>
