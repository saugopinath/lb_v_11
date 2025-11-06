@extends('layouts.public')

@section('content')
  <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 p-2  px-4 rounded shadow-sm"
        style="background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%); color: white;">
        <div>
            <h1 class="h4 mb-0 fw-bold">Terms & Conditions</h1>
        </div>

        <a href="{{ url('/login') }}"
            class="btn btn-light rounded-circle d-flex align-items-center justify-content-center shadow-sm"
            style="width: 36px; height: 36px;"
            title="Back to Home">
            <i class="fas fa-arrow-left text-primary fa-lg"></i>
        </a>
    </div>
    <section id="p_text" class="mt-2">
                <blockquote class="blockquote text-center">
                    <p id="p_text_1">
                        In case of any variance between what is stated and that contained in the relevant Acts, Rules, Regulations, Policy, Statements, etc, the latter shall prevail. Under no circumstances will Respective Departments be liable for any expense, loss or damage including, without limitation, indirect or consequential loss or damage, or any expense, loss or damage whatsoever arising from use, or loss of use, of data, arising out of or in connection with the use of this website. These terms and conditions shall be governed by and construed in accordance with the Indian Laws. Any dispute arising under these terms and conditions shall be subject to the jurisdiction of the courts of India. The information posted on this website could include hypertext links or pointers to information created and maintained by non-Government / private organizations. Respective Departments is providing these links and pointers solely for your information and convenience. When you select a link to an This website is designed, developed and maintained by National Informatics Centre (NIC) and content provided by Respective Departments for the information to general public. The documents and information displayed in this website are for reference purposes only and do not purport to a legal document. Though all efforts have been made to ensure the accuracy and currency of the content on this website, the same should not be construed as a statement of law or used for any legal purposes. In case of any ambiguity or doubts, users are advised to verify / check with the Department(s) and / or other source(s), and to obtain appropriate professional advice before use of information. You are leaving the Jai Bangla website and are subject to the privacy and security policies of the owners / sponsors of the outside website. Respective Departments does not guarantee the availability of such linked pages at all times. Respective Departments cannot authorize the use of copyrighted materials contained in linked websites. Users are advised to request such authorization from the owner of the linked website. Respective Departments does not guarantee that linked websites comply with Indian Government Web Guidelines. Respective Departments neither endorses in any way nor offers any judgment or warranty and accepts no responsibility or liability for the authenticity, availability of any of the goods or services or for any damage, loss or harm, directly or consequential or any violation of international or local laws that may be incurred by your visiting and transacting on these websites.
                    </p>
                </blockquote>
            </section>
        </div>
@endsection