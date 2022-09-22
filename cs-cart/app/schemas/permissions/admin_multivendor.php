<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

/** @var array $schema */
$schema['companies'] = [
    'modes' => [
        'manage' => [
            'permissions' => ['GET' => 'view_vendors', 'POST' => 'manage_vendors'],
        ],
        'add' => [
            'permissions' => 'manage_vendors',
        ],
        'invite' => [
            'permissions' => 'manage_vendors',
        ],
        'invitations' => [
            'permissions' => 'manage_vendors',
        ],
        'm_delete_invitations' => [
            'permissions' => 'manage_vendors',
        ],
        'delete_invitation' => [
            'permissions' => 'manage_vendors',
        ],
        'update' => [
            'permissions' => ['GET' => 'view_vendors', 'POST' => 'manage_vendors'],
        ],
        'get_companies_list' => [
            'permissions' => 'view_vendors',
        ],
        'payouts_m_delete' => [
            'permissions' => 'manage_payouts',
        ],
        'payouts_add' => [
            'permissions' => 'manage_payouts',
        ],
        'payout_delete' => [
            'permissions' => 'manage_payouts',
        ],
        'update_payout_comments' => [
            'permissions' => 'manage_payouts',
        ],
        'balance' => [
            'permissions' => 'view_payouts',
        ],
        'payouts' => [
            'permissions' => 'manage_payouts',
        ],
        'm_delete_payouts' => [
            'permissions' => 'manage_payouts',
        ],
        'merge' => [
            'permissions' => 'merge_vendors',
            'condition' => [
                'operator' => 'and',
                'function' => ['fn_check_current_user_access', 'manage_vendors'],
            ],
        ],
    ],
    'permissions' => 'manage_vendors',
];

$schema['exim']['modes']['export']['param_permissions']['section']['vendors'] = 'view_vendors';
$schema['exim']['modes']['import']['param_permissions']['section']['vendors'] = 'manage_vendors';

$schema['shippings']['modes']['apply_to_vendors'] = [
    'permissions' => 'manage_vendors',
];

return $schema;
