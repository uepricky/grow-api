<?php

namespace App\Services\RegisteredUserService;

use App\Services\{
    RegisteredUserService\RegisteredUserServiceInterface,
    StoreService\StoreServiceInterface,
};
use App\Models\{
    Group,
    User,
    SysMenuCategory,
    GroupRole,
    Permission,
    StoreRole,
};
use App\Repositories\{
    UserRepository\UserRepositoryInterface,
    GroupRepository\GroupRepositoryInterface,
    MenuCategoryRepository\MenuCategoryRepositoryInterface,
    MenuRepository\MenuRepositoryInterface,
    SetMenuRepository\SetMenuRepositoryInterface,
};
use App\Repositories\GroupRoleRepository\GroupRoleRepositoryInterface;
use App\Repositories\StoreRoleRepository\StoreRoleRepositoryInterface;

class RegisteredUserService implements RegisteredUserServiceInterface
{

    const DUMMY_STORE = [
        'group_id' => null,
        'name' => 'ダミー店舗',
        'image_path' => 'example.jpg',
        'address' => 'ダミー住所',
        'postal_code' => '1234567',
        'tel_number' => '123-456-789',
        'opening_time' => '19:00',
        'closing_time' => '00:00',
        'working_time_unit_id' => 4,
        'subscription_id' => null
    ];

    const DUMMY_STORE_DETAIL = [
        'invoice_registration_number' => '123456789',
        'service_rate' => 20,
        'service_rate_digit_id' => 1,
        'service_rate_rounding_method_id' => 1,
        'consumption_tax_rate' => 10,
        'consumption_tax_rate_digit_id' => 1,
        'consumption_tax_rate_rounding_method_id' => 1,
        'consumption_tax_type_id' => 1,
        'user_incentive_digit_id' => 1,
        'user_incentive_rounding_method_id' => 1,
    ];

    const DUMMY_FIRSTSET_MENU_CATEGORY = [
        'sys_menu_category_id' => SysMenuCategory::CATEGORIES['FIRST_SET']['id'],
        'name' => '初回セット',
        'code' => '001'
    ];

    const DUMMY_FIRSTSET_MENU = [
        'menu' => [
            'name' => '初回セット',
            'price' => 3000,
            'insentive_amount' => 1000,
            'code' => '001',
            'display' => true
        ],
        'setMenu' => [
            'minutes' => 50
        ]
    ];

    const DUMMY_EXTENSIONSET_MENU_CATEGORY = [
        'sys_menu_category_id' => SysMenuCategory::CATEGORIES['EXTENSION_SET']['id'],
        'name' => '延長セット',
        'code' => '002'
    ];

    const DUMMY_EXTENSIONSET_MENU = [
        'menu' => [
            'name' => '延長セット',
            'price' => 3000,
            'insentive_amount' => 1000,
            'code' => '002',
            'display' => true
        ],
        'setMenu' => [
            'minutes' => 40
        ]
    ];

    const DUMMY_SELECTION_MENU_CATEGORY = [
        'sys_menu_category_id' => SysMenuCategory::CATEGORIES['SELECTION']['id'],
        'name' => '指名',
        'code' => '003'
    ];

    const DUMMY_DOUHAN_MENU = [
        'name' => '同伴',
        'price' => 3000,
        'insentive_amount' => 1000,
        'code' => '003',
        'display' => true
    ];

    const DUMMY_HONSHIMEI_MENU = [
        'name' => '本指名',
        'price' => 3000,
        'insentive_amount' => 1000,
        'code' => '004',
        'display' => true
    ];

    const DUMMY_JOUNAISHIMEI_MENU = [
        'name' => '場内指名',
        'price' => 2000,
        'insentive_amount' => 500,
        'code' => '005',
        'display' => true
    ];

    const DUMMY_TSUIKASHIMEI_MENU = [
        'name' => '追加指名',
        'price' => 1000,
        'code' => '006',
        'display' => true
    ];

    const DUMMY_BOTTLE_MENU_CATEGORY = [
        'sys_menu_category_id' => SysMenuCategory::CATEGORIES['DRINK_FOOD']['id'],
        'name' => 'ボトル',
        'code' => '004'
    ];

    const DUMMY_BLACKNIKKA_MENU = [
        'name' => 'ブラックニッカ',
        'price' => 5000,
        'insentive_amount' => 1000,
        'code' => '007',
        'display' => true
    ];

    const DUMMY_DRINK_MENU_CATEGORY = [
        'sys_menu_category_id' => SysMenuCategory::CATEGORIES['DRINK_FOOD']['id'],
        'name' => 'ドリンク',
        'code' => '005'
    ];

    const DUMMY_CASTDRINK_MENU = [
        'name' => 'キャストドリンク',
        'price' => 2000,
        'insentive_amount' => 500,
        'code' => '008',
        'display' => true
    ];

    const DUMMY_FOOD_MENU_CATEGORY = [
        'sys_menu_category_id' => SysMenuCategory::CATEGORIES['DRINK_FOOD']['id'],
        'name' => 'フード',
        'code' => '006'
    ];

    const DUMMY_SNACK_MENU = [
        'name' => 'お菓子盛り合わせ',
        'price' => 1000,
        'code' => '009',
        'display' => true
    ];

    const DUMMY_MANAGER_USER = [
        'display_name' => '店長',

    ];

    const DUMMY_STAFF1_USER = [
        'display_name' => 'スタッフ1',
    ];

    const DUMMY_STAFF2_USER = [
        'display_name' => 'スタッフ2',
    ];

    const DUMMY_STAFF3_USER = [
        'display_name' => 'スタッフ3',
    ];

    const DUMMY_CAST1_USER = [
        'display_name' => 'キャスト1',
    ];

    const DUMMY_CAST2_USER = [
        'display_name' => 'キャスト2',
    ];

    const DUMMY_CAST3_USER = [
        'display_name' => 'キャスト3',
    ];

    const DUMMY_CAST4_USER = [
        'display_name' => 'キャスト4',
    ];

    const DUMMY_CAST5_USER = [
        'display_name' => 'キャスト5',
    ];

    const DEFAULT_GROUP_ROLES = [
        "ADMIN" => [
            'name' => '管理者',
            'permissionIds' => [
                Permission::PERMISSIONS['OPERATION_UNDER_GROUP_DASHBOARD']['id']
            ]
        ],
        "GENERAL" => [
            'name' => '一般',
            'permissionIds' => []
        ],
    ];

    public function __construct(
        public readonly UserRepositoryInterface $userRepo,
        public readonly GroupRepositoryInterface $groupRepo,
        public readonly MenuCategoryRepositoryInterface $menuCategoryRepo,
        public readonly MenuRepositoryInterface $menuRepo,
        public readonly SetMenuRepositoryInterface $setMenuRepo,

        public readonly StoreServiceInterface $storeServ,

        public readonly GroupRoleRepositoryInterface $groupRoleRepo,
        public readonly StoreRoleRepositoryInterface $storeRoleRepo,
    ) {
    }

    /**
     * 引数.userを契約者として登録する
     * @param User $user
     * @param string $groupName
     */
    public function registerContractUser(User $user, string $groupName)
    {
        // 契約ユーザー登録
        $this->userRepo->createContractUser($user);

        // グループの作成
        $group = $this->createGroup($groupName);

        // グループ、ユーザーの紐づけ
        $this->userRepo->attachToGroup($user, $group);

        // 管理者のグループロールを作成
        // $adminGroupRole = self::DEFAULT_GROUP_ROLES['ADMIN'];
        // $adminGroupRole['group_id'] = $group->id;
        // $groupRole = $this->groupRoleRepo->createGroupRole($adminGroupRole);

        // デフォルトのグループロールを作成
        foreach (self::DEFAULT_GROUP_ROLES as $defaultGroupRole) {
            $defaultGroupRole['group_id'] = $group->id;
            $groupRole = $this->groupRoleRepo->createGroupRole($defaultGroupRole);
            if ($groupRole->name === self::DEFAULT_GROUP_ROLES['ADMIN']['name']) {
                // 契約者に管理者権限を付与
                $this->userRepo->attachGroupRolesToUser($user, [$groupRole->id]);
            }
            // グループロールに権限を付与
            $this->groupRoleRepo->attachPermissionsToGroupRole($groupRole, $defaultGroupRole['permissionIds']);
        }


        /** ダミーデータ登録 */

        // ダミー店舗作成
        $dummyStore = self::DUMMY_STORE;
        $dummyStore['group_id'] = $group->id;
        $store = $this->storeServ->createStore($dummyStore, self::DUMMY_STORE_DETAIL);

        // ストアロール取得
        $storeRoleManager = $this->storeRoleRepo->getStoreRoleByName($store->id, $this->storeServ::DEFAULT_STORE_ROLES['MANAGER']['name']);
        // ユーザーとストアロールの紐付け
        $this->userRepo->attachStoreRolesToUser($user, [$storeRoleManager->id]);

        /** メニュー系 */
        // ダミーメニューカテゴリ（初回セット）
        $dummyFirstSetMenuCategoryData = array_merge(self::DUMMY_FIRSTSET_MENU_CATEGORY, ['store_id' => $store->id]);
        $dummyFirstSetMenuCategory = $this->menuCategoryRepo->createMenuCategory($dummyFirstSetMenuCategoryData);
        // 初回セットのセットメニュー作成
        $dummyFirstSetMenuData = array_merge(self::DUMMY_FIRSTSET_MENU['menu'], ['menu_category_id' => $dummyFirstSetMenuCategory->id]);
        $createdFirstSetMenu = $this->menuRepo->createMenu($dummyFirstSetMenuData);
        $this->setMenuRepo->createSetMenu($createdFirstSetMenu, self::DUMMY_FIRSTSET_MENU['setMenu']);

        // ダミーメニューカテゴリ（延長セット）
        $dummyExtensionSetMenuCategoryData = array_merge(self::DUMMY_EXTENSIONSET_MENU_CATEGORY, ['store_id' => $store->id]);
        $dummyExtensionSetMenuCategory = $this->menuCategoryRepo->createMenuCategory($dummyExtensionSetMenuCategoryData);
        // 延長セットのセットメニュー作成
        $dummyExtensionSetMenuData = array_merge(self::DUMMY_EXTENSIONSET_MENU['menu'], ['menu_category_id' => $dummyExtensionSetMenuCategory->id]);
        $createdExtensionSetMenu = $this->menuRepo->createMenu($dummyExtensionSetMenuData);
        $this->setMenuRepo->createSetMenu($createdExtensionSetMenu, self::DUMMY_EXTENSIONSET_MENU['setMenu']);

        // ダミーメニューカテゴリ（指名）
        $dummySelectionMenuCategoryData = array_merge(self::DUMMY_SELECTION_MENU_CATEGORY, ['store_id' => $store->id]);
        $dummySelectionMenuCategory = $this->menuCategoryRepo->createMenuCategory($dummySelectionMenuCategoryData);
        // 同伴のセットメニュー作成
        $dummyDouhanMenuData = array_merge(self::DUMMY_DOUHAN_MENU, ['menu_category_id' => $dummySelectionMenuCategory->id]);
        $this->menuRepo->createMenu($dummyDouhanMenuData);
        // 本指名
        $dummyHonshimeiMenuData = array_merge(self::DUMMY_HONSHIMEI_MENU, ['menu_category_id' => $dummySelectionMenuCategory->id]);
        $this->menuRepo->createMenu($dummyHonshimeiMenuData);
        // 場内指名
        $dummyJounaiMenuData = array_merge(self::DUMMY_JOUNAISHIMEI_MENU, ['menu_category_id' => $dummySelectionMenuCategory->id]);
        $this->menuRepo->createMenu($dummyJounaiMenuData);
        // 追加指名
        $dummyTsuikaMenuData = array_merge(self::DUMMY_TSUIKASHIMEI_MENU, ['menu_category_id' => $dummySelectionMenuCategory->id]);
        $this->menuRepo->createMenu($dummyTsuikaMenuData);

        // ダミーメニューカテゴリ（ボトル）
        $dummyBottleMenuCategoryData = array_merge(self::DUMMY_BOTTLE_MENU_CATEGORY, ['store_id' => $store->id]);
        $dummyBottleMenuCategory = $this->menuCategoryRepo->createMenuCategory($dummyBottleMenuCategoryData);
        // ブラックニッカ
        $dummyBlacknikkaMenuData = array_merge(self::DUMMY_BLACKNIKKA_MENU, ['menu_category_id' => $dummyBottleMenuCategory->id]);
        $this->menuRepo->createMenu($dummyBlacknikkaMenuData);

        // ダミーメニューカテゴリ（ドリンク）
        $dummyDrinkMenuCategoryData = array_merge(self::DUMMY_DRINK_MENU_CATEGORY, ['store_id' => $store->id]);
        $dummyDrinkMenuCategory = $this->menuCategoryRepo->createMenuCategory($dummyDrinkMenuCategoryData);
        // キャストドリンク
        $dummyCastDrinkMenuData = array_merge(self::DUMMY_CASTDRINK_MENU, ['menu_category_id' => $dummyDrinkMenuCategory->id]);
        $this->menuRepo->createMenu($dummyCastDrinkMenuData);

        // ダミーメニューカテゴリ（フード）
        $dummyFoodMenuCategoryData = array_merge(self::DUMMY_FOOD_MENU_CATEGORY, ['store_id' => $store->id]);
        $dummyFoodMenuCategory = $this->menuCategoryRepo->createMenuCategory($dummyFoodMenuCategoryData);
        // お菓子盛り合わせ
        $dummySnackMenuData = array_merge(self::DUMMY_SNACK_MENU, ['menu_category_id' => $dummyFoodMenuCategory->id]);
        $this->menuRepo->createMenu($dummySnackMenuData);

        /** ダミーユーザー作成 */
        // TODO: リファクタ

        // グループロール一般を取得
        $generalGroupRole = $this->groupRoleRepo->getGroupRoleByName(
            $group->id,
            self::DEFAULT_GROUP_ROLES['GENERAL']['name']
        );

        // マネージャ
        // ユーザー作成
        $managerUser = $this->userRepo->createGeneralUser(self::DUMMY_MANAGER_USER, ['can_login' => false]);
        // ユーザーをグループに所属させる※削除予定
        $this->userRepo->attachToGroup($managerUser, $group);
        // ユーザーにグループロール"一般"を付与する
        $this->userRepo->attachGroupRolesToUser($managerUser, [$generalGroupRole->id]);
        // ユーザーを店舗に所属させる
        $this->userRepo->attachToStores($managerUser, [$store->id]);
        // ストアロール取得
        $storeRoleManager = $this->storeRoleRepo->getStoreRoleByName($store->id, $this->storeServ::DEFAULT_STORE_ROLES['MANAGER']['name']);
        // ユーザーとストアロールの紐付け
        $this->userRepo->attachStoreRolesToUser($managerUser, [$storeRoleManager->id]);

        // スタッフ1
        $staff1User = $this->userRepo->createGeneralUser(self::DUMMY_STAFF1_USER, ['can_login' => false]);
        // ユーザーをグループに所属させる※削除予定
        $this->userRepo->attachToGroup($staff1User, $group);
        // ユーザーにグループロール"一般"を付与する
        $this->userRepo->attachGroupRolesToUser($staff1User, [$generalGroupRole->id]);
        // ユーザーを店舗に所属させる
        $this->userRepo->attachToStores($staff1User, [$store->id]);
        // ストアロール取得
        $storeRoleStaff = $this->storeRoleRepo->getStoreRoleByName($store->id, $this->storeServ::DEFAULT_STORE_ROLES['STAFF']['name']);
        // ユーザーとストアロールの紐付け
        $this->userRepo->attachStoreRolesToUser($staff1User, [$storeRoleStaff->id]);

        // // スタッフ2
        // $staff2User = $this->userRepo->createGeneralUser(self::DUMMY_STAFF2_USER, ['can_login' => false]);
        // // ユーザーをグループに所属させる
        // $this->userRepo->attachToGroup($staff2User, $group);
        // // ユーザーとグループロールの紐付け
        // $this->roleRepo->attachGroupRolesToUser($staff2User, [$generalGroupRole->id]);
        // // ユーザーを店舗に所属させる
        // $this->userRepo->attachToStores($staff2User, [$store->id]);
        // // ユーザーとストアロールの紐付け
        // $this->roleRepo->attachStoreRolesToUser($staff2User, [$staffStoreRole->id]);

        // // スタッフ3
        // $staff3User = $this->userRepo->createGeneralUser(self::DUMMY_STAFF3_USER, ['can_login' => false]);
        // // ユーザーをグループに所属させる
        // $this->userRepo->attachToGroup($staff3User, $group);
        // // ユーザーとグループロールの紐付け
        // $this->roleRepo->attachGroupRolesToUser($staff3User, [$generalGroupRole->id]);
        // // ユーザーを店舗に所属させる
        // $this->userRepo->attachToStores($staff3User, [$store->id]);
        // // ユーザーとストアロールの紐付け
        // $this->roleRepo->attachStoreRolesToUser($staff3User, [$staffStoreRole->id]);

        // // キャスト1
        // $cast1User = $this->userRepo->createGeneralUser(self::DUMMY_CAST1_USER, ['can_login' => false]);
        // // ユーザーをグループに所属させる
        // $this->userRepo->attachToGroup($cast1User, $group);
        // // ユーザーとグループロールの紐付け
        // $this->roleRepo->attachGroupRolesToUser($cast1User, [$generalGroupRole->id]);
        // // ユーザーを店舗に所属させる
        // $this->userRepo->attachToStores($cast1User, [$store->id]);
        // // ユーザーとストアロールの紐付け
        // $this->roleRepo->attachStoreRolesToUser($cast1User, [$castStoreRole->id]);

        // // キャスト2
        // $cast2User = $this->userRepo->createGeneralUser(self::DUMMY_CAST2_USER, ['can_login' => false]);
        // // ユーザーをグループに所属させる
        // $this->userRepo->attachToGroup($cast2User, $group);
        // // ユーザーとグループロールの紐付け
        // $this->roleRepo->attachGroupRolesToUser($cast2User, [$generalGroupRole->id]);
        // // ユーザーを店舗に所属させる
        // $this->userRepo->attachToStores($cast2User, [$store->id]);
        // // ユーザーとストアロールの紐付け
        // $this->roleRepo->attachStoreRolesToUser($cast2User, [$castStoreRole->id]);

        // // キャスト3
        // $cast3User = $this->userRepo->createGeneralUser(self::DUMMY_CAST3_USER, ['can_login' => false]);
        // // ユーザーをグループに所属させる
        // $this->userRepo->attachToGroup($cast3User, $group);
        // // ユーザーとグループロールの紐付け
        // $this->roleRepo->attachGroupRolesToUser($cast3User, [$generalGroupRole->id]);
        // // ユーザーを店舗に所属させる
        // $this->userRepo->attachToStores($cast3User, [$store->id]);
        // // ユーザーとストアロールの紐付け
        // $this->roleRepo->attachStoreRolesToUser($cast3User, [$castStoreRole->id]);

        // // キャスト4
        // $cast4User = $this->userRepo->createGeneralUser(self::DUMMY_CAST4_USER, ['can_login' => false]);
        // // ユーザーをグループに所属させる
        // $this->userRepo->attachToGroup($cast4User, $group);
        // // ユーザーとグループロールの紐付け
        // $this->roleRepo->attachGroupRolesToUser($cast4User, [$generalGroupRole->id]);
        // // ユーザーを店舗に所属させる
        // $this->userRepo->attachToStores($cast4User, [$store->id]);
        // // ユーザーとストアロールの紐付け
        // $this->roleRepo->attachStoreRolesToUser($cast4User, [$castStoreRole->id]);

        // // キャスト5
        // $cast5User = $this->userRepo->createGeneralUser(self::DUMMY_CAST5_USER, ['can_login' => false]);
        // // ユーザーをグループに所属させる
        // $this->userRepo->attachToGroup($cast5User, $group);
        // // ユーザーとグループロールの紐付け
        // $this->roleRepo->attachGroupRolesToUser($cast5User, [$generalGroupRole->id]);
        // // ユーザーを店舗に所属させる
        // $this->userRepo->attachToStores($cast5User, [$store->id]);
        // // ユーザーとストアロールの紐付け
        // $this->roleRepo->attachStoreRolesToUser($cast5User, [$castStoreRole->id]);
    }

    /**
     * グループを作成する
     * @param string $groupName グループ名
     * @return Group
     */
    private function createGroup(string $groupName): Group
    {
        // グループを作成
        $group = $this->groupRepo->createGroup([
            'name' => $groupName,
        ]);

        return $group;
    }
}
