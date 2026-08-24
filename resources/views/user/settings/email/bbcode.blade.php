<div class="col-lg-5">
    <table class="table table-striped" style="border: 1px solid #0000005a;">
        <thead>
            <tr>
                <th scope="col">{{ __('Short Code') }}</th>
                <th scope="col">{{  __('Meaning') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{customer_name}</td>
                <td scope="row">{{ __('Name of The Customer') }}</td>
            </tr>

            @if ($templateInfo->email_type == 'email_verification')
                <tr>
                    <td>{verification_link}</td>
                    <td scope="row">{{ __('Email Verification Link') }}</td>
                </tr>
            @endif

            @if ($templateInfo->email_type == 'appointment_booking_notification')
                <tr>
                    <td>{sl_no}</td>
                    <td scope="row">{{ __('Appointment Serial Number') }}
                    </td>
                </tr>
                <tr>
                    <td>{booking_date}</td>
                    <td scope="row">{{  __('Appointment Booking Date') }}</td>
                </tr>
                <tr>
                    <td>{booking_time}</td>
                    <td scope="row">{{ __('Appointment Booking Time') }}</td>
                </tr>
                <tr>
                    <td>{total_fee}</td>
                    <td scope="row">{{__('Appointment Total Fee') }}</td>
                </tr>
                <tr>
                    <td>{paid}</td>
                    <td scope="row">{{  __('Paid Amount') }}</td>
                </tr>
                <tr>
                    <td>{due}</td>
                    <td scope="row">{{ __('Due Amount') }}</td>
                </tr>
                <tr>
                    <td>{category}</td>
                    <td scope="row">{{  __('Appointment Category') }}</td>
                </tr>
            @endif


            @if ($templateInfo->email_type == 'reset_password')
                <tr>
                    <td>{password_reset_link}</td>
                    <td scope="row">{{ __('Password Reset Link') }}</td>
                </tr>
            @endif
            <tr>
                <td>{website_title}</td>
                <td scope="row">{{ __('Website Title') }}</td>
            </tr>
        </tbody>
    </table>
</div>
