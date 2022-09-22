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

namespace Tygh\Addons\VendorPanelConfigurator\HookHandlers;

use Tygh\Enum\UserTypes;

class MenuHookHandler
{
    /** @var array<string, string> */
    protected $menu_schema_names = [
        UserTypes::ADMIN         => 'menu',
        UserTypes::VENDOR        => 'menu_vendor',
        self::FALLBACK_USER_TYPE => 'menu',
    ];

    const FALLBACK_USER_TYPE = '__default';

    /** @var string */
    protected $user_type = UserTypes::ADMIN;

    /**
     * MenuHookHandler constructor.
     *
     * @param string $user_type Current user type
     */
    public function __construct($user_type)
    {
        $this->user_type = $user_type;
    }

    /**
     * The "backend_menu_get_schema_name_post" hook handler.
     *
     * Actions performed:
     * - Replaces schema to load menu from.
     *
     * @param string $menu_schema_name Menu schema name to use
     *
     * @return void
     *
     * @see \Tygh\BackendMenu::getSchemaName()
     */
    public function onAfterGetSchemaName(&$menu_schema_name)
    {
        $menu_schema_name = isset($this->menu_schema_names[$this->user_type])
            ? $this->menu_schema_names[$this->user_type]
            : $this->menu_schema_names[self::FALLBACK_USER_TYPE];
    }

    /**
     * The "backend_menu_generate_after_process_item" hook handler.
     *
     * Actions performed:
     * - Moves nested items from the menu to the top level menu when menu has only one nested item.
     * - Removes subitems from menu items that would not be moved to the upper level.
     *
     * @param string                                      $group Menu group (top, central)
     * @param string                                      $root  Menu parent
     * @param array<string, string|array<string, string>> $items Nested menu items
     *
     * @return void
     *
     * @see \Tygh\BackendMenu::generate()
     */
    public function onAfterGenerateItem($group, $root, array &$items)
    {
        if (
            !UserTypes::isVendor($this->user_type)
            || $group !== 'central'
            || !isset($items['items'])
        ) {
            return;
        }
        if (count($items['items']) === 1) {
            // Menus with single nested item are useless. Unwrap them into top-level menu
            $items = $this->movesMenuItemToUpperLevel($items);
        } else {
            $items = $this->removesMenuSubitems($items);
        }
    }


    /**
     * Moves nested items from the menu to the top level menu when menu has only one nested item.
     *
     * @param array<string, string|array<string, string>> $items Nested menu items
     *
     * @return array<string, string|array<string, string>>
     */
    private function movesMenuItemToUpperLevel(array $items)
    {
        $root_item = reset($items['items']);
        $possible_title = key($items['items']);
        $root_item['items'] = [];
        if (isset($items['position'])) {
            $root_item['position'] = $items['position'];
        }
        if (isset($root_item['root_title'])) {
            $root_item['title'] = $root_item['root_title'];
        } elseif (!isset($root_item['title'])) {
            $root_item['title'] = __($possible_title);
        }

        return $root_item;
    }

    /**
     * Moves nested items from the menu to the top level menu when menu has only one nested item.
     *
     * @param array<string, string|array<string, string>> $items Nested menu items
     *
     * @return array<string, string|array<string, string>>
     */
    private function removesMenuSubitems(array $items)
    {
        if (!is_array($items['items'])) {
            return $items;
        }
        foreach ($items['items'] as &$second_level_item) {
            unset($second_level_item['subitems']);
        }
        unset($second_level_item);

        return $items;
    }
}
