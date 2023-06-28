<x-app-layout>
    <x-slot name="slot">
       <div class="min-h-screen bg-gray-200 flex justify-center items-center">
            <div class="p-12 m-10 bg-white rounded-2xl shadow-xl z-20">
                <div class="text-left font-bold my-3 text-3xl w-full">
                    Thank you for accepting our invitation to participate in this study
                </div>
                <p class="text-xl max-w-4xl text-left pt-4 font-bold capitalize">
                   Informed Consent
                </p>
                <div class="text-lg pt-3 font-bold italic"> What this study is about </div>
                <p class="text-xl max-w-4xl text-left pt-4">
                    In this study, we ask you to imagine being Andrea, a 28 years old guy who lives in Rome, Italy. <br>
                    Andrea uses various social networks, among which are Instagram, Facebook, Twitter, and TikTok. Moreover, Andrea uses eBay and Amazon to make online purchases with his Italian credit card. Andrea loves music and goes to live concerts monthly. <br/>
                    Andrea works for an IT company and has accepted to test a new email client that his company has recently adopted. To test the new email client, Andrea must interact with it, by reading all his emails in the inbox and checking that the links in them, if any, are working.
                </p>

                <div class="text-lg pt-3 font-bold italic"> Participation in the study and reward</div>
                <p class="text-xl max-w-4xl text-left pt-4">
                    Participation in the study takes about 20 minutes. You may take a break or leave the study at any time without having to give a reason. You may also withdraw from the study after the session by contacting the researcher. <br>
                    Participation in the study will be rewarded according to the policies of Prolific. In this case, we set an average payment of 9,30£/hr.
                </p>

                <div class="text-lg pt-3 font-bold italic"> How we will use the questionnaire data  </div>
                <p class="text-xl max-w-4xl text-left pt-4">
                    Only the scientific head of the research (Prof. Giuseppe Desolda) and his assigned collaborators at the IVU Lab (Dr. Francesco Greco, Prof. Antonio Piccinno and Prof. Paolo Buono) can access the results of the study. We may publish research reports, articles or training content that include these results. <br>
                    If the information obtained is used for any reason, we will not provide any details that would allow third parties to identify you, nor will we use such information in a way that would harm you.
                </p>

                <div class="text-lg pt-3 font-bold italic"> Retention of personal information and session data </div>
                <p class="text-xl max-w-4xl text-left pt-4">
                    We will retain and process your personal information and session data until we deem the content no longer necessary for the research purposes described above. This data will not include any of your personal information. <br>
                    If you wish to withdraw your consent in the future, please contact the principal investigator listed below, who will destroy all data collected as part of this research. In this case, please provide your identification number in Prolific.
                </p>

                <div class="text-lg pt-3 font-bold italic">Consent</div>
                <div class="text-lg" style="font-style: italic;"> Informed Consent for the Collection of Personal Data </div>
                <div class="text-xl max-w-4xl text-left pt-4">
                    <p>
                        I give my voluntary and informed consent for the collection and processing of my personal data during the study conducted by the Interaction Visualization and Usability and UX (IVU) Lab in accordance with the regulations of the General Data Protection Regulation (GDPR). <br>
                    </p>
                    <ul class="list-disc" style="margin: revert; padding: revert;">
                        <li>
                            <i>Purpose of data processing:</i> Personal data collected will be used exclusively for research purposes in the context of this study. This data include:
                            <ul class="list-disc list-inside" style="list-style-type: circle; padding: revert; margin: revert;">
                                <li>age,</li>
                                <li>gender,</li>
                                <li>Internet experience,</li>
                                <li>link clicked,</li>
                                <li>answer to the final questionnaire.</li>
                            </ul>
                        </li>
                        <li>
                            <i>Data controller:</i> IVU Lab, headquartered at Via E. Orabona 4, 70125 Bari (BA), Italy, will act as the data controller and ensure the security and protection of personal data collected during the survey.
                        </li>
                        <li>
                            <i>Legal basis of processing:</i> The processing of personal data will be based on my explicit consent in accordance with Article 6(1)(a) of the GDPR.
                        </li>
                        <li>
                            <i>Rights of the data subject:</i> As a data subject, I have the right to access, rectify, erase, restrict or object to the processing of my personal data. I can exercise these rights by contacting the IVU Lab organization at <a href="mailto:giuseppe.desolda@uniba.it">giuseppe.desolda@uniba.it</a>.
                        </li>
                        <li>
                            <i>Data Retention:</i> My personal data will be retained for the period necessary to achieve the purposes of the study and will subsequently be deleted or anonymized in accordance with applicable data protection regulations.
                        </li>
                        <li>
                            <i>Data Transfer:</i> My personal data may be transferred to third parties involved in data processing and analysis, provided that appropriate measures are taken to ensure the security, confidentiality and anonymization of personal data.
                        </li>
                        <li>
                            <i>Data Confidentiality and Security:</i> IVU Lab is committed to protecting my personal data from unauthorized access, loss, misuse or disclosure by taking appropriate technical and organizational measures to ensure data security.
                        </li>
                        <li>
                            <i>Voluntary Consent:</i> I confirm that my participation in the study is voluntary and that I fully understand the purposes, data subject rights and data protection information provided in this informed consent.
                        </li>
                    </ul>
                </div>

                <div class="text-center mt-10">
                    <div class="w-full">
                        <div class="flex flex-row">
                            <button style="margin: 0 1rem;" onclick="window.location.replace('{{ route('no_consent') }}')"
                                    class="py-3 w-1/2 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl"> DISAGREE
                            </button>
                            <button style="margin: 0 1rem;" onclick="window.location.replace('{{ route('consent') }}')"
                                    class="py-3 w-1/2 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl"> AGREE
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>
