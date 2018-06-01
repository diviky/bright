<?php

return [
    /*
     |----------------------------------------------------------------------------
     | Enable sending sms
     |----------------------------------------------------------------------------
     |
     | You can enable or disble sending sms in real. All Sms will be logged
     | if logging enabled in sms log table in database.
     */
    'enable' => false,

    /*
     |----------------------------------------------------------------------------
     | Log configuration
     |----------------------------------------------------------------------------
     |
     | Enable or disable the sms logging. Each sms sent from the system will
     | be logged in database in addon_email_logs table.
     | You can able to disable the logging of perticular mails by providing the
     | tag log => false in while sending sms
     */
    'log' => true,

    /*
     |----------------------------------------------------------------------------
     | Default Mail service provider
     |----------------------------------------------------------------------------
     |
     | Set the default sms service provider here. can be overwrite for each sms
     | by specifying the provider tag
     |
     */
    'provider' => '',
    /*
     |
     | Enable checking black listing table before sending an sms
     | If number exists in the list mail will not be sent to that number.
     |
     */
    'blacklist' => false,

    /*
     |----------------------------------------------------------------------------
     | From Configuration
     |----------------------------------------------------------------------------
     |
     | Change the values to reflect from name & from email address.
     | From can be overwrite for each sms or for provider
     */
    'from' => 'SENDER',

    /*
     |----------------------------------------------------------------------------
     | List of SMS service providers
     |----------------------------------------------------------------------------
     |
     | Here you may configure the multiple sms providers
     */
    'providers' => [

    ],
];
