<?php
return [
    'search_payment_status' => [
        'B' => 'Beneficiary ID',
        'A' => 'Application ID',
        'S' => 'Sasthyasathi Card',
    ],
    'de_activation_reason' => [
        '1' => 'Bank Information Not Valid',
        '2' => 'Duplicate Ration Card',
        '4' => 'Duplicate Swasthyasathi Card No.',
        '5' => 'Duplicate Aadhaar',
        '3' => 'Others',
    ],
    'failed_type' => [
        '1' => 'Account Validation',
        '2' => 'Payment',
	'3' => 'Name Validation',
	'4' => 'Name Validation'
    ],
    'pmt_mode' => [
        '1' => 'Bandhan Bank',
        '2' => 'SBI',
    ],
    'lot_status' => [
        'R' => 'Beneficiary Ready for Lot',
        'G' => 'Beneficiary Lot Generated',
        'P' => 'Beneficiary Lot Pushed (Yet to receive payment response from bank)',
        'S' => 'Payment Success',
        'F' => 'Payment Failure',
        'E' => 'Lot Failed And Bank Edited',
    ],
    'acc_validated' => [
        '0' => 'Ready for account validation',
        '1' => 'Validation Lot Generated (Yet to receive validation response from bank)',
        '2' => 'Validation Success. Ready For Payment',
        '3' => 'Validation Error',
        '4' => 'Payment Transaction Error',
        '5' => 'Validation Lot Generated (Yet to receive validation response from bank)',
        '6' => 'Validation Success. Ready For Payment',
        '7' => 'Validation Error',
        '8' => 'Payment Transaction Error',
    ],
    'ben_status' => [
        '0' => 'Duplicate aadhar beneficiary',
        '1' => 'Active beneficiary',
        '9' => 'DOB or name or ss_card is null',
        '-99' => 'Deactivate Stop Beneficiary',
        '-98' => 'Rejected for duplicate bank information',
        '-97' => 'Marking as duplicate bank information',
        '-102' => 'Under caste modification',
        '-400' => 'Application rejected due to major misamtch account information',
        '-30' => 'Deactivate Stop Beneficiary',
        '-94' => 'Beneficiary Payment has been Suspended due to Death case (As per the data Comes from Janma-Mrityu Portal)'
    ],
    'edited_status' => [
        '0' => 'Not edited',
        '1' => 'Edited',
        '2' => 'Bank Details Approved',
        '9' => 'Forcefully update for failed records because of invalid response from bandhan bank',
    ],
    'next_level_role_id' => [
        '0' => 'Approved Bank Details',
        '1' => 'Verified Bank Details',
        '2' => 'Reverted Bank Details',
        '9' => 'Forcefully update for failed records because of invalid response from bandhan bank',
    ],
];
