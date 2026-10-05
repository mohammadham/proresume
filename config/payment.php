<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Digest
    |--------------------------------------------------------------------------
    |
    | Recipient of the payment:digest failure email. Falls back to the first
    | admins-table email when empty.
    |
    */

    'digest_recipient' => env('PAYMENT_DIGEST_RECIPIENT', ''),

];